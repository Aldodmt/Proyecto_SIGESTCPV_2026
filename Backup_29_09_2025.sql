/*
SQLyog Community v12.5.1 (64 bit)
MySQL - 8.3.0 : Database - sysweb
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sysweb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sysweb`;

/*Table structure for table `ajuste_com` */

DROP TABLE IF EXISTS `ajuste_com`;

CREATE TABLE `ajuste_com` (
  `id_ajuste` int NOT NULL,
  `fecha_ajuste` date NOT NULL,
  `motivo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estado` varchar(30) NOT NULL,
  PRIMARY KEY (`id_ajuste`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `ajuste_com` */

/*Table structure for table `ciudad` */

DROP TABLE IF EXISTS `ciudad`;

CREATE TABLE `ciudad` (
  `cod_ciudad` int NOT NULL,
  `descrip_ciudad` varchar(25) DEFAULT NULL,
  `id_departamento` int NOT NULL,
  PRIMARY KEY (`cod_ciudad`),
  KEY `id_departamento` (`id_departamento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `id_cliente` int NOT NULL,
  `cod_ciudad` int DEFAULT NULL,
  `ci_ruc` varchar(10) NOT NULL,
  `cli_nombre` varchar(30) NOT NULL,
  `cli_apellido` varchar(50) NOT NULL,
  `cli_direccion` varchar(50) DEFAULT NULL,
  `cli_telefono` int DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `clientes_cod_ciudad_fkey` (`cod_ciudad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `clientes` */

insert  into `clientes`(`id_cliente`,`cod_ciudad`,`ci_ruc`,`cli_nombre`,`cli_apellido`,`cli_direccion`,`cli_telefono`) values 
(1,1,'5629997','Carlos','Ortiz','Capiata km27',976524098),
(2,2,'5628992','Aldo','Marin','Barrio san Agustin',992356262),
(3,4,'5863952','Adrian','Gimenez','Calle B',993626538);

/*Table structure for table `compra` */

DROP TABLE IF EXISTS `compra`;

CREATE TABLE `compra` (
  `cod_compra` int NOT NULL,
  `cod_proveedor` int NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(15) NOT NULL,
  `hora` time NOT NULL,
  `id_user` int NOT NULL,
  `id_orden_comp` int NOT NULL,
  PRIMARY KEY (`cod_compra`),
  KEY `cod_proveedor` (`cod_proveedor`),
  KEY `id_orden_compra_compra_fk` (`id_orden_comp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `compra` */

/*Table structure for table `cuentas_a_pagar` */

DROP TABLE IF EXISTS `cuentas_a_pagar`;

CREATE TABLE `cuentas_a_pagar` (
  `id_cuenta` int NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` varchar(20) NOT NULL,
  `cod_compra` int NOT NULL,
  PRIMARY KEY (`id_cuenta`),
  KEY `cod_compra_cuenta_fk` (`cod_compra`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `cuentas_a_pagar` */

/*Table structure for table `departamento` */

DROP TABLE IF EXISTS `departamento`;

CREATE TABLE `departamento` (
  `id_departamento` int NOT NULL,
  `dep_descripcion` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id_departamento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `departamento` */

insert  into `departamento`(`id_departamento`,`dep_descripcion`) values 
(1,'Central'),
(2,'Alto Paraná'),
(3,'Misiones');

/*Table structure for table `deposito` */

DROP TABLE IF EXISTS `deposito`;

CREATE TABLE `deposito` (
  `cod_deposito` int NOT NULL,
  `descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `deposito` */

insert  into `deposito`(`cod_deposito`,`descrip`) values 
(1,'Depo Central'),
(2,'Depo 2');

/*Table structure for table `det_ajuste` */

DROP TABLE IF EXISTS `det_ajuste`;

CREATE TABLE `det_ajuste` (
  `id_ajuste` int NOT NULL,
  `cod_producto` int NOT NULL,
  `id_user` int NOT NULL,
  `cantidad_ajustada` int NOT NULL,
  `cantidad_anterior` int NOT NULL,
  `cod_deposito` int NOT NULL,
  PRIMARY KEY (`id_ajuste`,`cod_producto`,`id_user`),
  KEY `usuarios_det_ajuste_fk` (`id_user`),
  KEY `producto_det_ajuste_fk` (`cod_producto`),
  KEY `depo_det_ajuste_fk` (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_ajuste` */

/*Table structure for table `det_cuenta_a_pagar` */

DROP TABLE IF EXISTS `det_cuenta_a_pagar`;

CREATE TABLE `det_cuenta_a_pagar` (
  `id_cuenta` int NOT NULL,
  `monto_total` int NOT NULL,
  `monto_pagado` int NOT NULL,
  PRIMARY KEY (`id_cuenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_cuenta_a_pagar` */

/*Table structure for table `det_nota_credit_debit` */

DROP TABLE IF EXISTS `det_nota_credit_debit`;

CREATE TABLE `det_nota_credit_debit` (
  `id_nota` int NOT NULL,
  `cod_proveedor` int NOT NULL,
  `monto` int NOT NULL,
  `razon` varchar(50) NOT NULL,
  `cod_producto` int NOT NULL,
  `cod_deposito` int NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`id_nota`,`cod_proveedor`),
  KEY `proveedor_det_nota_credit_debit_fk` (`cod_proveedor`),
  KEY `producto_nota_det_fk` (`cod_producto`),
  KEY `deposito_nota_det_fk` (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_nota_credit_debit` */

/*Table structure for table `det_pedido` */

DROP TABLE IF EXISTS `det_pedido`;

CREATE TABLE `det_pedido` (
  `cod_producto` int NOT NULL,
  `id_pedido` int NOT NULL,
  `cantidad` int NOT NULL,
  KEY `pedido_det_pedido_fk` (`id_pedido`),
  KEY `producto_det_pedido_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_pedido` */

/*Table structure for table `det_pedido_v` */

DROP TABLE IF EXISTS `det_pedido_v`;

CREATE TABLE `det_pedido_v` (
  `id_pedido_v` int NOT NULL,
  `cod_producto` int NOT NULL,
  `cod_deposito` int NOT NULL,
  `cantidad` int NOT NULL,
  KEY `pedido_v_det_fk` (`id_pedido_v`),
  KEY `producto_det_v_fk` (`cod_producto`),
  KEY `depositodet_v_fk` (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_pedido_v` */

insert  into `det_pedido_v`(`id_pedido_v`,`cod_producto`,`cod_deposito`,`cantidad`) values 
(2,1,1,12),
(1,1,1,13),
(3,1,1,1),
(4,1,1,10);

/*Table structure for table `det_presu` */

DROP TABLE IF EXISTS `det_presu`;

CREATE TABLE `det_presu` (
  `id_presupuesto` int NOT NULL,
  `cod_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unit` int NOT NULL,
  KEY `producto_det_presu_fk` (`cod_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_presu` */

/*Table structure for table `det_venta` */

DROP TABLE IF EXISTS `det_venta`;

CREATE TABLE `det_venta` (
  `cod_producto` int NOT NULL,
  `cod_venta` int NOT NULL,
  `cod_deposito` int NOT NULL,
  `det_precio_unit` int NOT NULL,
  `det_cantidad` int NOT NULL,
  PRIMARY KEY (`cod_venta`),
  KEY `deposito_det_venta_fk` (`cod_deposito`),
  KEY `venta_det_venta_fk` (`cod_venta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `det_venta` */

insert  into `det_venta`(`cod_producto`,`cod_venta`,`cod_deposito`,`det_precio_unit`,`det_cantidad`) values 
(1,2,1,10400,1),
(1,1,1,10400,10),
(1,3,1,10400,5);

/*Table structure for table `detalle_compra` */

DROP TABLE IF EXISTS `detalle_compra`;

CREATE TABLE `detalle_compra` (
  `cod_producto` int NOT NULL,
  `cod_compra` int NOT NULL,
  `precio` int NOT NULL,
  `cantidad` int NOT NULL,
  KEY `compra_detalle_compra_fk` (`cod_compra`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `detalle_compra` */

/*Table structure for table `detalle_orden_comp` */

DROP TABLE IF EXISTS `detalle_orden_comp`;

CREATE TABLE `detalle_orden_comp` (
  `id_orden_comp` int NOT NULL,
  `cod_producto` int NOT NULL,
  `precio_unit` int NOT NULL,
  `cantidad_aprobada` int NOT NULL,
  KEY `orden_compra_det_orden_comp_fk` (`id_orden_comp`),
  KEY `cod_producto_detalle_orden_compra_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `detalle_orden_comp` */

/*Table structure for table `factura_compra` */

DROP TABLE IF EXISTS `factura_compra`;

CREATE TABLE `factura_compra` (
  `fac_cod` bigint unsigned NOT NULL AUTO_INCREMENT,
  `com_cod` int NOT NULL,
  `fac_numero` varchar(30) NOT NULL,
  `fac_fecha` date NOT NULL,
  `fac_vencimiento` date DEFAULT NULL,
  `fac_total` decimal(12,2) NOT NULL,
  `fac_exentas` decimal(12,2) DEFAULT '0.00',
  `fac_iva5` decimal(12,2) DEFAULT '0.00',
  `fac_iva10` decimal(12,2) DEFAULT '0.00',
  `fac_estado` varchar(15) NOT NULL,
  `tim_cod` int NOT NULL,
  PRIMARY KEY (`fac_cod`),
  UNIQUE KEY `fac_cod` (`fac_cod`),
  KEY `fk_compra` (`com_cod`),
  KEY `fk_timbrado` (`tim_cod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `factura_compra` */

/*Table structure for table `nota_credito_debito` */

DROP TABLE IF EXISTS `nota_credito_debito`;

CREATE TABLE `nota_credito_debito` (
  `id_nota` int NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fecha_emision` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `cod_compra` int NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id_nota`),
  KEY `cod_compra_nota_fk` (`cod_compra`),
  KEY `usu_nota_fk` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `nota_credito_debito` */

/*Table structure for table `orden_compra` */

DROP TABLE IF EXISTS `orden_compra`;

CREATE TABLE `orden_compra` (
  `id_orden_comp` int NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `hora` time NOT NULL,
  `id_user` int NOT NULL,
  `id_presupuesto` int NOT NULL,
  PRIMARY KEY (`id_orden_comp`),
  KEY `id_user_orden_fk` (`id_user`),
  KEY `id_presu_orden_comp_fk` (`id_presupuesto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `orden_compra` */

/*Table structure for table `pedido` */

DROP TABLE IF EXISTS `pedido`;

CREATE TABLE `pedido` (
  `id_pedido` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `hora` time NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `id_user_pedido_fk` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `pedido` */

insert  into `pedido`(`id_pedido`,`fecha`,`estado`,`hora`,`id_user`) values 
(8,'2025-09-29','Pendiente','14:17:26',1),
(7,'2025-09-29','Pendiente','14:17:12',1),
(6,'2025-09-29','Pendiente','14:16:51',1);

/*Table structure for table `pedido_v` */

DROP TABLE IF EXISTS `pedido_v`;

CREATE TABLE `pedido_v` (
  `id_pedido_v` int NOT NULL,
  `fecha_pedido` date NOT NULL,
  `hora` time NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id_pedido_v`),
  KEY `id_user_pedido_v_fk` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `pedido_v` */

insert  into `pedido_v`(`id_pedido_v`,`fecha_pedido`,`hora`,`estado`,`id_user`) values 
(2,'2025-01-05','15:48:05','aprobado',1),
(1,'2025-01-02','20:00:11','aprobado',1),
(3,'2025-01-12','15:29:32','pendiente',1),
(4,'2025-01-15','19:35:41','aprobado',1);

/*Table structure for table `presupuesto` */

DROP TABLE IF EXISTS `presupuesto`;

CREATE TABLE `presupuesto` (
  `id_presupuesto` int NOT NULL,
  `fecha_presu` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `cod_proveedor` int NOT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_pedido` int NOT NULL,
  PRIMARY KEY (`id_presupuesto`,`cod_proveedor`),
  KEY `proveedor_presu_prov_fk` (`cod_proveedor`),
  KEY `id_pedido_presu_fk` (`id_pedido`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `presupuesto` */

/*Table structure for table `producto` */

DROP TABLE IF EXISTS `producto`;

CREATE TABLE `producto` (
  `cod_producto` int NOT NULL,
  `cod_tipo_prod` int NOT NULL,
  `id_u_medida` int NOT NULL,
  `p_descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_producto`),
  KEY `tipo_producto_producto_fk` (`cod_tipo_prod`),
  KEY `u_medida_producto_fk` (`id_u_medida`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `producto` */

insert  into `producto`(`cod_producto`,`cod_tipo_prod`,`id_u_medida`,`p_descrip`) values 
(1,2,1,'Vaso Plastico 2x ');

/*Table structure for table `proveedor` */

DROP TABLE IF EXISTS `proveedor`;

CREATE TABLE `proveedor` (
  `cod_proveedor` int NOT NULL,
  `razon_social` varchar(75) NOT NULL,
  `ruc` varchar(9) NOT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `telefono` int NOT NULL,
  PRIMARY KEY (`cod_proveedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `proveedor` */

insert  into `proveedor`(`cod_proveedor`,`razon_social`,`ruc`,`direccion`,`telefono`) values 
(1,'Empresa de lacteos','5892365','Calle X',985361242),
(2,'Cervepar','80023269','Calle D',982555623);

/*Table structure for table `stock` */

DROP TABLE IF EXISTS `stock`;

CREATE TABLE `stock` (
  `cod_deposito` int NOT NULL,
  `cod_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`cod_deposito`,`cod_producto`),
  KEY `producto_stock_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `stock` */

insert  into `stock`(`cod_deposito`,`cod_producto`,`cantidad`) values 
(1,1,93),
(1,2,12);

/*Table structure for table `timbrado_compra` */

DROP TABLE IF EXISTS `timbrado_compra`;

CREATE TABLE `timbrado_compra` (
  `tim_cod` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prov_cod` int NOT NULL,
  `tim_numero` varchar(20) NOT NULL,
  `tim_fecha_ini` date NOT NULL,
  `tim_fecha_fin` date NOT NULL,
  `tim_estado` varchar(15) NOT NULL,
  PRIMARY KEY (`tim_cod`),
  UNIQUE KEY `tim_cod` (`tim_cod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `timbrado_compra` */

/*Table structure for table `tipo_producto` */

DROP TABLE IF EXISTS `tipo_producto`;

CREATE TABLE `tipo_producto` (
  `cod_tipo_prod` int NOT NULL,
  `t_p_descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_tipo_prod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tipo_producto` */

insert  into `tipo_producto`(`cod_tipo_prod`,`t_p_descrip`) values 
(1,'Lacteos'),
(2,'Bebidas');

/*Table structure for table `tmp` */

DROP TABLE IF EXISTS `tmp`;

CREATE TABLE `tmp` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `id_producto` int DEFAULT NULL,
  `cantidad_tmp` int DEFAULT NULL,
  `session_id` varchar(765) DEFAULT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp` */

/*Table structure for table `tmp_compra` */

DROP TABLE IF EXISTS `tmp_compra`;

CREATE TABLE `tmp_compra` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `id_orden_comp` int NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_comp_tmp_compra_fk` (`id_orden_comp`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp_compra` */

/*Table structure for table `tmp_nota` */

DROP TABLE IF EXISTS `tmp_nota`;

CREATE TABLE `tmp_nota` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `cod_compra` int NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp_nota` */

/*Table structure for table `tmp_orden` */

DROP TABLE IF EXISTS `tmp_orden`;

CREATE TABLE `tmp_orden` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `id_presupuesto` int NOT NULL,
  `session_id` varchar(765) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_tmp_fk` (`id_presupuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp_orden` */

/*Table structure for table `tmp_presu` */

DROP TABLE IF EXISTS `tmp_presu`;

CREATE TABLE `tmp_presu` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int DEFAULT NULL,
  `session_id` varchar(765) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_pedido_tmp_presu_fk` (`id_pedido`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp_presu` */

/*Table structure for table `tmp_venta` */

DROP TABLE IF EXISTS `tmp_venta`;

CREATE TABLE `tmp_venta` (
  `id_tmp` int NOT NULL AUTO_INCREMENT,
  `cod_producto` int NOT NULL,
  `cantidad_tmp` int NOT NULL,
  `precio_tmp` int NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `tmp_venta` */

/*Table structure for table `u_medida` */

DROP TABLE IF EXISTS `u_medida`;

CREATE TABLE `u_medida` (
  `id_u_medida` int NOT NULL,
  `u_descrip` varchar(20) NOT NULL,
  PRIMARY KEY (`id_u_medida`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `u_medida` */

insert  into `u_medida`(`id_u_medida`,`u_descrip`) values 
(1,'1 Litro'),
(2,'1/2 Litro');

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(150) DEFAULT NULL,
  `name_user` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(39) DEFAULT NULL,
  `foto` varchar(300) DEFAULT NULL,
  `permisos_acceso` varchar(300) DEFAULT NULL,
  `status` char(27) DEFAULT NULL,
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `usuarios` */

insert  into `usuarios`(`id_user`,`username`,`name_user`,`password`,`email`,`telefono`,`foto`,`permisos_acceso`,`status`) values 
(1,'aldo','Aldo Torres','202cb962ac59075b964b07152d234b70','aldo28071987@gmail.com','0987264101','jefe_choto_2.jpg','Super Admin','activo'),
(2,'Ucompras','Usuario de compras','0cc175b9c0f1b6a831c399e269772661','usuariocompras@gmail.com','0987654321','3135768.png','Compras','activo'),
(3,'Uventas','Usuario de ventas','0cc175b9c0f1b6a831c399e269772661','uventas@gmail.com','0123654789','3135768.png','Ventas','activo');

/*Table structure for table `venta` */

DROP TABLE IF EXISTS `venta`;

CREATE TABLE `venta` (
  `cod_venta` int NOT NULL,
  `id_cliente` int NOT NULL,
  `id_user` int NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(15) NOT NULL,
  `hora` time NOT NULL,
  `nro_factura` int NOT NULL AUTO_INCREMENT,
  `id_timbrado` int NOT NULL,
  PRIMARY KEY (`cod_venta`),
  KEY `clientes_venta_fk` (`id_cliente`),
  KEY `timb_venta_fk` (`id_timbrado`),
  KEY `nro_factura` (`nro_factura`)
) ENGINE=MyISAM AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `borrar_tmp_nota` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `borrar_tmp_nota` AFTER INSERT ON `nota_credito_debito` FOR EACH ROW BEGIN
   DELETE FROM tmp_nota;
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

/*Table structure for table `v_clientes` */

DROP TABLE IF EXISTS `v_clientes`;

/*!50001 DROP VIEW IF EXISTS `v_clientes` */;
/*!50001 DROP TABLE IF EXISTS `v_clientes` */;

/*!50001 CREATE TABLE  `v_clientes`(
 `id_cliente` int ,
 `ci_ruc` varchar(10) ,
 `cli_nombre` varchar(30) ,
 `cli_apellido` varchar(50) ,
 `cli_direccion` varchar(50) ,
 `cli_telefono` int ,
 `cod_ciudad` int ,
 `descrip_ciudad` varchar(25) ,
 `id_departamento` int ,
 `dep_descripcion` varchar(35) 
)*/;

/*Table structure for table `v_producto` */

DROP TABLE IF EXISTS `v_producto`;

/*!50001 DROP VIEW IF EXISTS `v_producto` */;
/*!50001 DROP TABLE IF EXISTS `v_producto` */;

/*!50001 CREATE TABLE  `v_producto`(
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) 
)*/;

/*Table structure for table `v_stock` */

DROP TABLE IF EXISTS `v_stock`;

/*!50001 DROP VIEW IF EXISTS `v_stock` */;
/*!50001 DROP TABLE IF EXISTS `v_stock` */;

/*!50001 CREATE TABLE  `v_stock`(
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_deposito` int ,
 `descrip` varchar(50) ,
 `t_p_descrip` varchar(50) ,
 `u_descrip` varchar(20) ,
 `cantidad` int 
)*/;

/*Table structure for table `v_pedido` */

DROP TABLE IF EXISTS `v_pedido`;

/*!50001 DROP VIEW IF EXISTS `v_pedido` */;
/*!50001 DROP TABLE IF EXISTS `v_pedido` */;

/*!50001 CREATE TABLE  `v_pedido`(
 `id_pedido` int ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `fecha` date ,
 `hora` time ,
 `estado` varchar(30) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `cantidad` int 
)*/;

/*Table structure for table `v_presu` */

DROP TABLE IF EXISTS `v_presu`;

/*!50001 DROP VIEW IF EXISTS `v_presu` */;
/*!50001 DROP TABLE IF EXISTS `v_presu` */;

/*!50001 CREATE TABLE  `v_presu`(
 `id_presupuesto` int ,
 `id_pedido` int ,
 `cod_proveedor` int ,
 `razon_social` varchar(75) ,
 `fecha_presu` date ,
 `fecha_vencimiento` date ,
 `estado` varchar(20) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cantidad` int ,
 `precio_unit` int 
)*/;

/*Table structure for table `v_orden_comp` */

DROP TABLE IF EXISTS `v_orden_comp`;

/*!50001 DROP VIEW IF EXISTS `v_orden_comp` */;
/*!50001 DROP TABLE IF EXISTS `v_orden_comp` */;

/*!50001 CREATE TABLE  `v_orden_comp`(
 `id_orden_comp` int ,
 `fecha` date ,
 `estado` varchar(30) ,
 `hora` time ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `id_presupuesto` int ,
 `precio_unit` int ,
 `cantidad_aprobada` int 
)*/;

/*Table structure for table `v_compras` */

DROP TABLE IF EXISTS `v_compras`;

/*!50001 DROP VIEW IF EXISTS `v_compras` */;
/*!50001 DROP TABLE IF EXISTS `v_compras` */;

/*!50001 CREATE TABLE  `v_compras`(
 `cod_compra` int ,
 `cod_proveedor` int ,
 `razon_social` varchar(75) ,
 `fecha` date ,
 `estado` varchar(15) ,
 `hora` time ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `id_orden_comp` int ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `precio` int ,
 `cantidad` int 
)*/;

/*Table structure for table `v_cuentas` */

DROP TABLE IF EXISTS `v_cuentas`;

/*!50001 DROP VIEW IF EXISTS `v_cuentas` */;
/*!50001 DROP TABLE IF EXISTS `v_cuentas` */;

/*!50001 CREATE TABLE  `v_cuentas`(
 `id_cuenta` int ,
 `fecha_emision` date ,
 `fecha_vencimiento` date ,
 `estado` varchar(20) ,
 `cod_compra` int ,
 `monto_total` int ,
 `monto_pagado` int 
)*/;

/*Table structure for table `v_nota` */

DROP TABLE IF EXISTS `v_nota`;

/*!50001 DROP VIEW IF EXISTS `v_nota` */;
/*!50001 DROP TABLE IF EXISTS `v_nota` */;

/*!50001 CREATE TABLE  `v_nota`(
 `id_nota` int ,
 `tipo` varchar(20) ,
 `fecha_emision` date ,
 `estado` varchar(50) ,
 `cod_compra` int ,
 `cod_proveedor` int ,
 `razon_social` varchar(75) ,
 `monto` int ,
 `razon` varchar(50) ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `cod_deposito` int ,
 `descrip` varchar(50) ,
 `cantidad` int 
)*/;

/*Table structure for table `v_ajuste` */

DROP TABLE IF EXISTS `v_ajuste`;

/*!50001 DROP VIEW IF EXISTS `v_ajuste` */;
/*!50001 DROP TABLE IF EXISTS `v_ajuste` */;

/*!50001 CREATE TABLE  `v_ajuste`(
 `id_ajuste` int ,
 `fecha_ajuste` date ,
 `motivo` varchar(50) ,
 `estado` varchar(30) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `cantidad_ajustada` int ,
 `cantidad_anterior` int 
)*/;

/*Table structure for table `v_ventas` */

DROP TABLE IF EXISTS `v_ventas`;

/*!50001 DROP VIEW IF EXISTS `v_ventas` */;
/*!50001 DROP TABLE IF EXISTS `v_ventas` */;

/*!50001 CREATE TABLE  `v_ventas`(
 `cod_venta` int ,
 `id_cliente` int ,
 `cli_nombre` varchar(30) ,
 `cli_apellido` varchar(50) ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `fecha` date ,
 `estado` varchar(15) ,
 `hora` time ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `det_precio_unit` int ,
 `det_cantidad` int 
)*/;

/*Table structure for table `v_pedido_v` */

DROP TABLE IF EXISTS `v_pedido_v`;

/*!50001 DROP VIEW IF EXISTS `v_pedido_v` */;
/*!50001 DROP TABLE IF EXISTS `v_pedido_v` */;

/*!50001 CREATE TABLE  `v_pedido_v`(
 `id_pedido_v` int ,
 `fecha_pedido` date ,
 `hora` time ,
 `estado` varchar(20) ,
 `id_user` int ,
 `name_user` varchar(150) ,
 `cod_producto` int ,
 `p_descrip` varchar(50) ,
 `cod_tipo_prod` int ,
 `t_p_descrip` varchar(50) ,
 `id_u_medida` int ,
 `u_descrip` varchar(20) ,
 `cod_deposito` int ,
 `descrip` varchar(50) ,
 `cantidad` int 
)*/;

/*View structure for view v_clientes */

/*!50001 DROP TABLE IF EXISTS `v_clientes` */;
/*!50001 DROP VIEW IF EXISTS `v_clientes` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clientes` AS select `cli`.`id_cliente` AS `id_cliente`,`cli`.`ci_ruc` AS `ci_ruc`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`cli`.`cli_direccion` AS `cli_direccion`,`cli`.`cli_telefono` AS `cli_telefono`,`ciu`.`cod_ciudad` AS `cod_ciudad`,`ciu`.`descrip_ciudad` AS `descrip_ciudad`,`dep`.`id_departamento` AS `id_departamento`,`dep`.`dep_descripcion` AS `dep_descripcion` from ((`clientes` `cli` join `departamento` `dep`) join `ciudad` `ciu`) where ((`cli`.`cod_ciudad` = `ciu`.`cod_ciudad`) and (`ciu`.`id_departamento` = `dep`.`id_departamento`)) */;

/*View structure for view v_producto */

/*!50001 DROP TABLE IF EXISTS `v_producto` */;
/*!50001 DROP VIEW IF EXISTS `v_producto` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_producto` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`um`.`id_u_medida` AS `id_u_medida`,`um`.`u_descrip` AS `u_descrip` from ((`producto` `pro` join `tipo_producto` `tp`) join `u_medida` `um`) where ((`tp`.`cod_tipo_prod` = `pro`.`cod_tipo_prod`) and (`um`.`id_u_medida` = `pro`.`id_u_medida`)) */;

/*View structure for view v_stock */

/*!50001 DROP TABLE IF EXISTS `v_stock` */;
/*!50001 DROP VIEW IF EXISTS `v_stock` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`dep`.`cod_deposito` AS `cod_deposito`,`dep`.`descrip` AS `descrip`,`tpro`.`t_p_descrip` AS `t_p_descrip`,`um`.`u_descrip` AS `u_descrip`,`st`.`cantidad` AS `cantidad` from ((((`stock` `st` join `producto` `pro`) join `tipo_producto` `tpro`) join `u_medida` `um`) join `deposito` `dep`) where ((`st`.`cod_producto` = `pro`.`cod_producto`) and (`st`.`cod_deposito` = `dep`.`cod_deposito`) and (`pro`.`cod_tipo_prod` = `tpro`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `um`.`id_u_medida`)) */;

/*View structure for view v_pedido */

/*!50001 DROP TABLE IF EXISTS `v_pedido` */;
/*!50001 DROP VIEW IF EXISTS `v_pedido` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido` AS select `pe`.`id_pedido` AS `id_pedido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pe`.`fecha` AS `fecha`,`pe`.`hora` AS `hora`,`pe`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad` from (((((`pedido` `pe` join `det_pedido` `det`) join `usuarios` `usu`) join `producto` `pro`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`pe`.`id_user` = `usu`.`id_user`) and (`pe`.`id_pedido` = `det`.`id_pedido`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_presu */

/*!50001 DROP TABLE IF EXISTS `v_presu` */;
/*!50001 DROP VIEW IF EXISTS `v_presu` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_presu` AS select `pre`.`id_presupuesto` AS `id_presupuesto`,`ped`.`id_pedido` AS `id_pedido`,`prv`.`cod_proveedor` AS `cod_proveedor`,`prv`.`razon_social` AS `razon_social`,`pre`.`fecha_presu` AS `fecha_presu`,`pre`.`fecha_vencimiento` AS `fecha_vencimiento`,`pre`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`det`.`cantidad` AS `cantidad`,`det`.`precio_unit` AS `precio_unit` from ((((`presupuesto` `pre` join `proveedor` `prv`) join `producto` `pro`) join `det_presu` `det`) join `pedido` `ped`) where ((`pre`.`id_presupuesto` = `det`.`id_presupuesto`) and (`pre`.`id_pedido` = `ped`.`id_pedido`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`pre`.`cod_proveedor` = `prv`.`cod_proveedor`)) */;

/*View structure for view v_orden_comp */

/*!50001 DROP TABLE IF EXISTS `v_orden_comp` */;
/*!50001 DROP VIEW IF EXISTS `v_orden_comp` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_orden_comp` AS select `orde`.`id_orden_comp` AS `id_orden_comp`,`orde`.`fecha` AS `fecha`,`orde`.`estado` AS `estado`,`orde`.`hora` AS `hora`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`presu`.`id_presupuesto` AS `id_presupuesto`,`det`.`precio_unit` AS `precio_unit`,`det`.`cantidad_aprobada` AS `cantidad_aprobada` from ((((((`orden_compra` `orde` join `detalle_orden_comp` `det`) join `producto` `pro`) join `usuarios` `usu`) join `presupuesto` `presu`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`orde`.`id_orden_comp` = `det`.`id_orden_comp`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`orde`.`id_user` = `usu`.`id_user`) and (`orde`.`id_presupuesto` = `presu`.`id_presupuesto`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_compras */

/*!50001 DROP TABLE IF EXISTS `v_compras` */;
/*!50001 DROP VIEW IF EXISTS `v_compras` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_compras` AS select `com`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`com`.`fecha` AS `fecha`,`com`.`estado` AS `estado`,`com`.`hora` AS `hora`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`orde`.`id_orden_comp` AS `id_orden_comp`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`precio` AS `precio`,`det`.`cantidad` AS `cantidad` from (((((((`compra` `com` join `detalle_compra` `det`) join `orden_compra` `orde`) join `producto` `pro`) join `proveedor` `prov`) join `usuarios` `usu`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`com`.`cod_compra` = `det`.`cod_compra`) and (`com`.`cod_proveedor` = `prov`.`cod_proveedor`) and (`com`.`id_user` = `usu`.`id_user`) and (`com`.`id_orden_comp` = `orde`.`id_orden_comp`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_cuentas */

/*!50001 DROP TABLE IF EXISTS `v_cuentas` */;
/*!50001 DROP VIEW IF EXISTS `v_cuentas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cuentas` AS select `cue`.`id_cuenta` AS `id_cuenta`,`cue`.`fecha_emision` AS `fecha_emision`,`cue`.`fecha_vencimiento` AS `fecha_vencimiento`,`cue`.`estado` AS `estado`,`cue`.`cod_compra` AS `cod_compra`,`det`.`monto_total` AS `monto_total`,`det`.`monto_pagado` AS `monto_pagado` from ((`cuentas_a_pagar` `cue` join `det_cuenta_a_pagar` `det`) join `compra` `com`) where ((`cue`.`id_cuenta` = `det`.`id_cuenta`) and (`cue`.`cod_compra` = `com`.`cod_compra`)) */;

/*View structure for view v_nota */

/*!50001 DROP TABLE IF EXISTS `v_nota` */;
/*!50001 DROP VIEW IF EXISTS `v_nota` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_nota` AS select `nota`.`id_nota` AS `id_nota`,`nota`.`tipo` AS `tipo`,`nota`.`fecha_emision` AS `fecha_emision`,`nota`.`estado` AS `estado`,`com`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`det`.`monto` AS `monto`,`det`.`razon` AS `razon`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`dep`.`cod_deposito` AS `cod_deposito`,`dep`.`descrip` AS `descrip`,`det`.`cantidad` AS `cantidad` from ((((((((`nota_credito_debito` `nota` join `det_nota_credit_debit` `det`) join `compra` `com`) join `proveedor` `prov`) join `usuarios` `usu`) join `producto` `pro`) join `deposito` `dep`) join `u_medida` `u`) join `tipo_producto` `tp`) where ((`nota`.`id_nota` = `det`.`id_nota`) and (`det`.`cod_proveedor` = `prov`.`cod_proveedor`) and (`nota`.`cod_compra` = `com`.`cod_compra`) and (`nota`.`id_user` = `usu`.`id_user`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`det`.`cod_deposito` = `dep`.`cod_deposito`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) */;

/*View structure for view v_ajuste */

/*!50001 DROP TABLE IF EXISTS `v_ajuste` */;
/*!50001 DROP VIEW IF EXISTS `v_ajuste` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ajuste` AS select `aju`.`id_ajuste` AS `id_ajuste`,`aju`.`fecha_ajuste` AS `fecha_ajuste`,`aju`.`motivo` AS `motivo`,`aju`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`det`.`cantidad_ajustada` AS `cantidad_ajustada`,`det`.`cantidad_anterior` AS `cantidad_anterior` from (((((`ajuste_com` `aju` join `det_ajuste` `det`) join `producto` `pro`) join `usuarios` `usu`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`aju`.`id_ajuste` = `det`.`id_ajuste`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`det`.`id_user` = `usu`.`id_user`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_ventas */

/*!50001 DROP TABLE IF EXISTS `v_ventas` */;
/*!50001 DROP VIEW IF EXISTS `v_ventas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas` AS select `v`.`cod_venta` AS `cod_venta`,`cli`.`id_cliente` AS `id_cliente`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`v`.`fecha` AS `fecha`,`v`.`estado` AS `estado`,`v`.`hora` AS `hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`det_precio_unit` AS `det_precio_unit`,`det`.`det_cantidad` AS `det_cantidad` from ((((((`venta` `v` join `det_venta` `det`) join `producto` `pro`) join `usuarios` `usu`) join `clientes` `cli`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`v`.`cod_venta` = `det`.`cod_venta`) and (`v`.`id_cliente` = `cli`.`id_cliente`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`v`.`id_user` = `usu`.`id_user`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*View structure for view v_pedido_v */

/*!50001 DROP TABLE IF EXISTS `v_pedido_v` */;
/*!50001 DROP VIEW IF EXISTS `v_pedido_v` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido_v` AS select `p`.`id_pedido_v` AS `id_pedido_v`,`p`.`fecha_pedido` AS `fecha_pedido`,`p`.`hora` AS `hora`,`p`.`estado` AS `estado`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`dep`.`cod_deposito` AS `cod_deposito`,`dep`.`descrip` AS `descrip`,`det`.`cantidad` AS `cantidad` from ((((((`pedido_v` `p` join `det_pedido_v` `det`) join `usuarios` `usu`) join `producto` `pro`) join `deposito` `dep`) join `tipo_producto` `tp`) join `u_medida` `u`) where ((`p`.`id_pedido_v` = `det`.`id_pedido_v`) and (`det`.`cod_producto` = `pro`.`cod_producto`) and (`det`.`cod_deposito` = `dep`.`cod_deposito`) and (`p`.`id_user` = `usu`.`id_user`) and (`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`) and (`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
