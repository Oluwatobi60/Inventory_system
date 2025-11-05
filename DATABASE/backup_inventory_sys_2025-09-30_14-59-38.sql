-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: inventory_sys
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asset_replacement_log`
--

DROP TABLE IF EXISTS `asset_replacement_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_replacement_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `replaced_quantity` int NOT NULL,
  `replaced_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `replaced` int NOT NULL,
  `reg_no` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `floor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_replacement_log`
--

LOCK TABLES `asset_replacement_log` WRITE;
/*!40000 ALTER TABLE `asset_replacement_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_replacement_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_table`
--

DROP TABLE IF EXISTS `asset_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `dateofpurchase` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_table`
--

LOCK TABLES `asset_table` WRITE;
/*!40000 ALTER TABLE `asset_table` DISABLE KEYS */;
INSERT INTO `asset_table` VALUES (31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','2025-11-05','2025-11-05'),(32,'ISL340','HP ELITEBOOK 840 G2','Good Condition','2','Laptops','2025-11-05','2025-11-05'),(33,'ISL704','Z230','In Good condition','2','Desktops','2025-11-05','2025-11-05'),(34,'ISL299','HP LAZERJET PRINTER','In Good Condition','2','Printers','2025-11-05',NULL),(35,'ISL898','Scanner','In Good Condition','3','Scanner','2025-11-05',NULL),(36,'ISL982','Old Desktop','Have been for a long time','0','Desktops','2025-11-05','2025-11-05'),(37,'ISL276','Old Laptop','Very Old','0','Laptops','2025-11-05','2025-11-05'),(38,'ISL427','HP ELITEBOOK 850 G3','In Good Condition','0','Laptops','2025-11-05',NULL);
/*!40000 ALTER TABLE `asset_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrow_table`
--

DROP TABLE IF EXISTS `borrow_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrow_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `purpose` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `borrow_date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `borrow_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `admin_borrow_for` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `hod_status` int NOT NULL,
  `pro_status` int NOT NULL,
  `returned` int NOT NULL,
  `returned_date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow_table`
--

