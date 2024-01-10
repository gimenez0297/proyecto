/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 8.0.21 : Database - victoria_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `auditoria` */

DROP TABLE IF EXISTS `auditoria`;

CREATE TABLE `auditoria` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT NULL,
  `query` text,
  `usuario` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Data for the table `auditoria` */

insert  into `auditoria`(`id_auditoria`,`fecha`,`query`,`usuario`) values 
(1,'2022-04-05 17:19:53','INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,\'Clientes\',\'Clientes\',\'#\',\'<i class=\\\"fas fa-users\\\"></i>\',\'2\',\'Habilitado\');','admin'),
(2,'2022-04-05 17:19:53','INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 51, 1, 1, 1, 1)','admin'),
(3,'2022-04-05 17:21:10','INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(51,\'Administrar Clientes\',\'Administrar Clientes\',\'./clientes\',\'<i class=\\\"fas fa-user-edit\\\"></i>\',\'2.1\',\'Habilitado\');','admin'),
(4,'2022-04-05 17:21:10','INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 52, 1, 1, 1, 1)','admin'),
(5,'2022-04-05 17:21:25','UPDATE menus SET id_menu_padre=51, menu=\'Administrar Clientes\', titulo=\'Administrar Clientes\', url=\'./clientes\', icono=\'<i class=\\\"fas fa-user-edit mt-1\\\"></i>\', orden=\'2.1\', estado=\'Habilitado\' WHERE id_menu = \'52\'','admin'),
(6,'2022-04-05 17:53:22','UPDATE clientes SET ruc=\'44444401-7\', razon_social=\'SIN NOMBRE\', telefono=\'\', celular=\'\', direccion=\'\',email=\'sin-nombre@gmail.com\', obs=\'\', usuario=\'admin\' WHERE id_cliente = \'1\'','admin'),
(7,'2022-04-05 17:53:45','INSERT INTO clientes (razon_social, ruc, direccion, telefono, celular, email, tipo, obs, usuario, fecha) VALUES (\'GONZALEZ PRADO JOSE MANUEL\',\'5233844-4\',\'HUMBERTO ZARZA 129\',\'(021)447408\',\'\',\'\',\'Minorista\',\'\',\'admin\',NOW())','admin'),
(8,'2022-04-05 17:53:52','DELETE FROM clientes WHERE id_cliente = 2','admin'),
(9,'2022-04-05 17:58:25','INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,\'Compras\',\'Compras\',\'#\',\'<i class=\\\"fas fa-shopping-cart\\\"></i>\',\'3\',\'Habilitado\');','admin'),
(10,'2022-04-05 17:58:25','INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 53, 1, 1, 1, 1)','admin'),
(11,'2022-04-05 17:59:50','INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,\'Administrar Proveedores\',\'Administrar Proveedores\',\'./proveedores\',\'<i class=\\\"fas fa-people-carry\\\"></i>\',\'3.1\',\'Habilitado\');','admin'),
(12,'2022-04-05 17:59:50','INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 54, 1, 1, 1, 1)','admin'),
(13,'2022-04-05 18:00:08','UPDATE menus SET id_menu_padre=53, menu=\'Proveedores\', titulo=\'Administrar Proveedores\', url=\'./proveedores\', icono=\'<i class=\\\"fas fa-people-carry mt-1\\\"></i>\', orden=\'3.1\', estado=\'Habilitado\' WHERE id_menu = \'54\'','admin'),
(14,'2022-04-05 18:03:29','INSERT INTO proveedores (proveedor, ruc, nombre_fantasia, contacto, direccion, telefono, email, obs) VALUES (\'GONZALEZ PRADO JOSE MANUEL\',\'5233844-4\', \'JP DESARROLLADOR\', \'\',\'\',\'\',\'\',\'\')','admin'),
(15,'2022-04-05 18:03:36','DELETE FROM proveedores WHERE id_proveedor = 5','admin');

/*Table structure for table `clientes` */

DROP TABLE IF EXISTS `clientes`;

CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) DEFAULT NULL,
  `ruc` varchar(45) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(200) DEFAULT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL COMMENT '1 activo 0 inactivo',
  `usuario` varchar(45) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `RUC_EXISTENTE` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `clientes` */

