-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: inventory_sys
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
-- Table structure for table `asset_table`
--

DROP TABLE IF EXISTS `asset_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(200) NOT NULL,
  `description` varchar(250) NOT NULL,
  `quantity` varchar(20) NOT NULL,
  `category` varchar(30) NOT NULL,
  `dateofpurchase` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_table`
--

LOCK TABLES `asset_table` WRITE;
/*!40000 ALTER TABLE `asset_table` DISABLE KEYS */;
INSERT INTO `asset_table` VALUES (23,'ISL158','HP 840 G3','All are bought with good condition','48','Laptops','2025-09-09'),(24,'ISL913','HP 820 G3','Everything is in good condition','4','Laptops','2025-09-11');
/*!40000 ALTER TABLE `asset_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrow_table`
--

DROP TABLE IF EXISTS `borrow_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrow_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `quantity` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `borrow_date` varchar(100) NOT NULL,
  `borrow_by` varchar(100) NOT NULL,
  `admin_borrow_for` varchar(30) NOT NULL,
  `hod_status` int(11) NOT NULL,
  `pro_status` int(11) NOT NULL,
  `returned` int(11) NOT NULL,
  `returned_date` varchar(100) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (5,'Laptops'),(13,'Printers'),(16,'Desktops');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_borrow_table`
--

DROP TABLE IF EXISTS `department_borrow_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_borrow_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(50) DEFAULT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `quantity` varchar(10) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `borrow_by_dept` varchar(100) NOT NULL,
  `borrow_date` varchar(100) DEFAULT NULL,
  `hod_name` varchar(100) NOT NULL,
  `hod_status` int(11) NOT NULL,
  `returned` int(11) NOT NULL,
  `return_date` varchar(100) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(100) NOT NULL,
  `floor` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_table`
--

LOCK TABLES `department_table` WRITE;
/*!40000 ALTER TABLE `department_table` DISABLE KEYS */;
INSERT INTO `department_table` VALUES (15,'Nursing1','First Floor'),(16,'Nursing2','Second Floor'),(17,'Nursing3','Third Floor'),(18,'Nursing4','Fourth Floor'),(19,'Nursing5','Fifth Floor'),(20,'Nursing6','Sixth Floor'),(21,'Facility','Admin building');
/*!40000 ALTER TABLE `department_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_table`
--

DROP TABLE IF EXISTS `maintenance_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `category` varchar(20) NOT NULL,
  `department` varchar(30) NOT NULL,
  `last_service` varchar(20) NOT NULL,
  `next_service` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_table`
--

LOCK TABLES `maintenance_table` WRITE;
/*!40000 ALTER TABLE `maintenance_table` DISABLE KEYS */;
INSERT INTO `maintenance_table` VALUES (18,'ISL707','HP Laserjet Black Printer','Good','Printers','Nursing1','2025-09-16','2025-11-15'),(19,'ISL707','HP Laserjet Black Printer','Good','Printers','Nursing2','2025-09-16','2025-11-15');
/*!40000 ALTER TABLE `maintenance_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair_asset`
--

DROP TABLE IF EXISTS `repair_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repair_asset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `quantity` int(11) DEFAULT 1,
  `report_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Under Repair',
  `completed_date` varchar(40) NOT NULL,
  `completed` int(11) NOT NULL,
  `withdrawn_date` varchar(30) NOT NULL,
  `withdrawn` int(11) NOT NULL,
  `replaced_date` varchar(30) NOT NULL,
  `replaced` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_repair` (`asset_id`,`status`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_reg_no` (`reg_no`),
  KEY `idx_status` (`status`),
  CONSTRAINT `repair_asset_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `staff_table` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair_asset`
--

LOCK TABLES `repair_asset` WRITE;
/*!40000 ALTER TABLE `repair_asset` DISABLE KEYS */;
INSERT INTO `repair_asset` VALUES (41,43,'ISL158','HP 840 G3','Nursing5','Odeyemi Oluwatobi','Marked for repair','General',1,'2025-09-17 09:53:47',NULL,'2025-09-17 09:58:30',1,'',0,'',0),(42,43,'ISL158','HP 840 G3','Nursing5','Odeyemi Oluwatobi','Marked for repair','General',1,'2025-09-17 10:01:25',NULL,'',0,'2025-09-17 10:01:31',1,'2025-09-17 10:04:11',1),(43,43,'ISL158','HP 840 G3','Nursing5','Odeyemi Oluwatobi','Marked for repair','General',1,'2025-09-17 10:05:36',NULL,'',0,'2025-09-18 22:43:59',1,'2025-09-18 22:44:23',1),(46,43,'ISL158','HP 840 G3','Nursing5','Odeyemi Oluwatobi','Marked for repair','General',1,'2025-09-22 21:19:44',NULL,'2025-09-23 10:21:54',1,'',0,'',0),(47,43,'ISL158','HP 840 G3','Nursing5','Daramola Damola','Marked for repair','General',1,'2025-09-23 10:22:02',NULL,'2025-09-28 22:50:43',1,'',0,'',0);
/*!40000 ALTER TABLE `repair_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_table`
--

DROP TABLE IF EXISTS `request_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `quantity` varchar(10) NOT NULL,
  `category` varchar(30) NOT NULL,
  `department` varchar(30) NOT NULL,
  `assigned_employee` varchar(30) NOT NULL,
  `requested_by` varchar(30) NOT NULL,
  `request_date` varchar(30) NOT NULL,
  `hod_approved` int(2) NOT NULL,
  `pro_approved` int(2) NOT NULL,
  `approval_date` varchar(100) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_role` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'admin'),(2,'user'),(3,'hod'),(7,'procurement');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_table`
--

DROP TABLE IF EXISTS `staff_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `floor` varchar(100) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `request_date` varchar(100) NOT NULL,
  `hod_approved` int(11) NOT NULL,
  `pro_approved` int(11) NOT NULL,
  `approval_date` varchar(100) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `withdrawn` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_table`
--

LOCK TABLES `staff_table` WRITE;
/*!40000 ALTER TABLE `staff_table` DISABLE KEYS */;
INSERT INTO `staff_table` VALUES (43,'ISL158','HP 840 G3','All are bought with good condition','1','Laptops','Nursing5','Fifth Floor','Tobestic','2025-09-17 09:51:00',0,0,'',NULL,0);
/*!40000 ALTER TABLE `staff_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_table`
--

DROP TABLE IF EXISTS `user_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `role` varchar(30) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `department` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_table`
--

LOCK TABLES `user_table` WRITE;
/*!40000 ALTER TABLE `user_table` DISABLE KEYS */;
INSERT INTO `user_table` VALUES (3,'Odeyemi','Timothy','Tobestics','odeyemioluwatobi60@gmail.com','12345','hod','08154883262','Computer Science'),(6,'Daramola','Damola','Daraminds','daramolaadewunmi@gmail.com','12345','procurement','08143405244','Procurement/Maintenance'),(7,'Odeyemi','Oluwatobi','Tobestic','tobestic53@gmail.com','12345','admin','08143405243','Admin'),(16,'Odeyemi','Admin','admin','admin@gmail.com','$2y$10$oGOPN8Ah8t0k1X6wk27YnO9PJnboS1O36ISmGZGZ1dt6vP2asHgQ.','admin','08154883267','Facility'),(17,'Ola','Facility','facility','fas@gmail.com','$2y$10$SbeccdeGSAcNkGoiFfINdOGL4Q4S/g08js3hYlMicWAVAkVMtfGUS','procurement','08154883267','Facility'),(18,'Tim','user','user','user@gmail.com','$2y$10$/ia23/q1H5qsIR8gO7gsLODObp6LKPQK3hieEzHwN2Kr8i4TkDef.','hod','08154883278','Facility');
/*!40000 ALTER TABLE `user_table` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-30 13:59:39