LOCK TABLES `borrow_table` WRITE;
/*!40000 ALTER TABLE `borrow_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `borrow_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (5,'Laptops'),(13,'Printers'),(16,'Desktops'),(21,'AC'),(22,'Scanner');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `completed_asset`
--

DROP TABLE IF EXISTS `completed_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `completed_asset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `quantity` int NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `floor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `completed` int NOT NULL,
  `completed_date` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `reg_no` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `reported_by` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completed_asset`
--

LOCK TABLES `completed_asset` WRITE;
/*!40000 ALTER TABLE `completed_asset` DISABLE KEYS */;
/*!40000 ALTER TABLE `completed_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_borrow_table`
--

DROP TABLE IF EXISTS `department_borrow_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_borrow_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_general_ci,
  `quantity` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `borrow_by_dept` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `borrow_date` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hod_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `hod_status` int NOT NULL,
  `returned` int NOT NULL,
  `return_date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_borrow_table`
--

LOCK TABLES `department_borrow_table` WRITE;
/*!40000 ALTER TABLE `department_borrow_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_borrow_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_table`
--

DROP TABLE IF EXISTS `department_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `floor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_table`
--

LOCK TABLES `department_table` WRITE;
/*!40000 ALTER TABLE `department_table` DISABLE KEYS */;
INSERT INTO `department_table` VALUES (16,'Nursing2','Second Floor'),(17,'Nursing3','Third Floor'),(18,'Nursing4','Fourth Floor'),(19,'Nursing5','Fifth Floor'),(20,'Nursing6','Sixth Floor'),(21,'Facility','Annex Building'),(22,'Audit','Annex Building'),(23,'Procurement','Annex Building'),(24,'Information Technology','IT Office'),(25,'Account','Annex Building'),(26,'HR Office','Annex Building'),(27,'Billing Cost Control (BCC)','Annex Building'),(28,'Client Help Desk','Ground Floor'),(29,'Client Help-Desk','Second Floor'),(30,'Client Support','First Floor'),(31,'Client-Support','Second Floor'),(32,'Pharmarcy1','First Floor'),(33,'Pharmarcy2','Second Floor'),(34,'Inpatient Billing Pharmarcy3','Third Floor'),(35,'Inpatient Billing Pharmarcy4','Fourth Floor'),(36,'Inpatient Billing Pharmarcy5','Fifth Floor'),(37,'Inpatient Billing3','Third Floor'),(38,'Inpatient Billing5','Fifth Floor'),(39,'Lab1','First Floor'),(40,'Lab2','Second Floor'),(41,'Nursing Vital1','First Floor'),(42,'Nursing Vital2','Second Floor'),(43,'Nursing Treatment Room1','First Floor'),(44,'Nursing Treatment Room2','Second Floor'),(45,'Nursing Emmergency','Ground Floor'),(46,'Nursing Monitor','First Floor'),(47,'Nursing Monitor2','Second Floor'),(48,'Client Cash Desk','Second Floor'),(49,'Radiology','Second Floor'),(50,'Radiology2','Annex Building'),(51,'Data Validation','Second Floor'),(52,'Doctor Consultation Room','First Floor'),(53,'Doctor Consultation Room1','Second Floor'),(54,'General','Second Floor'),(55,'General1','First Floor'),(56,'MD Office','Second Floor'),(57,'Doctor Theater Room','Third Floor'),(58,'Doctor Consult','Fifth Floor'),(59,'Nursing7','Seventh Floor'),(60,'Doctor Consultation7','Seventh Floor'),(61,'Main Store','Annex Building');
/*!40000 ALTER TABLE `department_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_table`
--

DROP TABLE IF EXISTS `maintenance_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `last_service` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `next_service` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_table`
--

LOCK TABLES `maintenance_table` WRITE;
/*!40000 ALTER TABLE `maintenance_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair_asset`
--

DROP TABLE IF EXISTS `repair_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair_asset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `reg_no` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `reported_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'General',
  `quantity` int DEFAULT '1',
  `report_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Under Repair',
  `floor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `completed_date` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `completed` int NOT NULL,
  `withdrawn_date` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `withdrawn` int NOT NULL,
  `withdrawn_reason` text COLLATE utf8mb4_general_ci NOT NULL,
  `replaced_date` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `replaced` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_repair` (`asset_id`,`status`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_reg_no` (`reg_no`),
  KEY `idx_status` (`status`),
  CONSTRAINT `repair_asset_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `staff_table` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair_asset`
--

LOCK TABLES `repair_asset` WRITE;
/*!40000 ALTER TABLE `repair_asset` DISABLE KEYS */;
/*!40000 ALTER TABLE `repair_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_table`
--

DROP TABLE IF EXISTS `request_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `assigned_employee` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `requested_by` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `request_date` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `hod_approved` int NOT NULL,
  `pro_approved` int NOT NULL,
  `approval_date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_table`
--

LOCK TABLES `request_table` WRITE;
/*!40000 ALTER TABLE `request_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_role` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'admin'),(3,'audit'),(7,'procurement'),(8,'facility'),(9,'account');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_table`
--

DROP TABLE IF EXISTS `staff_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `reg_no` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `floor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `requested_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `request_date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdrawn` int NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_table`
--

LOCK TABLES `staff_table` WRITE;
/*!40000 ALTER TABLE `staff_table` DISABLE KEYS */;
INSERT INTO `staff_table` VALUES (78,33,'ISL704','Z230','In Good condition','3','Desktops','Client Support','First Floor','admin','2025-11-05 13:21:00',NULL,0,NULL),(79,33,'ISL704','Z230','In Good condition','2','Desktops','Pharmarcy1','First Floor','admin','2025-11-05 13:22:00',NULL,0,NULL),(80,32,'ISL340','HP ELITEBOOK 840 G2','Good Condition','1','Laptops','Lab1','First Floor','admin','2025-11-05 13:22:00',NULL,0,NULL),(82,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Pharmarcy1','First Floor','admin','2025-11-05 13:26:00',NULL,0,NULL),(83,33,'ISL704','Z230','In Good condition','2','Desktops','Client Help Desk','Ground Floor','admin','2025-11-05 13:27:00',NULL,0,'2025-11-04 23:00:00'),(85,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','1','Laptops','Nursing Emmergency','Ground Floor','admin','2025-11-05 14:58:00',NULL,0,'2025-11-04 23:00:00'),(86,32,'ISL340','HP ELITEBOOK 840 G2','Good Condition','1','Laptops','Nursing Emmergency','Ground Floor','admin','2025-11-05 14:59:00',NULL,0,'2025-11-04 23:00:00'),(87,36,'ISL982','Old Desktop','Have been for a long time','2','Desktops','Nursing Vital1','First Floor','admin','2025-11-05 15:03:00',NULL,0,'2025-11-04 23:00:00'),(88,32,'ISL340','HP ELITEBOOK 840 G2','Good Condition','1','Laptops','Nursing Treatment Room1','First Floor','admin','2025-11-05 15:04:00',NULL,0,'2025-11-04 23:00:00'),(89,33,'ISL704','Z230','In Good condition','3','Desktops','Client-Support','Second Floor','admin','2025-11-05 15:05:00',NULL,0,NULL),(90,33,'ISL704','Z230','In Good condition','1','Desktops','Client Help Desk','Second Floor','admin','2025-11-05 15:06:00',NULL,0,'2025-11-04 23:00:00'),(91,33,'ISL704','Z230','In Good condition','2','Desktops','Pharmarcy2','Second Floor','admin','2025-11-05 15:07:00',NULL,0,NULL),(92,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Client-Support','Second Floor','admin','2025-11-05 15:08:00',NULL,0,NULL),(93,33,'ISL704','Old Desktop','In Good condition','1','Desktops','Nursing Treatment Room2','Second Floor','admin','2025-11-05 15:10:00',NULL,0,'2025-11-04 23:00:00'),(94,33,'ISL704','Z230','In Good condition','1','Desktops','Nursing Monitor','First Floor','admin','2025-11-05 15:16:00',NULL,0,NULL),(95,33,'ISL704','Z230','In Good condition','1','Desktops','Nursing Monitor2','Second Floor','admin','2025-11-05 15:16:00',NULL,0,NULL),(96,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Pharmarcy2','Second Floor','admin','2025-11-05 15:17:00',NULL,0,NULL),(97,36,'ISL982','Old Desktop','Have been for a long time','2','Desktops','Nursing Vital2','Second Floor','admin','2025-11-05 15:19:00',NULL,0,'2025-11-04 23:00:00'),(98,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','3','Laptops','Client Cash Desk','Second Floor','admin','2025-11-05 15:40:00',NULL,0,NULL),(99,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Radiology','Second Floor','admin','2025-11-05 15:41:00',NULL,0,NULL),(100,33,'ISL704','Z230','In Good condition','1','Desktops','Radiology','Second Floor','admin','2025-11-05 15:42:00',NULL,0,NULL),(101,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','5','Laptops','Data Validation','Second Floor','admin','2025-11-05 15:45:00',NULL,0,'2025-11-04 23:00:00'),(102,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','7','Laptops','Doctor Consultation Room','First Floor','admin','2025-11-05 16:12:00',NULL,0,NULL),(103,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','7','Laptops','Doctor Consultation Room1','Second Floor','admin','2025-11-05 16:12:00',NULL,0,'2025-11-04 23:00:00'),(104,34,'ISL299','HP LAZERJET PRINTER','In Good Condition','1','Printers','Client Help Desk','Ground Floor','admin','2025-11-05 16:14:00',NULL,0,NULL),(105,34,'ISL299','HP LAZERJET PRINTER','In Good Condition','1','Printers','General','Second Floor','admin','2025-11-05 16:16:00',NULL,0,NULL),(106,34,'ISL299','HP LAZERJET PRINTER','In Good Condition','1','Printers','General1','First Floor','admin','2025-11-05 16:17:00',NULL,0,NULL),(107,34,'ISL299','HP LAZERJET PRINTER','In Good Condition','1','Printers','Inpatient Billing3','Third Floor','admin','2025-11-05 16:17:00',NULL,0,NULL),(108,34,'ISL299','HP LAZERJET PRINTER','In Good Condition','1','Printers','MD Office','Second Floor','admin','2025-11-05 16:18:00',NULL,0,NULL),(109,33,'ISL704','Z230','In Good condition','2','Desktops','Lab2','Second Floor','admin','2025-11-05 16:20:00',NULL,0,NULL),(110,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Lab2','Second Floor','admin','2025-11-05 16:20:00',NULL,0,NULL),(111,33,'ISL704','Z230','In Good condition','2','Desktops','Inpatient Billing3','Third Floor','admin','2025-11-05 16:21:00',NULL,0,NULL),(112,33,'ISL704','Z230','In Good condition','1','Desktops','Inpatient Billing Pharmarcy3','Third Floor','admin','2025-11-05 16:24:00',NULL,0,NULL),(113,33,'ISL704','Z230','In Good condition','1','Desktops','MD Office','Second Floor','admin','2025-11-05 16:24:00',NULL,0,NULL),(114,33,'ISL704','Z230','In Good condition','1','Desktops','Nursing3','Third Floor','admin','2025-11-05 16:25:00',NULL,0,NULL),(115,33,'ISL704','Z230','In Good condition','1','Desktops','Doctor Theater Room','Third Floor','admin','2025-11-05 16:26:00',NULL,0,NULL),(116,31,'ISL106','Old Laptop','Bought in Good condition','1','Laptops','Doctor Theater Room','Third Floor','admin','2025-11-05 16:26:00',NULL,0,'2025-11-04 23:00:00'),(117,33,'ISL704','Z230','In Good condition','2','Desktops','Nursing4','Fourth Floor','admin','2025-11-05 16:27:00',NULL,0,NULL),(118,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Nursing3','Third Floor','admin','2025-11-05 16:28:00',NULL,0,'2025-11-04 23:00:00'),(119,33,'ISL704','Z230','In Good condition','2','Desktops','Nursing5','Fifth Floor','admin','2025-11-05 16:29:00',NULL,0,NULL),(120,33,'ISL704','Z230','In Good condition','1','Desktops','Inpatient Billing Pharmarcy5','Fifth Floor','admin','2025-11-05 16:30:00',NULL,0,NULL),(121,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','1','Laptops','Inpatient Billing Pharmarcy5','Fifth Floor','admin','2025-11-05 16:32:00',NULL,0,NULL),(122,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','1','Laptops','Doctor Consult','Fifth Floor','admin','2025-11-05 16:33:00',NULL,0,NULL),(123,33,'ISL704','Z230','In Good condition','1','Desktops','Nursing6','Sixth Floor','admin','2025-11-05 16:34:00',NULL,0,NULL),(124,36,'ISL982','Old Desktop','Have been for a long time','1','Desktops','Nursing6','Sixth Floor','admin','2025-11-05 16:34:00',NULL,0,NULL),(125,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','1','Laptops','Nursing6','Sixth Floor','admin','2025-11-05 16:34:00',NULL,0,NULL),(126,33,'ISL704','Z230','In Good condition','1','Desktops','Nursing7','Seventh Floor','admin','2025-11-05 16:35:00',NULL,0,NULL),(127,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','1','Laptops','Doctor Consultation7','Seventh Floor','admin','2025-11-05 16:36:00',NULL,0,NULL),(128,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','14','Laptops','Billing Cost Control (BCC)','Annex Building','admin','2025-11-05 16:41:00',NULL,0,NULL),(129,37,'ISL276','Old Laptop','Very Old','2','Laptops','Billing Cost Control (BCC)','Annex Building','admin','2025-11-05 16:42:00',NULL,0,NULL),(130,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Procurement','Annex Building','admin','2025-11-05 16:43:00',NULL,0,NULL),(131,37,'ISL276','Old Laptop','Very Old','2','Laptops','Main Store','Annex Building','admin','2025-11-05 16:44:00',NULL,0,NULL),(132,37,'ISL276','Old Laptop','Very Old','1','Laptops','Facility','Annex Building','admin','2025-11-05 16:46:00',NULL,0,NULL),(133,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','4','Laptops','HR Office','Annex Building','admin','2025-11-05 16:47:00',NULL,0,NULL),(134,38,'ISL427','HP ELITEBOOK 850 G3','In Good Condition','1','Laptops','Audit','Annex Building','admin','2025-11-05 17:13:00',NULL,0,NULL),(135,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','2','Laptops','Audit','Annex Building','admin','2025-11-05 17:15:00',NULL,0,NULL),(136,32,'ISL340','HP ELITEBOOK 840 G2','Good Condition','1','Laptops','Audit','Annex Building','admin','2025-11-05 17:16:00',NULL,0,NULL),(137,31,'ISL106','HP ELITEBOOK 840 G3','Bought in Good condition','6','Laptops','Account','Annex Building','admin','2025-11-05 17:16:00',NULL,0,NULL),(138,38,'ISL427','HP ELITEBOOK 850 G3','In Good Condition','1','Laptops','Account','Annex Building','admin','2025-11-05 17:18:00',NULL,0,NULL);
/*!40000 ALTER TABLE `staff_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_table`
--

DROP TABLE IF EXISTS `user_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_table`
--

LOCK TABLES `user_table` WRITE;
/*!40000 ALTER TABLE `user_table` DISABLE KEYS */;
INSERT INTO `user_table` VALUES (7,'Odeyemi','Oluwatobi','Tobestic','tobestic53@gmail.com','12345','admin','08143405243','Admin'),(19,'Odeyemi','Timothy','facility','olasunkanmioye17@gmail.com','$2y$10$k9K.q6TUrwpL9NZ3ZzmQaOtSgDTLkIR4wRbpufTNjiOznypjKDFUa','facility','08154883269','Facility'),(20,'Odeyemi','Oluwatobi','admin','odeyemioluwatobi60@gmail.com','$2y$10$63jf2Bx.9hcQ5BG5C7AlbuGXLDsa2ZfnqDl0Ts0Qf1wVlM37hCdBy','admin','08154883254','Information Technology'),(21,'Odeyemi','Olasunkanmi','audit','olavikessential24@gmail.com','$2y$10$8Q6R5tOO6ijJ8Pf/X80BVuuLlhNMSzbQpBWHD7OWxkBkuyLLnujOS','audit','08154883763','Audit'),(22,'Odeyemi','Oye','procurement','oluwakemiruth.olanipekun@gmail.com','$2y$10$j1gK9hPo8TtBxiaOdW3EjewSktaTJf1ypRGu0a2.rmjNC/dmFXbbm','procurement','0815488452','Procurement'),(23,'Odeyemi','Ola','account','ola@gmail.com','$2y$10$OZqP.rFramygMw8Q5sSoaephGW1FEL1L81zfKQMwfGX6Nis09bFyW','account','08154834567','Account');
/*!40000 ALTER TABLE `user_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawn_asset`
--

DROP TABLE IF EXISTS `withdrawn_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawn_asset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `reg_no` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `asset_name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `floor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `withdrawn_date` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `withdrawn_by` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `withdrawn_reason` text COLLATE utf8mb4_general_ci NOT NULL,
  `qty` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawn_asset`
--

LOCK TABLES `withdrawn_asset` WRITE;
/*!40000 ALTER TABLE `withdrawn_asset` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawn_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'inventory_sys'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-05 17:48:39