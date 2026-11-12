/*
  MIGRACIÓN  v2 -> v3   (31/10/2026)
  Estados "finalizados" del flujo de compras.

  Nueva regla de la aplicación: al CONFIRMAR un documento, el documento del que nace queda finalizado y ya no admite
  acciones (config/compras.php, COMPRAS_FLUJO):
        presupuesto APROBADO  -> su pedido pasa de CONFIRMADO a PRESUPUESTADO
        orden APROBADA        -> su presupuesto pasa de APROBADO a ORDENADO
        compra ACTIVO         -> su orden pasa de APROBADO a COMPRADA
  (al anular el documento hijo, el padre vuelve a su estado anterior).

  Esta migración NO cambia tablas ni columnas: solo alinea los DATOS que ya existían (creados antes de la regla),
  para que un pedido que ya tiene un presupuesto aprobado no siga apareciendo como CONFIRMADO, etc.
  - Es IDEMPOTENTE: se puede ejecutar varias veces.
  - Se aplica de abajo hacia arriba (compra -> orden -> presupuesto -> pedido) para que cada paso vea el resultado del anterior.
  - Cómo aplicarla sobre la base actual (conserva todos los datos):
        mysql -u root sysweb < database/migraciones/v2_a_v3_estados_del_flujo.sql
    o, desde phpMyAdmin: seleccionar la base "sysweb" > Importar > este archivo.
*/

SET NAMES utf8mb4;
USE `sysweb`;

-- 1) Órdenes con una compra ACTIVA -> COMPRADA
UPDATE `orden_compra` o
   SET o.estado = 'COMPRADA'
 WHERE UPPER(o.estado) = 'APROBADO'
   AND EXISTS (SELECT 1 FROM `compra` c WHERE c.id_orden_comp = o.id_orden_comp AND UPPER(c.estado) = 'ACTIVO');

-- 2) Presupuestos con una orden vigente y aprobada (o ya comprada) -> ORDENADO
UPDATE `presupuesto` p
   SET p.estado = 'ORDENADO'
 WHERE UPPER(p.estado) = 'APROBADO'
   AND EXISTS (SELECT 1 FROM `orden_compra` o WHERE o.id_presupuesto = p.id_presupuesto AND UPPER(o.estado) IN ('APROBADO', 'COMPRADA'));

-- 3) Pedidos con un presupuesto aprobado (o ya ordenado) -> PRESUPUESTADO
UPDATE `pedido` pe
   SET pe.estado = 'PRESUPUESTADO'
 WHERE UPPER(pe.estado) = 'CONFIRMADO'
   AND EXISTS (SELECT 1 FROM `presupuesto` p WHERE p.id_pedido = pe.id_pedido AND UPPER(p.estado) IN ('APROBADO', 'ORDENADO'));

-- 4) Registrar la versión
INSERT IGNORE INTO `esquema_version` (`version`, `descripcion`, `aplicado`)
VALUES (3, 'Estados finalizados del flujo de compras (PRESUPUESTADO, ORDENADO, COMPRADA)', NOW());
