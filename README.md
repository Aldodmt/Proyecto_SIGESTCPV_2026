# Sysweb

Sistema web de gestión de compras (y, a futuro, ventas) hecho en **PHP + MySQL** sobre **AdminLTE 4** (Bootstrap 5),
con **control de acceso por roles y permisos** (RBAC).

## Requisitos
- XAMPP (Apache + PHP 8.x + MariaDB/MySQL)
- Base de datos `sysweb`: importar `database/backups/v2_Backup_*.sql` (ver `database/backups/README.md`)
- Copiar `config/mail.example.php` como `config/mail.php` y completar los datos de correo

## Estructura
| Ruta | Contenido |
|---|---|
| `index.php`, `login-check.php`, `logout.php` | Acceso al sistema (bloqueo a 3 intentos, registro de accesos, recuperación con token de 15 min) |
| `main.php` | Plantilla principal: valida sesión y permisos, arma la página |
| `content.php` | Incluye la pantalla pedida |
| `layout/` | Partes de la plantilla: `head`, `header` (barra superior), `sidebar`, `footer`, `auth_*` (login) |
| `config/routes.php` | **Registro de pantallas** (`?module=...`): archivo, título, módulo y acciones que aplican |
| `config/menu.php` | **Estructura del sidebar** (grupos desplegables anidables) |
| `config/permisos.php` | **Control de acceso**: `puede()`, `guard_endpoint()`, recorte de botones |
| `config/layout.php` | Funciones del menú y del router |
| `config/auth.php` | Contraseñas (bcrypt, 12 caracteres), registro de accesos, correo, recuperación |
| `config/ui.php`, `config/catalogo.php` | Utilidades de interfaz y motor de catálogos (género, nacionalidad, cargo) |
| `modules/<nombre>/` | Cada pantalla: `view.php` (listado), `form.php`, `proses.php`/`process.php`, `print*.php` |
| `ajax/` | Endpoints que devuelven fragmentos HTML para los formularios |
| `assets/adminlte/` | AdminLTE 4, Bootstrap 5 JS, Bootstrap Icons y OverlayScrollbars (locales) |
| `assets/css/sysweb.css` | Ajustes propios sobre la plantilla |
| `assets/plugins/vendor/` | Html2Pdf y TCPDF (reportes PDF) |
| `database/backups/` | Una copia completa de la base por versión (`v1_`, `v2_`...) |
| `database/migraciones/` | Migraciones entre versiones (`v1_a_v2_...sql`) |

## Control de acceso (roles y permisos)
```
usuario ──> rol ──> permiso ──> página ──> módulo
                    (leer, insertar, editar, borrar)
```
- Un **usuario** tiene un **rol** (y, opcionalmente, un **funcionario**: la persona del personal a la que pertenece).
- Cada **página** es una pantalla del sistema; se agrupan en **módulos** (General, Compras, Personal, Seguridad...).
- El **permiso** dice, por rol y página, qué acciones se pueden hacer. Se administra en *Seguridad > Permisos por rol*.
- **Denegado por defecto**: lo que no está marcado no se puede hacer. El rol *Super Admin* es protegido (acceso total).
- Se comprueba **en el servidor** en cada petición (pantallas, procesos, AJAX y PDF), no solo en el menú.
- En el código: `puede('pedido', 'insertar')` pregunta por una acción; `guard_endpoint('pedido')` protege un endpoint.

## Cómo agregar un módulo nuevo
1. Crear `modules/<modulo>/view.php` (y `form.php`, `proses.php` si hace falta). En `proses.php` llamar a
   `guard_endpoint('<pagina>')` al principio.
2. Registrar cada pantalla en `config/routes.php`: las páginas con su módulo y acciones; los formularios con `menu` => su página.
3. Agregar un grupo desplegable en `config/menu.php`.
4. En el sistema: *Seguridad > Páginas y módulos > Sincronizar con el sistema* (registra las pantallas nuevas) y luego
   *Seguridad > Permisos por rol* para darle acceso a los roles que correspondan. Hasta entonces nadie, salvo el Super Admin, las ve.

No hace falta tocar `main.php`, `content.php` ni el layout.

## Convenciones de las vistas
- Cada vista abre con `<section class="app-content-header">` (breadcrumb + título) y `<section class="app-content">` (contenido).
- Formularios con Bootstrap 5 (`row mb-3`, `col-sm-*`, `col-form-label`); iconos con Bootstrap Icons (`bi bi-*`).
- **No** volver a incluir jQuery, DataTables ni Select2 en las vistas: ya los carga `layout/head.php`.
- Los botones de agregar/editar/eliminar se ocultan solos si el usuario no tiene el permiso (`permisos_recortar`).
  Aun así, el proceso debe estar protegido con `guard_endpoint()`.
