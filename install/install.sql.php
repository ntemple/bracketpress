<?php die(); // dbschema ?>
1.0.2

-- MySQL dump 10.13  Distrib 5.1.66, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bp
-- ------------------------------------------------------
-- Server version	5.1.66-0ubuntu0.10.04.3

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
-- Table structure for table `bp_location`
--

DROP TABLE IF EXISTS `bp_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_location` (
`ID` int(11) NOT NULL AUTO_INCREMENT,
`match_id` SMALLINT UNSIGNED,
`venue` text NOT NULL,
`game_date` date NOT NULL,
`game_time` time DEFAULT NULL,
PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bp_location`
--

LOCK TABLES `bp_location` WRITE;
/*!40000 ALTER TABLE `bp_location` DISABLE KEYS */;
/*!40000 ALTER TABLE `bp_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bp_match`
--

CREATE TABLE IF NOT EXISTS `bp_match` (
`match_id` smallint(45) unsigned NOT NULL,
`post_id` int(11) unsigned NOT NULL,
`user_id` int(10) unsigned NOT NULL,
`team1_score` int(11) DEFAULT NULL,
`team2_score` int(11) DEFAULT NULL,
`winner_id` int(11) DEFAULT NULL,
`points_awarded` int(11) DEFAULT NULL,
UNIQUE KEY `match_id` (`match_id`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bp_match`
--

LOCK TABLES `bp_match` WRITE;
/*!40000 ALTER TABLE `bp_match` DISABLE KEYS */;
/*!40000 ALTER TABLE `bp_match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bp_team`
--

DROP TABLE IF EXISTS `bp_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_team` (
`ID` int(11) NOT NULL,
`seed` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`region` int(11) NOT NULL,
`conference` varchar(255) DEFAULT NULL,
PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bp_team`
--

LOCK TABLES `bp_team` WRITE;
/*!40000 ALTER TABLE `bp_team` DISABLE KEYS */;
/*!40000 ALTER TABLE `bp_team` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-05 13:54:50