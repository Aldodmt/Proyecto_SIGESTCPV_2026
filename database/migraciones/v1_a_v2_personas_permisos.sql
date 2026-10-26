/*
  MIGRACIÓN  v1 -> v2   (14/10/2026)
  Personas, funcionarios, cargos, roles, módulos, páginas y permisos (RBAC).

  - Parte de la versión 1 del esquema (Backup_06_1_2026.sql, que ya incluye log_accesos y password_resets).
  - NO borra ni modifica datos existentes: solo agrega tablas/columnas/relaciones y carga datos iniciales.
  - Es IDEMPOTENTE: se puede ejecutar más de una vez sin duplicar nada.
  - Cómo aplicarla sobre la base actual (conserva todos los datos):
        mysql -u root sysweb < database/migraciones/v1_a_v2_personas_permisos.sql
    o, desde phpMyAdmin: seleccionar la base "sysweb" > Importar > este archivo.

  Convenciones (las del proyecto): clave primaria id_<entidad>, columnas con prefijo de la tabla
  (per_, fun_, car_, rol_, mod_, pag_, perm_), textos en español, charset utf8mb4.
*/

SET NAMES utf8mb4;
USE `sysweb`;

/* ------------------------------------------------------------------------------------------
   0. Control de versiones del esquema
   ------------------------------------------------------------------------------------------ */
