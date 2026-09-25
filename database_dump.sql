-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kerala_vision
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `box_models`
--

DROP TABLE IF EXISTS `box_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `box_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) NOT NULL,
  `model_code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `box_models_model_name_unique` (`model_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `box_models`
--

LOCK TABLES `box_models` WRITE;
/*!40000 ALTER TABLE `box_models` DISABLE KEYS */;
INSERT INTO `box_models` VALUES (1,'KV HD Smart Box 4K Model A1','MDL-KV-4KA1','4K Android Smart Hybrid STB','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(2,'KV HEVC Hybrid STB Model H2','MDL-KV-H2','HEVC High Definition Box','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(3,'KV Standard Digital Box S10','MDL-KV-S10','Standard Digital DVB-C Box','active','2026-07-28 16:17:20','2026-07-28 16:17:20');
/*!40000 ALTER TABLE `box_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checkin_voucher_items`
--

DROP TABLE IF EXISTS `checkin_voucher_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checkin_voucher_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `checkin_voucher_id` bigint(20) unsigned NOT NULL,
  `set_top_box_id` bigint(20) unsigned NOT NULL,
  `barcode_number` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkin_voucher_items_checkin_voucher_id_foreign` (`checkin_voucher_id`),
  KEY `checkin_voucher_items_set_top_box_id_foreign` (`set_top_box_id`),
  CONSTRAINT `checkin_voucher_items_checkin_voucher_id_foreign` FOREIGN KEY (`checkin_voucher_id`) REFERENCES `checkin_vouchers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `checkin_voucher_items_set_top_box_id_foreign` FOREIGN KEY (`set_top_box_id`) REFERENCES `set_top_boxes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checkin_voucher_items`
--

LOCK TABLES `checkin_voucher_items` WRITE;
/*!40000 ALTER TABLE `checkin_voucher_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `checkin_voucher_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checkin_vouchers`
--

DROP TABLE IF EXISTS `checkin_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checkin_vouchers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `voucher_number` varchar(255) NOT NULL,
  `checkin_date` date NOT NULL,
  `operator_id` bigint(20) unsigned NOT NULL,
  `total_boxes` int(11) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkin_vouchers_voucher_number_unique` (`voucher_number`),
  KEY `checkin_vouchers_operator_id_foreign` (`operator_id`),
  KEY `checkin_vouchers_created_by_foreign` (`created_by`),
  CONSTRAINT `checkin_vouchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `checkin_vouchers_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checkin_vouchers`
--

LOCK TABLES `checkin_vouchers` WRITE;
/*!40000 ALTER TABLE `checkin_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `checkin_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `opening_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sales_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_item_code_unique` (`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'HDMI Cable 1.5m Gold Plated','ITM-HDMI-01',150.00,120.00,250.00,'High speed 4K HDMI cable 1.5 meters','active','2026-07-28 16:17:19','2026-07-28 16:17:19'),(2,'12V 1.5A Power Adapter','ITM-PWR-02',200.00,180.00,350.00,'Standard set top box power supply unit','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(3,'KV Universal Remote Control','ITM-RMT-03',300.00,90.00,200.00,'Kerala Vision smart universal remote','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(4,'RG6 Coaxial Cable (per meter)','ITM-CBL-04',1000.00,12.00,25.00,'Heavy duty shielded coaxial cable','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(5,'Single Output Ku-Band LNB','ITM-LNB-05',80.00,250.00,450.00,'Universal single output LNB','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(6,'2-Way Signal Splitter 5-2400MHz','ITM-SPL-06',120.00,45.00,100.00,'High frequency RF signal splitter','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(7,'AV Composite Cable 3-RCA','ITM-RCA-07',90.00,40.00,90.00,'Standard 3.5mm to RCA audio video cable','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(8,'Smartcard Chip Module','ITM-SCM-08',50.00,300.00,600.00,'CAS encryption conditional access smart card','active','2026-07-28 16:17:20','2026-07-28 16:17:20');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_stocks`
--

DROP TABLE IF EXISTS `main_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_stocks_item_id_unique` (`item_id`),
  CONSTRAINT `main_stocks_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_stocks`
--

LOCK TABLES `main_stocks` WRITE;
/*!40000 ALTER TABLE `main_stocks` DISABLE KEYS */;
INSERT INTO `main_stocks` VALUES (1,1,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(2,2,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(3,3,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(4,4,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(5,5,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(6,6,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(7,7,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20'),(8,8,0.00,'2026-07-28 16:17:20','2026-07-28 16:17:20');
/*!40000 ALTER TABLE `main_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000001_create_staff_table',1),(5,'2026_01_01_000002_create_items_table',1),(6,'2026_01_01_000003_create_set_top_boxes_table',1),(7,'2026_01_01_000004_create_main_stocks_table',1),(8,'2026_01_01_000005_create_staff_stocks_table',1),(9,'2026_01_01_000006_create_stock_transactions_table',1),(10,'2026_01_01_000007_create_stock_transfers_table',1),(11,'2026_01_01_000008_create_service_transactions_table',1),(12,'2026_01_01_000009_create_service_items_table',1),(13,'2026_01_01_000010_create_activity_logs_table',1),(14,'2026_01_01_000011_create_settings_table',1),(15,'2026_01_01_000012_create_box_models_table',1),(16,'2026_01_01_000013_add_box_model_id_to_set_top_boxes_table',1),(17,'2026_01_01_000014_create_operators_table',1),(18,'2026_01_01_000015_add_operator_and_stb_status_to_set_top_boxes_table',1),(19,'2026_01_01_000016_create_qc_checks_table',1),(20,'2026_01_01_000017_create_checkin_vouchers_and_items_tables',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `operator_name` varchar(255) NOT NULL,
  `operator_code` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operators_operator_code_unique` (`operator_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES (1,'Kaloor Cable Vision','LCO-KLR-001','Suresh Kumar','9847012345','Kaloor Junction, Ernakulam','active','2026-07-28 16:17:19','2026-07-28 16:17:19'),(2,'Ernakulam Digital Network','LCO-EKM-002','Mathew Joseph','9847023456','MG Road, Kochi','active','2026-07-28 16:17:19','2026-07-28 16:17:19'),(3,'Cochin Cable Communications','LCO-COCH-003','Firoz Khan','9847034567','Fort Kochi','active','2026-07-28 16:17:19','2026-07-28 16:17:19');
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_checks`
--

DROP TABLE IF EXISTS `qc_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qc_checks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `set_top_box_id` bigint(20) unsigned NOT NULL,
  `service_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `qc_user_id` bigint(20) unsigned NOT NULL,
  `qc_status` enum('tested_ok','complaint','flash') NOT NULL,
  `qc_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qc_checks_set_top_box_id_foreign` (`set_top_box_id`),
  KEY `qc_checks_service_transaction_id_foreign` (`service_transaction_id`),
  KEY `qc_checks_qc_user_id_foreign` (`qc_user_id`),
  CONSTRAINT `qc_checks_qc_user_id_foreign` FOREIGN KEY (`qc_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `qc_checks_service_transaction_id_foreign` FOREIGN KEY (`service_transaction_id`) REFERENCES `service_transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `qc_checks_set_top_box_id_foreign` FOREIGN KEY (`set_top_box_id`) REFERENCES `set_top_boxes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_checks`
--

LOCK TABLES `qc_checks` WRITE;
/*!40000 ALTER TABLE `qc_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `qc_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_items`
--

DROP TABLE IF EXISTS `service_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_transaction_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_items_service_transaction_id_foreign` (`service_transaction_id`),
  KEY `service_items_item_id_foreign` (`item_id`),
  CONSTRAINT `service_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_items_service_transaction_id_foreign` FOREIGN KEY (`service_transaction_id`) REFERENCES `service_transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_items`
--

LOCK TABLES `service_items` WRITE;
/*!40000 ALTER TABLE `service_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_transactions`
--

DROP TABLE IF EXISTS `service_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(255) NOT NULL,
  `service_date` date NOT NULL,
  `set_top_box_id` bigint(20) unsigned NOT NULL,
  `staff_id` bigint(20) unsigned NOT NULL,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_transactions_service_code_unique` (`service_code`),
  KEY `service_transactions_set_top_box_id_foreign` (`set_top_box_id`),
  KEY `service_transactions_staff_id_foreign` (`staff_id`),
  KEY `service_transactions_created_by_foreign` (`created_by`),
  CONSTRAINT `service_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_transactions_set_top_box_id_foreign` FOREIGN KEY (`set_top_box_id`) REFERENCES `set_top_boxes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_transactions_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_transactions`
--

LOCK TABLES `service_transactions` WRITE;
/*!40000 ALTER TABLE `service_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `set_top_boxes`
--

DROP TABLE IF EXISTS `set_top_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `set_top_boxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `box_model_id` bigint(20) unsigned DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `box_name` varchar(255) NOT NULL,
  `barcode_number` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `stb_status` varchar(50) NOT NULL DEFAULT 'complaint',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `set_top_boxes_barcode_number_unique` (`barcode_number`),
  KEY `set_top_boxes_box_model_id_foreign` (`box_model_id`),
  KEY `set_top_boxes_operator_id_foreign` (`operator_id`),
  CONSTRAINT `set_top_boxes_box_model_id_foreign` FOREIGN KEY (`box_model_id`) REFERENCES `box_models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `set_top_boxes_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `set_top_boxes`
--

LOCK TABLES `set_top_boxes` WRITE;
/*!40000 ALTER TABLE `set_top_boxes` DISABLE KEYS */;
INSERT INTO `set_top_boxes` VALUES (1,1,NULL,'KV HD Smart Box 4K Model A1','890123456701',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(2,1,NULL,'KV HD Smart Box 4K Model A1','890123456702',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(3,2,NULL,'KV HEVC Hybrid STB Model H2','890123456703',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(4,2,NULL,'KV HEVC Hybrid STB Model H2','890123456704',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(5,3,NULL,'KV Standard Digital Box S10','890123456705',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20'),(6,3,NULL,'KV Standard Digital Box S10','890123456706',NULL,'complaint','active','2026-07-28 16:17:20','2026-07-28 16:17:20');
/*!40000 ALTER TABLE `set_top_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'company_name','Kerala Vision Service Center','2026-07-28 16:17:18','2026-07-28 16:17:18'),(2,'company_phone','+91 98470 12345','2026-07-28 16:17:18','2026-07-28 16:17:18'),(3,'company_email','support@keralavision.in','2026-07-28 16:17:18','2026-07-28 16:17:18'),(4,'company_address','KV Tower, Main Road, Kochi, Kerala - 682011','2026-07-28 16:17:18','2026-07-28 16:17:18'),(5,'currency_symbol','₹','2026-07-28 16:17:18','2026-07-28 16:17:18'),(6,'low_stock_threshold','10','2026-07-28 16:17:18','2026-07-28 16:17:18');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Rajesh Kumar','Senior Service Technician','9846011223','Palarivattom, Kochi','rajesh','active','2026-07-28 16:17:19','2026-07-28 16:17:19'),(2,'Anoop V','Field Support Engineer','9846022334','Kaloor, Kochi','anoop','active','2026-07-28 16:17:19','2026-07-28 16:17:19'),(3,'Divya Nair','Customer Care Technician','9846033445','Edappally, Kochi','divya','active','2026-07-28 16:17:19','2026-07-28 16:17:19');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_stocks`
--

DROP TABLE IF EXISTS `staff_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_stocks_staff_id_item_id_unique` (`staff_id`,`item_id`),
  KEY `staff_stocks_item_id_foreign` (`item_id`),
  CONSTRAINT `staff_stocks_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_stocks_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_stocks`
--

LOCK TABLES `staff_stocks` WRITE;
/*!40000 ALTER TABLE `staff_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) NOT NULL DEFAULT 'purchase',
  `date` date NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transactions_item_id_foreign` (`item_id`),
  KEY `stock_transactions_created_by_foreign` (`created_by`),
  CONSTRAINT `stock_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transactions_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_code` varchar(255) NOT NULL,
  `transfer_date` date NOT NULL,
  `staff_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_code_unique` (`transfer_code`),
  KEY `stock_transfers_staff_id_foreign` (`staff_id`),
  KEY `stock_transfers_item_id_foreign` (`item_id`),
  KEY `stock_transfers_created_by_foreign` (`created_by`),
  CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `staff_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','admin','admin@keralavision.com',NULL,'$2y$12$us30rK7fWpKqCa72t6bGs.WQs.lKsH.lFEPGdrxDFlkT1TN4Iu9Py','admin',NULL,'active',NULL,'2026-07-28 16:17:18','2026-07-28 16:17:18'),(2,'QC Lead Inspector','qc_inspector','qc@keralavision.com',NULL,'$2y$12$lcc3Oh6gkl8ASl68pzLBW.JOJPhpg7/o87HK9BX3kNl.Eydkq2HZG','qc',NULL,'active',NULL,'2026-07-28 16:17:19','2026-07-28 16:17:19'),(3,'Rajesh Kumar','rajesh','rajesh@keralavision.com',NULL,'$2y$12$kWVdoSNmAWFlkWNlbygpwer7/XJaqKUiJBNckRW0RFBAVrnvtffbC','staff',1,'active',NULL,'2026-07-28 16:17:19','2026-07-28 16:17:19'),(4,'Anoop V','anoop','anoop@keralavision.com',NULL,'$2y$12$bsQvP.BwDdzNedoNY7YADegm2OSlthWLCPMMZ0nDao3GTzZ4Pyb6y','staff',2,'active',NULL,'2026-07-28 16:17:19','2026-07-28 16:17:19'),(5,'Divya Nair','divya','divya@keralavision.com',NULL,'$2y$12$zhtDcGsM34QcNvhX9G/y0ujYff3BbQ.XJviWzKw5e8kxas0lyRbQa','staff',3,'active',NULL,'2026-07-28 16:17:19','2026-07-28 16:17:19');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checkout_vouchers`
--

DROP TABLE IF EXISTS `checkout_vouchers`;
CREATE TABLE `checkout_vouchers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `voucher_number` varchar(255) NOT NULL,
  `checkout_date` date NOT NULL,
  `operator_id` bigint(20) unsigned NOT NULL,
  `total_boxes` int(11) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkout_vouchers_voucher_number_unique` (`voucher_number`),
  KEY `checkout_vouchers_operator_id_foreign` (`operator_id`),
  KEY `checkout_vouchers_created_by_foreign` (`created_by`),
  CONSTRAINT `checkout_vouchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `checkout_vouchers_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `checkout_voucher_items`
--

DROP TABLE IF EXISTS `checkout_voucher_items`;
CREATE TABLE `checkout_voucher_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `checkout_voucher_id` bigint(20) unsigned NOT NULL,
  `set_top_box_id` bigint(20) unsigned NOT NULL,
  `barcode_number` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkout_voucher_items_checkout_voucher_id_foreign` (`checkout_voucher_id`),
  KEY `checkout_voucher_items_set_top_box_id_foreign` (`set_top_box_id`),
  CONSTRAINT `checkout_voucher_items_checkout_voucher_id_foreign` FOREIGN KEY (`checkout_voucher_id`) REFERENCES `checkout_vouchers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `checkout_voucher_items_set_top_box_id_foreign` FOREIGN KEY (`set_top_box_id`) REFERENCES `set_top_boxes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-03 15:19:05
