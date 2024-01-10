/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 5.7.28-0ubuntu0.19.04.2 : Database - backup_farma
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `origen` */

DROP TABLE IF EXISTS `origen`;

CREATE TABLE `origen` (
  `idORIGEN` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ORIGEN` char(15) COLLATE latin1_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idORIGEN`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

/*Data for the table `origen` */

insert  into `origen`(`idORIGEN`,`ORIGEN`) values 
(1,'NACIONAL'),
(2,'IMPORTADO'),
(3,'TRAT.IMP');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