CREATE TABLE IF NOT EXISTS `esquema_version` (
  `version` INT NOT NULL,
  `descripcion` VARCHAR(150) NOT NULL,
  `aplicado` DATETIME NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* ------------------------------------------------------------------------------------------
   1. Motor InnoDB en las tablas que van a participar de claves foráneas.
      MyISAM no soporta FOREIGN KEY ni transacciones; InnoDB sí (integridad referencial + ACID).
   ------------------------------------------------------------------------------------------ */
ALTER TABLE `departamento` ENGINE=InnoDB;
ALTER TABLE `ciudad` ENGINE=InnoDB;
ALTER TABLE `usuarios` ENGINE=InnoDB;

/* La tabla usuarios no tenía clave primaria (solo un índice): se la define (integridad de entidad). */
DROP PROCEDURE IF EXISTS `v2_pk_usuarios`;
DELIMITER $$
CREATE PROCEDURE `v2_pk_usuarios`()
BEGIN
  IF (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND CONSTRAINT_TYPE = 'PRIMARY KEY') = 0 THEN
    ALTER TABLE `usuarios` DROP INDEX `id_user`, ADD PRIMARY KEY (`id_user`);
  END IF;
END$$
DELIMITER ;
CALL `v2_pk_usuarios`();
DROP PROCEDURE `v2_pk_usuarios`;

/* El nombre de usuario es clave candidata: no puede repetirse. */
ALTER TABLE `usuarios` ADD UNIQUE KEY IF NOT EXISTS `uq_usuarios_username` (`username`);

/* Cada ciudad pertenece a un departamento (así lo marca el DER). */
ALTER TABLE `ciudad`
  ADD CONSTRAINT `fk_ciudad_departamento` FOREIGN KEY IF NOT EXISTS (`id_departamento`)
  REFERENCES `departamento` (`id_departamento`) ON UPDATE CASCADE ON DELETE RESTRICT;

/* ------------------------------------------------------------------------------------------
   2. Referenciales de personas
   ------------------------------------------------------------------------------------------ */
CREATE TABLE IF NOT EXISTS `genero` (
  `id_genero` INT NOT NULL AUTO_INCREMENT,
  `gen_descripcion` VARCHAR(30) NOT NULL,
  `gen_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_genero`),
  UNIQUE KEY `uq_genero_descripcion` (`gen_descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `nacionalidad` (
  `id_nacionalidad` INT NOT NULL AUTO_INCREMENT,
  `nac_descripcion` VARCHAR(60) NOT NULL,
  `nac_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_nacionalidad`),
  UNIQUE KEY `uq_nacionalidad_descripcion` (`nac_descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cargo` (
  `id_cargo` INT NOT NULL AUTO_INCREMENT,
  `car_descripcion` VARCHAR(60) NOT NULL,
  `car_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_cargo`),
  UNIQUE KEY `uq_cargo_descripcion` (`car_descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* ------------------------------------------------------------------------------------------
   3. Persona y funcionario
      persona      : datos personales de cualquier individuo (el departamento se obtiene a través de la ciudad).
      funcionario  : especialización de persona (relación 1 a 1 opcional) con cargo y fecha de ingreso.
   ------------------------------------------------------------------------------------------ */
CREATE TABLE IF NOT EXISTS `persona` (
  `id_persona` INT NOT NULL AUTO_INCREMENT,
  `per_nombre` VARCHAR(80) NOT NULL,
  `per_apellido` VARCHAR(80) NOT NULL,
  `per_ci` VARCHAR(15) NOT NULL,
  `per_fecha_nac` DATE NOT NULL,
  `per_telefono` VARCHAR(20) DEFAULT NULL,
  `per_correo` VARCHAR(150) DEFAULT NULL,
  `per_direccion` VARCHAR(150) DEFAULT NULL,
  `id_genero` INT NOT NULL,
  `id_nacionalidad` INT NOT NULL,
  `cod_ciudad` INT NOT NULL,
  `per_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `per_creado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_persona`),
  UNIQUE KEY `uq_persona_ci` (`per_ci`),
  KEY `idx_persona_apellido` (`per_apellido`, `per_nombre`),
  CONSTRAINT `fk_persona_genero` FOREIGN KEY (`id_genero`) REFERENCES `genero` (`id_genero`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_persona_nacionalidad` FOREIGN KEY (`id_nacionalidad`) REFERENCES `nacionalidad` (`id_nacionalidad`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_persona_ciudad` FOREIGN KEY (`cod_ciudad`) REFERENCES `ciudad` (`cod_ciudad`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `ck_persona_ci` CHECK (`per_ci` <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `funcionario` (
  `id_funcionario` INT NOT NULL AUTO_INCREMENT,
  `id_persona` INT NOT NULL,
  `id_cargo` INT NOT NULL,
  `fun_fecha_ingreso` DATE NOT NULL,
  `fun_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `uq_funcionario_persona` (`id_persona`),
  KEY `idx_funcionario_cargo` (`id_cargo`),
  CONSTRAINT `fk_funcionario_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_funcionario_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* ------------------------------------------------------------------------------------------
   4. Seguridad: roles, módulos, páginas y permisos (RBAC)
   ------------------------------------------------------------------------------------------ */
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `rol_descripcion` VARCHAR(50) NOT NULL,
  `rol_observacion` VARCHAR(150) DEFAULT NULL,
  `rol_protegido` TINYINT(1) NOT NULL DEFAULT 0,          -- 1 = rol del sistema: acceso total y no editable (evita quedarse sin administrador)
  `rol_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol_descripcion` (`rol_descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo` (
  `id_modulo` INT NOT NULL AUTO_INCREMENT,
  `mod_clave` VARCHAR(30) NOT NULL,
  `mod_nombre` VARCHAR(60) NOT NULL,
  `mod_orden` INT NOT NULL DEFAULT 0,
  `mod_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `uq_modulo_clave` (`mod_clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `pagina` (
  `id_pagina` INT NOT NULL AUTO_INCREMENT,
  `pag_clave` VARCHAR(40) NOT NULL,                       -- clave de ruta (?module=<clave>) definida en config/routes.php
  `pag_nombre` VARCHAR(80) NOT NULL,
  `pag_direccion` VARCHAR(128) NOT NULL,
  `id_modulo` INT NOT NULL,
  `pag_acciones` VARCHAR(40) NOT NULL DEFAULT 'leer,insertar,editar,borrar',   -- acciones que tienen sentido en esa pantalla
  `pag_estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_pagina`),
  UNIQUE KEY `uq_pagina_clave` (`pag_clave`),
  KEY `idx_pagina_modulo` (`id_modulo`),
  CONSTRAINT `fk_pagina_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `permiso` (
  `id_rol` INT NOT NULL,
  `id_pagina` INT NOT NULL,
  `perm_leer` TINYINT(1) NOT NULL DEFAULT 0,
  `perm_insertar` TINYINT(1) NOT NULL DEFAULT 0,
  `perm_editar` TINYINT(1) NOT NULL DEFAULT 0,
  `perm_borrar` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_rol`, `id_pagina`),
  KEY `idx_permiso_pagina` (`id_pagina`),
  CONSTRAINT `fk_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_permiso_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `pagina` (`id_pagina`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* ------------------------------------------------------------------------------------------
   5. Usuarios: ahora tienen rol (tabla) y, opcionalmente, un funcionario (persona) asociado.
      La columna permisos_acceso (texto) se CONSERVA mientras el resto del sistema la siga leyendo;
      el sistema la mantiene sincronizada con el nombre del rol.
   ------------------------------------------------------------------------------------------ */
ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `id_rol` INT NULL AFTER `permisos_acceso`,
  ADD COLUMN IF NOT EXISTS `id_funcionario` INT NULL AFTER `id_rol`;

ALTER TABLE `usuarios` ADD UNIQUE KEY IF NOT EXISTS `uq_usuarios_funcionario` (`id_funcionario`);
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY IF NOT EXISTS (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_funcionario` FOREIGN KEY IF NOT EXISTS (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON UPDATE CASCADE ON DELETE SET NULL;

/* ------------------------------------------------------------------------------------------
   6. Datos iniciales
   ------------------------------------------------------------------------------------------ */
INSERT IGNORE INTO `genero` (`gen_descripcion`) VALUES ('Masculino'), ('Femenino');

INSERT IGNORE INTO `nacionalidad` (`nac_descripcion`) VALUES
  ('Paraguaya'), ('Argentina'), ('Brasileña'), ('Uruguaya'), ('Boliviana'),
  ('Chilena'), ('Peruana'), ('Colombiana'), ('Venezolana'), ('Otra');

INSERT IGNORE INTO `cargo` (`car_descripcion`) VALUES
  ('Administrador de sistemas'), ('Gerente general'), ('Jefe de compras'), ('Auxiliar de compras'),
  ('Jefe de ventas'), ('Vendedor'), ('Jefe de local'), ('Encargado de producción');

/* Roles: mismos nombres que ya usaba usuarios.permisos_acceso */
INSERT IGNORE INTO `rol` (`rol_descripcion`, `rol_observacion`, `rol_protegido`) VALUES
  ('Super Admin', 'Acceso total al sistema. Rol protegido: no se puede editar ni dar de baja.', 1),
  ('Compras',     'Gestión del módulo de compras.', 0),
  ('Ventas',      'Gestión del módulo de ventas.', 0),
  ('Produccion',  'Personal de producción: acceso básico.', 0);

INSERT IGNORE INTO `modulo` (`mod_clave`, `mod_nombre`, `mod_orden`) VALUES
  ('general',       'General',                 1),
  ('referenciales', 'Referenciales generales', 2),
  ('personal',      'Personal',                3),
  ('compras',       'Compras',                 4),
  ('ventas',        'Ventas',                  5),
  ('seguridad',     'Seguridad',               6);

/* Catálogo de páginas = pantallas principales de config/routes.php (los formularios cuelgan de su página) */
INSERT IGNORE INTO `pagina` (`pag_clave`, `pag_nombre`, `pag_direccion`, `id_modulo`, `pag_acciones`)
SELECT x.clave, x.nombre, CONCAT('?module=', x.clave), m.id_modulo, x.acciones
FROM (
  SELECT 'start' clave, 'Inicio' nombre, 'general' modulo, 'leer' acciones
  UNION ALL SELECT 'perfil', 'Mi perfil', 'general', 'leer,editar'
  UNION ALL SELECT 'password', 'Cambiar contraseña', 'general', 'leer,editar'

  UNION ALL SELECT 'departamento', 'Departamentos', 'referenciales', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'ciudad', 'Ciudades', 'referenciales', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'nacionalidad', 'Nacionalidades', 'referenciales', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'genero', 'Géneros', 'referenciales', 'leer,insertar,editar,borrar'

  UNION ALL SELECT 'persona', 'Personas', 'personal', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'funcionario', 'Funcionarios', 'personal', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'cargo', 'Cargos', 'personal', 'leer,insertar,editar,borrar'

  UNION ALL SELECT 'deposito', 'Depósitos', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'proveedor', 'Proveedores', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'producto', 'Productos', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'tipo_producto', 'Tipos de producto', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'u_medida', 'Unidades de medida', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'pedido', 'Pedidos', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'presupuesto', 'Presupuestos', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'orden_c', 'Órdenes de compra', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'compra', 'Compras', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'cuenta', 'Cuentas a pagar', 'compras', 'leer,editar,borrar'
  UNION ALL SELECT 'nota_c_d', 'Notas de crédito/débito', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'ajuste', 'Ajustes de stock', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'nota_remision', 'Notas de remisión', 'compras', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'stock', 'Stock', 'compras', 'leer'
  UNION ALL SELECT 'info_producto', 'Informe de productos', 'compras', 'leer'
  UNION ALL SELECT 'info_proveedores', 'Informe de proveedores', 'compras', 'leer'
  UNION ALL SELECT 'info_u_medida', 'Informe de unidades de medida', 'compras', 'leer'
  UNION ALL SELECT 'info_pedido', 'Informe de pedidos', 'compras', 'leer'
  UNION ALL SELECT 'info_presu', 'Informe de presupuestos', 'compras', 'leer'
  UNION ALL SELECT 'info_orden', 'Informe de órdenes de compra', 'compras', 'leer'
  UNION ALL SELECT 'info_facturacion_compra', 'Informe de compras', 'compras', 'leer'
  UNION ALL SELECT 'info_nota_cd', 'Informe de notas de crédito/débito', 'compras', 'leer'
  UNION ALL SELECT 'info_ajuste', 'Informe de ajustes', 'compras', 'leer'
  UNION ALL SELECT 'info_notaR', 'Informe de notas de remisión', 'compras', 'leer'

  UNION ALL SELECT 'clientes', 'Clientes', 'ventas', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'pedido_v', 'Pedidos de venta', 'ventas', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'ventas', 'Ventas', 'ventas', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'info_pedido_v', 'Informe de pedidos de venta', 'ventas', 'leer'

  UNION ALL SELECT 'user', 'Usuarios', 'seguridad', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'rol', 'Roles', 'seguridad', 'leer,insertar,editar,borrar'
  UNION ALL SELECT 'permiso', 'Permisos', 'seguridad', 'leer,editar'
  UNION ALL SELECT 'pagina', 'Páginas y módulos', 'seguridad', 'leer,editar'
  UNION ALL SELECT 'accesos', 'Registro de accesos', 'seguridad', 'leer'
) x
JOIN `modulo` m ON m.mod_clave = x.modulo;

/* ------------------------------------------------------------------------------------------
   7. Permisos iniciales (reproducen lo que cada rol podía hacer hasta ahora; ajustables desde el sistema)
      Regla general: las columnas de acción se activan solo si la acción aplica a la página (pag_acciones).
   ------------------------------------------------------------------------------------------ */
/* 7.1 Super Admin: todo */
INSERT IGNORE INTO `permiso` (`id_rol`, `id_pagina`, `perm_leer`, `perm_insertar`, `perm_editar`, `perm_borrar`)
SELECT r.id_rol, p.id_pagina,
       FIND_IN_SET('leer', p.pag_acciones) > 0, FIND_IN_SET('insertar', p.pag_acciones) > 0,
       FIND_IN_SET('editar', p.pag_acciones) > 0, FIND_IN_SET('borrar', p.pag_acciones) > 0
FROM `rol` r JOIN `pagina` p
WHERE r.rol_descripcion = 'Super Admin';

/* 7.2 Todos los demás roles: módulo General (inicio, perfil, cambiar contraseña) */
INSERT IGNORE INTO `permiso` (`id_rol`, `id_pagina`, `perm_leer`, `perm_insertar`, `perm_editar`, `perm_borrar`)
SELECT r.id_rol, p.id_pagina,
       FIND_IN_SET('leer', p.pag_acciones) > 0, FIND_IN_SET('insertar', p.pag_acciones) > 0,
       FIND_IN_SET('editar', p.pag_acciones) > 0, FIND_IN_SET('borrar', p.pag_acciones) > 0
FROM `rol` r JOIN `pagina` p JOIN `modulo` m ON m.id_modulo = p.id_modulo
WHERE r.rol_descripcion IN ('Compras', 'Ventas', 'Produccion') AND m.mod_clave = 'general';

/* 7.3 Compras: módulo Compras */
INSERT IGNORE INTO `permiso` (`id_rol`, `id_pagina`, `perm_leer`, `perm_insertar`, `perm_editar`, `perm_borrar`)
SELECT r.id_rol, p.id_pagina,
       FIND_IN_SET('leer', p.pag_acciones) > 0, FIND_IN_SET('insertar', p.pag_acciones) > 0,
       FIND_IN_SET('editar', p.pag_acciones) > 0, FIND_IN_SET('borrar', p.pag_acciones) > 0
FROM `rol` r JOIN `pagina` p JOIN `modulo` m ON m.id_modulo = p.id_modulo
WHERE r.rol_descripcion = 'Compras' AND m.mod_clave = 'compras';

/* 7.4 Ventas: módulo Ventas */
INSERT IGNORE INTO `permiso` (`id_rol`, `id_pagina`, `perm_leer`, `perm_insertar`, `perm_editar`, `perm_borrar`)
SELECT r.id_rol, p.id_pagina,
       FIND_IN_SET('leer', p.pag_acciones) > 0, FIND_IN_SET('insertar', p.pag_acciones) > 0,
       FIND_IN_SET('editar', p.pag_acciones) > 0, FIND_IN_SET('borrar', p.pag_acciones) > 0
FROM `rol` r JOIN `pagina` p JOIN `modulo` m ON m.id_modulo = p.id_modulo
WHERE r.rol_descripcion = 'Ventas' AND m.mod_clave = 'ventas';

/* 7.5 Departamento y Ciudad: hasta ahora los usaban todos los roles */
INSERT IGNORE INTO `permiso` (`id_rol`, `id_pagina`, `perm_leer`, `perm_insertar`, `perm_editar`, `perm_borrar`)
SELECT r.id_rol, p.id_pagina, 1, 1, 1, 1
FROM `rol` r JOIN `pagina` p
WHERE r.rol_descripcion IN ('Compras', 'Ventas', 'Produccion') AND p.pag_clave IN ('departamento', 'ciudad');

/* 7.6 Las páginas nuevas (nacionalidad, género, persona, funcionario, cargo, rol, permiso, pagina) quedan solo
       para el Super Admin: criterio de "denegado por defecto". Se habilitan desde Seguridad > Permisos. */

/* ------------------------------------------------------------------------------------------
   8. Vincular los usuarios existentes con su rol (a partir del texto de permisos_acceso)
   ------------------------------------------------------------------------------------------ */
UPDATE `usuarios` u
JOIN `rol` r ON r.rol_descripcion = u.permisos_acceso
SET u.id_rol = r.id_rol
WHERE u.id_rol IS NULL;

/* ------------------------------------------------------------------------------------------
   9. Vistas de consulta (el proyecto ya usa vistas v_* para los listados)
   ------------------------------------------------------------------------------------------ */
CREATE OR REPLACE VIEW `v_persona` AS
SELECT p.id_persona, p.per_nombre, p.per_apellido, CONCAT(p.per_nombre, ' ', p.per_apellido) AS per_nombre_completo,
       p.per_ci, p.per_fecha_nac, TIMESTAMPDIFF(YEAR, p.per_fecha_nac, CURDATE()) AS per_edad,
       p.per_telefono, p.per_correo, p.per_direccion, p.per_estado,
       g.id_genero, g.gen_descripcion,
       n.id_nacionalidad, n.nac_descripcion,
       c.cod_ciudad, c.descrip_ciudad,
       d.id_departamento, d.dep_descripcion
FROM persona p
JOIN genero g ON g.id_genero = p.id_genero
JOIN nacionalidad n ON n.id_nacionalidad = p.id_nacionalidad
JOIN ciudad c ON c.cod_ciudad = p.cod_ciudad
JOIN departamento d ON d.id_departamento = c.id_departamento;

CREATE OR REPLACE VIEW `v_funcionario` AS
SELECT f.id_funcionario, f.fun_fecha_ingreso, f.fun_estado,
       ca.id_cargo, ca.car_descripcion,
       v.id_persona, v.per_nombre_completo, v.per_ci, v.per_correo, v.per_telefono, v.per_estado,
       u.id_user, u.username
FROM funcionario f
JOIN cargo ca ON ca.id_cargo = f.id_cargo
JOIN v_persona v ON v.id_persona = f.id_persona
LEFT JOIN usuarios u ON u.id_funcionario = f.id_funcionario;

CREATE OR REPLACE VIEW `v_usuarios` AS
SELECT u.id_user, u.username, u.name_user, u.email, u.telefono, u.foto, u.status, u.intentos_fallidos,
       r.id_rol, r.rol_descripcion,
       f.id_funcionario, f.per_nombre_completo, f.car_descripcion
FROM usuarios u
LEFT JOIN rol r ON r.id_rol = u.id_rol
LEFT JOIN v_funcionario f ON f.id_funcionario = u.id_funcionario;

/* ------------------------------------------------------------------------------------------
   10. Registrar la versión
   ------------------------------------------------------------------------------------------ */
INSERT IGNORE INTO `esquema_version` (`version`, `descripcion`, `aplicado`)
VALUES (1, 'Esquema base + seguridad del login (Backup_06_1_2026)', '2026-10-06 22:23:00');
INSERT IGNORE INTO `esquema_version` (`version`, `descripcion`, `aplicado`)
VALUES (2, 'Personas, funcionarios, cargos, roles, módulos, páginas y permisos (RBAC)', NOW());
