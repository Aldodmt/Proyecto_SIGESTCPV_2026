# CLAUDE.md — Sysweb

Sistema web de gestión de compras (ventas a futuro, hoy oculto) para la materia de la UTIC. PHP procedural + MySQL
sobre XAMPP, interfaz AdminLTE 4, con control de acceso por roles y permisos (RBAC) guardados en la base de datos.
Se desarrolla por etapas siguiendo las observaciones del docente; el README describe el uso funcional.

## Stack
- PHP 8.2.12 (XAMPP, `C:\xampp\php\php.exe`), sin framework, sin namespaces propios.
- MySQL/MariaDB de XAMPP, base `sysweb`, conexión `mysqli` en `config/database.php` (utf8mb4).
- AdminLTE 4 + Bootstrap 5 + Bootstrap Icons (locales en `assets/adminlte/`); jQuery 3.7.1, DataTables 1.13.6
  y Select2 4.0.13 desde **CDN** en `layout/head.php` (sin internet, los listados y selects no funcionan).
- PDF: Html2Pdf / TCPDF en `assets/plugins/vendor/` (autoload propio).
- Correo: PHPMailer desde la carpeta `PHPMailer/` (no desde `vendor/`; ver Gotchas).
- No hay Node, Python, Docker, CI, linter ni tests automatizados.

## Comandos esenciales
| Tarea | Comando |
|---|---|
| Ejecutar | Iniciar Apache + MySQL desde el panel de XAMPP y abrir `http://localhost/Proyecto2/` |
| Revisar sintaxis de un archivo | `C:\xampp\php\php.exe -l ruta/archivo.php` |
| Revisar sintaxis de lo modificado | `git diff --name-only -- '*.php' \| xargs -n1 /c/xampp/php/php.exe -l` (Bash) |
| Migrar v1 → v2 (conserva datos) | `mysql -u root sysweb < database/migraciones/v1_a_v2_personas_permisos.sql` |
| Base v2 desde cero (reemplaza tablas) | `mysql -u root < database/backups/v2_Backup_14_10_2026_personas_permisos.sql` |
| Versión del esquema | `SELECT * FROM esquema_version;` |

- Instalar dependencias: no hace falta, todo está versionado. `composer.json` solo declara `phpmailer/phpmailer`
  y `composer` no está instalado en el equipo.
- Build: no hay paso de compilación.
- Slash commands del proyecto: `/check`, `/review`, `/new-module`, `/new-migration` (en `.claude/commands/`).

## Estructura
| Ruta | Propósito |
|---|---|
| `index.php`, `login-check.php`, `logout.php` | Login (bloqueo a 3 intentos, `log_accesos`), cierre de sesión |
| `main.php` → `content.php` | Plantilla: valida sesión, revalida usuario/permisos en cada petición e incluye la pantalla |
| `config/routes.php` | Registro de pantallas `?module=clave` (páginas `$p` y formularios `$f`) |
| `config/menu.php` | Árbol del sidebar (visibilidad = permiso `leer`) |
| `config/permisos.php` | RBAC: `puede()`, `guard_endpoint()`, `permisos_recortar()` |
| `config/auth.php` | Contraseñas bcrypt, constantes `AUTH_*`, envío de correo, tokens de recuperación |
| `config/ui.php`, `config/catalogo.php` | Helpers de interfaz (`h()`, `ui_alertas()`...) y motor genérico de catálogos |
| `modules/<nombre>/` | Una pantalla: `view.php`, `form.php`, `proses.php`/`process.php`, `print.php` + `printview.php` |
| `ajax/`, `modules/*/ajax/` | Endpoints que devuelven fragmentos HTML o JSON para los formularios de movimientos |
| `layout/` | `head`, `header`, `sidebar`, `footer` y `auth_*` (pantallas de login) |
| `database/backups/`, `database/migraciones/` | Copia completa por versión (`vN_`) y migraciones idempotentes |

## Arquitectura y decisiones clave
- Front controller simple: todo pasa por `main.php?module=...`; `route_resolve()` (en `config/layout.php`) decide si
  la pantalla existe y si el rol puede leerla. Las vistas se incluyen y comparten `$mysqli` y `$_SESSION`.
- Los endpoints (`proses.php`, `ajax/*.php`, `print.php`) se llaman directo por URL, así que **cada uno** se protege
  solo con `guard_endpoint('<pagina>', <accion|null>, 'pagina'|'json'|'fragmento')` al principio.
- La acción se deduce de `?act=` (`accion_desde_act()`): insert→insertar, update/confirm→editar, delete/cancel/off→borrar.
- Denegado por defecto. Super Admin es un rol protegido. `start`, `perfil` y `password` son páginas básicas de todo rol.
- Los permisos viven solo en la base (`rol`, `modulo`, `pagina`, `permiso`); `routes.php` no define roles.
- `usuarios.permisos_acceso` (texto) se mantiene sincronizado con el rol por compatibilidad con código viejo.
- Más detalle: @docs/ARCHITECTURE.md

