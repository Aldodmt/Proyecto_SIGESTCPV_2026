---
description: Prepara una nueva versión del esquema (migración idempotente + backup vN) sin tocar el MySQL del usuario
argument-hint: "<descripción corta del cambio, ej. ventas_clientes>"
---

Cambio de esquema: $ARGUMENTS

1. Lee `database/backups/README.md` y la última migración en `database/migraciones/` para tomar la versión
   actual (N) y el estilo (comentarios con la teoría, `IF NOT EXISTS`, InnoDB, utf8mb4, FK con nombre `fk_<tabla>_<ref>`).
2. Escribe `database/migraciones/v<N>_a_v<N+1>_<descripcion>.sql`:
   - Idempotente: `CREATE TABLE IF NOT EXISTS`, `ADD COLUMN IF NOT EXISTS`, `ADD CONSTRAINT ... IF NOT EXISTS`,
     `INSERT IGNORE` para datos iniciales.
   - Columnas con prefijo de la tabla y estado `'activo'/'inactivo'` cuando corresponda.
   - Al final registra la versión en `esquema_version`.
   - Si agrega páginas nuevas, recuerda que también hay que registrarlas en `config/routes.php` (la tabla `pagina`
     se completa con "Sincronizar").
3. **No** la ejecutes en el MySQL del usuario. Si hay que probarla, pídeme permiso y usa una instancia aislada
   (ver "Probar contra una base sin tocar la del usuario" en `docs/DEVELOPMENT.md`): importar el backup vN,
   correr la migración **dos veces** (para probar que es idempotente) y comparar.
4. Solo después de probarla, y si te lo pido, genera `database/backups/v<N+1>_Backup_DD_MM_AAAA_<descripcion>.sql`
   desde la instancia aislada. Nunca sobrescribas un backup existente.
5. Actualiza las tablas de `database/backups/README.md` (versión, archivo, fecha, cantidad de tablas/vistas, qué
   incluye) y apaga la instancia con `mysqladmin -uroot shutdown`.
