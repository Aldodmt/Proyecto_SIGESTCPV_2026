-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sysweb
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sysweb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sysweb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sysweb`;

--
-- Table structure for table `ajuste_com`
--

DROP TABLE IF EXISTS `ajuste_com`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ajuste_com`
--

LOCK TABLES `ajuste_com` WRITE;
/*!40000 ALTER TABLE `ajuste_com` DISABLE KEYS */;
INSERT INTO `ajuste_com` VALUES (1,'2025-10-11','Si2','ACTIVO',1,NULL,NULL,NULL),(2,'2025-10-11','Si3','ACTIVO',1,NULL,NULL,NULL),(3,'2025-10-11','Si4','ANULADO',1,1,'2025-10-11','11:38:45'),(4,'2025-10-23','Vencimiento','ACTIVO',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `ajuste_com` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_ajuste` AFTER INSERT ON `ajuste_com` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `car_descripcion` varchar(60) NOT NULL,
  `car_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_cargo`),
  UNIQUE KEY `uq_cargo_descripcion` (`car_descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo`
--

LOCK TABLES `cargo` WRITE;
/*!40000 ALTER TABLE `cargo` DISABLE KEYS */;
INSERT INTO `cargo` VALUES (1,'Administrador de sistemas','activo'),(2,'Gerente general','activo'),(3,'Jefe de compras','activo'),(4,'Auxiliar de compras','activo'),(5,'Jefe de ventas','activo'),(6,'Vendedor','activo'),(7,'Jefe de local','activo'),(8,'Encargado de producción','activo');
/*!40000 ALTER TABLE `cargo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ciudad`
--

DROP TABLE IF EXISTS `ciudad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ciudad` (
  `cod_ciudad` int(11) NOT NULL,
  `descrip_ciudad` varchar(25) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  PRIMARY KEY (`cod_ciudad`),
  KEY `id_departamento` (`id_departamento`),
  CONSTRAINT `fk_ciudad_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamento` (`id_departamento`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciudad`
--

LOCK TABLES `ciudad` WRITE;
/*!40000 ALTER TABLE `ciudad` DISABLE KEYS */;
INSERT INTO `ciudad` VALUES (1,'Asunción',1),(2,'Capiatá',1),(3,'Hernandarias',2),(4,'San Ignacio',3),(5,'Nueva italia',1);
/*!40000 ALTER TABLE `ciudad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,1,'5629997','Carlos','Ortiz','Capiata km27',976524098),(2,2,'5628992','Aldo','Marin','Barrio san Agustin',992356262),(3,4,'5863952','Adrian','Gimenez','Calle B',993626538);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compra`
--

DROP TABLE IF EXISTS `compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compra`
--

LOCK TABLES `compra` WRITE;
/*!40000 ALTER TABLE `compra` DISABLE KEYS */;
INSERT INTO `compra` VALUES (1,2,'2025-10-10','ACTIVO','14:50:28',1,1,'001-001-0000001','0000-00-00','','con',445454,'2025-11-10','CONTADO',35540,NULL,NULL,NULL),(2,1,'2025-10-10','ANULADO','15:02:14',1,3,'001-001-0000002','0000-00-00','','con',54545454,'2025-11-10','CREDITO',87500,1,'2025-10-10','12:42:24'),(3,2,'2025-10-11','ACTIVO','01:00:27',1,2,'001-001-0000003','0000-00-00','','sin',22222222,'2025-11-10','CREDITO',45000,NULL,NULL,NULL),(4,2,'2025-10-11','ACTIVO','01:17:39',1,4,'001-001-0000004','0000-00-00','','con',33333333,'2025-12-10','CREDITO',100000,NULL,NULL,NULL),(5,1,'2025-10-19','ACTIVO','22:29:54',1,6,'001-001-0000005','0000-00-00','','sin',55555555,'2025-11-19','CREDITO',75000,NULL,NULL,NULL),(7,2,'2025-10-23','ACTIVO','22:35:41',1,9,'001-001-0000007','0000-00-00','','CON',56565656,'2025-11-23','CREDITO',60000,NULL,NULL,NULL),(6,1,'2025-10-22','ACTIVO','11:49:24',1,8,'001-001-0000006','0000-00-00','','CON',66666666,'2025-11-22','CREDITO',150000,NULL,NULL,NULL),(8,1,'2025-10-25','PENDIENTE','13:40:24',1,5,'001-001-0000008','2025-10-24','ELECTRONICA','SIN',88888888,'0000-00-00','CREDITO',5000,NULL,NULL,NULL),(9,2,'2025-10-25','ACTIVO','13:43:17',1,NULL,'001-001-0000009','2025-10-23','ELECTRONICA','SIN',99999999,'0000-00-00','CREDITO',100000,NULL,NULL,NULL);
/*!40000 ALTER TABLE `compra` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_c` AFTER INSERT ON `compra` FOR EACH ROW BEGIN
   DELETE FROM tmp_compra;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cuentas_a_pagar`
--

DROP TABLE IF EXISTS `cuentas_a_pagar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_a_pagar`
--

LOCK TABLES `cuentas_a_pagar` WRITE;
/*!40000 ALTER TABLE `cuentas_a_pagar` DISABLE KEYS */;
INSERT INTO `cuentas_a_pagar` VALUES (25,1,1,35540,0,'2025-10-10','PAGADO'),(29,2,2,43750,43750,'2025-11-09','ANULADO'),(28,2,1,43750,43750,'2025-10-10','ANULADO'),(30,3,1,20000,20000,'2025-10-11','PENDIENTE'),(31,3,2,20000,20000,'2025-10-26','PENDIENTE'),(32,4,1,33333,33333,'2025-10-11','PENDIENTE'),(33,4,2,33333,33333,'2025-10-31','PENDIENTE'),(34,4,3,33333,33333,'2025-11-20','PENDIENTE'),(36,5,1,32500,32500,'2025-10-19','PENDIENTE'),(37,5,2,32500,32500,'2025-11-18','PENDIENTE'),(40,6,1,33333,33333,'2025-10-22','PENDIENTE'),(41,6,2,33333,33333,'2025-11-21','PENDIENTE'),(42,6,3,33333,33333,'2025-12-21','PENDIENTE'),(43,7,1,17500,17500,'2025-10-23','PENDIENTE'),(44,7,2,17500,17500,'2025-11-22','PENDIENTE'),(45,8,1,2500,2500,'2025-10-25','PENDIENTE'),(46,8,2,2500,2500,'2025-11-24','PENDIENTE'),(47,9,1,50000,50000,'2025-10-25','PENDIENTE'),(48,9,2,50000,50000,'2025-11-24','PENDIENTE');
/*!40000 ALTER TABLE `cuentas_a_pagar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamento`
--

DROP TABLE IF EXISTS `departamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departamento` (
  `id_departamento` int(11) NOT NULL,
  `dep_descripcion` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id_departamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamento`
--

LOCK TABLES `departamento` WRITE;
/*!40000 ALTER TABLE `departamento` DISABLE KEYS */;
INSERT INTO `departamento` VALUES (1,'Central'),(2,'Alto Paraná'),(3,'Misiones');
/*!40000 ALTER TABLE `departamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposito`
--

DROP TABLE IF EXISTS `deposito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deposito` (
  `cod_deposito` int(11) NOT NULL,
  `descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposito`
--

LOCK TABLES `deposito` WRITE;
/*!40000 ALTER TABLE `deposito` DISABLE KEYS */;
INSERT INTO `deposito` VALUES (1,'Depo Central'),(2,'Depo 2');
/*!40000 ALTER TABLE `deposito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_ajuste`
--

DROP TABLE IF EXISTS `det_ajuste`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_ajuste` (
  `id_ajuste` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad_ajustada` int(11) NOT NULL,
  `cantidad_anterior` int(11) NOT NULL,
  PRIMARY KEY (`id_ajuste`,`cod_producto`),
  KEY `producto_det_ajuste_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_ajuste`
--

LOCK TABLES `det_ajuste` WRITE;
/*!40000 ALTER TABLE `det_ajuste` DISABLE KEYS */;
INSERT INTO `det_ajuste` VALUES (1,1,1,34),(2,2,2,60),(3,1,1,33),(3,2,1,58),(4,3,3,9);
/*!40000 ALTER TABLE `det_ajuste` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_nota_credit_debit`
--

DROP TABLE IF EXISTS `det_nota_credit_debit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_nota_credit_debit`
--

LOCK TABLES `det_nota_credit_debit` WRITE;
/*!40000 ALTER TABLE `det_nota_credit_debit` DISABLE KEYS */;
INSERT INTO `det_nota_credit_debit` VALUES (24,2,5.00,5000.00,5,0.00,1190.48,0.00),(25,3,5.00,5000.00,10,0.00,0.00,2272.73),(25,2,5.00,5000.00,10,0.00,0.00,2272.73),(26,3,2.00,5000.00,10,0.00,0.00,909.09),(27,1,5.00,5000.00,10,0.00,0.00,2272.73);
/*!40000 ALTER TABLE `det_nota_credit_debit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_notar_compra`
--

DROP TABLE IF EXISTS `det_notar_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_notar_compra` (
  `id_notaR` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`id_notaR`,`cod_producto`),
  KEY `cod_producto` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_notar_compra`
--

LOCK TABLES `det_notar_compra` WRITE;
/*!40000 ALTER TABLE `det_notar_compra` DISABLE KEYS */;
INSERT INTO `det_notar_compra` VALUES (3,1,2),(3,2,2),(4,1,10),(4,2,10),(4,3,10);
/*!40000 ALTER TABLE `det_notar_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_pedido`
--

DROP TABLE IF EXISTS `det_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_pedido` (
  `cod_producto` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `pedido_det_pedido_fk` (`id_pedido`),
  KEY `producto_det_pedido_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_pedido`
--

LOCK TABLES `det_pedido` WRITE;
/*!40000 ALTER TABLE `det_pedido` DISABLE KEYS */;
INSERT INTO `det_pedido` VALUES (1,136,1),(2,112,2),(2,136,1),(2,139,1),(2,140,1),(2,141,1),(1,112,2),(1,171,5),(1,169,5),(2,177,15),(1,177,10),(2,178,20),(1,184,5),(1,186,5),(3,198,5),(1,198,5),(1,205,1),(1,206,10),(2,206,10),(3,206,10),(1,207,10),(2,207,1),(3,207,1),(1,216,1);
/*!40000 ALTER TABLE `det_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_pedido_v`
--

DROP TABLE IF EXISTS `det_pedido_v`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_pedido_v` (
  `id_pedido_v` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cod_deposito` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `pedido_v_det_fk` (`id_pedido_v`),
  KEY `producto_det_v_fk` (`cod_producto`),
  KEY `depositodet_v_fk` (`cod_deposito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_pedido_v`
--

LOCK TABLES `det_pedido_v` WRITE;
/*!40000 ALTER TABLE `det_pedido_v` DISABLE KEYS */;
/*!40000 ALTER TABLE `det_pedido_v` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_presu`
--

DROP TABLE IF EXISTS `det_presu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_presu` (
  `id_presupuesto` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unit` int(11) NOT NULL,
  KEY `producto_det_presu_fk` (`cod_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_presu`
--

LOCK TABLES `det_presu` WRITE;
/*!40000 ALTER TABLE `det_presu` DISABLE KEYS */;
INSERT INTO `det_presu` VALUES (5,2,15,2500),(4,2,1,5000),(3,2,1,45000),(2,2,1,45),(7,1,5,10000),(2,1,1,45),(7,3,5,5000),(6,2,20,5000),(5,1,10,5000),(1,1,2,15220),(1,2,2,2550),(8,2,1,2550),(9,1,5,5000),(10,1,10,5000),(10,2,10,5000),(10,3,10,5000),(11,1,10,5000),(11,2,1,5000),(11,3,1,5000),(12,1,10,5000),(13,1,5,5000),(14,1,1,1);
/*!40000 ALTER TABLE `det_presu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_venta`
--

DROP TABLE IF EXISTS `det_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_venta`
--

LOCK TABLES `det_venta` WRITE;
/*!40000 ALTER TABLE `det_venta` DISABLE KEYS */;
INSERT INTO `det_venta` VALUES (1,2,1,10400,1),(1,1,1,10400,10),(1,3,1,10400,5);
/*!40000 ALTER TABLE `det_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_compra`
--

DROP TABLE IF EXISTS `detalle_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_compra`
--

LOCK TABLES `detalle_compra` WRITE;
/*!40000 ALTER TABLE `detalle_compra` DISABLE KEYS */;
INSERT INTO `detalle_compra` VALUES (1,1,15220,2,'10%',0.00,0.00,3230.91),(2,1,2550,2,'10%',0.00,0.00,463.64),(2,2,2500,15,'10%',0.00,0.00,7954.55),(1,2,5000,10,'10%',0.00,0.00,4545.45),(2,3,45000,1,'10%',0.00,0.00,4090.91),(2,4,5000,20,'5%',0.00,4761.90,0.00),(3,5,5000,5,'10%',0.00,0.00,2272.73),(1,5,10000,5,'10%',0.00,0.00,4545.45),(1,6,5000,10,'10%',0.00,0.00,4545.45),(2,6,5000,10,'10%',0.00,0.00,4545.45),(3,6,5000,10,'10%',0.00,0.00,4545.45),(1,7,5000,10,'10%',0.00,0.00,4545.45),(2,7,5000,1,'5%',0.00,0.00,238.10),(3,7,5000,1,'10%',0.00,0.00,238.10),(2,8,5000,1,'5%',0.00,238.10,0.00),(1,9,5000,10,'10%',0.00,0.00,4545.45),(2,9,5000,10,'5%',0.00,2380.95,0.00);
/*!40000 ALTER TABLE `detalle_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_orden_comp`
--

DROP TABLE IF EXISTS `detalle_orden_comp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_orden_comp` (
  `id_orden_comp` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `precio_unit` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  KEY `orden_compra_det_orden_comp_fk` (`id_orden_comp`),
  KEY `cod_producto_detalle_orden_compra_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_comp`
--

LOCK TABLES `detalle_orden_comp` WRITE;
/*!40000 ALTER TABLE `detalle_orden_comp` DISABLE KEYS */;
INSERT INTO `detalle_orden_comp` VALUES (5,2,5000,1),(4,2,5000,20),(3,1,5000,10),(3,2,2500,15),(2,2,45000,1),(1,2,2550,2),(1,1,15220,2),(6,1,10000,5),(6,3,5000,5),(7,1,5000,5),(8,1,5000,10),(8,2,5000,10),(8,3,5000,10),(9,1,5000,10),(9,2,5000,1),(9,3,5000,1),(10,1,5000,10);
/*!40000 ALTER TABLE `detalle_orden_comp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esquema_version`
--

DROP TABLE IF EXISTS `esquema_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esquema_version` (
  `version` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `aplicado` datetime NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esquema_version`
--

LOCK TABLES `esquema_version` WRITE;
/*!40000 ALTER TABLE `esquema_version` DISABLE KEYS */;
INSERT INTO `esquema_version` VALUES (1,'Esquema base + seguridad del login (Backup_06_1_2026)','2026-10-06 22:23:00'),(2,'Personas, funcionarios, cargos, roles, módulos, páginas y permisos (RBAC)','2026-10-14 20:41:39');
/*!40000 ALTER TABLE `esquema_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcionario`
--

DROP TABLE IF EXISTS `funcionario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `fun_fecha_ingreso` date NOT NULL,
  `fun_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `uq_funcionario_persona` (`id_persona`),
  KEY `idx_funcionario_cargo` (`id_cargo`),
  CONSTRAINT `fk_funcionario_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_funcionario_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcionario`
--

LOCK TABLES `funcionario` WRITE;
/*!40000 ALTER TABLE `funcionario` DISABLE KEYS */;
/*!40000 ALTER TABLE `funcionario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genero`
--

DROP TABLE IF EXISTS `genero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genero` (
  `id_genero` int(11) NOT NULL AUTO_INCREMENT,
  `gen_descripcion` varchar(30) NOT NULL,
  `gen_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_genero`),
  UNIQUE KEY `uq_genero_descripcion` (`gen_descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genero`
--

LOCK TABLES `genero` WRITE;
/*!40000 ALTER TABLE `genero` DISABLE KEYS */;
INSERT INTO `genero` VALUES (1,'Masculino','activo'),(2,'Femenino','activo');
/*!40000 ALTER TABLE `genero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_accesos`
--

DROP TABLE IF EXISTS `log_accesos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_accesos`
--

LOCK TABLES `log_accesos` WRITE;
/*!40000 ALTER TABLE `log_accesos` DISABLE KEYS */;
INSERT INTO `log_accesos` VALUES (10,1,'aldo','***','2026-10-06 21:56:43','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',171),(11,1,'aldo','***','2026-10-06 22:14:14','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',120),(12,NULL,'adlo','****','2026-10-06 22:15:14','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),(13,NULL,'adlo','****','2026-10-06 22:15:16','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),(14,NULL,'adlo','***','2026-10-06 22:15:17','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','FALLIDO','Usuario inexistente',0),(15,1,'aldo','***','2026-10-06 22:15:21','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','EXITOSO','Inicio de sesión',104);
/*!40000 ALTER TABLE `log_accesos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulo`
--

DROP TABLE IF EXISTS `modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulo` (
  `id_modulo` int(11) NOT NULL AUTO_INCREMENT,
  `mod_clave` varchar(30) NOT NULL,
  `mod_nombre` varchar(60) NOT NULL,
  `mod_orden` int(11) NOT NULL DEFAULT 0,
  `mod_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `uq_modulo_clave` (`mod_clave`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulo`
--

LOCK TABLES `modulo` WRITE;
/*!40000 ALTER TABLE `modulo` DISABLE KEYS */;
INSERT INTO `modulo` VALUES (1,'general','General',1,'activo'),(2,'referenciales','Referenciales generales',2,'activo'),(3,'personal','Personal',3,'activo'),(4,'compras','Compras',4,'activo'),(5,'ventas','Ventas',5,'activo'),(6,'seguridad','Seguridad',6,'activo');
/*!40000 ALTER TABLE `modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nacionalidad`
--

DROP TABLE IF EXISTS `nacionalidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nacionalidad` (
  `id_nacionalidad` int(11) NOT NULL AUTO_INCREMENT,
  `nac_descripcion` varchar(60) NOT NULL,
  `nac_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_nacionalidad`),
  UNIQUE KEY `uq_nacionalidad_descripcion` (`nac_descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nacionalidad`
--

LOCK TABLES `nacionalidad` WRITE;
/*!40000 ALTER TABLE `nacionalidad` DISABLE KEYS */;
INSERT INTO `nacionalidad` VALUES (1,'Paraguaya','activo'),(2,'Argentina','activo'),(3,'Brasileña','activo'),(4,'Uruguaya','activo'),(5,'Boliviana','activo'),(6,'Chilena','activo'),(7,'Peruana','activo'),(8,'Colombiana','activo'),(9,'Venezolana','activo'),(10,'Otra','activo');
/*!40000 ALTER TABLE `nacionalidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nota_credito_debito`
--

DROP TABLE IF EXISTS `nota_credito_debito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nota_credito_debito`
--

LOCK TABLES `nota_credito_debito` WRITE;
/*!40000 ALTER TABLE `nota_credito_debito` DISABLE KEYS */;
INSERT INTO `nota_credito_debito` VALUES (24,4,'001-001-0000004','CREDITO','ajuste_p','33333333','33333333','2025-10-11','ANULADO',1,25000.00,'Si1',1,'2025-10-11','09:05:51'),(25,6,'001-001-0000006','CREDITO','ajuste_p','12121212','66666666','2025-10-22','ACTIVO',1,50000.00,'Si3',NULL,NULL,NULL),(26,5,'001-001-0000005','CREDITO','ajuste_p','12121212','55555555','2025-10-23','ACTIVO',1,10000.00,'a',NULL,NULL,NULL),(27,7,'001-001-0000007','CREDITO','ajuste_p','27777777','27777777','2025-10-26','ACTIVO',1,25000.00,'Si7',NULL,NULL,NULL),(28,3,'001-001-0000003','CREDITO','ajuste_c','88888888','88888888','2025-10-31','ACTIVO',1,5000.00,'Si8',NULL,NULL,NULL);
/*!40000 ALTER TABLE `nota_credito_debito` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_nota_credito_debito_ajuste` AFTER INSERT ON `nota_credito_debito` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_nota_credito_debito_anulacion` AFTER UPDATE ON `nota_credito_debito` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notar_compra`
--

DROP TABLE IF EXISTS `notar_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notar_compra`
--

LOCK TABLES `notar_compra` WRITE;
/*!40000 ALTER TABLE `notar_compra` DISABLE KEYS */;
INSERT INTO `notar_compra` VALUES (4,6,1,'001-001-0000006','2025-10-22','2025-09-22','POR COMPRA','PROVEEDOR','JUANITO ALIMAñA','2687425','CCCC444',1,'ACTIVO',NULL,NULL,NULL),(3,1,2,'001-001-0000001','2025-10-12','2025-10-01','POR COMPRA','PROVEEDOR','JUANITO','5628442','AAAA123',1,'ANULADO',1,'2025-10-11','22:38:00');
/*!40000 ALTER TABLE `notar_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orden_compra`
--

DROP TABLE IF EXISTS `orden_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orden_compra`
--

LOCK TABLES `orden_compra` WRITE;
/*!40000 ALTER TABLE `orden_compra` DISABLE KEYS */;
INSERT INTO `orden_compra` VALUES (5,'2025-10-13','APROBADO','20:58:17',1,4,1,NULL,NULL,NULL),(4,'2025-10-11','APROBADO','01:17:19',1,6,2,NULL,NULL,NULL),(3,'2025-10-07','APROBADO','23:17:18',1,5,1,NULL,NULL,NULL),(2,'2025-10-07','APROBADO','15:16:25',1,3,2,NULL,NULL,NULL),(1,'2025-10-03','APROBADO','16:12:44',1,1,2,NULL,NULL,NULL),(6,'2025-10-14','APROBADO','19:37:53',1,7,1,NULL,NULL,NULL),(7,'2025-10-19','ANULADO','11:47:20',1,9,2,1,'2025-10-19','08:54:18'),(8,'2025-10-22','APROBADO','11:48:39',1,10,1,NULL,NULL,NULL),(9,'2025-10-23','APROBADO','22:33:26',1,11,2,NULL,NULL,NULL),(10,'2025-10-24','APROBADO','20:01:06',1,NULL,2,NULL,NULL,NULL);
/*!40000 ALTER TABLE `orden_compra` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_orden` AFTER INSERT ON `orden_compra` FOR EACH ROW BEGIN
   DELETE FROM tmp_orden;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pagina`
--

DROP TABLE IF EXISTS `pagina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagina` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `pag_clave` varchar(40) NOT NULL,
  `pag_nombre` varchar(80) NOT NULL,
  `pag_direccion` varchar(128) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `pag_acciones` varchar(40) NOT NULL DEFAULT 'leer,insertar,editar,borrar',
  `pag_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_pagina`),
  UNIQUE KEY `uq_pagina_clave` (`pag_clave`),
  KEY `idx_pagina_modulo` (`id_modulo`),
  CONSTRAINT `fk_pagina_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagina`
--

LOCK TABLES `pagina` WRITE;
/*!40000 ALTER TABLE `pagina` DISABLE KEYS */;
INSERT INTO `pagina` VALUES (1,'info_notaR','Informe de notas de remisión','?module=info_notaR',4,'leer','activo'),(2,'info_ajuste','Informe de ajustes','?module=info_ajuste',4,'leer','activo'),(3,'info_nota_cd','Informe de notas de crédito/débito','?module=info_nota_cd',4,'leer','activo'),(4,'info_facturacion_compra','Informe de compras','?module=info_facturacion_compra',4,'leer','activo'),(5,'info_orden','Informe de órdenes de compra','?module=info_orden',4,'leer','activo'),(6,'info_presu','Informe de presupuestos','?module=info_presu',4,'leer','activo'),(7,'info_pedido','Informe de pedidos','?module=info_pedido',4,'leer','activo'),(8,'info_u_medida','Informe de unidades de medida','?module=info_u_medida',4,'leer','activo'),(9,'info_proveedores','Informe de proveedores','?module=info_proveedores',4,'leer','activo'),(10,'info_producto','Informe de productos','?module=info_producto',4,'leer','activo'),(11,'stock','Stock','?module=stock',4,'leer','activo'),(12,'nota_remision','Notas de remisión','?module=nota_remision',4,'leer,insertar,editar,borrar','activo'),(13,'ajuste','Ajustes de stock','?module=ajuste',4,'leer,insertar,editar,borrar','activo'),(14,'nota_c_d','Notas de crédito/débito','?module=nota_c_d',4,'leer,insertar,editar,borrar','activo'),(15,'cuenta','Cuentas a pagar','?module=cuenta',4,'leer,editar,borrar','activo'),(16,'compra','Compras','?module=compra',4,'leer,insertar,editar,borrar','activo'),(17,'orden_c','Órdenes de compra','?module=orden_c',4,'leer,insertar,editar,borrar','activo'),(18,'presupuesto','Presupuestos','?module=presupuesto',4,'leer,insertar,editar,borrar','activo'),(19,'pedido','Pedidos','?module=pedido',4,'leer,insertar,editar,borrar','activo'),(20,'u_medida','Unidades de medida','?module=u_medida',4,'leer,insertar,editar,borrar','activo'),(21,'tipo_producto','Tipos de producto','?module=tipo_producto',4,'leer,insertar,editar,borrar','activo'),(22,'producto','Productos','?module=producto',4,'leer,insertar,editar,borrar','activo'),(23,'proveedor','Proveedores','?module=proveedor',4,'leer,insertar,editar,borrar','activo'),(24,'deposito','Depósitos','?module=deposito',4,'leer,insertar,editar,borrar','activo'),(25,'password','Cambiar contraseña','?module=password',1,'leer,editar','activo'),(26,'perfil','Mi perfil','?module=perfil',1,'leer,editar','activo'),(27,'start','Inicio','?module=start',1,'leer','activo'),(28,'cargo','Cargos','?module=cargo',3,'leer,insertar,editar,borrar','activo'),(29,'funcionario','Funcionarios','?module=funcionario',3,'leer,insertar,editar,borrar','activo'),(30,'persona','Personas','?module=persona',3,'leer,insertar,editar,borrar','activo'),(31,'genero','Géneros','?module=genero',2,'leer,insertar,editar,borrar','activo'),(32,'nacionalidad','Nacionalidades','?module=nacionalidad',2,'leer,insertar,editar,borrar','activo'),(33,'ciudad','Ciudades','?module=ciudad',2,'leer,insertar,editar,borrar','activo'),(34,'departamento','Departamentos','?module=departamento',2,'leer,insertar,editar,borrar','activo'),(35,'accesos','Registro de accesos','?module=accesos',6,'leer','activo'),(36,'pagina','Páginas y módulos','?module=pagina',6,'leer,editar','activo'),(37,'permiso','Permisos','?module=permiso',6,'leer,editar','activo'),(38,'rol','Roles','?module=rol',6,'leer,insertar,editar,borrar','activo'),(39,'user','Usuarios','?module=user',6,'leer,insertar,editar,borrar','activo'),(40,'info_pedido_v','Informe de pedidos de venta','?module=info_pedido_v',5,'leer','activo'),(41,'ventas','Ventas','?module=ventas',5,'leer,insertar,editar,borrar','activo'),(42,'pedido_v','Pedidos de venta','?module=pedido_v',5,'leer,insertar,editar,borrar','activo'),(43,'clientes','Clientes','?module=clientes',5,'leer,insertar,editar,borrar','activo');
/*!40000 ALTER TABLE `pagina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

LOCK TABLES `pedido` WRITE;
/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES (177,'2025-10-07','CONFIRMADO','20:15:37',1,NULL,NULL,NULL),(171,'2025-10-02','PENDIENTE','18:30:25',1,NULL,NULL,NULL),(141,'2025-09-30','CONFIRMADO','15:50:48',1,NULL,NULL,NULL),(140,'2025-09-30','CONFIRMADO','15:48:55',1,NULL,NULL,NULL),(139,'2025-09-30','CONFIRMADO','15:48:11',1,NULL,NULL,NULL),(169,'2025-10-02','CONFIRMADO','18:27:13',1,NULL,NULL,NULL),(137,'2025-09-30','PENDIENTE','15:45:32',1,NULL,NULL,NULL),(136,'2025-09-30','CONFIRMADO','15:44:20',1,NULL,NULL,NULL),(112,'2025-09-30','ANULADO','15:12:15',1,1,'2025-10-31','15:48:34'),(178,'2025-10-07','CONFIRMADO','20:15:56',1,NULL,NULL,NULL),(184,'2025-10-14','PENDIENTE','15:23:32',1,NULL,NULL,NULL),(186,'2025-10-14','PENDIENTE','15:24:23',1,NULL,NULL,NULL),(198,'2025-10-14','CONFIRMADO','16:30:59',1,NULL,NULL,NULL),(200,'2025-10-19','ANULADO','08:41:10',1,1,'2025-10-19','08:43:16'),(205,'2025-10-21','CONFIRMADO','14:06:48',1,NULL,NULL,NULL),(206,'2025-10-22','CONFIRMADO','08:46:41',1,NULL,NULL,NULL),(207,'2025-10-22','CONFIRMADO','10:23:01',1,NULL,NULL,NULL),(216,'2025-10-31','CONFIRMADO','15:52:17',1,NULL,NULL,NULL),(218,'2026-10-01','BORRADOR','19:47:51',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_p` AFTER INSERT ON `pedido` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pedido_v`
--

DROP TABLE IF EXISTS `pedido_v`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_v` (
  `id_pedido_v` int(11) NOT NULL,
  `fecha_pedido` date NOT NULL,
  `hora` time NOT NULL,
  `estado` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_pedido_v`),
  KEY `id_user_pedido_v_fk` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_v`
--

LOCK TABLES `pedido_v` WRITE;
/*!40000 ALTER TABLE `pedido_v` DISABLE KEYS */;
INSERT INTO `pedido_v` VALUES (2,'2025-01-05','15:48:05','aprobado',1),(1,'2025-01-02','20:00:11','aprobado',1),(3,'2025-01-12','15:29:32','pendiente',1),(4,'2025-01-15','19:35:41','aprobado',1);
/*!40000 ALTER TABLE `pedido_v` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_p_v` AFTER INSERT ON `pedido_v` FOR EACH ROW BEGIN
   DELETE FROM tmp;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `permiso`
--

DROP TABLE IF EXISTS `permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permiso` (
  `id_rol` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `perm_leer` tinyint(1) NOT NULL DEFAULT 0,
  `perm_insertar` tinyint(1) NOT NULL DEFAULT 0,
  `perm_editar` tinyint(1) NOT NULL DEFAULT 0,
  `perm_borrar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_rol`,`id_pagina`),
  KEY `idx_permiso_pagina` (`id_pagina`),
  CONSTRAINT `fk_permiso_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `pagina` (`id_pagina`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permiso`
--

LOCK TABLES `permiso` WRITE;
/*!40000 ALTER TABLE `permiso` DISABLE KEYS */;
INSERT INTO `permiso` VALUES (1,1,1,0,0,0),(1,2,1,0,0,0),(1,3,1,0,0,0),(1,4,1,0,0,0),(1,5,1,0,0,0),(1,6,1,0,0,0),(1,7,1,0,0,0),(1,8,1,0,0,0),(1,9,1,0,0,0),(1,10,1,0,0,0),(1,11,1,0,0,0),(1,12,1,1,1,1),(1,13,1,1,1,1),(1,14,1,1,1,1),(1,15,1,0,1,1),(1,16,1,1,1,1),(1,17,1,1,1,1),(1,18,1,1,1,1),(1,19,1,1,1,1),(1,20,1,1,1,1),(1,21,1,1,1,1),(1,22,1,1,1,1),(1,23,1,1,1,1),(1,24,1,1,1,1),(1,25,1,0,1,0),(1,26,1,0,1,0),(1,27,1,0,0,0),(1,28,1,1,1,1),(1,29,1,1,1,1),(1,30,1,1,1,1),(1,31,1,1,1,1),(1,32,1,1,1,1),(1,33,1,1,1,1),(1,34,1,1,1,1),(1,35,1,0,0,0),(1,36,1,0,1,0),(1,37,1,0,1,0),(1,38,1,1,1,1),(1,39,1,1,1,1),(1,40,1,0,0,0),(1,41,1,1,1,1),(1,42,1,1,1,1),(1,43,1,1,1,1),(2,1,1,0,0,0),(2,2,1,0,0,0),(2,3,1,0,0,0),(2,4,1,0,0,0),(2,5,1,0,0,0),(2,6,1,0,0,0),(2,7,1,0,0,0),(2,8,1,0,0,0),(2,9,1,0,0,0),(2,10,1,0,0,0),(2,11,1,0,0,0),(2,12,1,1,1,1),(2,13,1,1,1,1),(2,14,1,1,1,1),(2,15,1,0,1,1),(2,16,1,1,1,1),(2,17,1,1,1,1),(2,18,1,1,1,1),(2,19,1,1,1,1),(2,20,1,1,1,1),(2,21,1,1,1,1),(2,22,1,1,1,1),(2,23,1,1,1,1),(2,24,1,1,1,1),(2,25,1,0,1,0),(2,26,1,0,1,0),(2,27,1,0,0,0),(2,33,1,1,1,1),(2,34,1,1,1,1),(3,25,1,0,1,0),(3,26,1,0,1,0),(3,27,1,0,0,0),(3,33,1,1,1,1),(3,34,1,1,1,1),(3,40,1,0,0,0),(3,41,1,1,1,1),(3,42,1,1,1,1),(3,43,1,1,1,1),(4,25,1,0,1,0),(4,26,1,0,1,0),(4,27,1,0,0,0),(4,33,1,1,1,1),(4,34,1,1,1,1);
/*!40000 ALTER TABLE `permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persona`
--

DROP TABLE IF EXISTS `persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona` (
  `id_persona` int(11) NOT NULL AUTO_INCREMENT,
  `per_nombre` varchar(80) NOT NULL,
  `per_apellido` varchar(80) NOT NULL,
  `per_ci` varchar(15) NOT NULL,
  `per_fecha_nac` date NOT NULL,
  `per_telefono` varchar(20) DEFAULT NULL,
  `per_correo` varchar(150) DEFAULT NULL,
  `per_direccion` varchar(150) DEFAULT NULL,
  `id_genero` int(11) NOT NULL,
  `id_nacionalidad` int(11) NOT NULL,
  `cod_ciudad` int(11) NOT NULL,
  `per_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `per_creado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_persona`),
  UNIQUE KEY `uq_persona_ci` (`per_ci`),
  KEY `idx_persona_apellido` (`per_apellido`,`per_nombre`),
  KEY `fk_persona_genero` (`id_genero`),
  KEY `fk_persona_nacionalidad` (`id_nacionalidad`),
  KEY `fk_persona_ciudad` (`cod_ciudad`),
  CONSTRAINT `fk_persona_ciudad` FOREIGN KEY (`cod_ciudad`) REFERENCES `ciudad` (`cod_ciudad`) ON UPDATE CASCADE,
  CONSTRAINT `fk_persona_genero` FOREIGN KEY (`id_genero`) REFERENCES `genero` (`id_genero`) ON UPDATE CASCADE,
  CONSTRAINT `fk_persona_nacionalidad` FOREIGN KEY (`id_nacionalidad`) REFERENCES `nacionalidad` (`id_nacionalidad`) ON UPDATE CASCADE,
  CONSTRAINT `ck_persona_ci` CHECK (`per_ci` <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persona`
--

LOCK TABLES `persona` WRITE;
/*!40000 ALTER TABLE `persona` DISABLE KEYS */;
/*!40000 ALTER TABLE `persona` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto`
--

DROP TABLE IF EXISTS `presupuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto`
--

LOCK TABLES `presupuesto` WRITE;
/*!40000 ALTER TABLE `presupuesto` DISABLE KEYS */;
INSERT INTO `presupuesto` VALUES (5,'2025-10-07','2025-11-07',1,'APROBADO',177,1,NULL,NULL,NULL),(4,'2025-10-07','2025-11-07',1,'APROBADO',140,1,NULL,NULL,NULL),(3,'2025-10-03','2025-12-03',2,'APROBADO',139,1,NULL,NULL,NULL),(2,'2025-10-03','2026-01-03',1,'ANULADO',136,1,NULL,NULL,NULL),(1,'2025-10-02','2026-01-02',2,'APROBADO',112,1,NULL,NULL,NULL),(6,'2025-10-11','2025-11-11',2,'APROBADO',178,1,NULL,NULL,NULL),(7,'2025-10-14','2025-11-14',1,'APROBADO',198,1,NULL,NULL,NULL),(8,'2025-10-19','2025-11-19',2,'ANULADO',141,1,1,'2025-10-19','08:46:08'),(9,'2025-10-19','2025-11-19',2,'APROBADO',169,1,NULL,NULL,NULL),(10,'2025-10-22','2025-11-22',1,'APROBADO',206,1,NULL,NULL,NULL),(11,'2025-10-23','2025-11-23',2,'APROBADO',207,1,NULL,NULL,NULL),(12,'2025-10-24','2025-11-24',2,'APROBADO',NULL,1,NULL,NULL,NULL),(13,'2025-10-30','2025-11-30',2,'PENDIENTE',NULL,1,NULL,NULL,NULL),(14,'2026-10-02','2026-10-31',1,'PENDIENTE',NULL,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `presupuesto` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_presu` AFTER INSERT ON `presupuesto` FOR EACH ROW BEGIN
   DELETE FROM tmp_presu;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (1,2,1,'Vaso Plastico 2x ','10%'),(2,1,2,'a1','5%'),(3,2,1,'Producto1','10%');
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedor` (
  `cod_proveedor` int(11) NOT NULL,
  `razon_social` varchar(75) NOT NULL,
  `ruc` varchar(9) NOT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `telefono` int(11) NOT NULL,
  PRIMARY KEY (`cod_proveedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,'Empresa de lacteos','589236599','Calle X',985361242),(2,'Cervepar','800232699','Calle D',982555623);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol_descripcion` varchar(50) NOT NULL,
  `rol_observacion` varchar(150) DEFAULT NULL,
  `rol_protegido` tinyint(1) NOT NULL DEFAULT 0,
  `rol_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol_descripcion` (`rol_descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Super Admin','Acceso total al sistema. Rol protegido: no se puede editar ni dar de baja.',1,'activo'),(2,'Compras','Gestión del módulo de compras.',0,'activo'),(3,'Ventas','Gestión del módulo de ventas.',0,'activo'),(4,'Produccion','Personal de producción: acceso básico.',0,'activo');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_prod`
--

DROP TABLE IF EXISTS `stock_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_prod` (
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`cod_producto`),
  KEY `producto_stock_fk` (`cod_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_prod`
--

LOCK TABLES `stock_prod` WRITE;
/*!40000 ALTER TABLE `stock_prod` DISABLE KEYS */;
INSERT INTO `stock_prod` VALUES (1,48),(2,74),(3,6);
/*!40000 ALTER TABLE `stock_prod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_producto`
--

DROP TABLE IF EXISTS `tipo_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_producto` (
  `cod_tipo_prod` int(11) NOT NULL,
  `t_p_descrip` varchar(50) NOT NULL,
  PRIMARY KEY (`cod_tipo_prod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_producto`
--

LOCK TABLES `tipo_producto` WRITE;
/*!40000 ALTER TABLE `tipo_producto` DISABLE KEYS */;
INSERT INTO `tipo_producto` VALUES (1,'Lacteos'),(2,'Bebidas');
/*!40000 ALTER TABLE `tipo_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp`
--

DROP TABLE IF EXISTS `tmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad_tmp` int(11) DEFAULT NULL,
  `session_id` varchar(765) DEFAULT NULL,
  `estado_tmp` varchar(20) DEFAULT 'BORRADOR',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=307 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp`
--

LOCK TABLES `tmp` WRITE;
/*!40000 ALTER TABLE `tmp` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_compra`
--

DROP TABLE IF EXISTS `tmp_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_compra` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_orden_comp` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_comp_tmp_compra_fk` (`id_orden_comp`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp_compra`
--

LOCK TABLES `tmp_compra` WRITE;
/*!40000 ALTER TABLE `tmp_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_nota`
--

DROP TABLE IF EXISTS `tmp_nota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_nota` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `cod_compra` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp_nota`
--

LOCK TABLES `tmp_nota` WRITE;
/*!40000 ALTER TABLE `tmp_nota` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_nota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_orden`
--

DROP TABLE IF EXISTS `tmp_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_orden` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_presupuesto` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_orden_tmp_fk` (`id_presupuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp_orden`
--

LOCK TABLES `tmp_orden` WRITE;
/*!40000 ALTER TABLE `tmp_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_presu`
--

DROP TABLE IF EXISTS `tmp_presu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_presu` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `session_id` varchar(765) DEFAULT NULL,
  PRIMARY KEY (`id_tmp`),
  KEY `id_pedido_tmp_presu_fk` (`id_pedido`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp_presu`
--

LOCK TABLES `tmp_presu` WRITE;
/*!40000 ALTER TABLE `tmp_presu` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_presu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_venta`
--

DROP TABLE IF EXISTS `tmp_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_venta` (
  `id_tmp` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `cantidad_tmp` int(11) NOT NULL,
  `precio_tmp` int(11) NOT NULL,
  `session_id` varchar(765) NOT NULL,
  PRIMARY KEY (`id_tmp`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmp_venta`
--

LOCK TABLES `tmp_venta` WRITE;
/*!40000 ALTER TABLE `tmp_venta` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `u_medida`
--

DROP TABLE IF EXISTS `u_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `u_medida` (
  `id_u_medida` int(11) NOT NULL,
  `u_descrip` varchar(20) NOT NULL,
  PRIMARY KEY (`id_u_medida`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `u_medida`
--

LOCK TABLES `u_medida` WRITE;
/*!40000 ALTER TABLE `u_medida` DISABLE KEYS */;
INSERT INTO `u_medida` VALUES (1,'1 Litro'),(2,'1/2 Litro');
/*!40000 ALTER TABLE `u_medida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(150) DEFAULT NULL,
  `name_user` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(39) DEFAULT NULL,
  `foto` varchar(300) DEFAULT NULL,
  `permisos_acceso` varchar(300) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `status` char(27) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_usuarios_username` (`username`),
  UNIQUE KEY `uq_usuarios_funcionario` (`id_funcionario`),
  KEY `fk_usuarios_rol` (`id_rol`),
  CONSTRAINT `fk_usuarios_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'aldo','Aldo Torres','$2y$10$fgRKLy5BNEK7plh7vHdiReQ1jw1hCSMjwfDBAP4XjieElx5dZ4INW','aldo28071987@gmail.com','0987264101','jefe_choto_2.jpg','Super Admin',1,NULL,'activo',0,NULL),(2,'Ucompras','Usuario de compras','0cc175b9c0f1b6a831c399e269772661','usuariocompras@gmail.com','0987654321','3135768.png','Compras',2,NULL,'bloqueado',0,NULL),(3,'Uventas','Usuario de ventas','0cc175b9c0f1b6a831c399e269772661','uventas@gmail.com','0123654789','3135768.png','Ventas',3,NULL,'bloqueado',0,NULL),(4,'AuxiliarC','UserCompra','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Compras',2,NULL,'activo',0,NULL),(5,'JefaC','UserJefaCompra','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Compras',2,NULL,'activo',0,NULL),(6,'JefaLocal','UserJefaLocal','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Produccion',4,NULL,'activo',0,NULL),(7,'Encargado Produccion','UserEncargadoProduccion','202cb962ac59075b964b07152d234b70',NULL,NULL,NULL,'Produccion',4,NULL,'activo',0,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_ajuste`
--

DROP TABLE IF EXISTS `v_ajuste`;
/*!50001 DROP VIEW IF EXISTS `v_ajuste`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ajuste` AS SELECT
 1 AS `id_ajuste`,
  1 AS `fecha_ajuste`,
  1 AS `motivo`,
  1 AS `estado`,
  1 AS `anulado_por`,
  1 AS `anulado_fecha`,
  1 AS `anulado_hora`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `cantidad_ajustada`,
  1 AS `cantidad_anterior` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_clientes`
--

DROP TABLE IF EXISTS `v_clientes`;
/*!50001 DROP VIEW IF EXISTS `v_clientes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_clientes` AS SELECT
 1 AS `id_cliente`,
  1 AS `ci_ruc`,
  1 AS `cli_nombre`,
  1 AS `cli_apellido`,
  1 AS `cli_direccion`,
  1 AS `cli_telefono`,
  1 AS `cod_ciudad`,
  1 AS `descrip_ciudad`,
  1 AS `id_departamento`,
  1 AS `dep_descripcion` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_compras`
--

DROP TABLE IF EXISTS `v_compras`;
/*!50001 DROP VIEW IF EXISTS `v_compras`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_compras` AS SELECT
 1 AS `cod_compra`,
  1 AS `cod_proveedor`,
  1 AS `razon_social`,
  1 AS `ruc`,
  1 AS `fecha`,
  1 AS `hora`,
  1 AS `estado`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `id_orden_comp`,
  1 AS `con_sin_remision`,
  1 AS `fac_numero`,
  1 AS `fac_emision`,
  1 AS `tipo_factura`,
  1 AS `timbrado_nro`,
  1 AS `timb_fecha_venci`,
  1 AS `com_codicion`,
  1 AS `total_compra`,
  1 AS `anulado_por`,
  1 AS `anulado_fecha`,
  1 AS `anulado_hora`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `precio`,
  1 AS `cantidad`,
  1 AS `tipo_iva`,
  1 AS `exentas`,
  1 AS `iva5`,
  1 AS `iva10` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cuentas`
--

DROP TABLE IF EXISTS `v_cuentas`;
/*!50001 DROP VIEW IF EXISTS `v_cuentas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_cuentas` AS SELECT
 1 AS `cap_cod`,
  1 AS `cod_compra`,
  1 AS `cod_proveedor`,
  1 AS `razon_social`,
  1 AS `ruc`,
  1 AS `nro_cuota`,
  1 AS `cap_monto`,
  1 AS `cap_saldo`,
  1 AS `cap_fecha_venci`,
  1 AS `cap_estado` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_funcionario`
--

DROP TABLE IF EXISTS `v_funcionario`;
/*!50001 DROP VIEW IF EXISTS `v_funcionario`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_funcionario` AS SELECT
 1 AS `id_funcionario`,
  1 AS `fun_fecha_ingreso`,
  1 AS `fun_estado`,
  1 AS `id_cargo`,
  1 AS `car_descripcion`,
  1 AS `id_persona`,
  1 AS `per_nombre_completo`,
  1 AS `per_ci`,
  1 AS `per_correo`,
  1 AS `per_telefono`,
  1 AS `per_estado`,
  1 AS `id_user`,
  1 AS `username` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_nota`
--

DROP TABLE IF EXISTS `v_nota`;
/*!50001 DROP VIEW IF EXISTS `v_nota`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_nota` AS SELECT
 1 AS `id_nota`,
  1 AS `cod_compra`,
  1 AS `fac_numero`,
  1 AS `tipo`,
  1 AS `causa`,
  1 AS `nro_nota`,
  1 AS `timbrado`,
  1 AS `fecha_emision`,
  1 AS `estado`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `monto_nota`,
  1 AS `observacion`,
  1 AS `cod_proveedor`,
  1 AS `razon_social`,
  1 AS `ruc`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `cantidad`,
  1 AS `precio_unitario`,
  1 AS `monto_total`,
  1 AS `tipo_iva`,
  1 AS `exentas`,
  1 AS `iva5`,
  1 AS `iva10` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_notar`
--

DROP TABLE IF EXISTS `v_notar`;
/*!50001 DROP VIEW IF EXISTS `v_notar`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_notar` AS SELECT
 1 AS `id_notaR`,
  1 AS `cod_compra`,
  1 AS `cod_proveedor`,
  1 AS `ruc`,
  1 AS `razon_social`,
  1 AS `nro_nota`,
  1 AS `fecha`,
  1 AS `fecha_ini_traslado`,
  1 AS `tipo_traslado`,
  1 AS `motivo_traslado`,
  1 AS `nom_transporte`,
  1 AS `ruc_ci_trans`,
  1 AS `placa_vehiculo`,
  1 AS `id_user`,
  1 AS `estado`,
  1 AS `anulado_por`,
  1 AS `anulado_fecha`,
  1 AS `anulado_hora`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_orden_comp`
--

DROP TABLE IF EXISTS `v_orden_comp`;
/*!50001 DROP VIEW IF EXISTS `v_orden_comp`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_orden_comp` AS SELECT
 1 AS `id_orden_comp`,
  1 AS `fecha`,
  1 AS `estado`,
  1 AS `hora`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `id_presupuesto`,
  1 AS `cod_proveedor`,
  1 AS `razon_social`,
  1 AS `ruc`,
  1 AS `precio_unit`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_pedido`
--

DROP TABLE IF EXISTS `v_pedido`;
/*!50001 DROP VIEW IF EXISTS `v_pedido`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_pedido` AS SELECT
 1 AS `id_pedido`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `fecha`,
  1 AS `hora`,
  1 AS `estado`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_pedido_v`
--

DROP TABLE IF EXISTS `v_pedido_v`;
/*!50001 DROP VIEW IF EXISTS `v_pedido_v`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_pedido_v` AS SELECT
 1 AS `id_pedido_v`,
  1 AS `fecha_pedido`,
  1 AS `hora`,
  1 AS `estado`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `cod_deposito`,
  1 AS `descrip`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_persona`
--

DROP TABLE IF EXISTS `v_persona`;
/*!50001 DROP VIEW IF EXISTS `v_persona`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_persona` AS SELECT
 1 AS `id_persona`,
  1 AS `per_nombre`,
  1 AS `per_apellido`,
  1 AS `per_nombre_completo`,
  1 AS `per_ci`,
  1 AS `per_fecha_nac`,
  1 AS `per_edad`,
  1 AS `per_telefono`,
  1 AS `per_correo`,
  1 AS `per_direccion`,
  1 AS `per_estado`,
  1 AS `id_genero`,
  1 AS `gen_descripcion`,
  1 AS `id_nacionalidad`,
  1 AS `nac_descripcion`,
  1 AS `cod_ciudad`,
  1 AS `descrip_ciudad`,
  1 AS `id_departamento`,
  1 AS `dep_descripcion` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_presu`
--

DROP TABLE IF EXISTS `v_presu`;
/*!50001 DROP VIEW IF EXISTS `v_presu`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_presu` AS SELECT
 1 AS `id_presupuesto`,
  1 AS `id_pedido`,
  1 AS `cod_proveedor`,
  1 AS `razon_social`,
  1 AS `ruc`,
  1 AS `fecha_presu`,
  1 AS `fecha_vencimiento`,
  1 AS `estado`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cantidad`,
  1 AS `precio_unit`,
  1 AS `id_user`,
  1 AS `username` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_producto`
--

DROP TABLE IF EXISTS `v_producto`;
/*!50001 DROP VIEW IF EXISTS `v_producto`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_producto` AS SELECT
 1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `tipo_impuesto` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_stock`
--

DROP TABLE IF EXISTS `v_stock`;
/*!50001 DROP VIEW IF EXISTS `v_stock`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_stock` AS SELECT
 1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `t_p_descrip`,
  1 AS `u_descrip`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_usuarios`
--

DROP TABLE IF EXISTS `v_usuarios`;
/*!50001 DROP VIEW IF EXISTS `v_usuarios`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_usuarios` AS SELECT
 1 AS `id_user`,
  1 AS `username`,
  1 AS `name_user`,
  1 AS `email`,
  1 AS `telefono`,
  1 AS `foto`,
  1 AS `status`,
  1 AS `intentos_fallidos`,
  1 AS `id_rol`,
  1 AS `rol_descripcion`,
  1 AS `id_funcionario`,
  1 AS `per_nombre_completo`,
  1 AS `car_descripcion` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_ventas`
--

DROP TABLE IF EXISTS `v_ventas`;
/*!50001 DROP VIEW IF EXISTS `v_ventas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ventas` AS SELECT
 1 AS `cod_venta`,
  1 AS `id_cliente`,
  1 AS `cli_nombre`,
  1 AS `cli_apellido`,
  1 AS `id_user`,
  1 AS `name_user`,
  1 AS `fecha`,
  1 AS `estado`,
  1 AS `hora`,
  1 AS `cod_producto`,
  1 AS `p_descrip`,
  1 AS `cod_tipo_prod`,
  1 AS `t_p_descrip`,
  1 AS `id_u_medida`,
  1 AS `u_descrip`,
  1 AS `det_precio_unit`,
  1 AS `det_cantidad` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `venta`
--

DROP TABLE IF EXISTS `venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venta`
--

LOCK TABLES `venta` WRITE;
/*!40000 ALTER TABLE `venta` DISABLE KEYS */;
INSERT INTO `venta` VALUES (1,2,1,'2025-01-11','anulado','14:02:49',1,1),(2,1,1,'2025-01-12','activo','15:31:45',2,1),(3,1,1,'2025-01-15','activo','19:36:03',3,1);
/*!40000 ALTER TABLE `venta` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `borrar_tmp_v` AFTER INSERT ON `venta` FOR EACH ROW BEGIN
   DELETE FROM tmp_venta;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'sysweb'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `recalcular_cuotas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `recalcular_cuotas`(
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Current Database: `sysweb`
--

USE `sysweb`;

--
-- Final view structure for view `v_ajuste`
--

/*!50001 DROP VIEW IF EXISTS `v_ajuste`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ajuste` AS select `aju`.`id_ajuste` AS `id_ajuste`,`aju`.`fecha_ajuste` AS `fecha_ajuste`,`aju`.`motivo` AS `motivo`,`aju`.`estado` AS `estado`,`aju`.`anulado_por` AS `anulado_por`,`aju`.`anulado_fecha` AS `anulado_fecha`,`aju`.`anulado_hora` AS `anulado_hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`det`.`cantidad_ajustada` AS `cantidad_ajustada`,`det`.`cantidad_anterior` AS `cantidad_anterior` from (((((`ajuste_com` `aju` join `det_ajuste` `det`) join `producto` `pro`) join `usuarios` `usu`) join `tipo_producto` `tp`) join `u_medida` `u`) where `aju`.`id_ajuste` = `det`.`id_ajuste` and `det`.`cod_producto` = `pro`.`cod_producto` and `aju`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_clientes`
--

/*!50001 DROP VIEW IF EXISTS `v_clientes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_clientes` AS select `cli`.`id_cliente` AS `id_cliente`,`cli`.`ci_ruc` AS `ci_ruc`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`cli`.`cli_direccion` AS `cli_direccion`,`cli`.`cli_telefono` AS `cli_telefono`,`ciu`.`cod_ciudad` AS `cod_ciudad`,`ciu`.`descrip_ciudad` AS `descrip_ciudad`,`dep`.`id_departamento` AS `id_departamento`,`dep`.`dep_descripcion` AS `dep_descripcion` from ((`clientes` `cli` join `departamento` `dep`) join `ciudad` `ciu`) where `cli`.`cod_ciudad` = `ciu`.`cod_ciudad` and `ciu`.`id_departamento` = `dep`.`id_departamento` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_compras`
--

/*!50001 DROP VIEW IF EXISTS `v_compras`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_compras` AS select `com`.`cod_compra` AS `cod_compra`,`com`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`com`.`fecha` AS `fecha`,`com`.`hora` AS `hora`,`com`.`estado` AS `estado`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`com`.`id_orden_comp` AS `id_orden_comp`,`com`.`con_sin_remision` AS `con_sin_remision`,`com`.`fac_numero` AS `fac_numero`,`com`.`fac_emision` AS `fac_emision`,`com`.`tipo_factura` AS `tipo_factura`,`com`.`timbrado_nro` AS `timbrado_nro`,`com`.`timb_fecha_venci` AS `timb_fecha_venci`,`com`.`com_condicion` AS `com_codicion`,`com`.`total_compra` AS `total_compra`,`com`.`anulado_por` AS `anulado_por`,`com`.`anulado_fecha` AS `anulado_fecha`,`com`.`anulado_hora` AS `anulado_hora`,`det`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`precio` AS `precio`,`det`.`cantidad` AS `cantidad`,`det`.`tipo_iva` AS `tipo_iva`,`det`.`exentas` AS `exentas`,`det`.`iva5` AS `iva5`,`det`.`iva10` AS `iva10` from ((((((`compra` `com` left join `detalle_compra` `det` on(`det`.`cod_compra` = `com`.`cod_compra`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `proveedor` `prov` on(`prov`.`cod_proveedor` = `com`.`cod_proveedor`)) join `usuarios` `usu` on(`com`.`id_user` = `usu`.`id_user`)) left join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) left join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cuentas`
--

/*!50001 DROP VIEW IF EXISTS `v_cuentas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cuentas` AS select `cap`.`cap_cod` AS `cap_cod`,`com`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`cap`.`nro_cuota` AS `nro_cuota`,`cap`.`cap_monto` AS `cap_monto`,`cap`.`cap_saldo` AS `cap_saldo`,`cap`.`cap_fecha_venci` AS `cap_fecha_venci`,`cap`.`cap_estado` AS `cap_estado` from ((`cuentas_a_pagar` `cap` join `compra` `com`) join `proveedor` `prov`) where `cap`.`cod_compra` = `com`.`cod_compra` and `com`.`cod_proveedor` = `prov`.`cod_proveedor` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_funcionario`
--

/*!50001 DROP VIEW IF EXISTS `v_funcionario`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_funcionario` AS select `f`.`id_funcionario` AS `id_funcionario`,`f`.`fun_fecha_ingreso` AS `fun_fecha_ingreso`,`f`.`fun_estado` AS `fun_estado`,`ca`.`id_cargo` AS `id_cargo`,`ca`.`car_descripcion` AS `car_descripcion`,`v`.`id_persona` AS `id_persona`,`v`.`per_nombre_completo` AS `per_nombre_completo`,`v`.`per_ci` AS `per_ci`,`v`.`per_correo` AS `per_correo`,`v`.`per_telefono` AS `per_telefono`,`v`.`per_estado` AS `per_estado`,`u`.`id_user` AS `id_user`,`u`.`username` AS `username` from (((`funcionario` `f` join `cargo` `ca` on(`ca`.`id_cargo` = `f`.`id_cargo`)) join `v_persona` `v` on(`v`.`id_persona` = `f`.`id_persona`)) left join `usuarios` `u` on(`u`.`id_funcionario` = `f`.`id_funcionario`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_nota`
--

/*!50001 DROP VIEW IF EXISTS `v_nota`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_nota` AS select `nota`.`id_nota` AS `id_nota`,`com`.`cod_compra` AS `cod_compra`,`nota`.`fac_numero` AS `fac_numero`,`nota`.`tipo` AS `tipo`,`nota`.`causa` AS `causa`,`nota`.`nro_nota` AS `nro_nota`,`nota`.`timbrado` AS `timbrado`,`nota`.`fecha_emision` AS `fecha_emision`,`nota`.`estado` AS `estado`,`nota`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`nota`.`monto_total` AS `monto_nota`,`nota`.`observacion` AS `observacion`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`det`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`pro`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad`,`det`.`precio_unitario` AS `precio_unitario`,`det`.`cantidad` * `det`.`precio_unitario` AS `monto_total`,`det`.`tipo_iva` AS `tipo_iva`,`det`.`exentas` AS `exentas`,`det`.`iva5` AS `iva5`,`det`.`iva10` AS `iva10` from (((((((`nota_credito_debito` `nota` left join `det_nota_credit_debit` `det` on(`nota`.`id_nota` = `det`.`id_nota`)) join `compra` `com` on(`nota`.`cod_compra` = `com`.`cod_compra`)) join `proveedor` `prov` on(`com`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `usuarios` `usu` on(`nota`.`id_user` = `usu`.`id_user`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) left join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) left join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_notar`
--

/*!50001 DROP VIEW IF EXISTS `v_notar`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_notar` AS select `n`.`id_notaR` AS `id_notaR`,`c`.`cod_compra` AS `cod_compra`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`ruc` AS `ruc`,`prov`.`razon_social` AS `razon_social`,`n`.`nro_nota` AS `nro_nota`,`n`.`fecha` AS `fecha`,`n`.`fecha_ini_traslado` AS `fecha_ini_traslado`,`n`.`tipo_traslado` AS `tipo_traslado`,`n`.`motivo_traslado` AS `motivo_traslado`,`n`.`nom_transporte` AS `nom_transporte`,`n`.`ruc_ci_trans` AS `ruc_ci_trans`,`n`.`placa_vehiculo` AS `placa_vehiculo`,`n`.`id_user` AS `id_user`,`n`.`estado` AS `estado`,`n`.`anulado_por` AS `anulado_por`,`n`.`anulado_fecha` AS `anulado_fecha`,`n`.`anulado_hora` AS `anulado_hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad` from (((((`notar_compra` `n` join `det_notar_compra` `det` on(`det`.`id_notaR` = `n`.`id_notaR`)) join `compra` `c` on(`c`.`cod_compra` = `n`.`cod_compra`)) join `proveedor` `prov` on(`c`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `u_medida` `u` on(`u`.`id_u_medida` = `pro`.`id_u_medida`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_orden_comp`
--

/*!50001 DROP VIEW IF EXISTS `v_orden_comp`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_orden_comp` AS select `orde`.`id_orden_comp` AS `id_orden_comp`,`orde`.`fecha` AS `fecha`,`orde`.`estado` AS `estado`,`orde`.`hora` AS `hora`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`presu`.`id_presupuesto` AS `id_presupuesto`,`prov`.`cod_proveedor` AS `cod_proveedor`,`prov`.`razon_social` AS `razon_social`,`prov`.`ruc` AS `ruc`,`det`.`precio_unit` AS `precio_unit`,`det`.`cantidad` AS `cantidad` from (((((((`orden_compra` `orde` join `detalle_orden_comp` `det` on(`orde`.`id_orden_comp` = `det`.`id_orden_comp`)) join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) join `usuarios` `usu` on(`orde`.`id_user` = `usu`.`id_user`)) left join `presupuesto` `presu` on(`orde`.`id_presupuesto` = `presu`.`id_presupuesto`)) join `proveedor` `prov` on(`orde`.`cod_proveedor` = `prov`.`cod_proveedor`)) join `tipo_producto` `tp` on(`pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod`)) join `u_medida` `u` on(`pro`.`id_u_medida` = `u`.`id_u_medida`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_pedido`
--

/*!50001 DROP VIEW IF EXISTS `v_pedido`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_pedido` AS select `pe`.`id_pedido` AS `id_pedido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pe`.`fecha` AS `fecha`,`pe`.`hora` AS `hora`,`pe`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`cantidad` AS `cantidad` from (((((`pedido` `pe` join `det_pedido` `det`) join `usuarios` `usu`) join `producto` `pro`) join `tipo_producto` `tp`) join `u_medida` `u`) where `pe`.`id_user` = `usu`.`id_user` and `pe`.`id_pedido` = `det`.`id_pedido` and `det`.`cod_producto` = `pro`.`cod_producto` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_pedido_v`
--

/*!50001 DROP VIEW IF EXISTS `v_pedido_v`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_pedido_v` AS select `p`.`id_pedido_v` AS `id_pedido_v`,`p`.`fecha_pedido` AS `fecha_pedido`,`p`.`hora` AS `hora`,`p`.`estado` AS `estado`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`dep`.`cod_deposito` AS `cod_deposito`,`dep`.`descrip` AS `descrip`,`det`.`cantidad` AS `cantidad` from ((((((`pedido_v` `p` join `det_pedido_v` `det`) join `usuarios` `usu`) join `producto` `pro`) join `deposito` `dep`) join `tipo_producto` `tp`) join `u_medida` `u`) where `p`.`id_pedido_v` = `det`.`id_pedido_v` and `det`.`cod_producto` = `pro`.`cod_producto` and `det`.`cod_deposito` = `dep`.`cod_deposito` and `p`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_persona`
--

/*!50001 DROP VIEW IF EXISTS `v_persona`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_persona` AS select `p`.`id_persona` AS `id_persona`,`p`.`per_nombre` AS `per_nombre`,`p`.`per_apellido` AS `per_apellido`,concat(`p`.`per_nombre`,' ',`p`.`per_apellido`) AS `per_nombre_completo`,`p`.`per_ci` AS `per_ci`,`p`.`per_fecha_nac` AS `per_fecha_nac`,timestampdiff(YEAR,`p`.`per_fecha_nac`,curdate()) AS `per_edad`,`p`.`per_telefono` AS `per_telefono`,`p`.`per_correo` AS `per_correo`,`p`.`per_direccion` AS `per_direccion`,`p`.`per_estado` AS `per_estado`,`g`.`id_genero` AS `id_genero`,`g`.`gen_descripcion` AS `gen_descripcion`,`n`.`id_nacionalidad` AS `id_nacionalidad`,`n`.`nac_descripcion` AS `nac_descripcion`,`c`.`cod_ciudad` AS `cod_ciudad`,`c`.`descrip_ciudad` AS `descrip_ciudad`,`d`.`id_departamento` AS `id_departamento`,`d`.`dep_descripcion` AS `dep_descripcion` from ((((`persona` `p` join `genero` `g` on(`g`.`id_genero` = `p`.`id_genero`)) join `nacionalidad` `n` on(`n`.`id_nacionalidad` = `p`.`id_nacionalidad`)) join `ciudad` `c` on(`c`.`cod_ciudad` = `p`.`cod_ciudad`)) join `departamento` `d` on(`d`.`id_departamento` = `c`.`id_departamento`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_presu`
--

/*!50001 DROP VIEW IF EXISTS `v_presu`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_presu` AS select `pre`.`id_presupuesto` AS `id_presupuesto`,`ped`.`id_pedido` AS `id_pedido`,`prv`.`cod_proveedor` AS `cod_proveedor`,`prv`.`razon_social` AS `razon_social`,`prv`.`ruc` AS `ruc`,`pre`.`fecha_presu` AS `fecha_presu`,`pre`.`fecha_vencimiento` AS `fecha_vencimiento`,`pre`.`estado` AS `estado`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`det`.`cantidad` AS `cantidad`,`det`.`precio_unit` AS `precio_unit`,`usu`.`id_user` AS `id_user`,`usu`.`username` AS `username` from (((((`presupuesto` `pre` join `proveedor` `prv` on(`pre`.`cod_proveedor` = `prv`.`cod_proveedor`)) join `det_presu` `det` on(`pre`.`id_presupuesto` = `det`.`id_presupuesto`)) left join `producto` `pro` on(`det`.`cod_producto` = `pro`.`cod_producto`)) left join `pedido` `ped` on(`pre`.`id_pedido` = `ped`.`id_pedido`)) join `usuarios` `usu` on(`pre`.`id_user` = `usu`.`id_user`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_producto`
--

/*!50001 DROP VIEW IF EXISTS `v_producto`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_producto` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`um`.`id_u_medida` AS `id_u_medida`,`um`.`u_descrip` AS `u_descrip`,`pro`.`tipo_impuesto` AS `tipo_impuesto` from ((`producto` `pro` join `tipo_producto` `tp`) join `u_medida` `um`) where `tp`.`cod_tipo_prod` = `pro`.`cod_tipo_prod` and `um`.`id_u_medida` = `pro`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_stock`
--

/*!50001 DROP VIEW IF EXISTS `v_stock`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_stock` AS select `pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tpro`.`t_p_descrip` AS `t_p_descrip`,`um`.`u_descrip` AS `u_descrip`,`st`.`cantidad` AS `cantidad` from (((`stock_prod` `st` join `producto` `pro`) join `tipo_producto` `tpro`) join `u_medida` `um`) where `st`.`cod_producto` = `pro`.`cod_producto` and `pro`.`cod_tipo_prod` = `tpro`.`cod_tipo_prod` and `pro`.`id_u_medida` = `um`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_usuarios`
--

/*!50001 DROP VIEW IF EXISTS `v_usuarios`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_usuarios` AS select `u`.`id_user` AS `id_user`,`u`.`username` AS `username`,`u`.`name_user` AS `name_user`,`u`.`email` AS `email`,`u`.`telefono` AS `telefono`,`u`.`foto` AS `foto`,`u`.`status` AS `status`,`u`.`intentos_fallidos` AS `intentos_fallidos`,`r`.`id_rol` AS `id_rol`,`r`.`rol_descripcion` AS `rol_descripcion`,`f`.`id_funcionario` AS `id_funcionario`,`f`.`per_nombre_completo` AS `per_nombre_completo`,`f`.`car_descripcion` AS `car_descripcion` from ((`usuarios` `u` left join `rol` `r` on(`r`.`id_rol` = `u`.`id_rol`)) left join `v_funcionario` `f` on(`f`.`id_funcionario` = `u`.`id_funcionario`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ventas`
--

/*!50001 DROP VIEW IF EXISTS `v_ventas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ventas` AS select `v`.`cod_venta` AS `cod_venta`,`cli`.`id_cliente` AS `id_cliente`,`cli`.`cli_nombre` AS `cli_nombre`,`cli`.`cli_apellido` AS `cli_apellido`,`usu`.`id_user` AS `id_user`,`usu`.`name_user` AS `name_user`,`v`.`fecha` AS `fecha`,`v`.`estado` AS `estado`,`v`.`hora` AS `hora`,`pro`.`cod_producto` AS `cod_producto`,`pro`.`p_descrip` AS `p_descrip`,`tp`.`cod_tipo_prod` AS `cod_tipo_prod`,`tp`.`t_p_descrip` AS `t_p_descrip`,`u`.`id_u_medida` AS `id_u_medida`,`u`.`u_descrip` AS `u_descrip`,`det`.`det_precio_unit` AS `det_precio_unit`,`det`.`det_cantidad` AS `det_cantidad` from ((((((`venta` `v` join `det_venta` `det`) join `producto` `pro`) join `usuarios` `usu`) join `clientes` `cli`) join `tipo_producto` `tp`) join `u_medida` `u`) where `v`.`cod_venta` = `det`.`cod_venta` and `v`.`id_cliente` = `cli`.`id_cliente` and `det`.`cod_producto` = `pro`.`cod_producto` and `v`.`id_user` = `usu`.`id_user` and `pro`.`cod_tipo_prod` = `tp`.`cod_tipo_prod` and `pro`.`id_u_medida` = `u`.`id_u_medida` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