## Convenciones de código
- Nombres en español y `snake_case`: módulos, claves de ruta, funciones (`catalogo_view`, `permisos_cargar`).
- Columnas con prefijo de tabla (`gen_descripcion`, `rol_estado`); PK `id_<tabla>`; estado `'activo'`/`'inactivo'`.
- Formularios: ruta `form_<pagina>` con `&form=add|edit|detalle` (esto decide la acción insertar/editar/leer).
- Código nuevo (catálogos, persona, funcionario, rol, permiso, pagina): **sentencias preparadas**, `h()` para
  escapar la salida, `proses_volver($modulo, $alert)` para volver a `main.php?module=X&alert=N` (códigos en
  `ui_alertas()`), `ui_auditar()` para registrar cambios, `require_once __DIR__ . '/...'`.
- Código heredado (compras): SQL interpolado con `mysqli_query` y `alert()` en JS. Al tocarlo, migrar a lo nuevo
  esa parte; no copiar ese estilo en archivos nuevos.
- Vistas: `<section class="app-content-header">` + `<section class="app-content">`, Bootstrap 5, iconos `bi bi-*`.
- Comentarios en español explicando el "por qué" (el docente pide la teoría detrás de cada decisión).
- Un catálogo nuevo (descripción + estado) se agrega en `catalogo_cfg()`; su módulo son 3 archivos de una línea.

## Testing
- No hay tests automatizados ni PHPUnit configurado (el `phpunit` que trae XAMPP no está conectado al proyecto).
- Mínimo antes de dar algo por terminado: `php -l` sobre cada archivo tocado y probar en el navegador con un usuario
  Super Admin **y** con un rol sin permisos (debe ver "Acceso denegado", no un error de PHP).
- Para probar contra la base usar una **instancia aislada** de MySQL (ver @docs/DEVELOPMENT.md), nunca la del usuario.

## Flujo de git
- `main` es la rama principal (`origin/main`). Una rama por etapa: `adminlte`, `personas-permisos`.
- Mensajes de commit en español, frase libre que describe la versión/etapa (sin prefijos tipo `feat:`).
- Commit o push solo cuando el usuario lo pida. `core.autocrlf=true` (los avisos LF→CRLF son normales).

## Gotchas / qué NO hacer
- **No importar ni crear bases en el MySQL en marcha del usuario** ni matar `mysqld` con taskkill: ya corrompió
  `ibdata1` una vez. Probar en instancia aislada y apagarla con `mysqladmin -uroot shutdown`.
- No sobrescribir backups existentes: cada cambio de esquema = backup `vN_` nuevo + migración idempotente que
  registre su versión en `esquema_version`.
- No crear un endpoint sin `guard_endpoint()`; ocultar el botón (`permisos_recortar`) no es seguridad.
- Pantalla nueva = `routes.php` + `menu.php` + *Seguridad > Páginas y módulos > Sincronizar* + asignar permisos.
  Hasta entonces solo el Super Admin la ve (no es un bug).
- No volver a incluir jQuery/DataTables/Select2 en las vistas (ya están en `layout/head.php`).
- No leer, mostrar ni commitear `config/mail.php` (credenciales, ignorado por Git). `config/database.php` está
  versionado con credenciales locales: no poner ahí contraseñas reales.
- PHPMailer se carga desde `PHPMailer/src/`; `vendor/` (Composer) no lo usa nadie (por confirmar si se puede borrar).
- Las contraseñas nunca se guardan en claro, tampoco en `log_accesos` (van enmascaradas). Mínimo 12 caracteres
  (`AUTH_MIN_PASSWORD`), token de recuperación de 15 min (`AUTH_TOKEN_MINUTOS`).
- Fuera de alcance salvo pedido explícito: 2FA, login externo, idioma, "recordar usuario", módulos clínicos del DER.
- Las acciones de borrar/dar de baja van por GET sin token CSRF (pendiente conocido; no agregar más).
- Los `.docx` de informes se generan fuera del repo (en Descargas del usuario), nunca dentro del proyecto.

## Más documentación
- @docs/ARCHITECTURE.md — módulos, flujo de una petición, modelo RBAC, dependencias
- @docs/DEVELOPMENT.md — instalación desde cero, base de datos aislada, debugging, problemas comunes
- @docs/ROADMAP.md — deuda técnica y próximos pasos
- `modules/CLAUDE.md` — patrones de los módulos (nuevo vs. heredado)
- `README.md` y `database/backups/README.md` — uso funcional y versiones de la base
