# Roadmap y deuda técnica

Estado al 15/10/2026, rama `personas-permisos` (cambios de v2 todavía **sin commitear**).

Leyenda:
- **[código]**: se ve directamente en el repo (con archivo de referencia).
- **[acordado]**: pendiente que ya se habló con el autor/docente.
- **[inferencia]**: sugerencia mía a partir de la lectura del código; confirmar antes de hacerla.

## Prioridad alta (seguridad / integridad)

1. **[acordado] Protección CSRF.** Las acciones de borrar, dar de baja, reactivar y anular van por GET con un
   enlace (`proses.php?act=delete&id=...`) y ningún formulario lleva token. Ejemplos: `config/catalogo.php`
   (`catalogo_view`), `modules/user/process.php` (`act=on|off`). Propuesta [inferencia]: token por sesión en
   `config/ui.php` + comprobarlo en `guard_endpoint()` para todo lo que no sea `leer`, y pasar esas acciones a POST.
2. **[código] Inyección SQL en módulos heredados.** Valores de `$_POST`/`$_GET` interpolados sin escapar ni
   convertir, por ejemplo `$codigo = $_POST['codigo']` dentro del `INSERT` en `modules/departamento/proses.php`,
   `modules/ciudad/proses.php`, `modules/clientes/proses.php`, `modules/deposito/process.php`. El resto del código
   heredado usa `intval()` o `mysqli_real_escape_string` de forma irregular. Pasar a `prepare()` módulo por módulo.
3. **[código] Restos de depuración que exponen la sesión.** `modules/pedido/form.php` imprime el `session_id()` en
   un `console.log` del navegador; `ajax/limpiar_tmp.php` lo escribe con `error_log` (mensaje "LIMPIAR tmp
   FEIAIIIIIIIIII"). Además `limpiar_tmp.php` interpola el `session_id` en el SQL. Quitarlos.
4. **[código] Credenciales en `config/database.php` versionadas.** Hoy son las locales de XAMPP; si algún día se
   despliega, mover a un `config/database.local.php` ignorado (mismo patrón que `mail.php` / `mail.example.php`).
5. **[inferencia] Acceso directo a vistas.** `modules/*/view.php` y `form.php` no se protegen solos (dependen de
   `main.php`). Abrirlos por URL no debería mostrar datos porque `$mysqli` no existe, pero puede mostrar avisos de
   PHP con rutas internas. Por confirmar en el navegador; una salida simple es un `.htaccess` que deniegue
   `modules/*/view.php` y `form.php`, o un `defined('SYSWEB') or exit;` al inicio.

## Prioridad media (mantenibilidad)

6. **[código] Dos estilos de código conviviendo** (ver `docs/ARCHITECTURE.md`, "Dos generaciones de código").
   Migrar los catálogos simples de compras (`departamento`, `ciudad`, `tipo_producto`, `u_medida`, `deposito`) al
   motor `config/catalogo.php` [inferencia: comprobar que tengan solo descripción + estado; `ciudad` tiene FK a
   departamento y quizás no encaje].
7. **[código] Nombres inconsistentes de archivos de proceso.** `proses.php` (mayoría) frente a `process.php`
   (`deposito`, `producto`, `proveedor`, `tipo_producto`, `u_medida`, `user`, `perfil`) y `printview.php` frente a
   `print_view.php`. `permisos_recortar()` ya acepta ambos; elegir uno para lo nuevo (sugerido: `proses.php`).
8. **[código] Comentario desactualizado.** `modules/recuperar/proses.php` dice "token de un solo uso (30 min)",
   pero `AUTH_TOKEN_MINUTOS = 15` en `config/auth.php`.
9. **[código] Dependencias duplicadas.** PHPMailer existe en `PHPMailer/` (el que se usa) y en `vendor/phpmailer`
   (Composer, no se usa). Y hay dos copias del backup v1: `Backup_06_1_2026.sql` en la raíz y
   `database/backups/v1_Backup_06_1_2026.sql`; además `database/login_seguridad.sql`. Decidir cuál queda.
10. **[código] Depósito sin usar.** Comentado en `config/menu.php` ("NO UTILIZO DEPOSITO EN MI SISTEMA") pero sigue
    en `config/routes.php` y en `modules/deposito/`. Si no se usa, dar de baja la página en *Páginas y módulos*.
11. **[inferencia] Dependencias por CDN.** jQuery, DataTables y Select2 se cargan de internet en `layout/head.php`;
    copiarlas a `assets/` permitiría usar el sistema sin conexión (útil para una defensa en clase).

## Prioridad baja / próximos pasos

12. **[acordado] Vincular los usuarios existentes con personas/funcionarios reales** (`usuarios.id_funcionario`).
13. **[acordado] Módulo de Ventas.** `clientes`, `pedido_v`, `ventas` e `info_pedido_v` están en rutas, pero el
    grupo del menú tiene `'hidden' => true`.
14. **[código] Compatibilidad `usuarios.permisos_acceso`.** Se mantiene sincronizado con el rol; cuando ningún
    archivo lo lea (`grep -rn permisos_acceso`), se puede retirar en una migración v3.
15. **[inferencia] Verificación automática mínima.** No hay tests ni CI. Un primer paso barato: un script que corra
    `php -l` en todo el repo y la auditoría de `guard_endpoint()` (ya están en `/check`), y más adelante pruebas de
    `config/permisos.php` (funciones puras como `accion_desde_act()` y `route_accion()`).

## Fuera de alcance (decidido)
2FA, login con proveedores externos, selección de idioma, "recordar usuario" y los módulos clínicos del DER de
referencia. No implementarlos sin pedido explícito.
