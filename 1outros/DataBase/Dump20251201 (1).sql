-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mydb
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `despesas`
--

CREATE DATABASE mydb;
USE mydb;

DROP TABLE IF EXISTS `despesas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despesas` (
  `id_despesas` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(255) NOT NULL,
  `valor_mensal` decimal(10,2) NOT NULL,
  `fixa_variavel` enum('Fixa','Variavel') NOT NULL,
  `id_utilizador` int NOT NULL,
  PRIMARY KEY (`id_despesas`),
  KEY `fk_despesas_utilizadores1_idx` (`id_utilizador`),
  CONSTRAINT `fk_despesas_utilizadores1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despesas`
--

LOCK TABLES `despesas` WRITE;
/*!40000 ALTER TABLE `despesas` DISABLE KEYS */;
/*!40000 ALTER TABLE `despesas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_financeiro`
--

DROP TABLE IF EXISTS `historico_financeiro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_financeiro` (
  `id_hist` int NOT NULL AUTO_INCREMENT,
  `mes` varchar(45) NOT NULL,
  `total_gastos` decimal(10,2) NOT NULL,
  `saldo_restante` decimal(10,2) NOT NULL,
  `id_utilizador` int NOT NULL,
  PRIMARY KEY (`id_hist`),
  KEY `fk_historico_financeiro_utilizadores1_idx` (`id_utilizador`),
  CONSTRAINT `fk_historico_financeiro_utilizadores1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_financeiro`
--

LOCK TABLES `historico_financeiro` WRITE;
/*!40000 ALTER TABLE `historico_financeiro` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_financeiro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `percentuais_orcamento`
--

DROP TABLE IF EXISTS `percentuais_orcamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `percentuais_orcamento` (
  `id_percentual` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(255) NOT NULL,
  `percentual_recomendado` tinyint NOT NULL,
  PRIMARY KEY (`id_percentual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `percentuais_orcamento`
--

LOCK TABLES `percentuais_orcamento` WRITE;
/*!40000 ALTER TABLE `percentuais_orcamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `percentuais_orcamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recomendacoes`
--

DROP TABLE IF EXISTS `recomendacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recomendacoes` (
  `id_recomendacoes` int NOT NULL AUTO_INCREMENT,
  `text_recomendacao` text NOT NULL,
  `data_recomendacao` date NOT NULL,
  `id_utilizador` int NOT NULL,
  PRIMARY KEY (`id_recomendacoes`),
  KEY `fk_recomendacoes_utilizadores1_idx` (`id_utilizador`),
  CONSTRAINT `fk_recomendacoes_utilizadores1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recomendacoes`
--

LOCK TABLES `recomendacoes` WRITE;
/*!40000 ALTER TABLE `recomendacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `recomendacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_util`
--

DROP TABLE IF EXISTS `tipos_util`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_util` (
  `idtipos` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`idtipos`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_util`
--

LOCK TABLES `tipos_util` WRITE;
/*!40000 ALTER TABLE `tipos_util` DISABLE KEYS */;
INSERT INTO `tipos_util` VALUES (1,'user');
/*!40000 ALTER TABLE `tipos_util` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilizadores`
--

DROP TABLE IF EXISTS `utilizadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `idade` int DEFAULT NULL,
  `ocupacao` varchar(100) DEFAULT NULL,
  `estilo_vida` varchar(255) DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `tipo_util` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_utilizadores_tipos_util_idx` (`tipo_util`),
  CONSTRAINT `fk_utilizadores_tipos_util` FOREIGN KEY (`tipo_util`) REFERENCES `tipos_util` (`idtipos`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilizadores`
--

LOCK TABLES `utilizadores` WRITE;
/*!40000 ALTER TABLE `utilizadores` DISABLE KEYS */;
INSERT INTO `utilizadores` VALUES (1,'AntonioTeste','teste@teste.com','$2y$10$wPYAEgEyt7QAC0iqlkGTs.cXcG23I9BoXGYgBVyWNJhtqI0qoCidq',18,'Estudante','Académico',950.00,1),(2,'teste','teste2@teste.com','$2y$10$HklehyWuYQkSWX5l75c2Sepi3ueo0Eqf9/6WCU.7b2vYEv0tG6ACa',NULL,NULL,NULL,NULL,1),(3,'teste','teste2@teste.com','$2y$10$xT/WazNnIJ7o3glb.6TXIO8J1aldWwf.ST7HJCJTtQoAOeTWsgbi6',NULL,NULL,NULL,NULL,1),(4,'Antonio','antoniorpvalente@gmail.com','$2y$10$gNrNCVavkBsL9wlg0cMUUOMwhFvmEX3cvK7lpxgStF/ejJ4Ox94ie',18,'Estudante','Académico',950.00,1),(5,'AntonioVa','antoniorpvalente@gmail.com','$2y$10$240a2cRKAmKQdD.4BCNZ5eUN6io/JGNe8W63rYhsS6TVl74QwbwuG',22,'Estudante','Académico',950.00,1),(6,'AntonioTeste2','teste4@teste4.com','$2y$10$CBaqpWAxAv9ZNAXewpW9N.b2a82.M0aFNQ9xIIvAszFuKaLISklny',18,'Trabalhador','Outro',950.00,1),(7,'AntonioTeste3','teste3@teste4.com','$2y$10$CqvrWXwyT.S3r8oIx3t58.lf6GsmCqw4YiUEdcwrhzufBvijo4rT6',18,'Trabalhador','Outro',950.00,1);
/*!40000 ALTER TABLE `utilizadores` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-01 12:44:23
