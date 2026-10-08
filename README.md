# Sysweb

Sistema web de gestión de compras (y, a futuro, ventas) hecho en **PHP + MySQL** sobre **AdminLTE 4** (Bootstrap 5).

## Requisitos
- XAMPP (Apache + PHP 8.x + MariaDB/MySQL)
- Base de datos `sysweb` (importar el último respaldo `Backup_*.sql`)
- Copiar `config/mail.example.php` como `config/mail.php` y completar los datos de correo

## Estructura
| Ruta | Contenido |
|---|---|
| `index.php`, `login-check.php`, `logout.php` | Acceso al sistema |
| `main.php` | Plantilla principal (valida sesión y rol, arma la página) |
| `content.php` | Incluye la pantalla pedida |
| `layout/` | Partes de la plantilla: `head`, `header` (barra superior), `sidebar`, `footer`, `auth_*` (login) |
| `config/routes.php` | **Registro de pantallas** (`?module=...`) con su archivo y los roles permitidos |
| `config/menu.php` | **Estructura del sidebar** (grupos desplegables anidables) |
| `config/layout.php` | Funciones del menú y del control de acceso |
| `config/auth.php` | Contraseñas, registro de accesos, correo, recuperación |
| `modules/<nombre>/` | Cada pantalla: `view.php` (listado), `form.php`, `proses.php`/`process.php`, `print*.php` |
| `ajax/` | Endpoints que devuelven fragmentos HTML para los formularios |
| `assets/adminlte/` | AdminLTE 4, Bootstrap 5 JS, Bootstrap Icons y OverlayScrollbars (locales) |
| `assets/css/sysweb.css` | Ajustes propios sobre la plantilla |
| `assets/plugins/vendor/` | Html2Pdf y TCPDF (reportes PDF) |
| `database/` | Migraciones SQL |

## Cómo agregar un módulo nuevo
1. Crear `modules/<modulo>/view.php` (y `form.php`, `proses.php` si hace falta).
2. Registrar cada pantalla en `config/routes.php` (archivo, título y roles).
3. Agregar un grupo desplegable en `config/menu.php`.

No hace falta tocar `main.php`, `content.php` ni el layout.

## Convenciones de las vistas
- Cada vista abre con `<section class="app-content-header">` (breadcrumb + título) y `<section class="app-content">` (contenido).
- Formularios con Bootstrap 5 (`row mb-3`, `col-sm-*`, `col-form-label`); iconos con Bootstrap Icons (`bi bi-*`).
- **No** volver a incluir jQuery, DataTables ni Select2 en las vistas: ya los carga `layout/head.php`.
