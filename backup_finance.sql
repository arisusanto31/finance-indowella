-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: finance_indowella_lokal
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
-- Table structure for table `book_journals`
--

DROP TABLE IF EXISTS `book_journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_journals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_journals_name_type_unique` (`name`,`type`),
  UNIQUE KEY `book_journals_name_theme_unique` (`name`,`theme`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_journals`
--

LOCK TABLES `book_journals` WRITE;
/*!40000 ALTER TABLE `book_journals` DISABLE KEYS */;
INSERT INTO `book_journals` VALUES (1,'Buku Manufaktur','pembukuan untuk manufaktur','manuf','theme-default-blue.css',NULL,NULL,NULL,NULL),(2,'Buku Toko','pembukuan untuk toko retail','retail','theme-default-green.css',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `book_journals` ENABLE KEYS */;
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
-- Table structure for table `chart_accounts`
--

DROP TABLE IF EXISTS `chart_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code_group` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT NULL,
  `is_child` tinyint(1) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `reference_model` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_accounts_code_group_unique` (`code_group`)
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_accounts`
--

LOCK TABLES `chart_accounts` WRITE;
/*!40000 ALTER TABLE `chart_accounts` DISABLE KEYS */;
INSERT INTO `chart_accounts` VALUES (1,'ASET','100000','Aset',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(2,'KAS DAN SETARA KAS','110000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,'PIUTANG USAHA','120000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(4,'PIUTANG LAIN','130000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(5,'PERSEDIAAN','140000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(6,'PAJAK DIBAYAR DIMUKA','150000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(7,'BEBAN DIBAYAR DIMUKA','160000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(9,'UANG MUKA PEMBELIAN','170000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(10,'KEWAJIBAN','200000','Kewajiban',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(11,'KEWAJIBAN JANGKA PENDEK','210000','Kewajiban',10,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(12,'KEWAJIBAN JANGKA PANJANG','220000','Kewajiban',10,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(13,'EKUITAS','300000','Ekuitas',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(14,'PENJUALAN','400000','Pendapatan',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(15,'BEBAN POKOK PENJUALAN','600000','Beban',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(16,'BEBAN PENJUALAN','700000','Beban',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(17,'BEBAN ADMINISTRASI DAN UMUM','800000','Beban',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(18,'PENDAPATAN DAN BEBAN LAIN','900000','Beban',NULL,NULL,0,0,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(19,'ASET TETAP','181000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(20,'AKUMULASI PENYUSUTAN ASET TETAP','182000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(21,'ASET LAIN','190000','Aset',1,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(22,'KAS','111000','Aset',2,NULL,0,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(23,'BANK','112000','Aset',2,NULL,0,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(24,'Kas Pusat PAKIS','111001','Aset',22,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(25,'Kas Toko ABDSALEH','111002','Aset',22,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(26,'Kas Toko PAKIS','111003','Aset',22,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(27,'Bank BCA Sales','112001','Aset',23,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(28,'Bank BCA Purchase','112002','Aset',23,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(29,'DOMPET MARKETPLACE','113000','Aset',2,NULL,0,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(30,'Dompet Tokopedia Indowella','113001','Aset',29,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(32,'Dompet Shopee Indowella','113003','Aset',29,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(33,'Piutang Usaha Offline','120001','Aset',3,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(34,'Piutang Usaha Tokopedia Indowella','120002','Aset',3,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(35,'Piutang Usaha Tokopedia Sahabat Kemasan','120003','Aset',3,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(36,'Piutang Usaha Shopee Indowella','120004','Aset',3,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(37,'Piutang Karyawan','130001','Aset',4,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(38,'Piutang lain-lain','130005','Aset',4,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(39,'Persediaan Barang Dagang','140001','Aset',5,NULL,1,2,'App\\Models\\KartuStock',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(40,'Persediaan Dalam Proses','140002','Aset',5,NULL,1,2,'App\\Models\\KartuStock',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(41,'PPh 21 Dimuka','150100','Aset',6,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(42,'PPh 23 Dimuka','150200','Aset',6,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(43,'BDD Sewa','160100','Aset',7,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(44,'BDD Lain Lain','160200','Aset',7,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(45,'Uang Muka Pembelian','170001','Aset',9,NULL,1,2,'App\\Models\\KartuPiutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(46,'Tanah','181100','Aset',19,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(48,'Bangunan','181200','Aset',19,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(49,'Inventaris Kantor','181300','Aset',19,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(50,'Inventaris Operasional','181400','Aset',19,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(51,'Kendaraan','181500','Aset',19,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(52,'Akumulasi Penyusutan Bangunan','182200','Aset',20,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(53,'Akumulasi Penyusutan Inventaris Kantor','182300','Aset',20,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(54,'Akumulasi Penyusutan Inventaris Operasional','182400','Aset',20,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(55,'Akumulasi Penyusutan Kendaraan','182500','Aset',20,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(56,'Aset Lain','191000','Aset',21,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(57,'Hutang Usaha','211000','Kewajiban',11,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(60,'Hutang Sewa Guna Usaha','220100','Kewajiban',12,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(61,'Hutang Lain Pihak ke-3','220200','Kewajiban',12,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(62,'Hutang Pemegang Saham','220300','Kewajiban',12,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(63,'Modal Disetor','301000','Ekuitas',13,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(64,'Saldo Laba','302000','Ekuitas',13,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(65,'Saldo Laba','302100','Ekuitas',64,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(66,'Laba Tahun Berjalan','302200','Ekuitas',64,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(67,'Prive','303000','Ekuitas',13,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(68,'Penjualan Barang Offline','401000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(69,'Penjualan Lain-Lain','409000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(70,'Potongan Penjualan','451000','Beban',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(71,'Retur Penjualan','461000','Beban',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(72,'Harga Pokok Barang','601000','Beban',15,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(73,'Beban Upah Harian','701001','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(74,'Beban Insentif','701002','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(75,'Beban THR','701003','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(76,'Beban Pulsa Handphone','701004','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(77,'Beban Marketing','701005','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(78,'Beban Gaji pegawai Bulanan','800001','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(79,'Beban Listrik','800002','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(80,'Beban Air','800003','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(81,'Beban Internet','800004','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(82,'Beban Alat Tulis Kantor','800005','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(83,'Beban Sewa','800006','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(84,'Beban Pajak','801000','Beban',17,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(85,'Beban Pemeliharaan Kantor','800008','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(86,'Beban Pemeliharaan Gudang','800009','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(87,'Beban Pemeliharaan Kendaraan','800010','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(88,'Beban Penyusutan Tanah','800011','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(89,'Beban Penyusutan Bangunan','800012','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(90,'Beban Rumah Tangga Kantor','800099','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(92,'Pendapatan Lain','901000','Pendapatan',18,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(93,'Beban Lain','902000','Beban',18,NULL,0,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(94,'EDC','114000','Aset',2,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(95,'PPh 25 Dimuka','150300','Aset',6,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(96,'Utang Pajak','212000','Kewajiban',11,NULL,0,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(97,'Beban Yang Masih Harus Dibayar','213000','Kewajiban',11,NULL,0,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(98,'Uang Muka Penjualan','214000','Kewajiban',11,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(99,'Pajak Pertambahan Nilai Masukan','150500','Aset',6,NULL,1,2,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(100,'Utang PPh 21','212100','Kewajiban',96,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(101,'Utang PPh 23','212200','Kewajiban',96,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(102,'Utang PPh 25','212300','Kewajiban',96,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(103,'Pajak Pertambahan Nilai Keluaran','212500','Kewajiban',96,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(104,'BYMH Gaji','213100','Kewajiban',97,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(105,'BYMH Listrik','213200','Kewajiban',97,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(106,'BYMH Air','213300','Kewajiban',97,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(107,'BYMH Sewa','213400','Kewajiban',97,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(108,'BYMH Lain-Lain','213500','Kewajiban',97,NULL,1,3,'App\\Models\\KartuHutang',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(109,'Beban Pengiriman','602000','Beban',15,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(110,'Potongan Pembelian','603000','Pendapatan',15,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(111,'Penjualan Sablon','407000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(112,'Penjualan Offset','408000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(113,'Beban Penyusutan Inventaris Kantor','800013','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(114,'Beban Penyusutan Inventaris Operasional','800014','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(115,'Beban BBM, Parkir Belanja & Pengiriman','701006','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(116,'Beban Fee Marketplace','701007','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(117,'Beban Penyusutan Kendaraan','800015','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(118,'Beban Jasa Profesional','800016','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(119,'Beban Konsumsi','800017','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(120,'Beban Iuran, Retribusi & Perijinan','800018','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(121,'Beban Penghapusan Piutang','800019','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(122,'Beban Administrasi Bank','800020','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(123,'Beban Charge EDC','800021','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(124,'Beban Denda Pajak','800022','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(125,'Pendapatan Olah Waste','901001','Pendapatan',92,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(126,'Pendapatan Selisih Kurs','901002','Pendapatan',92,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(127,'Pendapatan Selisih Bayar','901003','Pendapatan',92,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(128,'Pendapatan Selisih Opname','901004','Pendapatan',92,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(129,'Pendapatan Lain-Lain','901999','Pendapatan',92,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(130,'Beban Selisih Kurs','902001','Beban',93,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(131,'Beban Selisih Bayar','902002','Beban',93,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(132,'Beban Selisih Opname','902003','Beban',93,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(133,'Beban Pajak Jasa Giro','902004','Beban',93,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(134,'Beban Lain-Lain','902999','Beban',93,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(135,'PPh Final Dimuka','150400','Aset',6,NULL,1,2,'App\\Models\\KartuPrepaidExpense',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(136,'Utang PPh Final','212400','Kewajiban',96,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(137,'Beban PPh 21','801001','Beban',84,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(138,'Beban PPh 23','801002','Beban',84,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(139,'Beban PPh 25','801003','Beban',84,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(140,'Beban PPh Final','801004','Beban',84,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(141,'Beban Pertambahan Nilai','801005','Beban',84,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(143,'Akumulasi Penyusutan Tanah','182100','Aset',20,NULL,1,2,'App\\Models\\KartuInventory',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(144,'Penjualan Tokopedia Indowella','402000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(145,'Penjualan Tokopedia Sahabat Kemasan','403000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(146,'Penjualan Shopee Indowella','404000','Pendapatan',14,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(147,'Beban Pemakaian Cat Sablon','701008','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(148,'Beban Pemakaian Tinta Offset','701009','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(149,'Beban Untuk Packing','701010','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(150,'Beban Barang Rusak','701011','Beban',16,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(151,'Beban BBM, Parkir & Tol','800023','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(152,'Beban Lain','809000','Beban',17,NULL,1,1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(153,'Kas Pusat Admin PAKIS','111004','Aset',22,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(154,'Ayat Silang','119000','Aset',2,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(155,'Bank BSI Lailatus Saadah','112003','Aset',23,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(156,'Persediaan Dalam Perjalanan','140003','Aset',5,NULL,1,2,'App\\Models\\KartuStock',NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(157,'Bank BCA Indowella Berkah Jaya','112004','Aset',23,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(158,'Kas Toko Tumpang','111005','Aset',22,NULL,1,3,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(159,'QRIS','115000','Aset',2,NULL,1,2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `chart_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'OYI','malang','08251234234',NULL,NULL,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(2,'Balibul','malang','08251234234',NULL,NULL,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,'Hotwing','malang','08251234234',NULL,NULL,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(4,'Red Chicken','malang','08251234234',NULL,NULL,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_kartu_invoices`
--

DROP TABLE IF EXISTS `detail_kartu_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_kartu_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `invoice_pack_id` int(11) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `kartu_type` varchar(255) DEFAULT NULL,
  `kartu_id` int(11) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `amount_journal` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_kartu_invoices`
--

LOCK TABLES `detail_kartu_invoices` WRITE;
/*!40000 ALTER TABLE `detail_kartu_invoices` DISABLE KEYS */;
INSERT INTO `detail_kartu_invoices` VALUES (1,2,1,'JY-25042101','App\\Models\\KartuStock',1,1,3000000.00,'2025-04-22 01:35:58','2025-04-22 01:35:58'),(2,2,1,'JY-25042101','App\\Models\\KartuHutang',1,2,-3000000.00,'2025-04-22 01:35:58','2025-04-22 01:35:58');
/*!40000 ALTER TABLE `detail_kartu_invoices` ENABLE KEYS */;
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
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_aset` varchar(255) NOT NULL,
  `keterangan_qty_unit` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `nilai_perolehan` decimal(15,2) NOT NULL,
  `periode` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_packs`
--

DROP TABLE IF EXISTS `invoice_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_packs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `person_type` varchar(255) NOT NULL,
  `person_id` int(11) NOT NULL,
  `reference_model` varchar(255) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_price` decimal(20,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_packs_invoice_number_unique` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_packs`
--

LOCK TABLES `invoice_packs` WRITE;
/*!40000 ALTER TABLE `invoice_packs` DISABLE KEYS */;
INSERT INTO `invoice_packs` VALUES (1,2,'JY-25042101','App\\Models\\Supplier',1,'App\\Models\\InvoicePurchaseDetail','2025-04-22',3000000.00,'draft','2025-04-22 01:33:02','2025-04-22 01:33:02'),(2,2,'HW-25042201','App\\Models\\Customer',3,'App\\Models\\InvoiceSaleDetail','2025-04-22',590000.00,'draft','2025-04-22 02:37:39','2025-04-22 02:37:39');
/*!40000 ALTER TABLE `invoice_packs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_purchase_details`
--

DROP TABLE IF EXISTS `invoice_purchase_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_purchase_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `invoice_pack_id` bigint(20) unsigned NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `stock_id` bigint(20) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(255) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
  `unit_backend` varchar(255) DEFAULT NULL,
  `qty_backend` decimal(15,2) NOT NULL,
  `total_price` decimal(20,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_purchase_details_invoice_number_stock_id_unique` (`invoice_number`,`stock_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_purchase_details`
--

LOCK TABLES `invoice_purchase_details` WRITE;
/*!40000 ALTER TABLE `invoice_purchase_details` DISABLE KEYS */;
INSERT INTO `invoice_purchase_details` VALUES (1,2,1,'JY-25042101',1,NULL,500000.00,'Dus',3.00,NULL,0.00,1500000.00,0.00,NULL,0,1,NULL,NULL,'2025-04-22 01:33:02','2025-04-22 01:33:02'),(2,2,1,'JY-25042101',2,NULL,75000.00,'Rim',20.00,NULL,0.00,1500000.00,0.00,NULL,0,1,NULL,NULL,'2025-04-22 01:33:02','2025-04-22 01:33:02');
/*!40000 ALTER TABLE `invoice_purchase_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_sale_details`
--

DROP TABLE IF EXISTS `invoice_sale_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_sale_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_pack_id` bigint(20) unsigned NOT NULL,
  `stock_id` bigint(20) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_backend` varchar(255) NOT NULL,
  `qty_backend` decimal(15,2) NOT NULL,
  `total_price` decimal(20,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_sale_details_invoice_number_stock_id_unique` (`invoice_number`,`stock_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_sale_details`
--

LOCK TABLES `invoice_sale_details` WRITE;
/*!40000 ALTER TABLE `invoice_sale_details` DISABLE KEYS */;
INSERT INTO `invoice_sale_details` VALUES (1,2,'HW-25042201',2,1,NULL,30000.00,'Slop',3.00,'',0.00,90000.00,0.00,NULL,0,3,NULL,NULL,'2025-04-22 02:37:39','2025-04-22 02:37:39'),(2,2,'HW-25042201',2,2,NULL,100000.00,'Rim',5.00,'',0.00,500000.00,0.00,NULL,0,3,NULL,NULL,'2025-04-22 02:37:39','2025-04-22 02:37:39');
/*!40000 ALTER TABLE `invoice_sale_details` ENABLE KEYS */;
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
-- Table structure for table `journal_job_faileds`
--

DROP TABLE IF EXISTS `journal_job_faileds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_job_faileds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url_try_again` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `request` longtext DEFAULT NULL,
  `response` longtext DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_job_faileds`
--

LOCK TABLES `journal_job_faileds` WRITE;
/*!40000 ALTER TABLE `journal_job_faileds` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_job_faileds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_keys`
--

DROP TABLE IF EXISTS `journal_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `key_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_keys`
--

LOCK TABLES `journal_keys` WRITE;
/*!40000 ALTER TABLE `journal_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journals`
--

DROP TABLE IF EXISTS `journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `chart_account_id` int(11) NOT NULL,
  `index_date` decimal(14,0) NOT NULL,
  `journal_number` varchar(255) NOT NULL,
  `code_group` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount_debet` decimal(15,2) DEFAULT NULL,
  `amount_kredit` decimal(15,2) NOT NULL,
  `amount_saldo` decimal(15,2) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_model` varchar(255) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `is_auto_generated` int(11) DEFAULT NULL,
  `lawan_code_group` varchar(10) DEFAULT NULL,
  `is_backdate` int(11) DEFAULT NULL,
  `user_backdate_id` int(11) DEFAULT NULL,
  `toko_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journals_index_date_chart_account_id_unique` (`index_date`,`chart_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journals`
--

LOCK TABLES `journals` WRITE;
/*!40000 ALTER TABLE `journals` DISABLE KEYS */;
INSERT INTO `journals` VALUES (1,2,39,25042208355801,'JP-2504-000001','140001','hutang nomerJY-25042101 dari JOYOBOYO',3000000.00,0.00,3000000.00,NULL,NULL,'App\\Models\\KartuStock',1,1,'211000',NULL,NULL,NULL,'2025-04-22 01:35:58','2025-04-22 01:35:58'),(2,2,57,25042208355801,'JP-2504-000001','211000','hutang nomerJY-25042101 dari JOYOBOYO',0.00,3000000.00,3000000.00,NULL,NULL,'App\\Models\\KartuHutang',1,1,'140001',NULL,NULL,NULL,'2025-04-22 01:35:58','2025-04-22 01:35:58');
/*!40000 ALTER TABLE `journals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_hutangs`
--

DROP TABLE IF EXISTS `kartu_hutangs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_hutangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `code_group` decimal(6,0) NOT NULL,
  `code_group_name` varchar(6) NOT NULL,
  `lawan_code_group` decimal(6,0) NOT NULL,
  `factur_supplier_number` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount_kredit` decimal(12,2) NOT NULL,
  `amount_debet` decimal(12,2) NOT NULL,
  `amount_saldo_purchase` decimal(14,2) DEFAULT NULL,
  `amount_saldo_factur` decimal(14,2) DEFAULT NULL,
  `amount_saldo_person` decimal(14,2) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `person_type` varchar(255) DEFAULT NULL,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kartu_hutangs_factur_supplier_number_index` (`factur_supplier_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_hutangs`
--

LOCK TABLES `kartu_hutangs` WRITE;
/*!40000 ALTER TABLE `kartu_hutangs` DISABLE KEYS */;
INSERT INTO `kartu_hutangs` VALUES (1,0,'mutasi',211000,'Hutang',140001,'JY-25042101','2025-04-22','claim utang dari mutasi pembelian JY-25042101',0.00,3000000.00,3000000.00,3000000.00,3000000.00,NULL,NULL,1,'App\\Models\\Supplier','JP-2504-000001',2,'2025-04-22 01:35:58','2025-04-22 01:35:58');
/*!40000 ALTER TABLE `kartu_hutangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_inventories`
--

DROP TABLE IF EXISTS `kartu_inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `code_group` decimal(6,0) NOT NULL,
  `code_group_name` varchar(6) NOT NULL,
  `lawan_code_group` decimal(6,0) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type_mutasi` varchar(255) NOT NULL,
  `nilai_buku` decimal(15,2) NOT NULL,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_inventories`
--

LOCK TABLES `kartu_inventories` WRITE;
/*!40000 ALTER TABLE `kartu_inventories` DISABLE KEYS */;
/*!40000 ALTER TABLE `kartu_inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_piutangs`
--

DROP TABLE IF EXISTS `kartu_piutangs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_piutangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `code_group` decimal(6,0) NOT NULL,
  `lawan_code_group` decimal(6,0) NOT NULL,
  `code_group_name` varchar(6) NOT NULL,
  `package_number` varchar(255) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount_kredit` decimal(12,2) NOT NULL,
  `amount_debet` decimal(12,2) NOT NULL,
  `amount_saldo_transaction` decimal(14,2) DEFAULT NULL,
  `amount_saldo_factur` decimal(14,2) DEFAULT NULL,
  `amount_saldo_person` decimal(14,2) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `person_type` varchar(255) DEFAULT NULL,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_piutangs`
--

LOCK TABLES `kartu_piutangs` WRITE;
/*!40000 ALTER TABLE `kartu_piutangs` DISABLE KEYS */;
/*!40000 ALTER TABLE `kartu_piutangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_prepaid_expenses`
--

DROP TABLE IF EXISTS `kartu_prepaid_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_prepaid_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `code_group` decimal(6,0) NOT NULL,
  `code_group_name` varchar(6) NOT NULL,
  `lawan_code_group` decimal(6,0) NOT NULL,
  `prepaid_expense_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type_mutasi` varchar(255) NOT NULL,
  `nilai_buku` decimal(15,2) NOT NULL,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_prepaid_expenses`
--

LOCK TABLES `kartu_prepaid_expenses` WRITE;
/*!40000 ALTER TABLE `kartu_prepaid_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `kartu_prepaid_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stocks`
--

DROP TABLE IF EXISTS `kartu_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `code_group` decimal(6,0) NOT NULL,
  `code_group_name` varchar(255) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `mutasi_qty_backend` decimal(10,2) NOT NULL,
  `unit_backend` varchar(255) NOT NULL,
  `mutasi_quantity` decimal(10,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `mutasi_rupiah_on_unit` decimal(10,2) NOT NULL,
  `mutasi_rupiah_total` decimal(14,2) NOT NULL,
  `saldo_qty_backend` decimal(10,2) NOT NULL,
  `saldo_rupiah_total` decimal(14,2) NOT NULL,
  `is_uploaded` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `journal_number` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stocks`
--

LOCK TABLES `kartu_stocks` WRITE;
/*!40000 ALTER TABLE `kartu_stocks` DISABLE KEYS */;
INSERT INTO `kartu_stocks` VALUES (1,2,140001,'Persediaan Barang Dagang',1,3000.00,'Pcs',3.00,'Dus',500.00,1500000.00,3000.00,1500000.00,NULL,NULL,NULL,'JP-2504-000001',1,'2025-04-22 01:35:58','2025-04-22 01:35:58'),(2,2,140001,'Persediaan Barang Dagang',2,10000.00,'Pcs',20.00,'Rim',150.00,1500000.00,10000.00,1500000.00,NULL,NULL,NULL,'JP-2504-000001',1,'2025-04-22 01:35:58','2025-04-22 01:35:58');
/*!40000 ALTER TABLE `kartu_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawans`
--

DROP TABLE IF EXISTS `karyawans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karyawans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `npwp` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `date_masuk` date NOT NULL,
  `date_keluar` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawans`
--

LOCK TABLES `karyawans` WRITE;
/*!40000 ALTER TABLE `karyawans` DISABLE KEYS */;
/*!40000 ALTER TABLE `karyawans` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_11_19_195413_create_chart_accounts',1),(5,'2024_11_19_195528_create_journals',1),(6,'2024_11_27_063538_create_journal_job_failed',1),(7,'2024_12_24_115246_create_table_journal_key',1),(8,'2025_01_26_180915_create_table_kartu_utang2',1),(9,'2025_01_28_164424_create_table_kartu_piutang2',1),(10,'2025_02_07_165017_create_kartu_stocks',1),(11,'2025_03_07_104840_create_permission_tables',1),(12,'2025_03_26_012522_create_book_journals',1),(13,'2025_03_28_155553_create_suppliers',1),(14,'2025_03_28_155628_create_other_persons',1),(15,'2025_03_29_080947_create_customers',1),(16,'2025_04_01_135725_create_stocks',1),(17,'2025_04_03_072619_create_stock_categories',1),(18,'2025_04_05_103308_create_stock_units',1),(19,'2025_04_09_121807_create_inventories',1),(20,'2025_04_09_121819_create_prepaid_expenses',1),(21,'2025_04_09_122942_create_kartu_inventories',1),(22,'2025_04_09_123004_create_kartu_prepaid_expenses',1),(23,'2025_04_11_101256_create_invoice_sale_details_table',1),(24,'2025_04_11_103040_create_invoice_purchase_details_table',1),(25,'2025_04_14_151924_create_karyawans_table',1),(26,'2025_04_16_152118_create_invoice_packs_table',1),(27,'2025_04_21_133704_create_invoice_pack_kartus',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',2),(2,'App\\Models\\User',1),(2,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `other_persons`
--

DROP TABLE IF EXISTS `other_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `other_persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cp_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `other_persons_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `other_persons`
--

LOCK TABLES `other_persons` WRITE;
/*!40000 ALTER TABLE `other_persons` DISABLE KEYS */;
INSERT INTO `other_persons` VALUES (1,'UMU SHOLICHATI',NULL,'23451234','jalan kedamaian jalan surga',NULL,NULL,NULL),(2,'M SHOLEH',NULL,'09761234','jalan istiqomah tanpa putus',NULL,NULL,NULL);
/*!40000 ALTER TABLE `other_persons` ENABLE KEYS */;
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
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'read_hpp','web','2025-04-22 01:32:27','2025-04-22 01:32:27'),(2,'edit_data_journal','web','2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,'delete_data_journal','web','2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prepaid_expenses`
--

DROP TABLE IF EXISTS `prepaid_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prepaid_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_journal_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_bdd` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `nilai_perolehan` decimal(15,2) NOT NULL,
  `periode` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prepaid_expenses_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prepaid_expenses`
--

LOCK TABLES `prepaid_expenses` WRITE;
/*!40000 ALTER TABLE `prepaid_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `prepaid_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,2),(3,2);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2025-04-22 01:32:27','2025-04-22 01:32:27'),(2,'editor-journal','web','2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,'reader-journal','web','2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
-- Table structure for table `stock_categories`
--

DROP TABLE IF EXISTS `stock_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_categories`
--

LOCK TABLES `stock_categories` WRITE;
/*!40000 ALTER TABLE `stock_categories` DISABLE KEYS */;
INSERT INTO `stock_categories` VALUES (1,'plastik',NULL,0,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(2,'kertas bungkus',NULL,0,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,'kotak nasi',NULL,0,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(4,'alat hajatan',NULL,0,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `stock_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_units`
--

DROP TABLE IF EXISTS `stock_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `konversi` decimal(8,2) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_units_stock_id_unit_unique` (`stock_id`,`unit`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_units`
--

LOCK TABLES `stock_units` WRITE;
/*!40000 ALTER TABLE `stock_units` DISABLE KEYS */;
INSERT INTO `stock_units` VALUES (1,1,'Pcs',1.00,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(2,1,'Slop',50.00,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(3,1,'Dus',1000.00,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(4,2,'Pcs',1.00,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(5,2,'Rim',500.00,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `stock_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `unit_default` varchar(255) DEFAULT NULL,
  `unit_backend` varchar(255) NOT NULL,
  `parent_category_id` bigint(20) unsigned DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stocks`
--

LOCK TABLES `stocks` WRITE;
/*!40000 ALTER TABLE `stocks` DISABLE KEYS */;
INSERT INTO `stocks` VALUES (1,'Gelas Plastik 12oz',1,'Slop','Pcs',1,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28'),(2,'Kertas Nasi 22*27 p500',2,'Rim','Pcs',2,NULL,NULL,'2025-04-22 01:32:28','2025-04-22 01:32:28');
/*!40000 ALTER TABLE `stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `cp_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'JOYOBOYO',NULL,NULL,NULL,'123456789','jalan banyak tikungan',NULL,NULL,NULL),(2,'PANCA BUDI',NULL,NULL,NULL,'987654321','jalan terus tapi gak pernah jadian',NULL,NULL,NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
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
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'iqom','iqom@indowella.com',NULL,'$2y$12$hRISDr2v7BulNbzyJmL.a..uf8XqZkpW8vgfYANIBjL2oPVkpDq3O',NULL,NULL,NULL),(2,'ari','ari@indowella.com',NULL,'$2y$12$08Agn6MGT1rR9cl1ax5gceA0ceT7qsnpj4x0kxSWqR8LSrsYXCrfu',NULL,NULL,NULL);
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

-- Dump completed on 2025-04-22 10:42:28
