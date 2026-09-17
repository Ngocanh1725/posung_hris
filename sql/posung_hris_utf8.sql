-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: posung_hris
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
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `cert_type` enum('Welding_6G','Welding_3G','HSE_Group3','HSE_Group6','ME_Certificate','BIM_Cert','Electrical_License','Crane_Operator','Fire_Safety','Other') NOT NULL DEFAULT 'Other',
  `cert_name` varchar(200) NOT NULL COMMENT 'T├¬n chß╗⌐ng chß╗ë chi tiß║┐t',
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `cert_file_path` varchar(255) DEFAULT NULL COMMENT '─É╞░ß╗¥ng dß║½n file scan',
  `issuing_authority` varchar(150) DEFAULT NULL COMMENT 'C╞í quan cß║Ñp',
  `is_mandatory_site` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Bß║»t buß╗Öc khi v├áo c├┤ng tr╞░ß╗¥ng',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cert_employee` (`employee_id`),
  KEY `idx_cert_type` (`cert_type`),
  KEY `idx_cert_expiry` (`expiry_date`),
  CONSTRAINT `fk_cert_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chß╗⌐ng chß╗ë nghß╗ü & an to├án thi c├┤ng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (1,5,'Welding_6G','Chß╗⌐ng chß╗ë H├án 6G ΓÇô SMAW/GTAW ß╗Éng th├⌐p ├íp lß╗▒c','2024-01-15','2027-01-15',NULL,'Viß╗çn H├án ΓÇô C─É Viß╗çt ─Éß╗⌐c',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,6,'Welding_6G','Chß╗⌐ng chß╗ë H├án 6G ΓÇô SMAW/GTAW ß╗Éng th├⌐p ├íp lß╗▒c','2024-03-20','2027-03-20',NULL,'C─É Nghß╗ü Lilama',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,7,'Welding_6G','Chß╗⌐ng chß╗ë H├án 6G ΓÇô FCAW ß╗Éng th├⌐p Carbon','2024-06-10','2027-06-10',NULL,'TT ─É├áo tß║ío Nghß╗ü HN',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,2,'HSE_Group3','Chß╗⌐ng chß╗ë An to├án Nh├│m 3 ΓÇô Kß╗╣ s╞░ hiß╗çn tr╞░ß╗¥ng','2024-02-01','2027-02-01',NULL,'Cß╗Ñc ATL─É ΓÇô Bß╗Ö L─ÉTBXH',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,4,'HSE_Group3','Chß╗⌐ng chß╗ë An to├án Nh├│m 3 ΓÇô Kß╗╣ s╞░ hiß╗çn tr╞░ß╗¥ng','2024-04-15','2027-04-15',NULL,'Cß╗Ñc ATL─É ΓÇô Bß╗Ö L─ÉTBXH',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(6,9,'HSE_Group3','Chß╗⌐ng chß╗ë An to├án Nh├│m 3 ΓÇô Gi├ím s├ít thi c├┤ng','2023-11-20','2026-11-20',NULL,'Cß╗Ñc ATL─É ΓÇô Bß╗Ö L─ÉTBXH',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(7,2,'ME_Certificate','Chß╗⌐ng chß╗ë h├ánh nghß╗ü Thiß║┐t kß║┐ ─Éiß╗çn M&E ΓÇô Hß║íng II','2023-06-01','2028-06-01',NULL,'Bß╗Ö X├óy dß╗▒ng',0,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(8,4,'ME_Certificate','Chß╗⌐ng chß╗ë h├ánh nghß╗ü Gi├ím s├ít CK ΓÇô Hß║íng II','2023-08-01','2028-08-01',NULL,'Bß╗Ö X├óy dß╗▒ng',0,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(9,3,'BIM_Cert','Autodesk Revit Professional ΓÇô MEP','2024-01-01','2026-12-31',NULL,'Autodesk',0,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clearance_checklists`
--

DROP TABLE IF EXISTS `clearance_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clearance_checklists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `ppe_returned` tinyint(1) NOT NULL DEFAULT 0,
  `tools_returned` tinyint(1) NOT NULL DEFAULT 0,
  `id_card_returned` tinyint(1) NOT NULL DEFAULT 0,
  `laptop_returned` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_clearance_employee` (`employee_id`),
  KEY `fk_clearance_creator` (`created_by`),
  CONSTRAINT `fk_clearance_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_clearance_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bi├¬n bß║ún b├án giao ─æiß╗çn tß╗¡ khi th├┤i viß╗çc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clearance_checklists`
--

