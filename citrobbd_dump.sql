-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 62.146.181.70    Database: citrobbd
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-ubu2404

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'citrob','$2y$10$SR7qWDJzk2C4YW9p2v3uo.jo7pJCXI3wNXgC8.E45rZ1haYTK0ek6','admin@citrob.cl','Administrador CITROB',1,'2026-03-18 21:24:00','2026-03-18 20:11:02');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'apps',
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'todos','Todos los Productos','apps',NULL,0,1,'2026-03-18 20:11:02'),(2,'microcontroladores','Microcontroladores','memory',NULL,1,1,'2026-03-18 20:11:02'),(3,'sensores','Sensores','sensors',NULL,2,1,'2026-03-18 20:11:02'),(4,'motores','Motores y Servos','precision_manufacturing',NULL,3,1,'2026-03-18 20:11:02'),(5,'componentes','Componentes','settings_input_component',NULL,4,1,'2026-03-18 20:11:02'),(6,'alimentacion','Alimentación','power',NULL,5,1,'2026-03-18 20:11:02'),(7,'displays','Displays','tv',NULL,6,1,'2026-03-18 20:11:02');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `address` text DEFAULT NULL,
  `city` varchar(100) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `qty` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` int(10) unsigned NOT NULL DEFAULT 0,
  `subtotal` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_item_order` (`order_id`),
  KEY `fk_item_product` (`product_id`),
  CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `address` text DEFAULT NULL,
  `city` varchar(100) NOT NULL DEFAULT '',
  `total` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_order_customer` (`customer_id`),
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT 2,
  `name` varchar(200) NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT '',
  `price` int(10) unsigned NOT NULL DEFAULT 0,
  `rating` decimal(3,1) NOT NULL DEFAULT 4.0,
  `reviews` int(10) unsigned NOT NULL DEFAULT 0,
  `badge` varchar(50) NOT NULL DEFAULT '',
  `badge_color` varchar(20) NOT NULL DEFAULT '',
  `image_url` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 100,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,2,'Arduino Uno R3 Compatible','Placa de Desarrollo',8990,4.5,42,'En Stock','green','/img/products/1.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:02'),(2,2,'Arduino Mega 2560 R3 Compatible','Placa de Desarrollo',14990,4.3,24,'','','/img/products/2.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:02'),(3,2,'ESP32 Development Board WiFi+Bluetooth','Placa IoT',6990,4.8,89,'Más Vendido','primary','/img/products/3.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:02'),(4,2,'Arduino Nano V3.0 Compatible','Placa Compacta',3990,4.6,67,'','','/img/products/4.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:02'),(5,2,'Raspberry Pi Pico RP2040','Microcontrolador',4990,4.9,56,'Nuevo','blue','/img/products/5.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:03'),(6,2,'NodeMCU V3 ESP8266 WiFi','Placa IoT',4490,4.4,31,'','','/img/products/6.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:03'),(7,2,'STM32 Blue Pill F103C8T6','Placa de Desarrollo',3490,4.2,18,'','','/img/products/7.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:03'),(8,2,'Teensy 4.0 USB Development','Placa de Desarrollo',24990,4.9,14,'Premium','purple','/img/products/8.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:03'),(9,3,'Sensor Ultrasónico HC-SR04','Sensor de Distancia',1490,4.7,95,'Popular','primary','/img/products/9.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:04'),(10,3,'Sensor de Temperatura DHT22','Sensor Ambiental',3990,4.6,48,'','','/img/products/10.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:04'),(11,3,'Módulo Acelerómetro MPU6050','Sensor Movimiento',2490,4.5,37,'','','/img/products/11.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:04'),(12,3,'Sensor Infrarrojo IR Obstáculos','Sensor de Proximidad',990,4.3,62,'Oferta','red','/img/products/12.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:04'),(13,3,'Sensor de Línea TCRT5000','Sensor Óptico',790,4.4,85,'','','/img/products/13.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:04'),(14,3,'Kit 37 Sensores para Arduino','Kit Completo',19990,4.8,28,'Más Vendido','primary','/img/products/14.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:05'),(15,4,'Servo Motor SG90 9g Mini','Micro Servo',1990,4.5,112,'Popular','primary','/img/products/15.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:05'),(16,4,'Motor DC con Caja Reductora','Motor DC',2490,4.3,45,'','','/img/products/16.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:05'),(17,4,'Servo Motor MG996R Alto Torque','Servo Metal',4990,4.6,38,'','','/img/products/17.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:06'),(18,4,'Motor Paso a Paso 28BYJ-48 + Driver','Motor Stepper',2990,4.4,52,'','','/img/products/18.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:06'),(19,4,'Driver Motor L298N Puente H','Driver de Motor',3490,4.7,73,'En Stock','green','/img/products/19.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:06'),(20,5,'Kit Resistencias 600 pcs Surtido','Resistencias',2990,4.6,33,'','','/img/products/20.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:07'),(21,5,'Kit LEDs 300 pcs Colores Surtidos','LEDs',2490,4.5,41,'','','/img/products/21.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:07'),(22,5,'Protoboard 830 Puntos','Protoboard',1990,4.7,88,'En Stock','green','/img/products/22.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:07'),(23,5,'Pulsadores Táctil Mini 4 Pines x20','Botones',990,4.3,57,'','','/img/products/23.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:07'),(24,5,'Cables Jumper Dupont 120pcs','Cables',2490,4.8,96,'Popular','primary','/img/products/24.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:08'),(25,5,'Circuito Integrado 555 Timer x5','IC',990,4.4,29,'','','/img/products/25.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:08'),(26,5,'Potenciómetro 10K Lineal x5','Potenciómetros',1490,4.5,22,'','','/img/products/26.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:08'),(27,6,'Fuente de Poder 5V 3A USB-C','Fuente',4990,4.6,34,'','','/img/products/27.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:08'),(28,6,'Porta Pilas 4xAA con Switch','Porta Pilas',990,4.3,47,'','','/img/products/28.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:09'),(29,6,'Batería LiPo 3.7V 1000mAh','Batería',3990,4.5,26,'','','/img/products/29.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:09'),(30,6,'Regulador de Voltaje LM7805 x5','Regulador',1290,4.4,19,'','','/img/products/30.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:09'),(31,7,'Pantalla OLED 0.96\" I2C 128x64','Display OLED',3990,4.7,64,'Popular','primary','/img/products/31.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:09'),(32,7,'LCD 16x2 con Módulo I2C','Display LCD',3490,4.5,53,'','','/img/products/32.jpg',NULL,100,1,0,'2026-03-18 20:11:02','2026-03-19 00:57:09');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-15 20:25:02
