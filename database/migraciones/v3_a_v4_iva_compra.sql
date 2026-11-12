/*
  MIGRACIÓN  v3 -> v4   (09/11/2026)
  IVA de compras: tabla iva_compra (en el DER: IVA_compra).

  Regla de negocio: al registrar una factura de compra se registra su IVA (5 %, 10 % y exentas) en una tabla ligada a la
  compra; las notas de crédito/débito también registran el suyo (crédito resta, débito suma). Ver config/iva.php.

  Columnas del DER: id_iva, iva_fecha, iva_5, iva_10, iva_exentas, iva_estado, cod_compra.
  Se agregan dos para distinguir el origen de cada fila: id_nota (NULL cuando es la factura) e iva_origen ('COMPRA'|'NOTA').

  - Es IDEMPOTENTE: se puede ejecutar varias veces (CREATE ... IF NOT EXISTS y carga inicial solo de lo que falta).
  - Carga el IVA de las compras y notas que ya existían, con la misma regla que usa el sistema:
        compras  -> se recalcula desde detalle_compra (tipo de impuesto, precio y cantidad)
        notas con productos -> se recalcula desde det_nota_credit_debit (crédito en negativo)
        notas por ajuste de cuentas (sin productos) -> prorrateo según la composición de la factura original
  - Cómo aplicarla sobre la base actual (conserva todos los datos):
        mysql -u root sysweb < database/migraciones/v3_a_v4_iva_compra.sql
    o, desde phpMyAdmin: seleccionar la base "sysweb" > Importar > este archivo.

  La tabla es InnoDB sin clave foránea a compra / nota_credito_debito porque esas tablas son MyISAM (no admiten claves
  foráneas): la relación es lógica y la mantiene la aplicación.
*/

SET NAMES utf8mb4;
USE `sysweb`;

CREATE TABLE IF NOT EXISTS `iva_compra` (
  `id_iva`       INT NOT NULL AUTO_INCREMENT,
  `iva_fecha`    DATE NOT NULL,
  `iva_5`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `iva_10`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `iva_exentas`  DECIMAL(12,2) NOT NULL DEFAULT 0,
  `iva_estado`   VARCHAR(10) NOT NULL DEFAULT 'ACTIVO',
  `cod_compra`   INT NOT NULL,
  `id_nota`      BIGINT UNSIGNED NULL,
  `iva_origen`   VARCHAR(10) NOT NULL DEFAULT 'COMPRA',
  PRIMARY KEY (`id_iva`),
  KEY `idx_iva_compra_compra` (`cod_compra`),
  KEY `idx_iva_compra_nota` (`id_nota`),
  KEY `idx_iva_compra_fecha` (`iva_fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 1) IVA de las facturas de compra existentes. Se RECALCULA con la regla vigente a partir de tipo de impuesto, precio y
--    cantidad (las columnas iva5/iva10 guardadas en filas antiguas no siempre coinciden con ella).
--    Fecha = emisión de la factura; si la factura antigua no tiene fecha válida se usa la fecha de la compra.
INSERT INTO `iva_compra` (`iva_fecha`, `iva_5`, `iva_10`, `iva_exentas`, `iva_estado`, `cod_compra`, `id_nota`, `iva_origen`)
SELECT IF(c.fac_emision < '2000-01-01', c.fecha, c.fac_emision),
       ROUND(COALESCE(SUM(IF(d.tipo_iva = '5%',  d.precio * d.cantidad - d.precio * d.cantidad / 1.05, 0)), 0), 2),
       ROUND(COALESCE(SUM(IF(d.tipo_iva = '10%', d.precio * d.cantidad - d.precio * d.cantidad / 1.10, 0)), 0), 2),
       ROUND(COALESCE(SUM(IF(d.tipo_iva IN ('5%', '10%'), 0, d.precio * d.cantidad)), 0), 2),
       IF(UPPER(c.estado) = 'ANULADO', 'ANULADO', 'ACTIVO'), c.cod_compra, NULL, 'COMPRA'
  FROM `compra` c
  LEFT JOIN `detalle_compra` d ON d.cod_compra = c.cod_compra
 WHERE NOT EXISTS (SELECT 1 FROM `iva_compra` i WHERE i.cod_compra = c.cod_compra AND i.id_nota IS NULL)
 GROUP BY c.cod_compra, c.fecha, c.fac_emision, c.estado;

-- 2) IVA de las notas existentes (crédito en negativo, débito en positivo): con productos se calcula por línea
--    (tipo_iva 10 / 5 / otro = exenta); sin productos (ajuste de cuentas) se prorratea con la composición de la factura.
INSERT INTO `iva_compra` (`iva_fecha`, `iva_5`, `iva_10`, `iva_exentas`, `iva_estado`, `cod_compra`, `id_nota`, `iva_origen`)
SELECT n.fecha_emision,
       ROUND(IF(n.tipo = 'CREDITO', -1, 1) * IF(d.nlin IS NOT NULL, d.i5,  COALESCE(n.monto_total * ic.iva_5       / NULLIF(c.total_compra, 0), 0)), 2),
       ROUND(IF(n.tipo = 'CREDITO', -1, 1) * IF(d.nlin IS NOT NULL, d.i10, COALESCE(n.monto_total * ic.iva_10      / NULLIF(c.total_compra, 0), 0)), 2),
       ROUND(IF(n.tipo = 'CREDITO', -1, 1) * IF(d.nlin IS NOT NULL, d.ex,  COALESCE(n.monto_total * ic.iva_exentas / NULLIF(c.total_compra, 0), 0)), 2),
       IF(UPPER(n.estado) = 'ANULADO', 'ANULADO', 'ACTIVO'), n.cod_compra, n.id_nota, 'NOTA'
  FROM `nota_credito_debito` n
  JOIN `compra` c ON c.cod_compra = n.cod_compra
  LEFT JOIN `iva_compra` ic ON ic.cod_compra = n.cod_compra AND ic.id_nota IS NULL
  LEFT JOIN (SELECT id_nota, COUNT(*) AS nlin,
                    SUM(IF(tipo_iva = 5,  cantidad * precio_unitario - cantidad * precio_unitario / 1.05, 0)) AS i5,
                    SUM(IF(tipo_iva = 10, cantidad * precio_unitario - cantidad * precio_unitario / 1.10, 0)) AS i10,
                    SUM(IF(tipo_iva IN (5, 10), 0, cantidad * precio_unitario)) AS ex
               FROM `det_nota_credit_debit` GROUP BY id_nota) d ON d.id_nota = n.id_nota
 WHERE NOT EXISTS (SELECT 1 FROM `iva_compra` i WHERE i.id_nota = n.id_nota);
-- 3) Registrar la versión
INSERT IGNORE INTO `esquema_version` (`version`, `descripcion`, `aplicado`)
VALUES (4, 'IVA de compras y notas (tabla iva_compra)', NOW());
