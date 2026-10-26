---
description: Verifica sintaxis PHP y control de acceso de los archivos modificados (lo más cercano a "tests" que tiene Sysweb)
argument-hint: "[ruta opcional: archivo o carpeta; por defecto, lo modificado y lo nuevo]"
---

Revisa los cambios de Sysweb sin tocar la base de datos. Alcance: $ARGUMENTS (si está vacío, archivos `.php`
modificados o nuevos según git).

1. Lista los archivos a revisar:
   `{ git diff --name-only --diff-filter=d; git ls-files --others --exclude-standard; } | grep '\.php$'`
   (o los de la ruta indicada).
2. Corre `/c/xampp/php/php.exe -l` sobre cada uno. Muestra solo los que fallen, con el error.
3. Para cada endpoint del alcance (`proses.php`, `process.php`, `print*.php`, `ajax/*.php`, `modules/*/ajax/*.php`):
   - Debe llamar a `guard_endpoint(...)` **antes** de leer `$_POST`/`$_GET` o consultar la base. Excepciones
     válidas: `modules/recuperar/*` (público) y los `proses.php` de catálogo (lo hace `catalogo_proses()`).
   - El formato de denegación debe coincidir con la respuesta: `'json'` si devuelve JSON, `'fragmento'` si
     devuelve HTML que se inyecta, `'pagina'` en los demás casos.
   - Si el endpoint usa valores de `act` nuevos, comprueba que `accion_desde_act()` en `config/permisos.php` los
     clasifique bien (si no, caen en `'cualquiera'`).
4. Para cada pantalla nueva en `modules/`: que esté en `config/routes.php` (página `$p` o formulario `$f` con
   `menu` correcto) y, si es una página, en `config/menu.php`.
5. Busca en el alcance: SQL con variables interpoladas (`mysqli_query(... "$...`), `echo` de datos sin `h()`,
   `console.log` / `error_log` / `var_dump` de depuración, y `<script src=` de jQuery/DataTables/Select2
   (ya los carga `layout/head.php`).
6. Resume en una tabla: archivo, problema, gravedad (bloqueante / mejorar). No modifiques nada salvo que te lo pida.
7. Recuérdame la prueba manual: Super Admin y un rol sin permisos sobre la página.