insert  into `clientes`(`id_cliente`,`razon_social`,`ruc`,`direccion`,`telefono`,`celular`,`email`,`tipo`,`obs`,`usuario`,`fecha`) values 
(1,'SIN NOMBRE','44444401-7','','','','sin-nombre@gmail.com','Minorista','','admin','2020-12-16 16:47:33');

/*Table structure for table `configuracion` */

DROP TABLE IF EXISTS `configuracion`;

CREATE TABLE `configuracion` (
  `id_configuracion` int NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `logo_horizontal` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `favicon` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `colores` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'Hasta 2 colores HTML separado por comas',
  `nombre_sistema` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `subtitulo_sistema` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `moneda` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL COMMENT '1 Activo, 2 Inactivo',
  PRIMARY KEY (`id_configuracion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*Data for the table `configuracion` */

insert  into `configuracion`(`id_configuracion`,`logo`,`logo_horizontal`,`favicon`,`colores`,`nombre_sistema`,`subtitulo_sistema`,`moneda`,`estado`) values 
(1,'dist/images/logo.png','dist/images/logo-horizontal.png','dist/images/favicon.png','#664d4d, #352828','Farmacia Santa Victoria',NULL,'Gs.',1);

/*Table structure for table `menus` */

DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `id_menu_padre` int DEFAULT NULL,
  `menu` varchar(100) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `icono` varchar(45) DEFAULT NULL,
  `orden` varchar(255) DEFAULT NULL,
  `estado` varchar(15) DEFAULT NULL COMMENT 'Habilitado, Deshabilitado',
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4;

/*Data for the table `menus` */

insert  into `menus`(`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`) values 
(1,NULL,'Inicio','Inicio','./inicio','<i class=\'fas fa-home\'></i>','1','Habilitado'),
(6,NULL,'Sistema','','','<i class=\'fas fa-cogs\'></i>','90','Habilitado'),
(7,6,'Usuarios','Usuarios','./usuarios','<i class=\'fa fa-users mt-1\'></i>','90.1','Habilitado'),
(8,6,'Roles','Administrar Roles','./administrar-roles','<i class=\'fa fa-user-edit mt-1\'></i>','90.2','Habilitado'),
(9,6,'Menú','Administrar Menús','./administrar-menus','<i class=\"fas fa-th-list mt-1\"></i>','90.3','Habilitado'),
(10,6,'Soporte','Soporte','./soporte','<i class=\'fas fa-mug-hot mt-1\'></i>','90.99','Habilitado'),
(51,NULL,'Clientes','Clientes','#','<i class=\"fas fa-users\"></i>','2','Habilitado'),
(52,51,'Administrar Clientes','Administrar Clientes','./clientes','<i class=\"fas fa-user-edit mt-1\"></i>','2.1','Habilitado'),
(53,NULL,'Compras','Compras','#','<i class=\"fas fa-shopping-cart\"></i>','3','Habilitado'),
(54,53,'Proveedores','Administrar Proveedores','./proveedores','<i class=\"fas fa-people-carry mt-1\"></i>','3.1','Habilitado');

/*Table structure for table `proveedores` */

DROP TABLE IF EXISTS `proveedores`;

CREATE TABLE `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(255) NOT NULL,
  `nombre_fantasia` varchar(255) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `contacto` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `proveedores` */

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `rol` varchar(100) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL COMMENT 'Activo, Inactivo',
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `roles` */

insert  into `roles`(`id_rol`,`rol`,`estado`) values 
(1,'Administrador del Sistema','Activo');

/*Table structure for table `roles_menu` */

DROP TABLE IF EXISTS `roles_menu`;

CREATE TABLE `roles_menu` (
  `id_rol_menu` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `id_menu` int NOT NULL,
  `acceso` tinyint(1) DEFAULT NULL COMMENT '1- Si, 0-No',
  `insertar` tinyint(1) DEFAULT NULL COMMENT '1- Si, 0-No',
  `editar` tinyint(1) DEFAULT NULL COMMENT '1- Si, 0-No',
  `eliminar` tinyint(1) DEFAULT NULL COMMENT '1- Si, 0-No',
  PRIMARY KEY (`id_rol_menu`),
  KEY `id_usuarios_rol` (`id_rol`,`id_menu`),
  KEY `id_menu` (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4;

/*Data for the table `roles_menu` */

insert  into `roles_menu`(`id_rol_menu`,`id_rol`,`id_menu`,`acceso`,`insertar`,`editar`,`eliminar`) values 
(1,1,1,1,1,1,1),
(6,1,6,1,1,1,1),
(7,1,7,1,1,1,1),
(8,1,8,1,1,1,1),
(9,1,9,1,1,1,1),
(10,1,10,1,1,1,1),
(52,1,51,1,1,1,1),
(53,1,52,1,1,1,1),
(54,1,53,1,1,1,1),
(55,1,54,1,1,1,1);

/*Table structure for table `sucursales` */

DROP TABLE IF EXISTS `sucursales`;

CREATE TABLE `sucursales` (
  `id_sucursal` int NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ruc` varchar(45) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `razon_social` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sucursal` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ciudad` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `departamento` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `pais` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `telefono` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `moneda` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL COMMENT '1 Activo, 0 Inactivo',
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*Data for the table `sucursales` */

insert  into `sucursales`(`id_sucursal`,`nombre_empresa`,`ruc`,`razon_social`,`sucursal`,`direccion`,`ciudad`,`departamento`,`pais`,`telefono`,`email`,`moneda`,`estado`) values 
(1,'Farmacia Santa Victoria',NULL,'Farmacia Santa Victoria','CASA MATRIZ',NULL,'Villarica','Guairá','Paraguay',NULL,'','Gs.',1);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(249) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_apellido` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ci` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_sucursal` int DEFAULT NULL,
  `foto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_rol` int DEFAULT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `verified` tinyint unsigned NOT NULL DEFAULT '0',
  `resettable` tinyint unsigned NOT NULL DEFAULT '1',
  `roles_mask` int unsigned NOT NULL DEFAULT '0',
  `registered` int unsigned NOT NULL,
  `last_login` int unsigned DEFAULT NULL,
  `force_logout` mediumint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`email`,`password`,`username`,`nombre_apellido`,`departamento`,`cargo`,`ci`,`telefono`,`direccion`,`id_sucursal`,`foto`,`id_rol`,`status`,`verified`,`resettable`,`roles_mask`,`registered`,`last_login`,`force_logout`) values 
(1,'soporte@freelancer.com.py','$2y$10$tLZGaaXdtbzx34YUi5DXme42LsgquZTJsJwXKD46qsbGqX/DVryfm','admin','Administrador','Sistemas','Analista de Sistemas','2511890','0981900730','Lillo 2173 entre Bélgica y Carmona',1,'dist/images/users/nobody.png',1,0,1,1,1,1534447970,1649193521,1);

/*Table structure for table `users_confirmations` */

DROP TABLE IF EXISTS `users_confirmations`;

CREATE TABLE `users_confirmations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `email` varchar(249) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `email_expires` (`email`,`expires`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users_confirmations` */

/*Table structure for table `users_remembered` */

DROP TABLE IF EXISTS `users_remembered`;

CREATE TABLE `users_remembered` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user` int unsigned NOT NULL,
  `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user` (`user`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users_remembered` */

insert  into `users_remembered`(`id`,`user`,`selector`,`token`,`expires`) values 
(2,1,'z-3rwdLgRCyRGC6yrj-7mJC8','$2y$10$kvomz6qkQnP0aqZc5hdZPuyv03f10QpZjcqTUCStmPzGAH.4CvSrK',1649236721);

/*Table structure for table `users_resets` */

DROP TABLE IF EXISTS `users_resets`;

CREATE TABLE `users_resets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user` int unsigned NOT NULL,
  `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user_expires` (`user`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users_resets` */

/*Table structure for table `users_throttling` */

DROP TABLE IF EXISTS `users_throttling`;

CREATE TABLE `users_throttling` (
  `bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `tokens` float unsigned NOT NULL,
  `replenished_at` int unsigned NOT NULL,
  `expires_at` int unsigned NOT NULL,
  PRIMARY KEY (`bucket`),
  KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users_throttling` */

insert  into `users_throttling`(`bucket`,`tokens`,`replenished_at`,`expires_at`) values 
('ejWtPDKvxt-q7LZ3mFjzUoIWKJYzu47igC8Jd9mffFk',74,1649193520,1649733520);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