LOCK TABLES `clearance_checklists` WRITE;
/*!40000 ALTER TABLE `clearance_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `clearance_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cost_centers`
--

DROP TABLE IF EXISTS `cost_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_centers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL COMMENT 'VD: CC_AMKOR_01, CC_SEHC_02',
  `name` varchar(150) NOT NULL,
  `project_id` int(10) unsigned DEFAULT NULL,
  `budget` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Ng├ón s├ích (VN─É)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cc_code` (`code`),
  KEY `idx_cc_project` (`project_id`),
  CONSTRAINT `fk_cc_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Trung t├óm chi ph├¡ ΓÇô ph├ón bß╗ò theo dß╗▒ ├ín';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_centers`
--

LOCK TABLES `cost_centers` WRITE;
/*!40000 ALTER TABLE `cost_centers` DISABLE KEYS */;
INSERT INTO `cost_centers` VALUES (1,'CC_AMKOR_01','CC Amkor ΓÇô Nh├ón c├┤ng M&E',1,12500000000.00,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,'CC_AMKOR_02','CC Amkor ΓÇô Vß║¡t t╞░ ß╗Éng',1,8500000000.00,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'CC_SEHC_01','CC Samsung ΓÇô Nh├ón c├┤ng HVAC',2,9800000000.00,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,'CC_SEHC_02','CC Samsung ΓÇô Ph├▓ng sß║ích CR',2,15200000000.00,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,'CC_STL_01','CC Starlake ΓÇô Nh├ón c├┤ng MEP',3,6300000000.00,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `cost_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dept_code` varchar(20) NOT NULL,
  `dept_name` varchar(150) NOT NULL,
  `branch` enum('Hanoi_HQ','HCM_Office','Factory_Spool','Site_Project') NOT NULL DEFAULT 'Hanoi_HQ',
  `parent_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_code` (`dept_code`),
  KEY `idx_dept_parent` (`parent_id`),
  CONSTRAINT `fk_dept_parent` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mß╗Ñc ph├▓ng ban / ─æ╞ín vß╗ï (kß║┐ thß╗½a DMDVI)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'HQ','Ban Gi├ím ─æß╗æc','Hanoi_HQ',NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,'HQ-HC','Ph├▓ng H├ánh ch├¡nh ΓÇô Nh├ón sß╗▒','Hanoi_HQ',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'HQ-KT','Ph├▓ng Kß║┐ to├ín ΓÇô T├ái ch├¡nh','Hanoi_HQ',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,'HQ-KD','Ph├▓ng Kinh doanh ΓÇô Dß╗▒ to├ín','Hanoi_HQ',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,'HQ-KS','Ph├▓ng Kß╗╣ thuß║¡t ΓÇô Thiß║┐t kß║┐ (BIM)','Hanoi_HQ',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(6,'PRJ-AMK','Ban ─ÉH DA Amkor Bß║»c Ninh','Site_Project',NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(7,'PRJ-SEHC','Ban ─ÉH DA Samsung SEHC','Site_Project',NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(8,'PRJ-STL','Ban ─ÉH DA Starlake T├óy Hß╗ô','Site_Project',NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(20) NOT NULL COMMENT 'M├ú nh├ón vi├¬n duy nhß║Ñt',
  `full_name` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL COMMENT 'Ng├áy sinh',
  `gender` enum('Male','Female','Other') NOT NULL DEFAULT 'Male',
  `id_card` varchar(30) DEFAULT NULL COMMENT 'CCCD / Hß╗Ö chiß║┐u',
  `id_card_date` date DEFAULT NULL COMMENT 'Ng├áy cß║Ñp',
  `id_card_place` varchar(150) DEFAULT NULL COMMENT 'N╞íi cß║Ñp',
  `hometown` varchar(200) DEFAULT NULL COMMENT 'Qu├¬ qu├ín',
  `address` varchar(250) DEFAULT NULL COMMENT '─Éß╗ïa chß╗ë th╞░ß╗¥ng tr├║',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nationality` varchar(50) NOT NULL DEFAULT 'Vietnam',
  `employee_type` enum('Expat','Office_BIM','Site_Engineer','Direct_Worker') NOT NULL DEFAULT 'Direct_Worker',
  `department_id` int(10) unsigned DEFAULT NULL,
  `current_project_id` int(10) unsigned DEFAULT NULL,
  `position_id` int(10) unsigned DEFAULT NULL,
  `join_date` date DEFAULT NULL COMMENT 'Ng├áy v├áo c├┤ng ty',
  `official_date` date DEFAULT NULL COMMENT 'Ng├áy ch├¡nh thß╗⌐c',
  `highest_degree` varchar(100) DEFAULT NULL COMMENT 'Tr├¼nh ─æß╗Ö chuy├¬n m├┤n cao nhß║Ñt',
  `party_join_date` date DEFAULT NULL COMMENT 'Ng├áy v├áo ─Éß║úng (nß║┐u c├│)',
  `status` enum('Probation','Active','Suspended','Resigned','Retired','Blacklisted') NOT NULL DEFAULT 'Probation',
  `avatar_path` varchar(255) DEFAULT NULL,
  `cv_file_path` varchar(255) DEFAULT NULL COMMENT '─É╞░ß╗¥ng dß║½n file CV/CCCD scan',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_code` (`emp_code`),
  KEY `idx_emp_dept` (`department_id`),
  KEY `idx_emp_project` (`current_project_id`),
  KEY `idx_emp_pos` (`position_id`),
  KEY `idx_emp_status` (`status`),
  KEY `idx_emp_type` (`employee_type`),
  KEY `idx_emp_nationality` (`nationality`),
  CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_emp_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_emp_project` FOREIGN KEY (`current_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hß╗ô s╞í nh├ón vi├¬n ch├¡nh (kß║┐ thß╗½a SOYEU)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'PS-0001','Park Joon Hyuk','1975-03-15','Male','M12345678','2023-01-10','Seoul, Korea','Seoul, South Korea','Lotte Center, Liß╗àu Giai, Ba ─É├¼nh, H├á Nß╗Öi','0912000001','park.jh@posung.co.kr','Korea','Expat',1,NULL,1,'2018-06-01','2018-06-01','M.Eng Mechanical Engineering',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,'PS-0010','Nguyß╗àn V─ân An','1992-08-20','Male','001092012345','2021-05-10','CA H├á Nß╗Öi','Thanh Tr├¼, H├á Nß╗Öi','12 Nguyß╗àn Tr├úi, Thanh Xu├ón, H├á Nß╗Öi','0912000010','an.nv@posung.vn','Vietnam','Site_Engineer',6,1,6,'2022-01-15','2022-04-15','Kß╗╣ s╞░ ─Éiß╗çn ΓÇô ─ÉHBK H├á Nß╗Öi',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'PS-0011','Trß║ºn Thß╗ï B├¡ch Ngß╗ìc','1994-11-03','Female','001094054321','2022-02-20','CA H├á Nß╗Öi','Ho├áng Mai, H├á Nß╗Öi','45 Tam Trinh, Ho├áng Mai, H├á Nß╗Öi','0912000011','ngoc.ttb@posung.vn','Vietnam','Office_BIM',5,NULL,7,'2023-03-01','2023-06-01','KTS Kß╗╣ thuß║¡t Hß║í tß║ºng ΓÇô ─ÉH XD',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,'PS-0012','L├¬ Minh ─Éß╗⌐c','1990-05-25','Male','001090067890','2020-11-15','CA Th├íi Nguy├¬n','TP. Th├íi Nguy├¬n','78 Ho├áng V─ân Thß╗Ñ, TP Th├íi Nguy├¬n','0912000012','duc.lm@posung.vn','Vietnam','Site_Engineer',7,2,6,'2021-07-01','2021-10-01','Kß╗╣ s╞░ C╞í kh├¡ ΓÇô ─ÉHBK H├á Nß╗Öi',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,'PS-0020','Phß║ím ─É├¼nh H├╣ng','1988-12-10','Male','036088012345','2020-03-05','CA Thanh H├│a','Hoß║▒ng H├│a, Thanh H├│a','KTX C├┤ng nh├ón KCN Y├¬n Phong','0912000020',NULL,'Vietnam','Direct_Worker',6,1,10,'2020-06-01','2020-09-01','TC H├án ΓÇô C─É CN Viß╗çt ─Éß╗⌐c',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(6,'PS-0021','B├╣i V─ân Thß║»ng','1991-07-22','Male','038091054321','2021-08-10','CA H├á T─⌐nh','─Éß╗⌐c Thß╗ì, H├á T─⌐nh','KTX C├┤ng nh├ón KCN Y├¬n Phong','0912000021',NULL,'Vietnam','Direct_Worker',6,1,10,'2022-02-01','2022-05-01','TC H├án 6G ΓÇô C─É Lilama',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(7,'PS-0022','─Éß╗ù Quß╗æc C╞░ß╗¥ng','1993-04-18','Male','001093098765','2022-01-20','CA H├á Nß╗Öi','─É├┤ng Anh, H├á Nß╗Öi','15 Cß╗ò Loa, ─É├┤ng Anh, H├á Nß╗Öi','0912000022',NULL,'Vietnam','Direct_Worker',8,3,10,'2023-05-01','2023-08-01','TC H├án 6G ΓÇô TT DN Nghß╗ü',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(8,'PS-0030','L╞░╞íng Thß╗ï Mai','1996-09-12','Female','001096112233','2022-06-15','CA H├á Nß╗Öi','Cß║ºu Giß║Ñy, H├á Nß╗Öi','20 Trß║ºn Duy H╞░ng, Cß║ºu Giß║Ñy, H├á Nß╗Öi','0912000030','mai.lt@posung.vn','Vietnam','Office_BIM',2,NULL,13,'2023-09-01','2023-12-01','Cß╗¡ nh├ón QTKD ΓÇô ─ÉH Th╞░╞íng Mß║íi',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(9,'PS-0040','Trß║ºn V─ân T├╣ng','1985-01-30','Male','036085223344','2019-04-20','CA Bß║»c Ninh','Y├¬n Phong, Bß║»c Ninh','TT Chß╗¥, Y├¬n Phong, Bß║»c Ninh','0912000040','tung.tv@posung.vn','Vietnam','Site_Engineer',6,1,9,'2019-08-01','2019-11-01','Kß╗╣ s╞░ ─Éiß╗çn lß║ính ΓÇô ─ÉH CN H├á Nß╗Öi',NULL,'Active',NULL,NULL,NULL,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expat_details`
--

DROP TABLE IF EXISTS `expat_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expat_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `passport_number` varchar(30) DEFAULT NULL,
  `visa_number` varchar(50) DEFAULT NULL,
  `visa_expiry` date DEFAULT NULL,
  `work_permit_number` varchar(50) DEFAULT NULL,
  `work_permit_expiry` date DEFAULT NULL,
  `trc_number` varchar(50) DEFAULT NULL COMMENT 'Thß║╗ tß║ím tr├║ (Temporary Residence Card)',
  `trc_expiry` date DEFAULT NULL,
  `emergency_korea_contact` varchar(200) DEFAULT NULL COMMENT 'Li├¬n hß╗ç khß║⌐n cß║Ñp tß║íi H├án Quß╗æc',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_expat_employee` (`employee_id`),
  KEY `idx_expat_visa_expiry` (`visa_expiry`),
  KEY `idx_expat_wp_expiry` (`work_permit_expiry`),
  KEY `idx_expat_trc_expiry` (`trc_expiry`),
  CONSTRAINT `fk_expat_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiß║┐t visa/TRC chuy├¬n gia n╞░ß╗¢c ngo├ái (Expat)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expat_details`
--

LOCK TABLES `expat_details` WRITE;
/*!40000 ALTER TABLE `expat_details` DISABLE KEYS */;
INSERT INTO `expat_details` VALUES (1,1,'M12345678','DL2025-VN-00123','2027-06-01','GP-L─É-2025-HN-0045','2027-06-01','TRC-2025-HN-0012','2027-06-01','Mrs. Park Sun Young ΓÇô Tel: +82-10-1234-5678 (Seoul)','2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `expat_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_movements`
--

DROP TABLE IF EXISTS `job_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_movements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `transfer_order_id` int(10) unsigned DEFAULT NULL,
  `movement_type` enum('Transfer','Promotion','Demotion','Assignment','Secondment') NOT NULL DEFAULT 'Transfer',
  `from_project_id` int(10) unsigned DEFAULT NULL,
  `to_project_id` int(10) unsigned DEFAULT NULL,
  `from_dept_id` int(10) unsigned DEFAULT NULL,
  `to_dept_id` int(10) unsigned DEFAULT NULL,
  `from_position_id` int(10) unsigned DEFAULT NULL,
  `to_position_id` int(10) unsigned DEFAULT NULL,
  `decision_number` varchar(50) DEFAULT NULL COMMENT 'Sß╗æ quyß║┐t ─æß╗ïnh',
  `effective_date` date NOT NULL,
  `cost_center_id` int(10) unsigned DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL COMMENT 'ID user ph├¬ duyß╗çt',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jm_employee` (`employee_id`),
  KEY `idx_jm_effective` (`effective_date`),
  KEY `idx_jm_type` (`movement_type`),
  KEY `fk_jm_from_project` (`from_project_id`),
  KEY `fk_jm_to_project` (`to_project_id`),
  KEY `fk_jm_from_dept` (`from_dept_id`),
  KEY `fk_jm_to_dept` (`to_dept_id`),
  KEY `fk_jm_from_pos` (`from_position_id`),
  KEY `fk_jm_to_pos` (`to_position_id`),
  KEY `fk_jm_cc` (`cost_center_id`),
  KEY `fk_jm_approver` (`approved_by`),
  KEY `fk_jm_to` (`transfer_order_id`),
  CONSTRAINT `fk_jm_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_cc` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_from_dept` FOREIGN KEY (`from_dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_from_pos` FOREIGN KEY (`from_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_from_project` FOREIGN KEY (`from_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_to` FOREIGN KEY (`transfer_order_id`) REFERENCES `transfer_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_to_dept` FOREIGN KEY (`to_dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_to_pos` FOREIGN KEY (`to_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jm_to_project` FOREIGN KEY (`to_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lß╗ïch sß╗¡ ─æiß╗üu ─æß╗Öng & bß╗ò nhiß╗çm (kß║┐ thß╗½a QTCTAC + QTBNHIEM)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_movements`
--

LOCK TABLES `job_movements` WRITE;
/*!40000 ALTER TABLE `job_movements` DISABLE KEYS */;
INSERT INTO `job_movements` VALUES (1,2,NULL,'Assignment',NULL,1,NULL,6,NULL,NULL,'Q─É-2022/PS-001','2022-01-15',1,'─Éiß╗üu ─æß╗Öng KS Nguyß╗àn V─ân An vß╗ü Ban ─ÉH DA Amkor Bß║»c Ninh',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,4,NULL,'Transfer',1,2,6,7,NULL,NULL,'Q─É-2024/PS-015','2024-06-01',3,'Chuyß╗ân KS L├¬ Minh ─Éß╗⌐c tß╗½ Amkor sang Samsung SEHC phß╗Ñ tr├ích HVAC',1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,7,NULL,'Assignment',NULL,3,NULL,8,NULL,NULL,'Q─É-2023/PS-008','2023-05-01',5,'─Éiß╗üu ─æß╗Öng thß╗ú h├án ─Éß╗ù Quß╗æc C╞░ß╗¥ng vß╗ü Ban ─ÉH DA Starlake',1,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `job_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payrolls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `month` tinyint(3) unsigned NOT NULL COMMENT '1-12',
  `year` smallint(5) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned DEFAULT NULL,
  `cost_center_id` int(10) unsigned DEFAULT NULL,
  `standard_days` decimal(4,1) NOT NULL DEFAULT 26.0,
  `actual_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `ot_pay` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Tiß╗ün t─âng ca',
  `allowances_total` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Tß╗òng phß╗Ñ cß║Ñp',
  `deductions_total` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Tß╗òng khß║Ñu trß╗½ (BH, thuß║┐ΓÇª)',
  `net_salary` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Thß╗▒c l├únh',
  `payment_status` enum('Draft','Calculated','Approved','Paid') NOT NULL DEFAULT 'Draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payroll_emp_month` (`employee_id`,`month`,`year`),
  KEY `idx_payroll_period` (`year`,`month`),
  KEY `idx_payroll_project` (`project_id`),
  KEY `idx_payroll_cc` (`cost_center_id`),
  KEY `idx_payroll_status` (`payment_status`),
  CONSTRAINT `fk_payroll_cc` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bß║úng l╞░╞íng tß╗òng hß╗úp theo th├íng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pos_code` varchar(20) NOT NULL,
  `pos_title` varchar(100) NOT NULL,
  `allowance_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Hß╗ç sß╗æ phß╗Ñ cß║Ñp chß╗⌐c vß╗Ñ',
  `job_level` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '1=Staff, 2=Lead, 3=Manager, 4=Director, 5=CEO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pos_code` (`pos_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mß╗Ñc chß╗⌐c vß╗Ñ (kß║┐ thß╗½a DMCVCQ)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'GD','Gi├ím ─æß╗æc / Director',3.00,5,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,'PGD','Ph├│ Gi├ím ─æß╗æc / Deputy Director',2.50,4,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'TP','Tr╞░ß╗ƒng ph├▓ng / Manager',2.00,3,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,'PP','Ph├│ ph├▓ng / Deputy Manager',1.50,3,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,'CDDA','Chß╗ë huy tr╞░ß╗ƒng Dß╗▒ ├ín / PM',2.00,3,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(6,'KSME','Kß╗╣ s╞░ M&E',1.00,2,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(7,'KSBIM','Kß╗╣ s╞░ BIM / Thiß║┐t kß║┐',1.00,2,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(8,'KSHSE','Kß╗╣ s╞░ An to├án (HSE)',1.00,2,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(9,'GSCT','Gi├ím s├ít thi c├┤ng',0.80,2,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(10,'TOHAN','Thß╗ú h├án (Welder)',0.50,1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(11,'TODIEN','Thß╗ú ─æiß╗çn (Electrician)',0.50,1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(12,'TOOK','Thß╗ú ß╗æng n╞░ß╗¢c (Pipe Fitter)',0.50,1,'2026-09-08 11:26:08','2026-09-08 11:26:08'),(13,'NV','Nh├ón vi├¬n / Staff',0.00,1,'2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_code` varchar(30) NOT NULL,
  `project_name` varchar(200) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL COMMENT 'Samsung, Amkor, Daewoo E&C, ΓÇª',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Planning','In_Progress','Completed','On_Hold','Cancelled') NOT NULL DEFAULT 'Planning',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_code` (`project_code`),
  KEY `idx_project_status` (`status`),
  KEY `idx_project_client` (`client_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mß╗Ñc dß╗▒ ├ín thi c├┤ng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'AMK-BN-2025','Nh├á m├íy Amkor Technology ΓÇô G├│i M&E Phase 2','KCN Y├¬n Phong, Bß║»c Ninh','Amkor Technology','2025-03-01','2026-12-31','In_Progress','2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,'SEHC-TN-2025','Samsung SEHC ΓÇô Hß╗ç thß╗æng HVAC & Ph├▓ng sß║ích','KCN Y├¬n B├¼nh, Th├íi Nguy├¬n','Samsung Electronics','2025-06-01','2026-09-30','In_Progress','2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'STL-HN-2025','Starlake T├óy Hß╗ô ΓÇô Hß╗ç thß╗æng MEP T├▓a nh├á VP','Starlake, T├óy Hß╗ô, H├á Nß╗Öi','Daewoo E&C','2025-08-01','2027-03-31','In_Progress','2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rewards_disciplines`
--

DROP TABLE IF EXISTS `rewards_disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rewards_disciplines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `type` enum('Reward','Discipline') NOT NULL,
  `decision_number` varchar(50) DEFAULT NULL,
  `decision_date` date DEFAULT NULL,
  `title` varchar(200) NOT NULL COMMENT 'Nß╗Öi dung khen th╞░ß╗ƒng / kß╗╖ luß║¡t',
  `amount` decimal(18,2) DEFAULT 0.00 COMMENT 'Sß╗æ tiß╗ün th╞░ß╗ƒng / phß║ít',
  `reason` text DEFAULT NULL,
  `is_safety_violation` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Vi phß║ím HSE ΓåÆ xem x├⌐t Blacklist',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rd_employee` (`employee_id`),
  KEY `idx_rd_type` (`type`),
  KEY `idx_rd_safety` (`is_safety_violation`),
  CONSTRAINT `fk_rd_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Khen th╞░ß╗ƒng & Kß╗╖ luß║¡t (kß║┐ thß╗½a QTKTKL)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rewards_disciplines`
--

LOCK TABLES `rewards_disciplines` WRITE;
/*!40000 ALTER TABLE `rewards_disciplines` DISABLE KEYS */;
/*!40000 ALTER TABLE `rewards_disciplines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salaries`
--

DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salaries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `effective_date` date NOT NULL COMMENT 'Ng├áy ├íp dß╗Ñng mß╗⌐c l╞░╞íng',
  `grade_code` varchar(20) DEFAULT NULL COMMENT 'Ngß║ích / bß║¡c l╞░╞íng',
  `base_salary` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'L╞░╞íng c╞í bß║ún (VN─É)',
  `project_allowance` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Phß╗Ñ cß║Ñp dß╗▒ ├ín',
  `cleanroom_allowance` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Phß╗Ñ cß║Ñp ph├▓ng sß║ích',
  `remote_allowance` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Phß╗Ñ cß║Ñp xa nh├á',
  `hazard_allowance` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Phß╗Ñ cß║Ñp ─æß╗Öc hß║íi',
  `insurance_rate` decimal(5,2) NOT NULL DEFAULT 10.50 COMMENT '% BHXH + BHYT + BHTN',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sal_employee` (`employee_id`),
  KEY `idx_sal_effective` (`effective_date`),
  CONSTRAINT `fk_sal_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Diß╗àn biß║┐n tiß╗ün l╞░╞íng (kß║┐ thß╗½a QTLUONG)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salaries`
--

LOCK TABLES `salaries` WRITE;
/*!40000 ALTER TABLE `salaries` DISABLE KEYS */;
INSERT INTO `salaries` VALUES (1,1,'2024-01-01','EXP-01',120000000.00,0.00,0.00,0.00,0.00,0.00,'L╞░╞íng Expat ΓÇô thanh to├ín USD quy ─æß╗òi','2026-09-08 11:26:08','2026-09-08 11:26:08'),(2,2,'2024-01-01','KS-B3',18000000.00,3500000.00,0.00,1500000.00,0.00,10.50,'KS M&E site Amkor','2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,3,'2024-01-01','KS-B2',16000000.00,0.00,0.00,0.00,0.00,10.50,'KS BIM v─ân ph├▓ng','2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,4,'2024-01-01','KS-B3',18500000.00,3500000.00,2000000.00,2000000.00,0.00,10.50,'KS M&E site Samsung SEHC ΓÇô ph├▓ng sß║ích','2026-09-08 11:26:08','2026-09-08 11:26:08'),(5,5,'2024-01-01','TH-B4',14000000.00,2500000.00,0.00,1500000.00,500000.00,10.50,'Thß╗ú h├án 6G site Amkor','2026-09-08 11:26:08','2026-09-08 11:26:08'),(6,6,'2024-01-01','TH-B3',13000000.00,2500000.00,0.00,1500000.00,500000.00,10.50,'Thß╗ú h├án 6G site Amkor','2026-09-08 11:26:08','2026-09-08 11:26:08'),(7,7,'2024-01-01','TH-B3',13000000.00,2500000.00,0.00,0.00,500000.00,10.50,'Thß╗ú h├án 6G site Starlake','2026-09-08 11:26:08','2026-09-08 11:26:08'),(8,8,'2024-01-01','NV-B2',10000000.00,0.00,0.00,0.00,0.00,10.50,'NV H├ánh ch├¡nh','2026-09-08 11:26:08','2026-09-08 11:26:08'),(9,9,'2024-01-01','GS-B4',16500000.00,3000000.00,0.00,1500000.00,0.00,10.50,'GSCT site Amkor','2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timesheets`
--

DROP TABLE IF EXISTS `timesheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timesheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned DEFAULT NULL,
  `work_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `shift_type` enum('Day','Night','Sunday','Holiday') NOT NULL DEFAULT 'Day',
  `is_cleanroom` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = L├ám viß╗çc trong ph├▓ng sß║ích',
  `ot_hours` decimal(4,1) NOT NULL DEFAULT 0.0 COMMENT 'Sß╗æ giß╗¥ t─âng ca',
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ts_emp_date` (`employee_id`,`work_date`),
  KEY `idx_ts_project` (`project_id`),
  KEY `idx_ts_date` (`work_date`),
  KEY `idx_ts_status` (`status`),
  KEY `fk_ts_approver` (`approved_by`),
  CONSTRAINT `fk_ts_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ts_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bß║úng chß║Ñm c├┤ng h├áng ng├áy';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timesheets`
--

LOCK TABLES `timesheets` WRITE;
/*!40000 ALTER TABLE `timesheets` DISABLE KEYS */;
INSERT INTO `timesheets` VALUES (1,1,NULL,'2026-09-01','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(2,4,2,'2026-09-01','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(3,1,NULL,'2026-09-02','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(4,4,2,'2026-09-02','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(5,1,NULL,'2026-09-03','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(6,4,2,'2026-09-03','08:00:00','17:00:00','Day',0,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(7,1,NULL,'2026-09-04','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(8,4,2,'2026-09-04','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(9,1,NULL,'2026-09-05','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(10,4,2,'2026-09-05','08:00:00','17:00:00','Night',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(11,4,2,'2026-09-06','08:00:00','17:00:00','Sunday',0,8.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(12,1,NULL,'2026-09-07','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(13,4,2,'2026-09-07','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(14,1,NULL,'2026-09-08','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(15,4,2,'2026-09-08','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(16,1,NULL,'2026-09-09','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(17,4,2,'2026-09-09','08:00:00','17:00:00','Day',0,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(18,1,NULL,'2026-09-10','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(19,4,2,'2026-09-10','08:00:00','17:00:00','Night',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(20,1,NULL,'2026-09-11','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(21,4,2,'2026-09-11','08:00:00','17:00:00','Day',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(22,1,NULL,'2026-09-12','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(23,4,2,'2026-09-12','08:00:00','17:00:00','Day',1,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(24,1,NULL,'2026-09-14','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(25,4,2,'2026-09-14','08:00:00','17:00:00','Day',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(26,1,NULL,'2026-09-15','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(27,4,2,'2026-09-15','08:00:00','17:00:00','Night',1,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(28,1,NULL,'2026-09-16','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(29,4,2,'2026-09-16','08:00:00','17:00:00','Day',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(30,1,NULL,'2026-09-17','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(31,4,2,'2026-09-17','08:00:00','17:00:00','Day',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(32,1,NULL,'2026-09-18','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(33,4,2,'2026-09-18','08:00:00','17:00:00','Day',1,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(34,1,NULL,'2026-09-19','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(35,4,2,'2026-09-19','08:00:00','17:00:00','Day',1,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(36,4,2,'2026-09-20','08:00:00','17:00:00','Sunday',0,8.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(37,1,NULL,'2026-09-21','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(38,4,2,'2026-09-21','08:00:00','17:00:00','Day',0,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(39,1,NULL,'2026-09-22','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(40,4,2,'2026-09-22','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(41,1,NULL,'2026-09-23','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(42,4,2,'2026-09-23','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(43,1,NULL,'2026-09-24','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(44,4,2,'2026-09-24','08:00:00','17:00:00','Day',0,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(45,1,NULL,'2026-09-25','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(46,4,2,'2026-09-25','08:00:00','17:00:00','Night',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(47,1,NULL,'2026-09-26','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(48,4,2,'2026-09-26','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(49,1,NULL,'2026-09-28','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(50,4,2,'2026-09-28','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(51,1,NULL,'2026-09-29','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(52,4,2,'2026-09-29','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(53,1,NULL,'2026-09-30','08:00:00','17:00:00','Day',0,0.0,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53'),(54,4,2,'2026-09-30','08:00:00','17:00:00','Night',0,2.5,'Approved',NULL,NULL,'2026-09-08 11:42:53','2026-09-08 11:42:53');
/*!40000 ALTER TABLE `timesheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfer_orders`
--

DROP TABLE IF EXISTS `transfer_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transfer_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `decision_number` varchar(50) NOT NULL COMMENT 'Sß╗æ quyß║┐t ─æß╗ïnh ─æiß╗üu ─æß╗Öng',
  `from_project_id` int(10) unsigned DEFAULT NULL,
  `to_project_id` int(10) unsigned DEFAULT NULL,
  `effective_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `decision_number` (`decision_number`),
  KEY `fk_to_from_project` (`from_project_id`),
  KEY `fk_to_to_project` (`to_project_id`),
  KEY `fk_to_created_by` (`created_by`),
  KEY `fk_to_approved_by` (`approved_by`),
  CONSTRAINT `fk_to_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_to_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_to_from_project` FOREIGN KEY (`from_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_to_to_project` FOREIGN KEY (`to_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lß╗çnh ─æiß╗üu ─æß╗Öng h├áng loß║ít (Transfer Orders)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_orders`
--

LOCK TABLES `transfer_orders` WRITE;
/*!40000 ALTER TABLE `transfer_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `transfer_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hashed',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('Admin','HR_Manager','Project_Manager','Site_Supervisor','Employee') NOT NULL DEFAULT 'Employee',
  `status` enum('Active','Inactive','Locked') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='T├ái khoß║ún hß╗ç thß╗æng HRIS';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$3L4gVHL0LOrd.iS/qN/IaOk2csESWVW/mmHahRcAXHhU4GPEeyxJm','Quß║ún trß╗ï vi├¬n','admin@posung.vn','Admin','Active','2026-09-08 11:26:08','2026-09-08 11:32:18'),(2,'hr_mgr','$2y$10$3L4gVHL0LOrd.iS/qN/IaOk2csESWVW/mmHahRcAXHhU4GPEeyxJm','Nguyß╗àn Thß╗ï H╞░╞íng','huong.nt@posung.vn','HR_Manager','Active','2026-09-08 11:26:08','2026-09-08 11:26:08'),(3,'pm_park','$2y$10$3L4gVHL0LOrd.iS/qN/IaOk2csESWVW/mmHahRcAXHhU4GPEeyxJm','Park Joon Hyuk','park.jh@posung.co.kr','Project_Manager','Active','2026-09-08 11:26:08','2026-09-08 11:26:08'),(4,'sv_tung','$2y$10$3L4gVHL0LOrd.iS/qN/IaOk2csESWVW/mmHahRcAXHhU4GPEeyxJm','Trß║ºn V─ân T├╣ng','tung.tv@posung.vn','Site_Supervisor','Active','2026-09-08 11:26:08','2026-09-08 11:26:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-08 12:06:48
