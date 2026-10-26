# Versiones de la base de datos `sysweb`

Esta carpeta guarda **una copia completa de la base por cada versión del esquema**. Las versiones nunca se
sobrescriben: cada cambio grande crea una versión nueva y su migración.

| Versión | Archivo | Fecha | Tablas | Vistas | Qué incluye |
|---|---|---|---|---|---|
| **v1** | `v1_Backup_06_1_2026.sql` | 06/10/2026 | 37 | 13 | Sistema de compras + seguridad del login (`log_accesos`, `password_resets`, `intentos_fallidos`, `bloqueado_fecha`). Es copia idéntica de `Backup_06_1_2026.sql` (que sigue intacto en la raíz del proyecto). |
| **v2** | `v2_Backup_14_10_2026_personas_permisos.sql` | 14/10/2026 | 47 | 16 | Todo lo de v1 + **personas** (`persona`, `genero`, `nacionalidad`), **personal** (`funcionario`, `cargo`) y **control de acceso por roles** (`rol`, `modulo`, `pagina`, `permiso`), más `esquema_version` y las vistas `v_persona`, `v_funcionario`, `v_usuarios`. |

Las migraciones están en `../migraciones/`:

| Migración | Hace |
|---|---|
| `v1_a_v2_personas_permisos.sql` | Lleva una base en v1 a v2 **sin perder datos** (agrega tablas, columnas y relaciones y carga los datos iniciales). Se puede ejecutar varias veces. |

## Cómo usarlas

**Opción A: actualizar la base que ya tienes (recomendada, conserva tus datos)**

```bash
mysql -u root sysweb < database/migraciones/v1_a_v2_personas_permisos.sql
```

Desde phpMyAdmin: seleccionar la base `sysweb` > *Importar* > elegir `v1_a_v2_personas_permisos.sql`.

**Opción B: empezar de cero con la versión 2 completa**

```bash
mysql -u root < database/backups/v2_Backup_14_10_2026_personas_permisos.sql
```

> Cuidado: cada backup trae `DROP TABLE IF EXISTS`, así que importarlo **reemplaza** las tablas de `sysweb`
> y sus datos por los del backup. Si tu base tiene datos más nuevos que el backup, exporta una copia antes.

Para saber en qué versión está una base: `SELECT * FROM esquema_version;`

## Convención para las próximas versiones

- Backup completo: `vN_Backup_DD_MM_AAAA_descripcion.sql` (en esta carpeta).
- Migración desde la anterior: `v(N-1)_a_vN_descripcion.sql` (en `../migraciones/`), idempotente y registrando su versión en `esquema_version`.
- Se genera el backup **después** de comprobar que la migración sobre el backup anterior da el mismo resultado.
