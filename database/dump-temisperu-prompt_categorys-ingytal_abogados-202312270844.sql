-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: localhost    Database: ingytal_abogados
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.25-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `prompts_category`
--

DROP TABLE IF EXISTS `prompts_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prompts_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prompts_category`
--

LOCK TABLES `prompts_category` WRITE;
/*!40000 ALTER TABLE `prompts_category` DISABLE KEYS */;
INSERT INTO `prompts_category` VALUES (1,'Defensa Penal',NULL,NULL),(2,'Derecho Administrativo y Constitucional',NULL,NULL),(3,'Derecho Ambiental',NULL,NULL),(4,'Derecho Civil',NULL,NULL),(5,'Derecho de empresa',NULL,NULL),(6,'Derecho de familia',NULL,NULL),(7,'Derecho Inmigración',NULL,NULL),(8,'Derecho Inmobiliario',NULL,NULL),(9,'Derecho Internacional',NULL,NULL),(10,'Derecho Laboral',NULL,NULL),(11,'Derecho Mercantil y Corporativo',NULL,NULL),(12,'Derecho Penal',NULL,NULL),(13,'Derecho Sanitario',NULL,NULL),(14,'Derecho Tributario',NULL,NULL),(15,'Derecho Propiedad Intelectual',NULL,NULL),(16,'Derecho Administrativo y Constitucional',NULL,NULL),(17,'Fundamentos jurídicos',NULL,NULL),(18,'Gestión y Administración de Despachos',NULL,NULL),(19,'Herramientas y Recursos',NULL,NULL),(20,'Plantillas',NULL,NULL),(21,'Tecnología y Derecho',NULL,NULL),(22,'Anuncios de Facebook',NULL,NULL),(23,'Anuncios de Google',NULL,NULL),(24,'	Branding y posicionamiento',NULL,NULL),(25,'Blog',NULL,NULL),(26,'Contabilidad',NULL,NULL),(27,'Copywriting',NULL,NULL),(28,'Estrategias Digitales',NULL,NULL),(29,'Generador de Ideas de Instagram',NULL,NULL),(30,'Fórmulas de Copy / Campaña',NULL,NULL),(31,'Linkedin Marketing	\r\n',NULL,NULL),(32,'Storytelling en branding	\r\n',NULL,NULL),(33,'Textos de promoción',NULL,NULL),(34,'Generales 1',NULL,NULL),(35,'Generales 2',NULL,NULL);
/*!40000 ALTER TABLE `prompts_category` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-27  8:44:18
