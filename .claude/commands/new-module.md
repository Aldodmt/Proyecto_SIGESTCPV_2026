---
description: Crea una pantalla/módulo nuevo de Sysweb siguiendo el patrón v2 (rutas, menú, permisos, prepared statements)
argument-hint: "<clave_modulo> <descripción de qué gestiona>"
---

Crear el módulo: $ARGUMENTS

Antes de escribir código:
1. Lee `config/routes.php`, `config/menu.php`, `config/ui.php` y un módulo de referencia:
   - Catálogo simple (descripción + estado) → `config/catalogo.php` y `modules/genero/`.
   - Entidad con más campos → `modules/persona/` (`view.php`, `form.php`, `proses.php`).
2. Si la pantalla necesita tablas o columnas nuevas, **para** y propón primero la migración con `/new-migration`.
   No modifiques la base del usuario.
3. Confírmame: clave de la página, módulo del sidebar (`general`, `referenciales`, `personal`, `compras`,
   `seguridad`...), acciones que aplican (`leer,insertar,editar,borrar` o un subconjunto) y dónde va en el menú.

Al implementar:
- **Si es un catálogo**: agrega la entrada en `catalogo_cfg()` y crea `modules/<clave>/view.php`, `form.php` y
  `proses.php` de una línea (`catalogo_view/form/proses('<clave>')`).
- **Si no**: `proses.php` empieza con `require_once __DIR__ . '/../../config/ui.php'`, `.../permisos.php` y
  `guard_endpoint('<clave>');`. Usa `prepare()` + `bind_param`, valida en el servidor, vuelve con
  `proses_volver('<clave>', N)` (códigos de `ui_alertas()`), y registra cambios sensibles con `ui_auditar()`.
- Las vistas: `ui_cabecera(...)` o `<section class="app-content-header">` + `<section class="app-content">`,
  `ui_alertas()`, tabla `#dataTables1` con DataTables, salida con `h()`. Sin incluir jQuery/DataTables/Select2.
- Enlaces de acciones con el formato que entiende `permisos_recortar()`: `?module=form_<clave>&form=add|edit|detalle`
  y `modules/<clave>/proses.php?act=...`.
- Registra en `config/routes.php` la página (`$p(...)`) y su formulario (`$f(..., '<clave>')`), y el ítem en
  `config/menu.php`.
- Comentarios en español explicando el porqué de las decisiones (lo pide el docente).

Al terminar:
- Corre `/check` sobre `modules/<clave>/` y los `config/` tocados.
- Recuérdame los pasos manuales: *Seguridad > Páginas y módulos > Sincronizar con el sistema* y luego asignar
  permisos en *Seguridad > Permisos por rol*. Hasta entonces solo el Super Admin la ve.
