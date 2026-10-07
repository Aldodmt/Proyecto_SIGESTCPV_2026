/*
SQLyog Community v12.5.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - sysweb
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sysweb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sysweb`;

/*Table structure for table `ajuste_com` */

DROP TABLE IF EXISTS `ajuste_com`;

CREATE TABLE `ajuste_com` (
  `id_ajuste` int(11) NOT NULL,
  `fecha_ajuste` date NOT NULL,
  `motivo` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `id_user` int(11) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_ajuste`),
  KEY `fk_user_ajuste` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `ajuste_com` */

insert  into `ajuste_com`(`id_ajuste`,`fecha_ajuste`,`motivo`,`estado`,`id_user`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(1,'2025-10-11','Si2','ACTIVO',1,NULL,NULL,NULL),
(2,'2025-10-11','Si3','ACTIVO',1,NULL,NULL,NULL),
(3,'2025-10-11','Si4','ANULADO',1,1,'2025-10-11','11:38:45'),
(4,'2025-10-23','Vencimiento','ACTIVO',1,NULL,NULL,NULL);

/*Table structure for table `ciudad` */

DROP TABLE IF EXISTS `ciudad`;

CREATE TABLE `ciudad` (
  `cod_ciudad` int(11) NOT NULL,
  `descrip_ciudad` varchar(25) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  PRIMARY KEY (`cod_ciudad`),
  KEY `id_departamento` (`id_departamento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `ciudad` */

insert  into `ciudad`(`cod_ciudad`,`descrip_ciudad`,`id_departamento`) values 
(1,'Asunción',1),
(2,'Capiatá',1),
(3,'Hernandarias',2),
(4,'San Ignacio',3),
(5,'Nueva italia',1);

/*Table structure for table `clientes` */

DROP TABLE IF EXISTS `clientes`;

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `cod_ciudad` int(11) DEFAULT NULL,
  `ci_ruc` varchar(10) NOT NULL,
  `cli_nombre` varchar(30) NOT NULL,
  `cli_apellido` varchar(50) NOT NULL,
  `cli_direccion` varchar(50) DEFAULT NULL,
  `cli_telefono` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `clientes_cod_ciudad_fkey` (`cod_ciudad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `clientes` */

insert  into `clientes`(`id_cliente`,`cod_ciudad`,`ci_ruc`,`cli_nombre`,`cli_apellido`,`cli_direccion`,`cli_telefono`) values 
(1,1,'5629997','Carlos','Ortiz','Capiata km27',976524098),
(2,2,'5628992','Aldo','Marin','Barrio san Agustin',992356262),
(3,4,'5863952','Adrian','Gimenez','Calle B',993626538);

/*Table structure for table `compra` */

DROP TABLE IF EXISTS `compra`;

CREATE TABLE `compra` (
  `cod_compra` int(11) NOT NULL,
  `cod_proveedor` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(15) NOT NULL,
  `hora` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_orden_comp` int(11) DEFAULT NULL,
  `fac_numero` varchar(30) NOT NULL,
  `fac_emision` date NOT NULL,
  `tipo_factura` varchar(20) NOT NULL,
  `con_sin_remision` varchar(30) NOT NULL,
  `timbrado_nro` int(11) NOT NULL,
  `timb_fecha_venci` date DEFAULT NULL,
  `com_condicion` varchar(30) NOT NULL,
  `total_compra` decimal(10,0) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`cod_compra`),
  KEY `cod_proveedor` (`cod_proveedor`),
  KEY `id_orden_compra_compra_fk` (`id_orden_comp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `compra` */

insert  into `compra`(`cod_compra`,`cod_proveedor`,`fecha`,`estado`,`hora`,`id_user`,`id_orden_comp`,`fac_numero`,`fac_emision`,`tipo_factura`,`con_sin_remision`,`timbrado_nro`,`timb_fecha_venci`,`com_condicion`,`total_compra`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(1,2,'2025-10-10','ACTIVO','14:50:28',1,1,'001-001-0000001','0000-00-00','','con',445454,'2025-11-10','CONTADO',35540,NULL,NULL,NULL),
(2,1,'2025-10-10','ANULADO','15:02:14',1,3,'001-001-0000002','0000-00-00','','con',54545454,'2025-11-10','CREDITO',87500,1,'2025-10-10','12:42:24'),
(3,2,'2025-10-11','ACTIVO','01:00:27',1,2,'001-001-0000003','0000-00-00','','sin',22222222,'2025-11-10','CREDITO',45000,NULL,NULL,NULL),
(4,2,'2025-10-11','ACTIVO','01:17:39',1,4,'001-001-0000004','0000-00-00','','con',33333333,'2025-12-10','CREDITO',100000,NULL,NULL,NULL),
(5,1,'2025-10-19','ACTIVO','22:29:54',1,6,'001-001-0000005','0000-00-00','','sin',55555555,'2025-11-19','CREDITO',75000,NULL,NULL,NULL),
(7,2,'2025-10-23','ACTIVO','22:35:41',1,9,'001-001-0000007','0000-00-00','','CON',56565656,'2025-11-23','CREDITO',60000,NULL,NULL,NULL),
(6,1,'2025-10-22','ACTIVO','11:49:24',1,8,'001-001-0000006','0000-00-00','','CON',66666666,'2025-11-22','CREDITO',150000,NULL,NULL,NULL),
(8,1,'2025-10-25','PENDIENTE','13:40:24',1,5,'001-001-0000008','2025-10-24','ELECTRONICA','SIN',88888888,'0000-00-00','CREDITO',5000,NULL,NULL,NULL),
(9,2,'2025-10-25','ACTIVO','13:43:17',1,NULL,'001-001-0000009','2025-10-23','ELECTRONICA','SIN',99999999,'0000-00-00','CREDITO',100000,NULL,NULL,NULL);

/*Table structure for table `cuentas_a_pagar` */

DROP TABLE IF EXISTS `cuentas_a_pagar`;

CREATE TABLE `cuentas_a_pagar` (
  `cap_cod` int(11) NOT NULL AUTO_INCREMENT,
  `cod_compra` int(11) NOT NULL,
  `nro_cuota` int(11) NOT NULL,
  `cap_monto` decimal(10,0) NOT NULL,
  `cap_saldo` decimal(10,0) NOT NULL,
  `cap_fecha_venci` date NOT NULL,
  `cap_estado` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`cap_cod`),
  KEY `fk_compra_cap` (`cod_compra`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `cuentas_a_pagar` */

insert  into `cuentas_a_pagar`(`cap_cod`,`cod_compra`,`nro_cuota`,`cap_monto`,`cap_saldo`,`cap_fecha_venci`,`cap_estado`) values 
(25,1,1,35540,0,'2025-10-10','PAGADO'),
(29,2,2,43750,43750,'2025-11-09','ANULADO'),
(28,2,1,43750,43750,'2025-10-10','ANULADO'),
(30,3,1,20000,20000,'2025-10-11','PENDIENTE'),
(31,3,2,20000,20000,'2025-10-26','PENDIENTE'),
(32,4,1,33333,33333,'2025-10-11','PENDIENTE'),
(33,4,2,33333,33333,'2025-10-31','PENDIENTE'),
(34,4,3,33333,33333,'2025-11-20','PENDIENTE'),
(36,5,1,32500,32500,'2025-10-19','PENDIENTE'),
(37,5,2,32500,32500,'2025-11-18','PENDIENTE'),
(40,6,1,33333,33333,'2025-10-22','PENDIENTE'),
(41,6,2,33333,33333,'2025-11-21','PENDIENTE'),
(42,6,3,33333,33333,'2025-12-21','PENDIENTE'),
(43,7,1,17500,17500,'2025-10-23','PENDIENTE'),
(44,7,2,17500,17500,'2025-11-22','PENDIENTE'),
(45,8,1,2500,2500,'2025-10-25','PENDIENTE'),
(46,8,2,2500,2500,'2025-11-24','PENDIENTE'),
(47,9,1,50000,50000,'2025-10-25','PENDIENTE'),
(48,9,2,50000,50000,'2025-11-24','PENDIENTE');

/*Table structure for table `departamento` */

DROP TABLE IF EXISTS `departamento`;

CREATE TABLE `departamento` (
  `id_departamento` int(11) NOT NULL,
  `dep_descripcion` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id_departamento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `departamento` */

insert  into `departamento`(`id_departamento`,`dep_descripcion`) values 
(1,'Central'),
(2,'Alto Paraná'),
(3,'Misiones');

/*Table structure for table `deposito` */

DROP TABLE IF EXISTS `deposito`;

CREATE TABLE `deposito` (
  `cod_deposito` int(11) NOT NULL,
  `descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `deposito` */

insert  into `deposito`(`cod_deposito`,`descrip`) values 
(1,'Depo Central'),
(2,'Depo 2');

/*Table structure for table `det_ajuste` */

DROP TABLE IF EXISTS `det_ajuste`;

CREATE TABLE `det_ajuste` (
  `id_ajuste` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad_ajustada` int(11) NOT NULL,
  `cantidad_anterior` int(11) NOT NULL,
  PRIMARY KEY (`id_ajuste`,`cod_producto`),
  KEY `producto_det_ajuste_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_ajuste` */

insert  into `det_ajuste`(`id_ajuste`,`cod_producto`,`cantidad_ajustada`,`cantidad_anterior`) values 
(1,1,1,34),
(2,2,2,60),
(3,1,1,33),
(3,2,1,58),
(4,3,3,9);

/*Table structure for table `det_nota_credit_debit` */

DROP TABLE IF EXISTS `det_nota_credit_debit`;

CREATE TABLE `det_nota_credit_debit` (
  `id_nota` int(11) NOT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `cantidad` decimal(12,2) DEFAULT NULL,
  `precio_unitario` decimal(12,2) DEFAULT NULL,
  `tipo_iva` int(11) DEFAULT NULL,
  `exentas` decimal(12,2) DEFAULT NULL,
  `iva5` decimal(12,2) DEFAULT NULL,
  `iva10` decimal(12,2) DEFAULT NULL,
  KEY `fk_nota` (`id_nota`),
  KEY `fk_producto` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_nota_credit_debit` */

insert  into `det_nota_credit_debit`(`id_nota`,`cod_producto`,`cantidad`,`precio_unitario`,`tipo_iva`,`exentas`,`iva5`,`iva10`) values 
(24,2,5.00,5000.00,5,0.00,1190.48,0.00),
(25,3,5.00,5000.00,10,0.00,0.00,2272.73),
(25,2,5.00,5000.00,10,0.00,0.00,2272.73),
(26,3,2.00,5000.00,10,0.00,0.00,909.09),
(27,1,5.00,5000.00,10,0.00,0.00,2272.73);

/*Table structure for table `det_notar_compra` */

DROP TABLE IF EXISTS `det_notar_compra`;

CREATE TABLE `det_notar_compra` (
  `id_notaR` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`id_notaR`,`cod_producto`),
  KEY `cod_producto` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_notar_compra` */

insert  into `det_notar_compra`(`id_notaR`,`cod_producto`,`cantidad`) values 
(3,1,2),
(3,2,2),
(4,1,10),
(4,2,10),
(4,3,10);

/*Table structure for table `det_pedido` */

DROP TABLE IF EXISTS `det_pedido`;

CREATE TABLE `det_pedido` (
  `cod_producto` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `pedido_det_pedido_fk` (`id_pedido`),
  KEY `producto_det_pedido_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_pedido` */

insert  into `det_pedido`(`cod_producto`,`id_pedido`,`cantidad`) values 
(1,136,1),
(2,112,2),
(2,136,1),
(2,139,1),
(2,140,1),
(2,141,1),
(1,112,2),
(1,171,5),
(1,169,5),
(2,177,15),
(1,177,10),
(2,178,20),
(1,184,5),
(1,186,5),
(3,198,5),
(1,198,5),
(1,205,1),
(1,206,10),
(2,206,10),
(3,206,10),
(1,207,10),
(2,207,1),
(3,207,1),
(1,216,1);

/*Table structure for table `det_pedido_v` */

DROP TABLE IF EXISTS `det_pedido_v`;

CREATE TABLE `det_pedido_v` (
  `id_pedido_v` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cod_deposito` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `pedido_v_det_fk` (`id_pedido_v`),
  KEY `producto_det_v_fk` (`cod_producto`),
  KEY `depositodet_v_fk` (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_pedido_v` */

/*Table structure for table `det_presu` */

DROP TABLE IF EXISTS `det_presu`;

CREATE TABLE `det_presu` (
  `id_presupuesto` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unit` int(11) NOT NULL,
  KEY `producto_det_presu_fk` (`cod_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_presu` */

insert  into `det_presu`(`id_presupuesto`,`cod_producto`,`cantidad`,`precio_unit`) values 
(5,2,15,2500),
(4,2,1,5000),
(3,2,1,45000),
(2,2,1,45),
(7,1,5,10000),
(2,1,1,45),
(7,3,5,5000),
(6,2,20,5000),
(5,1,10,5000),
(1,1,2,15220),
(1,2,2,2550),
(8,2,1,2550),
(9,1,5,5000),
(10,1,10,5000),
(10,2,10,5000),
(10,3,10,5000),
(11,1,10,5000),
(11,2,1,5000),
(11,3,1,5000),
(12,1,10,5000),
(13,1,5,5000),
(14,1,1,1);

/*Table structure for table `det_venta` */

DROP TABLE IF EXISTS `det_venta`;

CREATE TABLE `det_venta` (
  `cod_producto` int(11) NOT NULL,
  `cod_venta` int(11) NOT NULL,
  `cod_deposito` int(11) NOT NULL,
  `det_precio_unit` int(11) NOT NULL,
  `det_cantidad` int(11) NOT NULL,
  PRIMARY KEY (`cod_venta`),
  KEY `deposito_det_venta_fk` (`cod_deposito`),
  KEY `venta_det_venta_fk` (`cod_venta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `det_venta` */

insert  into `det_venta`(`cod_producto`,`cod_venta`,`cod_deposito`,`det_precio_unit`,`det_cantidad`) values 
(1,2,1,10400,1),
(1,1,1,10400,10),
(1,3,1,10400,5);

/*Table structure for table `detalle_compra` */

DROP TABLE IF EXISTS `detalle_compra`;

CREATE TABLE `detalle_compra` (
  `cod_producto` int(11) NOT NULL,
  `cod_compra` int(11) NOT NULL,
  `precio` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `tipo_iva` varchar(20) NOT NULL,
  `exentas` decimal(12,2) DEFAULT NULL,
  `iva5` decimal(12,2) DEFAULT NULL,
  `iva10` decimal(12,2) DEFAULT NULL,
  KEY `compra_detalle_compra_fk` (`cod_compra`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `detalle_compra` */

insert  into `detalle_compra`(`cod_producto`,`cod_compra`,`precio`,`cantidad`,`tipo_iva`,`exentas`,`iva5`,`iva10`) values 
(1,1,15220,2,'10%',0.00,0.00,3230.91),
(2,1,2550,2,'10%',0.00,0.00,463.64),
(2,2,2500,15,'10%',0.00,0.00,7954.55),
(1,2,5000,10,'10%',0.00,0.00,4545.45),
(2,3,45000,1,'10%',0.00,0.00,4090.91),
(2,4,5000,20,'5%',0.00,4761.90,0.00),
(3,5,5000,5,'10%',0.00,0.00,2272.73),
(1,5,10000,5,'10%',0.00,0.00,4545.45),
(1,6,5000,10,'10%',0.00,0.00,4545.45),
(2,6,5000,10,'10%',0.00,0.00,4545.45),
(3,6,5000,10,'10%',0.00,0.00,4545.45),
(1,7,5000,10,'10%',0.00,0.00,4545.45),
(2,7,5000,1,'5%',0.00,0.00,238.10),
(3,7,5000,1,'10%',0.00,0.00,238.10),
(2,8,5000,1,'5%',0.00,238.10,0.00),
(1,9,5000,10,'10%',0.00,0.00,4545.45),
(2,9,5000,10,'5%',0.00,2380.95,0.00);

/*Table structure for table `detalle_orden_comp` */

DROP TABLE IF EXISTS `detalle_orden_comp`;

CREATE TABLE `detalle_orden_comp` (
  `id_orden_comp` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `precio_unit` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `orden_compra_det_orden_comp_fk` (`id_orden_comp`),
  KEY `cod_producto_detalle_orden_compra_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `detalle_orden_comp` */

insert  into `detalle_orden_comp`(`id_orden_comp`,`cod_producto`,`precio_unit`,`cantidad`) values 
(5,2,5000,1),
(4,2,5000,20),
(3,1,5000,10),
(3,2,2500,15),
(2,2,45000,1),
(1,2,2550,2),
(1,1,15220,2),
(6,1,10000,5),
(6,3,5000,5),
(7,1,5000,5),
(8,1,5000,10),
(8,2,5000,10),
(8,3,5000,10),
(9,1,5000,10),
(9,2,5000,1),
(9,3,5000,1),
(10,1,5000,10);

/*Table structure for table `log_accesos` */

DROP TABLE IF EXISTS `log_accesos`;

CREATE TABLE `log_accesos` (
  `id_log` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `username` varchar(150) NOT NULL,
  `password_enmascarada` varchar(40) NOT NULL DEFAULT '',
  `fecha_hora` datetime NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `resultado` varchar(20) NOT NULL,
  `motivo` varchar(120) NOT NULL DEFAULT '',
  `tiempo_ms` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_log`),
  KEY `idx_fecha` (`fecha_hora`),
  KEY `idx_username` (`username`),
  KEY `idx_resultado` (`resultado`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `log_accesos` */

insert  into `log_accesos`(`id_log`,`id_user`,`username`,`password_enmascarada`,`fecha_hora`,`ip`,`user_agent`,`resultado`,`motivo`,`tiempo_ms`) values 
(10,1,'aldo','***','2026-10-06 21:56:43','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',171),
(11,1,'aldo','***','2026-10-06 22:14:14','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',120),
(12,NULL,'adlo','****','2026-10-06 22:15:14','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),
(13,NULL,'adlo','****','2026-10-06 22:15:16','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),
(14,NULL,'adlo','***','2026-10-06 22:15:17','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),
(15,1,'aldo','***','2026-10-06 22:15:21','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',104);

/*Table structure for table `nota_credito_debito` */

DROP TABLE IF EXISTS `nota_credito_debito`;

CREATE TABLE `nota_credito_debito` (
  `id_nota` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cod_compra` int(11) NOT NULL,
  `fac_numero` varchar(30) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `causa` varchar(30) NOT NULL,
  `nro_nota` varchar(50) NOT NULL,
  `timbrado` varchar(50) NOT NULL,
  `fecha_emision` date NOT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `monto_total` decimal(12,2) DEFAULT NULL,
  `observacion` varchar(200) DEFAULT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_nota`),
  UNIQUE KEY `id_nota` (`id_nota`),
  KEY `fk_usuario` (`id_user`),
  KEY `fk_cod_nota` (`cod_compra`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `nota_credito_debito` */

insert  into `nota_credito_debito`(`id_nota`,`cod_compra`,`fac_numero`,`tipo`,`causa`,`nro_nota`,`timbrado`,`fecha_emision`,`estado`,`id_user`,`monto_total`,`observacion`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(24,4,'001-001-0000004','CREDITO','ajuste_p','33333333','33333333','2025-10-11','ANULADO',1,25000.00,'Si1',1,'2025-10-11','09:05:51'),
(25,6,'001-001-0000006','CREDITO','ajuste_p','12121212','66666666','2025-10-22','ACTIVO',1,50000.00,'Si3',NULL,NULL,NULL),
(26,5,'001-001-0000005','CREDITO','ajuste_p','12121212','55555555','2025-10-23','ACTIVO',1,10000.00,'a',NULL,NULL,NULL),
(27,7,'001-001-0000007','CREDITO','ajuste_p','27777777','27777777','2025-10-26','ACTIVO',1,25000.00,'Si7',NULL,NULL,NULL),
(28,3,'001-001-0000003','CREDITO','ajuste_c','88888888','88888888','2025-10-31','ACTIVO',1,5000.00,'Si8',NULL,NULL,NULL);

/*Table structure for table `notar_compra` */

DROP TABLE IF EXISTS `notar_compra`;

CREATE TABLE `notar_compra` (
  `id_notaR` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cod_compra` int(11) NOT NULL,
  `cod_proveedor` int(11) NOT NULL,
  `nro_nota` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `fecha_ini_traslado` date NOT NULL,
  `tipo_traslado` varchar(20) NOT NULL,
  `motivo_traslado` varchar(30) NOT NULL,
  `nom_transporte` varchar(50) NOT NULL,
  `ruc_ci_trans` varchar(10) NOT NULL,
  `placa_vehiculo` varchar(30) NOT NULL,
  `id_user` int(11) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_notaR`),
  UNIQUE KEY `id_notaR` (`id_notaR`),
  KEY `cod_compra` (`cod_compra`),
  KEY `id_user` (`id_user`),
  KEY `cod_proveedor` (`cod_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `notar_compra` */

insert  into `notar_compra`(`id_notaR`,`cod_compra`,`cod_proveedor`,`nro_nota`,`fecha`,`fecha_ini_traslado`,`tipo_traslado`,`motivo_traslado`,`nom_transporte`,`ruc_ci_trans`,`placa_vehiculo`,`id_user`,`estado`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(4,6,1,'001-001-0000006','2025-10-22','2025-09-22','POR COMPRA','PROVEEDOR','JUANITO ALIMAñA','2687425','CCCC444',1,'ACTIVO',NULL,NULL,NULL),
(3,1,2,'001-001-0000001','2025-10-12','2025-10-01','POR COMPRA','PROVEEDOR','JUANITO','5628442','AAAA123',1,'ANULADO',1,'2025-10-11','22:38:00');

/*Table structure for table `orden_compra` */

DROP TABLE IF EXISTS `orden_compra`;

CREATE TABLE `orden_compra` (
  `id_orden_comp` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `hora` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_presupuesto` int(11) DEFAULT NULL,
  `cod_proveedor` int(11) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_orden_comp`),
  KEY `id_user_orden_fk` (`id_user`),
  KEY `id_presu_orden_comp_fk` (`id_presupuesto`),
  KEY `fk_prov_ord` (`cod_proveedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `orden_compra` */

insert  into `orden_compra`(`id_orden_comp`,`fecha`,`estado`,`hora`,`id_user`,`id_presupuesto`,`cod_proveedor`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(5,'2025-10-13','APROBADO','20:58:17',1,4,1,NULL,NULL,NULL),
(4,'2025-10-11','APROBADO','01:17:19',1,6,2,NULL,NULL,NULL),
(3,'2025-10-07','APROBADO','23:17:18',1,5,1,NULL,NULL,NULL),
(2,'2025-10-07','APROBADO','15:16:25',1,3,2,NULL,NULL,NULL),
(1,'2025-10-03','APROBADO','16:12:44',1,1,2,NULL,NULL,NULL),
(6,'2025-10-14','APROBADO','19:37:53',1,7,1,NULL,NULL,NULL),
(7,'2025-10-19','ANULADO','11:47:20',1,9,2,1,'2025-10-19','08:54:18'),
(8,'2025-10-22','APROBADO','11:48:39',1,10,1,NULL,NULL,NULL),
(9,'2025-10-23','APROBADO','22:33:26',1,11,2,NULL,NULL,NULL),
(10,'2025-10-24','APROBADO','20:01:06',1,NULL,2,NULL,NULL,NULL);

/*Table structure for table `password_resets` */

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `id_reset` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado` datetime NOT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `uq_token` (`token_hash`),
  KEY `idx_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `password_resets` */

/*Table structure for table `pedido` */

DROP TABLE IF EXISTS `pedido`;

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `hora` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `id_user_pedido_fk` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=219 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `pedido` */

insert  into `pedido`(`id_pedido`,`fecha`,`estado`,`hora`,`id_user`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(177,'2025-10-07','CONFIRMADO','20:15:37',1,NULL,NULL,NULL),
(171,'2025-10-02','PENDIENTE','18:30:25',1,NULL,NULL,NULL),
(141,'2025-09-30','CONFIRMADO','15:50:48',1,NULL,NULL,NULL),
(140,'2025-09-30','CONFIRMADO','15:48:55',1,NULL,NULL,NULL),
(139,'2025-09-30','CONFIRMADO','15:48:11',1,NULL,NULL,NULL),
(169,'2025-10-02','CONFIRMADO','18:27:13',1,NULL,NULL,NULL),
(137,'2025-09-30','PENDIENTE','15:45:32',1,NULL,NULL,NULL),
(136,'2025-09-30','CONFIRMADO','15:44:20',1,NULL,NULL,NULL),
(112,'2025-09-30','ANULADO','15:12:15',1,1,'2025-10-31','15:48:34'),
(178,'2025-10-07','CONFIRMADO','20:15:56',1,NULL,NULL,NULL),
(184,'2025-10-14','PENDIENTE','15:23:32',1,NULL,NULL,NULL),
(186,'2025-10-14','PENDIENTE','15:24:23',1,NULL,NULL,NULL),
(198,'2025-10-14','CONFIRMADO','16:30:59',1,NULL,NULL,NULL),
(200,'2025-10-19','ANULADO','08:41:10',1,1,'2025-10-19','08:43:16'),
(205,'2025-10-21','CONFIRMADO','14:06:48',1,NULL,NULL,NULL),
(206,'2025-10-22','CONFIRMADO','08:46:41',1,NULL,NULL,NULL),
(207,'2025-10-22','CONFIRMADO','10:23:01',1,NULL,NULL,NULL),
(216,'2025-10-31','CONFIRMADO','15:52:17',1,NULL,NULL,NULL),
(218,'2026-10-01','BORRADOR','19:47:51',1,NULL,NULL,NULL);

/*Table structure for table `pedido_v` */

DROP TABLE IF EXISTS `pedido_v`;

CREATE TABLE `pedido_v` (
  `id_pedido_v` int(11) NOT NULL,
  `fecha_pedido` date NOT NULL,
  `hora` time NOT NULL,
  `estado` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_pedido_v`),
  KEY `id_user_pedido_v_fk` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `pedido_v` */

insert  into `pedido_v`(`id_pedido_v`,`fecha_pedido`,`hora`,`estado`,`id_user`) values 
(2,'2025-01-05','15:48:05','aprobado',1),
(1,'2025-01-02','20:00:11','aprobado',1),
(3,'2025-01-12','15:29:32','pendiente',1),
(4,'2025-01-15','19:35:41','aprobado',1);

/*Table structure for table `presupuesto` */

DROP TABLE IF EXISTS `presupuesto`;

CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL,
  `fecha_presu` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `cod_proveedor` int(11) NOT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `anulado_fecha` date DEFAULT NULL,
  `anulado_hora` time DEFAULT NULL,
  PRIMARY KEY (`id_presupuesto`,`cod_proveedor`),
  KEY `proveedor_presu_prov_fk` (`cod_proveedor`),
  KEY `id_pedido_presu_fk` (`id_pedido`),
  KEY `fk_user_presu` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `presupuesto` */

insert  into `presupuesto`(`id_presupuesto`,`fecha_presu`,`fecha_vencimiento`,`cod_proveedor`,`estado`,`id_pedido`,`id_user`,`anulado_por`,`anulado_fecha`,`anulado_hora`) values 
(5,'2025-10-07','2025-11-07',1,'APROBADO',177,1,NULL,NULL,NULL),
(4,'2025-10-07','2025-11-07',1,'APROBADO',140,1,NULL,NULL,NULL),
(3,'2025-10-03','2025-12-03',2,'APROBADO',139,1,NULL,NULL,NULL),
(2,'2025-10-03','2026-01-03',1,'ANULADO',136,1,NULL,NULL,NULL),
(1,'2025-10-02','2026-01-02',2,'APROBADO',112,1,NULL,NULL,NULL),
(6,'2025-10-11','2025-11-11',2,'APROBADO',178,1,NULL,NULL,NULL),
(7,'2025-10-14','2025-11-14',1,'APROBADO',198,1,NULL,NULL,NULL),
(8,'2025-10-19','2025-11-19',2,'ANULADO',141,1,1,'2025-10-19','08:46:08'),
(9,'2025-10-19','2025-11-19',2,'APROBADO',169,1,NULL,NULL,NULL),
(10,'2025-10-22','2025-11-22',1,'APROBADO',206,1,NULL,NULL,NULL),
(11,'2025-10-23','2025-11-23',2,'APROBADO',207,1,NULL,NULL,NULL),
(12,'2025-10-24','2025-11-24',2,'APROBADO',NULL,1,NULL,NULL,NULL),
(13,'2025-10-30','2025-11-30',2,'PENDIENTE',NULL,1,NULL,NULL,NULL),
(14,'2026-10-02','2026-10-31',1,'PENDIENTE',NULL,1,NULL,NULL,NULL);

/*Table structure for table `producto` */

DROP TABLE IF EXISTS `producto`;

CREATE TABLE `producto` (
  `cod_producto` int(11) NOT NULL,
  `cod_tipo_prod` int(11) NOT NULL,
  `id_u_medida` int(11) NOT NULL,
  `p_descrip` varchar(50) NOT NULL,
  `tipo_impuesto` varchar(10) NOT NULL,
  PRIMARY KEY (`cod_producto`),
  KEY `tipo_producto_producto_fk` (`cod_tipo_prod`),
  KEY `u_medida_producto_fk` (`id_u_medida`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `producto` */

insert  into `producto`(`cod_producto`,`cod_tipo_prod`,`id_u_medida`,`p_descrip`,`tipo_impuesto`) values 
(1,2,1,'Vaso Plastico 2x ','10%'),
(2,1,2,'a1','5%'),
(3,2,1,'Producto1','10%');

/*Table structure for table `proveedor` */

DROP TABLE IF EXISTS `proveedor`;

CREATE TABLE `proveedor` (
  `cod_proveedor` int(11) NOT NULL,
  `razon_social` varchar(75) NOT NULL,
  `ruc` varchar(9) NOT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `telefono` int(11) NOT NULL,
  PRIMARY KEY (`cod_proveedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `proveedor` */

insert  into `proveedor`(`cod_proveedor`,`razon_social`,`ruc`,`direccion`,`telefono`) values 
(1,'Empresa de lacteos','589236599','Calle X',985361242),
(2,'Cervepar','800232699','Calle D',982555623);

/*Table structure for table `stock_prod` */

DROP TABLE IF EXISTS `stock_prod`;

CREATE TABLE `stock_prod` (
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`cod_producto`),
  KEY `producto_stock_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `stock_prod` */

insert  into `stock_prod`(`cod_producto`,`cantidad`) values 
(1,48),
(2,74),
(3,6);

/*Table structure for table `tipo_producto` */

DROP TABLE IF EXISTS `tipo_producto`;

CREATE TABLE `tipo_producto` (
  `cod_tipo_prod` int(11) NOT NULL,
  `t_p_descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_tipo_prod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tipo_producto` */

insert  into `tipo_producto`(`cod_tipo_prod`,`t_p_descrip`) values 
(1,'Lacteos'),
(2,'Bebidas');

/*Table structure for table `tmp` */

DROP TABLE IF EXISTS `tmp`;

CREATE TABLE `tmp` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad_tmp` int(11) DEFAULT NULL,
  `session_id` varchar(765) DEFAULT NULL,
  `estado_tmp` varchar(20) DEFAULT 'BORRADOR',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=307 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp` */

/*Table structure for table `tmp_compra` */

DROP TABLE IF EXISTS `tmp_compra`;

CREATE TABLE `tmp_compra` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_orden_comp` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_comp_tmp_compra_fk` (`id_orden_comp`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp_compra` */

/*Table structure for table `tmp_nota` */

DROP TABLE IF EXISTS `tmp_nota`;

CREATE TABLE `tmp_nota` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `cod_compra` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp_nota` */

/*Table structure for table `tmp_orden` */

DROP TABLE IF EXISTS `tmp_orden`;

CREATE TABLE `tmp_orden` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_presupuesto` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_tmp_fk` (`id_presupuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp_orden` */

/*Table structure for table `tmp_presu` */

DROP TABLE IF EXISTS `tmp_presu`;

CREATE TABLE `tmp_presu` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `session_id` varchar(765) DEFAULT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_pedido_tmp_presu_fk` (`id_pedido`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp_presu` */

/*Table structure for table `tmp_venta` */

DROP TABLE IF EXISTS `tmp_venta`;

CREATE TABLE `tmp_venta` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `cantidad_tmp` int(11) NOT NULL,
  `precio_tmp` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tmp_venta` */

/*Table structure for table `u_medida` */

DROP TABLE IF EXISTS `u_medida`;

CREATE TABLE `u_medida` (
  `id_u_medida` int(11) NOT NULL,
  `u_descrip` varchar(20) NOT NULL,
  PRIMARY KEY (`id_u_medida`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `u_medida` */

insert  into `u_medida`(`id_u_medida`,`u_descrip`) values 
(1,'1 Litro'),
(2,'1/2 Litro');

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(150) DEFAULT NULL,
  `name_user` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(39) DEFAULT NULL,
  `foto` varchar(300) DEFAULT NULL,
  `permisos_acceso` varchar(300) DEFAULT NULL,
  `status` char(27) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_fecha` datetime DEFAULT NULL,
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `usuarios` */

insert  into `usuarios`(`id_user`,`username`,`name_user`,`password`,`email`,`telefono`,`foto`,`permisos_acceso`,`status`,`intentos_fallidos`,`bloqueado_fecha`) values 
(1,'aldo','Aldo Torres','$2y$10$fgRKLy5BNEK7plh7vHdiReQ1jw1hCSMjwfDBAP4XjieElx5dZ4INW','aldo28071987@gmail.com','0987264101','jefe_choto_2.jpg','Super Admin','activo',0,NULL),
(2,'Ucompras','Usuario de compras','0cc175b9c0f1b6a831c399e269772661','usuariocompras@gmail.com','0987654321','3135768.png','Compras','bloqueado',0,NULL),
(3,'Uventas','Usuario de ventas','0cc175b9c0f1b6a831c399e269772661','uventas@gmail.com','0123654789','3135768.png','Ventas','bloqueado',0,NULL),
(4,'AuxiliarC','UserCompra','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Compras','activo',0,NULL),
(5,'JefaC','UserJefaCompra','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Compras','activo',0,NULL),
(6,'JefaLocal','UserJefaLocal','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Produccion','activo',0,NULL),
(7,'Encargado Produccion','UserEncargadoProduccion','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Produccion','activo',0,NULL);

/*Table structure for table `venta` */

DROP TABLE IF EXISTS `venta`;

CREATE TABLE `venta` (
  `cod_venta` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(15) NOT NULL,
  `hora` time NOT NULL,
  `nro_factura` int(11) NOT NULL AUTO_INCREMENT,
  `id_timbrado` int(11) NOT NULL,
  PRIMARY KEY (`cod_venta`),
  KEY `clientes_venta_fk` (`id_cliente`),
  KEY `timb_venta_fk` (`id_timbrado`),
  KEY `nro_factura` (`nro_factura`)
) ENGINE=MyISAM AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `venta` */

insert  into `venta`(`cod_venta`,`id_cliente`,`id_user`,`fecha`,`estado`,`hora`,`nro_factura`,`id_timbrado`) values 
(1,2,1,'2025-01-11','anulado','14:02:49',1,1),
(2,1,1,'2025-01-12','activo','15:31:45',2,1),
(3,1,1,'2025-01-15','activo','19:36:03',3,1);

/* Trigger structure for table `ajuste_com` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_ajuste` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_ajuste` AFTER INSERT ON `ajuste_com` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */$$


DELIMITER ;

/* Trigger structure for table `compra` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_c` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_c` AFTER INSERT ON `compra` FOR EACH ROW BEGIN
   DELETE FROM tmp_compra;
    END */$$


DELIMITER ;

/* Trigger structure for table `nota_credito_debito` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trg_nota_credito_debito_ajuste` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `trg_nota_credito_debito_ajuste` AFTER INSERT ON `nota_credito_debito` FOR EACH ROW BEGIN
    DECLARE v_suma_cuotas DECIMAL(12,2) DEFAULT 0;
    DECLARE v_nuevo_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_cant_cuotas INT DEFAULT 0;

    
    SELECT SUM(cap.cap_monto), COUNT(*)
    INTO v_suma_cuotas, v_cant_cuotas
    FROM cuentas_a_pagar cap
    WHERE cap.cod_compra = NEW.cod_compra;

    
    IF v_cant_cuotas > 0 THEN

        
        IF NEW.tipo = 'CREDITO' THEN
            SET v_nuevo_total = v_suma_cuotas - NEW.monto_total;
        ELSEIF NEW.tipo = 'DEBITO' THEN
            SET v_nuevo_total = v_suma_cuotas + NEW.monto_total;
        END IF;

        
        CALL recalcular_cuotas(NEW.cod_compra, v_nuevo_total);

    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `nota_credito_debito` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trg_nota_credito_debito_anulacion` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `trg_nota_credito_debito_anulacion` AFTER UPDATE ON `nota_credito_debito` FOR EACH ROW BEGIN
    DECLARE v_suma_cuotas DECIMAL(12,2) DEFAULT 0;
    DECLARE v_nuevo_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_cant_cuotas INT DEFAULT 0;

    
    IF NEW.estado = 'ANULADO' AND OLD.estado <> 'ANULADO' THEN

        
        SELECT SUM(cap.cap_monto), COUNT(*)
        INTO v_suma_cuotas, v_cant_cuotas
        FROM cuentas_a_pagar cap
        WHERE cap.cod_compra = NEW.cod_compra;

        IF v_cant_cuotas > 0 THEN
            
            IF OLD.tipo = 'CREDITO' THEN
                SET v_nuevo_total = v_suma_cuotas + OLD.monto_total;
            ELSEIF OLD.tipo = 'DEBITO' THEN
                SET v_nuevo_total = v_suma_cuotas - OLD.monto_total;
            END IF;

            
            CALL recalcular_cuotas(NEW.cod_compra, v_nuevo_total);
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `orden_compra` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_orden` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_orden` AFTER INSERT ON `orden_compra` FOR EACH ROW BEGIN
   DELETE FROM tmp_orden;
    END */$$


DELIMITER ;

/* Trigger structure for table `pedido` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_p` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_p` AFTER INSERT ON `pedido` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */$$


DELIMITER ;

/* Trigger structure for table `pedido_v` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_p_v` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_p_v` AFTER INSERT ON `pedido_v` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */$$


DELIMITER ;

/* Trigger structure for table `presupuesto` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_presu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_presu` AFTER INSERT ON `presupuesto` FOR EACH ROW BEGIN
   DELETE FROM tmp_presu;
    END */$$


DELIMITER ;

/* Trigger structure for table `venta` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_v` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_v` AFTER INSERT ON `venta` FOR EACH ROW BEGIN
   DELETE FROM tmp_venta;
    END */$$


DELIMITER ;

/* Procedure structure for procedure `recalcular_cuotas` */

/*!50003 DROP PROCEDURE IF EXISTS  `recalcular_cuotas` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `recalcular_cuotas`(
    IN p_cod_compra INT,
    IN p_nuevo_total DECIMAL(12,2)
)
BEGIN
    DECLARE v_cant_cuotas INT DEFAULT 0;
    DECLARE v_monto_cuota DECIMAL(12,2);

    
    SELECT COUNT(*) INTO v_cant_cuotas
    FROM cuentas_a_pagar
    WHERE cod_compra = p_cod_compra;

    IF v_cant_cuotas > 0 THEN
        SET v_monto_cuota = p_nuevo_total / v_cant_cuotas;

        
        UPDATE cuentas_a_pagar
        SET cap_monto = v_monto_cuota,
            cap_saldo = v_monto_cuota
        WHERE cod_compra = p_cod_compra;
    END IF;
END */$$
DELIMITER ;

/*Table structure for table `v_ajuste` */

DROP TABLE IF EXISTS `v_ajuste`;

/*!50001 DROP VIEW IF EXISTS `v_ajuste` */;
/*!50001 DROP TABLE IF EXISTS `v_ajuste` */;

/*!50001 CREATE TABLE  `v_ajuste`(
 `id_ajuste` int(11) ,
 `fecha_ajuste` date ,
 `motivo` varchar(50) ,
 `estado` varchar(30) ,
 `anulado_por` int(11) ,
 `anulado_fecha` date ,
 `anulado_hora` time ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `cantidad_ajustada` int(11) ,
 `cantidad_anterior` int(11) 
)*/;

/*Table structure for table `v_clientes` */

DROP TABLE IF EXISTS `v_clientes`;

/*!50001 DROP VIEW IF EXISTS `v_clientes` */;
/*!50001 DROP TABLE IF EXISTS `v_clientes` */;

/*!50001 CREATE TABLE  `v_clientes`(
 `id_cliente` int(11) ,
 `ci_ruc` varchar(10) ,
 `cli_nombre` varchar(30) ,
 `cli_apellido` varchar(50) ,
 `cli_direccion` varchar(50) ,
 `cli_telefono` int(11) ,
 `cod_ciudad` int(11) ,
 `descrip_ciudad` varchar(25) ,
 `id_departamento` int(11) ,
 `dep_descripcion` varchar(35) 
)*/;

/*Table structure for table `v_compras` */

DROP TABLE IF EXISTS `v_compras`;

/*!50001 DROP VIEW IF EXISTS `v_compras` */;
/*!50001 DROP TABLE IF EXISTS `v_compras` */;

/*!50001 CREATE TABLE  `v_compras`(
 `cod_compra` int(11) ,
 `cod_proveedor` int(11) ,
 `razon_social` varchar(75) ,
 `ruc` varchar(9) ,
 `fecha` date ,
 `hora` time ,
 `estado` varchar(15) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `id_orden_comp` int(11) ,
 `con_sin_remision` varchar(30) ,
 `fac_numero` varchar(30) ,
 `fac_emision` date ,
 `tipo_factura` varchar(20) ,
 `timbrado_nro` int(11) ,
 `timb_fecha_venci` date ,
 `com_codicion` varchar(30) ,
 `total_compra` decimal(10,0) ,
 `anulado_por` int(11) ,
 `anulado_fecha` date ,
 `anulado_hora` time ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `precio` int(11) ,
 `cantidad` int(11) ,
 `tipo_iva` varchar(20) ,
 `exentas` decimal(12,2) ,
 `iva5` decimal(12,2) ,
 `iva10` decimal(12,2) 
)*/;

/*Table structure for table `v_cuentas` */

DROP TABLE IF EXISTS `v_cuentas`;

/*!50001 DROP VIEW IF EXISTS `v_cuentas` */;
/*!50001 DROP TABLE IF EXISTS `v_cuentas` */;

/*!50001 CREATE TABLE  `v_cuentas`(
 `cap_cod` int(11) ,
 `cod_compra` int(11) ,
 `cod_proveedor` int(11) ,
 `razon_social` varchar(75) ,
 `ruc` varchar(9) ,
 `nro_cuota` int(11) ,
 `cap_monto` decimal(10,0) ,
 `cap_saldo` decimal(10,0) ,
 `cap_fecha_venci` date ,
 `cap_estado` varchar(15) 
)*/;

/*Table structure for table `v_nota` */

DROP TABLE IF EXISTS `v_nota`;

/*!50001 DROP VIEW IF EXISTS `v_nota` */;
/*!50001 DROP TABLE IF EXISTS `v_nota` */;

/*!50001 CREATE TABLE  `v_nota`(
 `id_nota` bigint(20) unsigned ,
 `cod_compra` int(11) ,
 `fac_numero` varchar(30) ,
 `tipo` varchar(10) ,
 `causa` varchar(30) ,
 `nro_nota` varchar(50) ,
 `timbrado` varchar(50) ,
 `fecha_emision` date ,
 `estado` varchar(20) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `monto_nota` decimal(12,2) ,
 `observacion` varchar(200) ,
 `cod_proveedor` int(11) ,
 `razon_social` varchar(75) ,
 `ruc` varchar(9) ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `cantidad` decimal(12,2) ,
 `precio_unitario` decimal(12,2) ,
 `monto_total` decimal(24,4) ,
 `tipo_iva` int(11) ,
 `exentas` decimal(12,2) ,
 `iva5` decimal(12,2) ,
 `iva10` decimal(12,2) 
)*/;

/*Table structure for table `v_notar` */

DROP TABLE IF EXISTS `v_notar`;

/*!50001 DROP VIEW IF EXISTS `v_notar` */;
/*!50001 DROP TABLE IF EXISTS `v_notar` */;

/*!50001 CREATE TABLE  `v_notar`(
 `id_notaR` bigint(20) unsigned ,
 `cod_compra` int(11) ,
 `cod_proveedor` int(11) ,
 `ruc` varchar(9) ,
 `razon_social` varchar(75) ,
 `nro_nota` varchar(20) ,
 `fecha` date ,
 `fecha_ini_traslado` date ,
 `tipo_traslado` varchar(20) ,
 `motivo_traslado` varchar(30) ,
 `nom_transporte` varchar(50) ,
 `ruc_ci_trans` varchar(10) ,
 `placa_vehiculo` varchar(30) ,
 `id_user` int(11) ,
 `estado` varchar(10) ,
 `anulado_por` int(11) ,
 `anulado_fecha` date ,
 `anulado_hora` time ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `cantidad` int(11) 
)*/;

/*Table structure for table `v_orden_comp` */

DROP TABLE IF EXISTS `v_orden_comp`;

/*!50001 DROP VIEW IF EXISTS `v_orden_comp` */;
/*!50001 DROP TABLE IF EXISTS `v_orden_comp` */;

/*!50001 CREATE TABLE  `v_orden_comp`(
 `id_orden_comp` int(11) ,
 `fecha` date ,
 `estado` varchar(30) ,
 `hora` time ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `id_presupuesto` int(11) ,
 `cod_proveedor` int(11) ,
 `razon_social` varchar(75) ,
 `ruc` varchar(9) ,
 `precio_unit` int(11) ,
 `cantidad` int(11) 
)*/;

/*Table structure for table `v_pedido` */

DROP TABLE IF EXISTS `v_pedido`;

/*!50001 DROP VIEW IF EXISTS `v_pedido` */;
/*!50001 DROP TABLE IF EXISTS `v_pedido` */;

/*!50001 CREATE TABLE  `v_pedido`(
 `id_pedido` int(11) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `fecha` date ,
 `hora` time ,
 `estado` varchar(30) ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `cantidad` int(11) 
)*/;

/*Table structure for table `v_pedido_v` */

DROP TABLE IF EXISTS `v_pedido_v`;

/*!50001 DROP VIEW IF EXISTS `v_pedido_v` */;
/*!50001 DROP TABLE IF EXISTS `v_pedido_v` */;

/*!50001 CREATE TABLE  `v_pedido_v`(
 `id_pedido_v` int(11) ,
 `fecha_pedido` date ,
 `hora` time ,
 `estado` varchar(20) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `cod_deposito` int(11) ,
 `descrip` varchar(50) ,
 `cantidad` int(11) 
)*/;

/*Table structure for table `v_presu` */

DROP TABLE IF EXISTS `v_presu`;

/*!50001 DROP VIEW IF EXISTS `v_presu` */;
/*!50001 DROP TABLE IF EXISTS `v_presu` */;

/*!50001 CREATE TABLE  `v_presu`(
 `id_presupuesto` int(11) ,
 `id_pedido` int(11) ,
 `cod_proveedor` int(11) ,
 `razon_social` varchar(75) ,
 `ruc` varchar(9) ,
 `fecha_presu` date ,
 `fecha_vencimiento` date ,
 `estado` varchar(20) ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cantidad` int(11) ,
 `precio_unit` int(11) ,
 `id_user` int(11) ,
 `username` varchar(150) 
)*/;

/*Table structure for table `v_producto` */

DROP TABLE IF EXISTS `v_producto`;

/*!50001 DROP VIEW IF EXISTS `v_producto` */;
/*!50001 DROP TABLE IF EXISTS `v_producto` */;

/*!50001 CREATE TABLE  `v_producto`(
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `tipo_impuesto` varchar(10) 
)*/;

/*Table structure for table `v_stock` */

DROP TABLE IF EXISTS `v_stock`;

/*!50001 DROP VIEW IF EXISTS `v_stock` */;
/*!50001 DROP TABLE IF EXISTS `v_stock` */;

/*!50001 CREATE TABLE  `v_stock`(
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `t_p_descrip` varchar(50) ,
 `u_descrip` varchar(20) ,
 `cantidad` int(11) 
)*/;

/*Table structure for table `v_ventas` */

DROP TABLE IF EXISTS `v_ventas`;

/*!50001 DROP VIEW IF EXISTS `v_ventas` */;
/*!50001 DROP TABLE IF EXISTS `v_ventas` */;

/*!50001 CREATE TABLE  `v_ventas`(
 `cod_venta` int(11) ,
 `id_cliente` int(11) ,
 `cli_nombre` varchar(30) ,
 `cli_apellido` varchar(50) ,
 `id_user` int(11) ,
 `name_user` varchar(150) ,
 `fecha` date ,
 `estado` varchar(15) ,
 `hora` time ,
 `cod_producto` int(11) ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int(11) ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int(11) ,
 `u_descrip` varchar(20) ,
 `det_precio_unit` int(11) ,
 `det_cantidad` int(11) 
)*/;

/*View structure for view v_ajuste */

/*!50001 DROP TABLE IF EXISTS `v_ajuste` */;
/*!50001 DROP VIEW IF EXISTS `v_ajuste` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ajuste` AS select `aju`.`id_ajuste` AS `id_ajuste`,`aju`.`fecha_ajuste` AS `fecha_ajuste`,`aju`.`motivo` AS `motivo`,`aju`.`estado` AS `estado`,`aju`.`anulado_por` AS `anulado_por`,`aju`.`anulado_fecha` AS `anulado_fecha`,`aju`.`anulado_hora` AS `anulado_hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`det`.`cantidad_ajustada` AS `cantidad_ajustada`,`det`.`cantidad_anterior` AS `cantidad_anterior` from (((((`ajuste_com` `aju` join `det_ajuste` `det`) join `producto` `pro`) join `usuarios` `usu`) join `tipo_producto` `tp`) join `u_medida` `u`) where `aju`.`id_ajuste` = `det`.`id_ajuste` and `det`.`cod_producto` = `pro`.`cod_producto` and `aju`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;

/*View structure for view v_clientes */

/*!50001 DROP TABLE IF EXISTS `v_clientes` */;
/*!50001 DROP VIEW IF EXISTS `v_clientes` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clientes` AS select `cli`.`id_cliente` AS `id_cliente`,`cli`.`ci_ruc` AS `ci_ruc`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`cli`.`cli_direccion` AS `cli_direccion`,`cli`.`cli_telefono` AS `cli_telefono`,`ciu`.`cod_ciudad` AS `cod_ciudad`,`ciu`.`descrip_ciudad` AS `descrip_ciudad`,`dep`.`id_departamento` AS `id_departamento`,`dep`.`dep_descripcion` AS `dep_descripcion` from ((`clientes` `cli` join `departamento` `dep`) join `ciudad` `ciu`) where `cli`.`cod_ciudad` = `ciu`.`cod_ciudad` and `ciu`.`id_departamento` = `dep`.`id_departamento` */;

/*View structure for view v_compras */

/*!50001 DROP TABLE IF EXISTS `v_compras` */;
/*!50001 DROP VIEW IF EXISTS `v_compras` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_compras` AS select `com`.`cod_compra` AS `cod_compra`,`com`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`com`.`fecha` AS `fecha`,`com`.`hora` AS `hora`,`com`.`estado` AS `estado`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`com`.`id_orden_comp` AS `id_orden_comp`,`com`.`con_sin_remision` AS `con_sin_remision`,`com`.`fac_numero` AS `fac_numero`,`com`.`fac_emision` AS `fac_emision`,`com`.`tipo_factura` AS `tipo_factura`,`com`.`timbrado_nro` AS `timbrado_nro`,`com`.`timb_fecha_venci` AS `timb_fecha_venci`,`com`.`com_condicion` AS `com_codicion`,`com`.`total_compra` AS `total_compra`,`com`.`anulado_por` AS `anulado_por`,`com`.`anulado_fecha` AS `anulado_fecha`,`com`.`anulado_hora` AS `anulado_hora`,`det`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`precio` AS `precio`,`det`.`cantidad` AS `cantidad`,`det`.`tipo_iva` AS `tipo_iva`,`det`.`exentas` AS `exentas`,`det`.`iva5` AS `iva5`,`det`.`iva10` AS `iva10` from ((((((`compra` `com` left join `detalle_compra` `det` on(`det`.`cod_compra` = `com`.`cod_compra`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `proveedor` `prov` on(`prov`.`cod_proveedor` = `com`.`cod_proveedor`)) join `usuarios` `usu` on(`com`.`id_user` = `usu`.`id_user`)) left join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) left join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_cuentas */

/*!50001 DROP TABLE IF EXISTS `v_cuentas` */;
/*!50001 DROP VIEW IF EXISTS `v_cuentas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cuentas` AS select `cap`.`cap_cod` AS `cap_cod`,`com`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`cap`.`nro_cuota` AS `nro_cuota`,`cap`.`cap_monto` AS `cap_monto`,`cap`.`cap_saldo` AS `cap_saldo`,`cap`.`cap_fecha_venci` AS `cap_fecha_venci`,`cap`.`cap_estado` AS `cap_estado` from ((`cuentas_a_pagar` `cap` join `compra` `com`) join `proveedor` `prov`) where `cap`.`cod_compra` = `com`.`cod_compra` and `com`.`cod_proveedor` = `prov`.`cod_proveedor` */;

/*View structure for view v_nota */

/*!50001 DROP TABLE IF EXISTS `v_nota` */;
/*!50001 DROP VIEW IF EXISTS `v_nota` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_nota` AS select `nota`.`id_nota` AS `id_nota`,`com`.`cod_compra` AS `cod_compra`,`nota`.`fac_numero` AS `fac_numero`,`nota`.`tipo` AS `tipo`,`nota`.`causa` AS `causa`,`nota`.`nro_nota` AS `nro_nota`,`nota`.`timbrado` AS `timbrado`,`nota`.`fecha_emision` AS `fecha_emision`,`nota`.`estado` AS `estado`,`nota`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`nota`.`monto_total` AS `monto_nota`,`nota`.`observacion` AS `observacion`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`det`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`pro`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad`,`det`.`precio_unitario` AS `precio_unitario`,`det`.`cantidad` * `det`.`precio_unitario` AS `monto_total`,`det`.`tipo_iva` AS `tipo_iva`,`det`.`exentas` AS `exentas`,`det`.`iva5` AS `iva5`,`det`.`iva10` AS `iva10` from (((((((`nota_credito_debito` `nota` left join `det_nota_credit_debit` `det` on(`nota`.`id_nota` = `det`.`id_nota`)) join `compra` `com` on(`nota`.`cod_compra` = `com`.`cod_compra`)) join `proveedor` `prov` on(`com`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `usuarios` `usu` on(`nota`.`id_user` = `usu`.`id_user`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) left join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) left join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) */;

/*View structure for view v_notar */

/*!50001 DROP TABLE IF EXISTS `v_notar` */;
/*!50001 DROP VIEW IF EXISTS `v_notar` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_notar` AS select `n`.`id_notaR` AS `id_notaR`,`c`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`ruc` AS `ruc`,`prov`.`razon_social` AS `razon_social`,`n`.`nro_nota` AS `nro_nota`,`n`.`fecha` AS `fecha`,`n`.`fecha_ini_traslado` AS `fecha_ini_traslado`,`n`.`tipo_traslado` AS `tipo_traslado`,`n`.`motivo_traslado` AS `motivo_traslado`,`n`.`nom_transporte` AS `nom_transporte`,`n`.`ruc_ci_trans` AS `ruc_ci_trans`,`n`.`placa_vehiculo` AS `placa_vehiculo`,`n`.`id_user` AS `id_user`,`n`.`estado` AS `estado`,`n`.`anulado_por` AS `anulado_por`,`n`.`anulado_fecha` AS `anulado_fecha`,`n`.`anulado_hora` AS `anulado_hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad` from (((((`notar_compra` `n` join `det_notar_compra` `det` on(`det`.`id_notaR` = `n`.`id_notaR`)) join `compra` `c` on(`c`.`cod_compra` = `n`.`cod_compra`)) join `proveedor` `prov` on(`c`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `u_medida` `u` on(`u`.`id_u_medida` = `pro`.`id_u_medida`)) */;

/*View structure for view v_orden_comp */

/*!50001 DROP TABLE IF EXISTS `v_orden_comp` */;
/*!50001 DROP VIEW IF EXISTS `v_orden_comp` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_orden_comp` AS select `orde`.`id_orden_comp` AS `id_orden_comp`,`orde`.`fecha` AS `fecha`,`orde`.`estado` AS `estado`,`orde`.`hora` AS `hora`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`presu`.`id_presupuesto` AS `id_presupuesto`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`det`.`precio_unit` AS `precio_unit`,`det`.`cantidad` AS `cantidad` from (((((((`orden_compra` `orde` join `detalle_orden_comp` `det` on(`orde`.`id_orden_comp` = `det`.`id_orden_comp`)) join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `usuarios` `usu` on(`orde`.`id_user` = `usu`.`id_user`)) left join `presupuesto` `presu` on(`orde`.`id_presupuesto` = `presu`.`id_presupuesto`)) join `proveedor` `prov` on(`orde`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_pedido */

/*!50001 DROP TABLE IF EXISTS `v_pedido` */;
/*!50001 DROP VIEW IF EXISTS `v_pedido` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido` AS select `pe`.`id_pedido` AS `id_pedido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pe`.`fecha` AS `fecha`,`pe`.`hora` AS `hora`,`pe`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad` from (((((`pedido` `pe` join `det_pedido` `det`) join `usuarios` `usu`) join `producto` `pro`) join `tipo_producto` `tp`) join `u_medida` `u`) where `pe`.`id_user` = `usu`.`id_user` and `pe`.`id_pedido` = `det`.`id_pedido` and `det`.`cod_producto` = `pro`.`cod_producto` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;

/*View structure for view v_pedido_v */

/*!50001 DROP TABLE IF EXISTS `v_pedido_v` */;
/*!50001 DROP VIEW IF EXISTS `v_pedido_v` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido_v` AS select `p`.`id_pedido_v` AS `id_pedido_v`,`p`.`fecha_pedido` AS `fecha_pedido`,`p`.`hora` AS `hora`,`p`.`estado` AS `estado`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`dep`.`cod_deposito` AS `cod_deposito`,`dep`.`descrip` AS `descrip`,`det`.`cantidad` AS `cantidad` from ((((((`pedido_v` `p` join `det_pedido_v` `det`) join `usuarios` `usu`) join `producto` `pro`) join `deposito` `dep`) join `tipo_producto` `tp`) join `u_medida` `u`) where `p`.`id_pedido_v` = `det`.`id_pedido_v` and `det`.`cod_producto` = `pro`.`cod_producto` and `det`.`cod_deposito` = `dep`.`cod_deposito` and `p`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;

/*View structure for view v_presu */

/*!50001 DROP TABLE IF EXISTS `v_presu` */;
/*!50001 DROP VIEW IF EXISTS `v_presu` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_presu` AS select `pre`.`id_presupuesto` AS `id_presupuesto`,`ped`.`id_pedido` AS `id_pedido`,`prv`.`cod_proveedor` AS `cod_proveedor`,`prv`.`razon_social` AS `razon_social`,`prv`.`ruc` AS `ruc`,`pre`.`fecha_presu` AS `fecha_presu`,`pre`.`fecha_vencimiento` AS `fecha_vencimiento`,`pre`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`det`.`cantidad` AS `cantidad`,`det`.`precio_unit` AS `precio_unit`,`usu`.`id_user` AS `id_user`,`usu`.`username` AS `username` from (((((`presupuesto` `pre` join `proveedor` `prv` on(`pre`.`cod_proveedor` = `prv`.`cod_proveedor`)) join `det_presu` `det` on(`pre`.`id_presupuesto` = `det`.`id_presupuesto`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) left join `pedido` `ped` on(`pre`.`id_pedido` = `ped`.`id_pedido`)) join `usuarios` `usu` on(`pre`.`id_user` = `usu`.`id_user`)) */;

/*View structure for view v_producto */

/*!50001 DROP TABLE IF EXISTS `v_producto` */;
/*!50001 DROP VIEW IF EXISTS `v_producto` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_producto` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`um`.`id_u_medida` AS `id_u_medida`,`um`.`u_descrip` AS `u_descrip`,`pro`.`tipo_impuesto` AS `tipo_impuesto` from ((`producto` `pro` join `tipo_producto` `tp`) join `u_medida` `um`) where `tp`.`cod_tipo_prod` = `pro`.`cod_tipo_prod` and `um`.`id_u_medida` = `pro`.`id_u_medida` */;

/*View structure for view v_stock */

/*!50001 DROP TABLE IF EXISTS `v_stock` */;
/*!50001 DROP VIEW IF EXISTS `v_stock` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tpro`.`t_p_descrip` AS `t_p_descrip`,`um`.`u_descrip` AS `u_descrip`,`st`.`cantidad` AS `cantidad` from (((`stock_prod` `st` join `producto` `pro`) join `tipo_producto` `tpro`) join `u_medida` `um`) where `st`.`cod_producto` = `pro`.`cod_producto` and `pro`.`cod_tipo_prod` = `tpro`.`cod_tipo_prod` and `pro`.`id_u_medida` = `um`.`id_u_medida` */;

/*View structure for view v_ventas */

/*!50001 DROP TABLE IF EXISTS `v_ventas` */;
/*!50001 DROP VIEW IF EXISTS `v_ventas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas` AS select `v`.`cod_venta` AS `cod_venta`,`cli`.`id_cliente` AS `id_cliente`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`v`.`fecha` AS `fecha`,`v`.`estado` AS `estado`,`v`.`hora` AS `hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`det_precio_unit` AS `det_precio_unit`,`det`.`det_cantidad` AS `det_cantidad` from ((((((`venta` `v` join `det_venta` `det`) join `producto` `pro`) join `usuarios` `usu`) join `clientes` `cli`) join `tipo_producto` `tp`) join `u_medida` `u`) where `v`.`cod_venta` = `det`.`cod_venta` and `v`.`id_cliente` = `cli`.`id_cliente` and `det`.`cod_producto` = `pro`.`cod_producto` and `v`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
