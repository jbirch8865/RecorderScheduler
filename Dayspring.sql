-- MySQL dump 10.16  Distrib 10.1.23-MariaDB, for debian-linux-gnueabihf (armv7l)
--
-- Host: localhost    Database: Dayspring
-- ------------------------------------------------------
-- Server version	10.1.23-MariaDB-9+deb9u1

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

CREATE DATABASE `Dayspring`;

USE `Dayspring`;

--
-- Table structure for table `Recording_Preferences`
--


DROP TABLE IF EXISTS `Recording_Preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Recording_Preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Auto_Record` tinyint(1) NOT NULL,
  `Day_Of_Week` int(11) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `Upload_Location` varchar(155) NOT NULL,
  `Email_Alert` varchar(55) NOT NULL,
  `Alert_On_Status` int(11) DEFAULT NULL,
  `Title_With_Date_Formats` varchar(55) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Alert_On_Status` (`Alert_On_Status`),
  KEY `Day_Of_Week` (`Day_Of_Week`),
  CONSTRAINT `Recording_Preferences_ibfk_1` FOREIGN KEY (`Alert_On_Status`) REFERENCES `Recording_Status` (`Status_id`),
  CONSTRAINT `Recording_Preferences_ibfk_2` FOREIGN KEY (`Day_Of_Week`) REFERENCES `Weekdays` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Recording_Preferences`
--

LOCK TABLES `Recording_Preferences` WRITE;
/*!40000 ALTER TABLE `Recording_Preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `Recording_Preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Recording_Status`
--

DROP TABLE IF EXISTS `Recording_Status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Recording_Status` (
  `Status_id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(55) NOT NULL,
  `Description` varchar(255) NOT NULL,
  PRIMARY KEY (`Status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Recording_Status`
--

LOCK TABLES `Recording_Status` WRITE;
/*!40000 ALTER TABLE `Recording_Status` DISABLE KEYS */;
INSERT INTO `Recording_Status` VALUES (1,'Pending','This hasn\'t done anything'),(2,'Recording','The first step is starting to record.  This just means that recording started'),(3,'Stopped','This means that recording has started at least once and is now stopped.  Resuming would set back to recording.'),(4,'Rendered','This means that the recording has finished and has gone through the custom rendering process'),(5,'Uploaded','This means that the file has been uploaded and nothing more can be done.');
/*!40000 ALTER TABLE `Recording_Status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Services_To_Record`
--

DROP TABLE IF EXISTS `Services_To_Record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Services_To_Record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Timestamp_To_Start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Timestamp_To_Stop` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `RecordingStatus` int(1) DEFAULT '1',
  `FileLocation` varchar(155) NOT NULL,
  `Alert_Email` varchar(155) NOT NULL,
  `Alert_Status` int(11) DEFAULT NULL,
  `Recording_Started` timestamp NULL DEFAULT NULL,
  `Recording_Set_To_End` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `RecordingStatus` (`RecordingStatus`),
  CONSTRAINT `Services_To_Record_ibfk_1` FOREIGN KEY (`RecordingStatus`) REFERENCES `Recording_Status` (`Status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Services_To_Record`
--

LOCK TABLES `Services_To_Record` WRITE;
/*!40000 ALTER TABLE `Services_To_Record` DISABLE KEYS */;
/*!40000 ALTER TABLE `Services_To_Record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Weekdays`
--

DROP TABLE IF EXISTS `Weekdays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Weekdays` (
  `id` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Weekdays`
--

LOCK TABLES `Weekdays` WRITE;
/*!40000 ALTER TABLE `Weekdays` DISABLE KEYS */;
INSERT INTO `Weekdays` VALUES (0,'Sunday'),(1,'Monday'),(2,'Tuesday'),(3,'Wednesday'),(4,'Thursday'),(5,'Friday'),(6,'Saturday');
/*!40000 ALTER TABLE `Weekdays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `syslog`
--

DROP TABLE IF EXISTS `syslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description` varchar(10000) NOT NULL,
  `Response` varchar(1000) NOT NULL,
  `Type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27571 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `syslog`
--

LOCK TABLES `syslog` WRITE;
/*!40000 ALTER TABLE `syslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `syslog` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-11 17:06:32
