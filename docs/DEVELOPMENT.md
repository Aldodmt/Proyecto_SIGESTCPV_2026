# Desarrollo local

## Requisitos
- Windows con **XAMPP** (Apache + PHP 8.2 + MariaDB/MySQL). La versión probada es PHP 8.2.12 (`C:\xampp\php\php.exe`).
- Git. Internet para jQuery, DataTables y Select2, que se cargan desde CDN.
- No hacen falta Composer, Node ni Python: las dependencias están versionadas.

## Instalación desde cero
1. Clonar o copiar el repositorio en `C:\xampp\htdocs\Proyecto2` (la carpeta define la URL).
2. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
3. Crear la base en versión 2:
   - Base nueva: `mysql -u root < database/backups/v2_Backup_14_10_2026_personas_permisos.sql`
     (crea `sysweb` si no existe y **reemplaza** sus tablas).
   - Base existente en v1: `mysql -u root sysweb < database/migraciones/v1_a_v2_personas_permisos.sql`
     (idempotente, conserva los datos).
   - También se puede hacer desde phpMyAdmin > Importar.
4. Conexión: revisar `config/database.php` (servidor, usuario, contraseña y base). Viene preparado para el `root`
   local de XAMPP. **No** subas ahí credenciales reales.
5. Correo (solo para recuperar contraseña y alertas de bloqueo): copiar `config/mail.example.php` como
   `config/mail.php` y completar los datos. Ese archivo lo ignora Git.
6. Abrir `http://localhost/Proyecto2/` e iniciar sesión con un usuario Super Admin de la base importada
   (las credenciales no están documentadas en el repo: **por confirmar** con el autor).

## Configuración
No hay `.env`. La configuración está en archivos PHP:

| Archivo | Claves | Versionado |
|---|---|---|
| `config/database.php` | `$server`, `$username`, `$password`, `$database` | Sí (solo valores locales) |
| `config/mail.php` | `host`, `port`, `username`, `password`, `from_name`, `base_url` | **No** (`.gitignore`) |
| `config/auth.php` | `AUTH_MAX_INTENTOS`, `AUTH_TOKEN_MINUTOS`, `AUTH_MIN_PASSWORD` | Sí |

`base_url` se usa para armar el enlace del correo de recuperación (si falta, ver `auth_base_url()` en `config/auth.php`).

## Verificación ("tests")
No hay tests automatizados, linter ni CI. Lo que se puede hacer hoy:

```bash
# Sintaxis de un archivo
/c/xampp/php/php.exe -l modules/persona/proses.php

# Sintaxis de todo lo modificado o nuevo (Bash)
{ git diff --name-only --diff-filter=d; git ls-files --others --exclude-standard; } | grep '\.php$' | xargs -n1 /c/xampp/php/php.exe -l

# Endpoints sin guard_endpoint() (debería salir solo modules/recuperar/proses.php, que es público,
# y los proses.php de catálogo, que lo llaman dentro de catalogo_proses())
for f in $(find modules ajax -name 'proses.php' -o -name 'process.php' -o -name 'print*.php' -o -path '*ajax/*.php'); do grep -q guard_endpoint "$f" || echo "$f"; done
```

Prueba manual mínima de un cambio:
1. Con un Super Admin: la pantalla abre, guarda, edita y borra.
2. Con un rol **sin** permiso sobre esa página: no aparece en el menú, `?module=...` muestra "No tienes permiso" y
   llamar al `proses.php` a mano devuelve 403.
3. Revisar que las tildes y la ñ se vean bien (utf8mb4).

## Probar contra una base sin tocar la del usuario
Importar o migrar dentro del MySQL que está corriendo puede romper datos (ya pasó: `ibdata1` corrupto y un
`mysqld.dmp`). Para pruebas de migraciones usar una instancia aislada:

```powershell
# 1. Copiar la plantilla de datos limpia de XAMPP a una carpeta temporal
Copy-Item C:\xampp\mysql\backup <carpeta_temporal> -Recurse
# 2. Comprobar que no haya otro mysqld en el mismo puerto
tasklist | findstr mysqld
# 3. Arrancar SIN my.ini (--no-defaults: el my.ini de XAMPP apunta a la carpeta de datos real)
C:\xampp\mysql\bin\mysqld.exe --no-defaults --datadir=<carpeta_temporal> --port=3306 `
  --character-set-server=utf8mb4 --collation-server=utf8mb4_general_ci
# 4. Al terminar, apagar SIEMPRE así (nunca con taskkill)
C:\xampp\mysql\bin\mysqladmin.exe -uroot shutdown
```

Si el MySQL del usuario ya está en el puerto 3306, usar otro puerto (ej. `--port=3307`) y conectar a ese.
Sin `--character-set-server=utf8mb4` las tildes salen mal.

## Debugging
- Errores de PHP: `C:\xampp\apache\logs\error.log` y `C:\xampp\php\logs\` (según `php.ini`). El proyecto no
  configura `display_errors`; depende de XAMPP.
- Errores de SQL en código heredado: suelen salir por `die(mysqli_error(...))` en pantalla o en la respuesta AJAX
  (pestaña Red del navegador).
- Endpoints AJAX/JSON (`pedido/proses.php`, `ajax/*.php`): revisar la respuesta en las herramientas del navegador.
  Un 401/403 con "Acceso denegado" viene de `guard_endpoint()`.
- Registro de accesos y auditoría: tabla `log_accesos` o pantalla *Seguridad > Accesos* (`?module=accesos`).
- Versión del esquema: `SELECT * FROM esquema_version;`

## Problemas comunes
| Síntoma | Causa probable | Solución |
|---|---|---|
| "Falta actualizar la base de datos" (503) al entrar | La base está en v1 (no existen `rol`, `modulo`, `pagina`, `permiso`) | Aplicar `database/migraciones/v1_a_v2_personas_permisos.sql` |
| Pantalla nueva no aparece ni para mí | No está registrada en `pagina` o el rol no tiene permiso | *Seguridad > Páginas y módulos > Sincronizar* y asignar en *Permisos por rol* |
| Pantalla nueva da "La página solicitada no existe" | Falta en `config/routes.php` o la ruta del archivo está mal | Revisar la entrada `$p`/`$f` |
| Tablas sin buscador ni paginación | Sin internet (DataTables/jQuery por CDN) o se volvió a incluir jQuery en la vista | Conectarse o quitar el `<script>` duplicado |
| Tildes rotas (`Ã©`) | Conexión o servidor sin utf8mb4 | `set_charset('utf8mb4')` ya está; revisar el charset del servidor/importación |
| El correo de recuperación no llega | Falta `config/mail.php` o credenciales SMTP inválidas | Copiar `mail.example.php` y revisar los datos |
| Usuario queda afuera tras 3 intentos | Bloqueo por `AUTH_MAX_INTENTOS` | Desbloquearlo desde *Usuarios* con un Super Admin |
| Avisos "LF will be replaced by CRLF" | `core.autocrlf=true` | Normal; no afecta |
