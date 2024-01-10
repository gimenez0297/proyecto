-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 08/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `proveedores`   
	ADD COLUMN `usuario` VARCHAR(45) NULL AFTER `obs`,
	ADD COLUMN `fecha` DATETIME NULL AFTER `usuario`;

CREATE TABLE `tipos_productos` (  
  `id_tipo_producto` INT NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_tipo_producto`) 
) ENGINE=INNODB;

CREATE TABLE `clasificaciones_productos` (  
  `id_clasificacion_producto` INT NOT NULL AUTO_INCREMENT,
  `clasificacion` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_clasificacion_producto`) 
) ENGINE=INNODB;

-- Menú Productos
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado)
VALUES(NULL,'Productos','Productos','#','<i class=\"fas fa-boxes\"></i>','4','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 55, 1, 1, 1, 1);

-- Menú Tipos De Productos
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado)
VALUES(55,'Tipos','Tipos De Productos','./tipos-productos','<i class=\"fa fa-sitemap mt-1\"></i>','4.1','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 56, 1, 1, 1, 1);

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado)
VALUES(55,'Clasificación','Clasificación De Productos','./clasificacion-productos','<i class=\"fa fa-sitemap mt-1\"></i>','4.2','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 57, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 11/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `principios_activos` (  
  `id_principio` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_principio`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(55,'Principios Activos','Principios Activos','./principios-activos','<i class=\"fa fa-capsules mt-1\"></i>','4.3','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 58, 1, 1, 1, 1);

CREATE TABLE `laboratorios` (  
  `id_laboratorio` INT NOT NULL AUTO_INCREMENT,
  `laboratorio` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_laboratorio`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(55,'Laboratorios','Laboratorios','./laboratorios','<i class=\"fa fa-vials mt-1\"></i>','4.4','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 59, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 12/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `clientes`   
	ADD COLUMN `fecha_nacimiento` DATE NULL AFTER `tipo`,
	ADD COLUMN `id_distrito` INT NULL AFTER `fecha_nacimiento`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `origenes` (  
  `id_origen` INT NOT NULL AUTO_INCREMENT,
  `origen` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_origen`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(55,'Origenes','Origenes','./origenes','<i class=\"fas fa-sitemap mt-1\"></i>','4.6','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 62, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 12/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Marcas','Marcas','./marcas','<i class=\"fas fa-bookmark mt-1\"></i>','4.5','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 61, 1, 1, 1, 1);

CREATE TABLE `marcas` (  
  `id_marca` INT NOT NULL AUTO_INCREMENT,
  `marca` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_marca`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Unidades De Medida','Unidades De Medida','./unidades-medidas','<i class=\"fas fa-balance-scale mt-1\"></i>','4.7','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 63, 1, 1, 1, 1);

CREATE TABLE `unidades_medidas` (  
  `id_unidad_medida` INT NOT NULL AUTO_INCREMENT,
  `unidad_medida` VARCHAR(255),
  `sigla` VARCHAR(15),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_unidad_medida`) 
) ENGINE=INNODB;

INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('MM', 'MILIMETRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('M', 'METRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('NM', 'NANÓMETRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('MES', 'MES', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('MG', 'MILIGRAMO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('NG', 'NANOGRAMO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('M3', 'METROS CUBICOS', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('ML', 'MILILITRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('μM', 'MICRÓMETRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('μG', 'MICROGRAMO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('μL', 'MICROLITRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('NN', 'SIN DEFINIR', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('M2', 'METROS CUADRADOS', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('DET.', 'DETERMINACION', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('KG.', 'KILOGRAMO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('LB.', 'LIBRA', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('L', 'LITRO', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('TON.', 'TONELADA', 1);
INSERT INTO unidades_medidas (sigla, unidad_medida, estado) VALUES('UNI', 'UNIDAD', 1);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `presentaciones` (  
  `id_presentacion` INT NOT NULL AUTO_INCREMENT,
  `presentacion` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_presentacion`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(55,'Presentaciones','Presentaciones','./presentaciones','<i class=\"fas fa-balance-scale mt-1\"></i>','4.8','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 64, 1, 1, 1, 1)

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 13/04/2022
-- ----------------------------------------------------------------------
UPDATE clientes SET celular=NULL, email=NULL WHERE celular='NULL' OR email='NULL';
UPDATE clientes SET tipo='Minorista';

-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `marcas` ADD COLUMN `logo` VARCHAR(255) NULL AFTER usuario;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 19/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Productos','Productos','./productos','<i class=\"fas fa-box mt-1\"></i>','4.5','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 71, 1, 1, 1, 1);

CREATE TABLE `monedas` (
  `id_moneda` INT(11) NOT NULL AUTO_INCREMENT,
  `moneda` VARCHAR(100) DEFAULT NULL,
  `simbolo` VARCHAR(45) DEFAULT NULL,
  `decimales` INT(2) DEFAULT NULL,
  PRIMARY KEY (`id_moneda`)
) ENGINE=INNODB CHARSET=utf8mb4;

INSERT INTO `monedas` (`moneda`, `simbolo`, `decimales`) VALUES
('Guaraníes', 'Gs.', '0'),
('Dólares Americanos', 'USD.', '2');

CREATE TABLE `cotizaciones` (
  `id_cotizacion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_moneda` INT(11) DEFAULT NULL,
  `compra` INT(11) DEFAULT NULL,
  `venta` INT(11) DEFAULT NULL,
  `fecha` DATETIME DEFAULT NULL,
  `usuario` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`id_cotizacion`)
) ENGINE=INNODB CHARSET=utf8mb4;

CREATE TABLE `rubros` (  
  `id_rubro` INT NOT NULL AUTO_INCREMENT,
  `rubro` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_rubro`) 
) ENGINE=INNODB;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 19/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(66,'Rubros','Rubros','./rubros','<i class="fas fa-sitemap mt-1"></i>','80.1.5','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 72, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 19/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `productos` (  
  `id_producto` INT NOT NULL AUTO_INCREMENT,
  `codigo` BIGINT(13),
  `codigo_proveedor` VARCHAR(255),
  `id_marca` INT,
  `id_laboratorio` INT,
  `id_proveedor` INT,
  `id_tipo_producto` INT,
  `id_origen` INT,
  `id_moneda` INT DEFAULT 1,
  `id_presentacion` INT,
  `id_principio_activo` INT,
  `id_clasificacion` INT,
  `id_unidad_medida` INT,
  `id_rubro` INT,
  `id_pais` INT,
  `producto` VARCHAR(255),
  `vencimiento` DATE,
  `precio` INT,
  `costo` INT,
  `copete` VARCHAR(255),
  `descripción` TEXT,
  `conservacion` TINYINT(1) DEFAULT 0 COMMENT '1-NORMAL, 2-REFRIGERADO',
  `controlado` TINYINT(1) DEFAULT 0 COMMENT '0-NO, 1-Si',
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_producto`) 
) ENGINE=INNODB CHARSET=utf8mb4;

CREATE TABLE `stock` (  
  `id_stock` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `id_sucursal` INT,
  `stock` INT,
  PRIMARY KEY (`id_stock`) 
) ENGINE=INNODB CHARSET=utf8mb4;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 20/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`   
	DROP COLUMN `id_proveedor`;

ALTER TABLE `productos`   
	ADD COLUMN `indicaciones` TEXT NULL AFTER `controlado`,
	ADD COLUMN `observaciones` TEXT NULL AFTER `indicaciones`;

ALTER TABLE `productos`   
	CHANGE `descripción` `descripcion` TEXT NULL;

ALTER TABLE `productos`   
	CHANGE `conservacion` `conservacion` TINYINT(1) DEFAULT 1 NULL COMMENT '1-NORMAL, 2-REFRIGERADO';

CREATE TABLE `productos_fotos` (  
  `id_producto_foto` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `foto` VARCHAR(255),
  `orden` INT,
  PRIMARY KEY (`id_producto_foto`) 
) ENGINE=INNODB CHARSET=utf8mb4;

CREATE TABLE `productos_etiquetas` (  
  `id_producto_etiqueta` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `etiqueta` VARCHAR(255),
  PRIMARY KEY (`id_producto_etiqueta`) 
) ENGINE=INNODB CHARSET=utf8mb4;

CREATE TABLE `productos_proveedores` (  
  `id_producto_proveedor` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `id_proveedor` INT,
  PRIMARY KEY (`id_producto_proveedor`) 
) ENGINE=INNODB CHARSET=utf8mb4;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 21/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(66,'Grupos','Grupos','./grupos-clasificaciones','<i class=\"fa fa-sitemap mt-1\"></i>','80.1.5','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 73, 1, 1, 1, 1);

CREATE TABLE `grupos` (  
  `id_grupo` INT NOT NULL AUTO_INCREMENT,
  `grupo` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_grupo`) 
) ENGINE=INNODB CHARSET=utf8mb4;

ALTER TABLE `clasificaciones_productos`   
	ADD COLUMN `id_grupo` INT NULL AFTER `clasificacion`,
  ADD CONSTRAINT `fk_id_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id_grupo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `productos`   
	ADD COLUMN `web` TINYINT(1) DEFAULT 0  NULL COMMENT '0-NO, 1-Si' AFTER `descripcion`;

ALTER TABLE `productos`
  DROP COLUMN `id_principio_activo`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 22/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `productos_principios` (  
  `id_producto_principio` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `id_principio` INT,
  PRIMARY KEY (`id_producto_principio`) 
) ENGINE=INNODB CHARSET=utf8mb4;

ALTER TABLE `productos_principios`
  ADD UNIQUE INDEX `u_id_principio` (`id_producto`, `id_principio`);

ALTER TABLE `productos_proveedores`
  ADD UNIQUE INDEX `u_id_proveedor` (`id_producto`, `id_proveedor`);

ALTER TABLE `tipos_productos`
  ADD COLUMN `principios_activos` TINYINT (1) DEFAULT 0 NULL COMMENT '0-No, 1-Si -- Define si es obligatoria la carga de principios activos' AFTER `tipo`;

UPDATE `tipos_productos` SET `principios_activos` = '1' WHERE `id_tipo_producto` = '1';

-- ----------------------------------------------------------------------
-- Angel Gimenez - 25/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `funcionarios` (  
  `id_funcionario` INT(11) NOT NULL AUTO_INCREMENT,
  `funcionario` VARCHAR(255) NOT NULL,
  `ci` VARCHAR(15) NOT NULL,
  `salario_real` INT(20) NOT NULL,
  `salario_nominal` INT(20) NOT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `telefono` VARCHAR(15) DEFAULT NULL,
  `celular` VARCHAR(15) DEFAULT NULL,
  `id_ciudad` INT NULL,
  `id_puesto` INT NULL,
  `fecha_baja` DATE DEFAULT NULL,
  `fecha_alta` DATE DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_funcionario`) 
) ENGINE=INNODB CHARSET=utf8mb4;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(NULL,'RRHH','Recursos Humanos','#','<i class=\"fas fa-archive\"></i>','5','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado)
VALUES(74,'Funcionarios','Funcionarios','./funcionarios','<i class=\"fas fa-address-card\"></i>','1','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 74, 1, 1, 1, 1);
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 76, 1, 1, 1, 1);

CREATE TABLE `puestos` (  
  `id_puesto` INT NOT NULL AUTO_INCREMENT,
  `puesto` VARCHAR(255),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_puesto`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(65,'RRHH','Recursos Humanos','#','<i class=\"fas fa-archive\"></i>','80.2','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 78, 1, 1, 1, 1);

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(78,'Puestos','Puestos','./puestos','<i class=\"fa fa-sitemap mt-1\"></i>','80.2.1','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 79, 1, 1, 1, 1)

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 25/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_id_tipo_producto` FOREIGN KEY (`id_tipo_producto`) REFERENCES `tipos_productos` (`id_tipo_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_clasificacion` FOREIGN KEY (`id_clasificacion`) REFERENCES `clasificaciones_productos` (`id_clasificacion_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratorios` (`id_laboratorio`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_presentacion` FOREIGN KEY (`id_presentacion`) REFERENCES `presentaciones` (`id_presentacion`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_unidad_medida` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidades_medidas` (`id_unidad_medida`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_origen` FOREIGN KEY (`id_origen`) REFERENCES `origenes` (`id_origen`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_rubro` FOREIGN KEY (`id_rubro`) REFERENCES `rubros` (`id_rubro`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_pais` FOREIGN KEY (`id_pais`) REFERENCES `paises` (`id_pais`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_moneda` FOREIGN KEY (`id_moneda`) REFERENCES `monedas` (`id_moneda`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `productos_principios`
  ADD CONSTRAINT `fk_id_principio` FOREIGN KEY (`id_principio`) REFERENCES `principios_activos` (`id_principio`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_id_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE ON DELETE CASCADE;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 25/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE funcionarios ADD COLUMN id_estado INT NOT NULL AFTER id_puesto;

CREATE TABLE `estado_civil` (
  `id_estado` INT(11) NOT NULL,
  `descripcion` VARCHAR(25) NOT NULL,
  `estado` TINYINT(1) NOT NULL DEFAULT '1'
) ENGINE=INNODB;

INSERT INTO `estado_civil` (`id_estado`, `descripcion`, `estado`) VALUES
(1, 'SOLTERO/A', 1),
(2, 'CASADO/A', 1),
(3, 'VIUDO/A', 1);

CREATE TABLE dias_habiles (
  `id_dia_habil` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cantidad` INT NOT NULL,
  `mes` INT NOT NULL,
  `anho` YEAR(4) NOT NULL,
  `usuario` VARCHAR,
  `fecha` DATETIME,
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  PRIMARY KEY (`id_dia_habil`) 
) ENGINE=INNODB  CHARSET=utf8mb4;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 26/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  DROP COLUMN `vencimiento`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 26/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios` ADD COLUMN `imagen` VARCHAR(255) NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 27/04/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios` ADD COLUMN `curriculum` TEXT NULL;
ALTER TABLE `funcionarios` ADD COLUMN `antecedente` TEXT NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 27/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,'Cargar Solicitud','Solicitud De Compra','./solicitud-compra','<i class=\"fas fa-edit mt-1\"></i>','3.2','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 79, 1, 1, 1, 1);

CREATE TABLE `solicitudes_compras` (
  `id_solicitud_compra` INT NOT NULL AUTO_INCREMENT,
  `id_proveedor` INT,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Pendiente, 1-Aprobado, 2-Rechazado',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_solicitud_compra`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `solicitudes_compras_productos` (
  `id_solicitud_compra_producto` INT NOT NULL AUTO_INCREMENT,
  `id_solicitud_compra` INT,
  `id_producto` INT,
  `cantidad` INT,
  PRIMARY KEY (`id_solicitud_compra_producto`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 28/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,'Administrar Solicitudes','Administrar Solicitudes','./solicitudes','<i class=\"fas fa-file-alt mt-1\"></i>','3.3','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 80, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 28/04/2022
-- ----------------------------------------------------------------------
CREATE TABLE `vacaciones` (
  `id_vacacion` INT(11) NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  `id_funcionario` INT(11) DEFAULT NULL,
  `ci` VARCHAR(30) NOT NULL,
  `funcionario` VARCHAR(200) DEFAULT NULL,
  `antiguedad` INT(11) DEFAULT (0),
  `total_vacacion` INT(12) DEFAULT (0),
  `utilizado` INT(2) DEFAULT (0),
  `importe` INT(12) DEFAULT (0),
  `anho` INT(11) DEFAULT (0),
  `fecha_desde` DATE NOT NULL,
  `fecha_hasta` DATE NOT NULL,
  `usuario` VARCHAR(45),
  `observacion` VARCHAR(250) DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Activo, 2-procesado, 3-cerrado',
  PRIMARY KEY (`id_vacacion`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(74,'Vacaciones','Vacaciones','./vacaciones','<i class=\"fas fa-umbrella-beach\"></i>','80.2.2','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 81, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/04/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(66,'Descuentos','Descuentos De Productos','./descuentos-productos','<i class=\"fas fa-cart-arrow-down mt-1\"></i>','80.1.6','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 82, 1, 1, 1, 1);

-- Productos sin nombre
DELETE FROM `productos` WHERE `id_producto` = '7623'; 
DELETE FROM `productos` WHERE `id_producto` = '21619'; 
DELETE FROM `productos` WHERE `id_producto` = '7310'; 
DELETE FROM `productos` WHERE `id_producto` = '22906'; 
DELETE FROM `productos` WHERE `id_producto` = '20120'; 

-- ----------------------------------------------------------------------
-- Angel Gimenez - 03/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(78,'Liquidación de Salarios','Liquidación de Salarios','./liquidacion-salarios','<i class="fas fa-address-book mt-1"></i>','5.2','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 83, 1, 1, 1, 1)

CREATE TABLE `descuentos_productos` (
  `id_descuento_producto` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `id_clasificacion` int(11) DEFAULT NULL,
  `porcentaje` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Inactivo',
  `usuario` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id_descuento_producto`),
  KEY `fk_descuentos_productos_productos` (`id_producto`),
  KEY `fk_descuentos_productos_marcas` (`id_marca`),
  KEY `fk_descuentos_productos_clasificacion_productos` (`id_clasificacion`),
  CONSTRAINT `fk_descuentos_productos_clasificacion_productos` FOREIGN KEY (`id_clasificacion`) REFERENCES `clasificaciones_productos` (`id_clasificacion_producto`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_descuentos_productos_marcas` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_descuentos_productos_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 04/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(6,'Sucursales','Sucursales','./sucursales','<i class=\"fas fa-home mt-1\"></i>','90.4','Habilitado'); 
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 84, 1, 1, 1, 1); 

ALTER TABLE `sucursales`
  DROP COLUMN `departamento`,
  CHANGE `ciudad` `id_distrito` INT NULL,
  CHANGE `pais` `id_pais` INT DEFAULT 172 NULL,
  CHANGE `moneda` `id_moneda` INT DEFAULT 1 NULL;

ALTER TABLE `sucursales`
  ADD COLUMN `fecha` DATETIME NULL AFTER `id_moneda`,
  ADD COLUMN `usuario` VARCHAR (45) NULL AFTER `fecha`;

UPDATE sucursales SET id_distrito=NULL, id_pais=172, id_moneda=1, usuario='admin', fecha='0000-00-00' WHERE id_sucursal=1;

ALTER TABLE `sucursales`
  ADD CONSTRAINT `fk_monedas_sucursales` FOREIGN KEY (`id_moneda`) REFERENCES `victoria_db`.`monedas` (`id_moneda`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_paises_sucursales` FOREIGN KEY (`id_pais`) REFERENCES `victoria_db`.`paises` (`id_pais`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_distritos_sucursales` FOREIGN KEY (`id_distrito`) REFERENCES `victoria_db`.`distritos` (`id_distrito`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 04/05/2022
-- ----------------------------------------------------------------------
CREATE TABLE `liquidacion_salarios` (
  `id_liquidacion` INT(11) NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME,
  `funcionario` VARCHAR(200) DEFAULT NULL,
  `id_funcionario` INT NOT NULL,
  `ci` VARCHAR(10) DEFAULT NULL,
  `neto_cobrar` INT DEFAULT NULL,
  `usuario` VARCHAR(45),
  `periodo` VARCHAR(20) NOT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0=generado, 1=cerrado',
  PRIMARY KEY (`id_liquidacion`)
) ENGINE=INNODB;

CREATE TABLE liquidacion_salarios_descuentos (
  `id_liquidacion` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `concepto` VARCHAR(250) NOT NULL,
  `importe` INT NOT NULL,
  `observacion` VARCHAR(250) DEFAULT NULL
) ENGINE=INNODB;

CREATE TABLE liquidacion_salarios_ingresos (
  `id_liquidacion` INT NOT NULL,
  `concepto` VARCHAR(220) DEFAULT NULL,
  `importe` INT NOT NULL,
  `observacion` VARCHAR(250)DEFAULT NULL
) ENGINE=INNODB;


ALTER TABLE `liquidacion_salarios_descuentos`
  ADD CONSTRAINT `fk_liquidacion_salarios_liquidacion_salarios_descuentos` 
  FOREIGN KEY (`id_liquidacion`) REFERENCES `victoria_db`.`liquidacion_salarios` (`id_liquidacion`) ON UPDATE RESTRICT ON DELETE CASCADE;

ALTER TABLE `liquidacion_salarios_ingresos`
  ADD CONSTRAINT `fk_;iquidacion_salarios_liquidacion_salarios_ingresos` FOREIGN KEY (`id_liquidacion`) REFERENCES `victoria_db`.`liquidacion_salarios` (`id_liquidacion`) ON UPDATE RESTRICT ON DELETE CASCADE;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `liquidacion_salarios_ingresos`
  ADD COLUMN `id_liquidacion_ingreso` INT NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (`id_liquidacion_ingreso`);

ALTER TABLE `liquidacion_salarios_descuentos`
  ADD COLUMN `id_liquidacion_descuento` INT NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (`id_liquidacion_descuento`);

ALTER TABLE `liquidacion_salarios`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 0 NULL COMMENT '0=pendiente, 1=aprobado, 2=anulado';

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(78,'Adm. Liquidaciones','Administrar Liquidaciones','./administrar-liquidaciones','<i class=\"fas fa-clipboard-list\"></i>','5.3','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 85, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 05/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,'Orden De Compra','Orden De Compra','./orden-compra','<i class=\"fas fa-edit mt-1\"></i>','3.4','Habilitado');
INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 86, 1, 1, 1, 1);

ALTER TABLE `productos`
  DROP COLUMN `costo`;

ALTER TABLE `productos_proveedores`
  ADD COLUMN `costo` INT NULL AFTER `id_proveedor`;

CREATE TABLE `ordenes_compras` (
  `id_orden_compra` INT NOT NULL AUTO_INCREMENT,
  `id_proveedor` INT,
  `condicion` TINYINT (1) COMMENT '1-Contado, 2-Crédito',
  `estado` TINYINT (1),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_orden_compra`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `ordenes_compras_productos` (
  `id_orden_compra_producto` INT NOT NULL AUTO_INCREMENT,
  `id_orden_compra` INT,
  `id_producto` INT,
  `id_solicitud_compra` INT,
  `codigo` BIGINT (20),
  `producto` VARCHAR (255),
  `costo` INT,
  `cantidad` INT,
  `estado` TINYINT (1) COMMENT '0-Pendiente, 1-Parcial, 2-Total',
  PRIMARY KEY (`id_orden_compra_producto`),
  CONSTRAINT `fk_ordenes_compras_ordenes_compras_productos` FOREIGN KEY (`id_orden_compra`) REFERENCES `victoria_db`.`ordenes_compras` (`id_orden_compra`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_productos_ordenes_compras_productos` FOREIGN KEY (`id_producto`) REFERENCES `victoria_db`.`productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_solicitudes_compras` FOREIGN KEY (`id_solicitud_compra`) REFERENCES `victoria_db`.`solicitudes_compras` (`id_solicitud_compra`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `ordenes_compras`
  ADD COLUMN `total_costo` INT NULL AFTER `condicion`,
  CHANGE `estado` `estado` TINYINT (1) NULL COMMENT '0-Pendiente, 1-Aprobado, 2-Rechazado';

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(53,'Compras','Administrar Ordenes de Compras','./administrar-compras','<i class=\"fas fa-list-ol\"></i>','3.5','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 87, 1, 1, 1, 1);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios`
  ADD UNIQUE INDEX `ci_unico` (`ci`);

ALTER TABLE `puestos`
  ADD COLUMN `comision` TINYINT (1) DEFAULT 0 NULL COMMENT '0-NO 1-SI' AFTER `estado`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 09/05/2022
-- ----------------------------------------------------------------------
DELIMITER $$

CREATE
    /*[DEFINER = { user | CURRENT_USER }]*/
    TRIGGER `asignar_menu` AFTER INSERT
    ON `menus`
    FOR EACH ROW BEGIN
	INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, new.id_menu, 1, 1, 1, 1);
    END$$

DELIMITER ;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 09/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios`
  ADD COLUMN `comision` INT DEFAULT 0 NULL AFTER `salario_nominal`;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(NULL,'Financiero','Financiero','','<i class=\"fas fa-dollar-sign\"></i>','6','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 88, 1, 1, 1, 1);

CREATE TABLE `bancos` (
  `id_banco` INT(11) NOT NULL AUTO_INCREMENT,
  `ruc` VARCHAR(15) NOT NULL,
  `banco` VARCHAR(50) NOT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Inactivo',
  `usuario` VARCHAR(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_banco`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(88,'Bancos','Bancos','./bancos','<i class=\"fas fa-money-check-alt\"></i>','6.1','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 09/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  DROP COLUMN `codigo_proveedor`;

ALTER TABLE `productos_proveedores` 
  ADD COLUMN `codigo` VARCHAR(255) NULL AFTER `id_proveedor`; 

-- ----------------------------------------------------------------------
-- Angel Gimenez - 09/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios`
  ADD COLUMN `id_banco` INT NULL AFTER `id_estado`,
  ADD COLUMN `nro_cuenta` VARCHAR (20) NULL AFTER `id_banco`;

CREATE TABLE `documentos_funcionarios` (
  `id_documento` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_funcionario` INT DEFAULT NULL,
  `fecha` DATETIME DEFAULT NULL,
  `documento` TEXT NULL,
  PRIMARY KEY (`id_documento`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 10/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  ADD COLUMN `precio_fraccionado` INT NULL AFTER `precio`,
  ADD COLUMN `cantidad_fracciones` INT NULL AFTER `precio_fraccionado`,
  ADD COLUMN `descuento_fraccionado` TINYINT (1) NULL AFTER `cantidad_fracciones`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 11/05/2022
-- ----------------------------------------------------------------------
CREATE TABLE `metodos_pagos` (
  `id_metodo_pago` INT NOT NULL AUTO_INCREMENT,
  `metodo_pago` VARCHAR (255),
  `estado` TINYINT (1) DEFAULT 1 COMMENT '0-Inactivo, 1-Activo',
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO metodos_pagos(metodo_pago, estado) VALUES
('Efectivo', 1),
('Tarjeta de Crédito', 1),
('Tarjeta de Débito', 1),
('Cheque', 1),
('Transferencia Bancaria', 1),
('Gift Card', 1),
('Nota de Crédito', 1),
('Vale', 1);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(74,'Descuentos','Descuentos Funcionarios','./descuentos-funcionarios','<i class=\"fas fa-arrow-down mt-1\"></i>','80.2.3','Habilitado');

CREATE TABLE `anticipos` (
  `id_anticipo` INT NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  `id_funcionario` INT DEFAULT NULL,
  `ci` VARCHAR(30) NOT NULL,
  `funcionario` VARCHAR(200)  DEFAULT NULL,
  `monto` INT DEFAULT(0),
  `usuario` VARCHAR(45),
  `observacion` VARCHAR(250) DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Activo, 2-procesado, 3-cerrado',
  PRIMARY KEY (`id_anticipo`) 
) ENGINE=INNODB;

CREATE TABLE `prestamos` (
  `id_prestamo` INT NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  `id_funcionario` INT DEFAULT NULL,
  `ci` VARCHAR(30) NOT NULL,
  `funcionario` VARCHAR(200) DEFAULT NULL,
  `monto` INT DEFAULT(0),
  `cantidad_cuota` INT DEFAULT(0),
  `usuario` VARCHAR(45),
  `observacion` VARCHAR(250) DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Activo, 2-procesado, 3-cerrado',
  PRIMARY KEY (`id_prestamo`) 
) ENGINE=INNODB;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/05/2022
-- ----------------------------------------------------------------------
CREATE TABLE `descuentos_funcionarios` (  
  `id_descuento` INT NOT NULL AUTO_INCREMENT,
  `descuento` VARCHAR(255),
  `id_funcionario` INT DEFAULT NULL,
  `ci` VARCHAR(30) NOT NULL,
  `funcionario` VARCHAR(200)  DEFAULT NULL,
  `monto` INT DEFAULT(0),
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  `observacion` VARCHAR(250) DEFAULT NULL,
  PRIMARY KEY (`id_descuento`) 
) ENGINE=INNODB;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 13/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `ordenes_compras`
  ADD COLUMN `observaciones` TEXT NULL AFTER `condicion`;

ALTER TABLE `ordenes_compras_productos`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 0 NULL COMMENT '0-Pendiente, 1-Parcial, 2-Total';

ALTER TABLE `solicitudes_compras_productos`
  ADD COLUMN `estado` TINYINT (1) DEFAULT 0 COMMENT '0-Pendiente, 1-Parcial, 2-Total' AFTER `cantidad`,
  ADD CONSTRAINT `fk_productos_solicitudes_compras_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `solicitudes_compras`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 1 NULL COMMENT '0-Pendiente, 1-Aprobado, 2-Rechazado, 3-Parcial, 4-Total';

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `anticipos`
  ADD COLUMN `fecha` DATE NOT NULL AFTER `id_anticipo`,
  CHANGE `fecha` `fecha_creacion` DATE NOT NULL;

ALTER TABLE `anticipos`
  CHANGE `fecha_creacion` `fecha_creacion` DATETIME NOT NULL;

ALTER TABLE `descuentos_funcionarios`
  ADD COLUMN `fecha` DATE NOT NULL AFTER `id_descuento`,
  CHANGE `fecha` `fecha_creacion` DATETIME NULL;

ALTER TABLE `prestamos`
  CHANGE `fecha` `fecha_creacion` DATETIME NOT NULL,
  ADD COLUMN `fecha` DATE NOT NULL AFTER `id_prestamo`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 17/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,'Recepción De Compras','Recepción De Compras','./recepcion-compras','<i class=\"fas fa-truck-loading mt-1\"></i>','3.6','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(53,'Administrar Recepciones','Administrar Recepciones De Compras','./recepciones-compras','<i class=\"fas fa-list-ol mt-1\"></i>','3.7','Habilitado');

CREATE TABLE `lotes` (
  `id_lote` INT NOT NULL AUTO_INCREMENT,
  `lote` VARCHAR (150),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_lote`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `lotes`
  ADD UNIQUE INDEX `LOTE_UNICO` (`lote`);

CREATE TABLE `recepciones_compras` (
  `id_recepcion_compra` INT NOT NULL AUTO_INCREMENT,
  `id_proveedor` INT,
  `numero_documento` VARCHAR (45),
  `condicion` TINYINT (1) COMMENT '1-Contado, 2-Crédito',
  `vencimiento` DATE,
  `total_costo` INT,
  `estado` TINYINT (1) COMMENT '1-Recepcionado, 2-Anulado',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_recepcion_compra`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `recepciones_compras_productos` (
  `id_recepcion_compra_producto` INT NOT NULL AUTO_INCREMENT,
  `id_recepcion_compra` INT,
  `id_producto` INT,
  `id_orden_compra` INT,
  `codigo` BIGINT (20),
  `producto` VARCHAR (255),
  `costo` INT,
  `id_lote` INT,
  `vencimiento` DATE,
  `cantidad` INT,
  `canje` TINYINT(1) COMMENT '0-No, 1-Si',
  `vencimiento_canje` DATE,
  PRIMARY KEY (`id_recepcion_compra_producto`),
  CONSTRAINT `fk_recepciones_compras_recepciones_compras_productos` FOREIGN KEY (`id_recepcion_compra`) REFERENCES `recepciones_compras` (`id_recepcion_compra`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_lotes_recepciones_compras_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_recepciones_compras_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_ordenes_compras_recepciones_compras_productos` FOREIGN KEY (`id_orden_compra`) REFERENCES `ordenes_compras` (`id_orden_compra`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 18/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `solicitudes_compras`
  ADD COLUMN `observacion` TEXT NULL AFTER `id_proveedor`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 19/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE liquidacion_salarios ADD COLUMN forma_pago INT NOT NULL;
ALTER TABLE liquidacion_salarios ADD COLUMN nro_cheque VARCHAR(15) NOT NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 19/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `ordenes_compras`
  CHANGE `observaciones` `observacion` TEXT NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 20/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE liquidacion_salarios ADD COLUMN nro_cuenta VARCHAR(15) NOT NULL;

CREATE TABLE `salario_minimo` (
  `id_salario` INT NOT NULL AUTO_INCREMENT,
  `monto` INT NOT NULL,
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY(`id_salario`)
) ENGINE=INNODB ;

ALTER TABLE `funcionarios` ADD COLUMN `cantidad_hijos` INT DEFAULT(0);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(78,'Asistencias','Asistencias','./asistencias','<i class=\"fas fa-user-clock mt-1\"></i>','5.4','Habilitado');

CREATE TABLE `asistencias` (
  `id_asistencia` INT NOT NULL AUTO_INCREMENT,
  `fecha_exportacion` DATETIME,
  `fecha` DATE,
  `id_funcionario` INT NOT NULL,
  `funcionario` INT NOT NULL,
  `dia` VARCHAR(45),.
  `llegada` TIME,
  `salida` TIME,
  `usuario` VARCHAR(45),
  PRIMARY KEY(`id_asistencia`)
) ENGINE=INNODB ;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 24/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `lotes`
  ADD COLUMN `vencimiento` DATE NULL AFTER `lote`,
  ADD COLUMN `canje` TINYINT (1) NULL COMMENT '0- No, 1-Si' AFTER `vencimiento`,
  ADD COLUMN `vencimiento_canje` DATE NULL AFTER `canje`;

ALTER TABLE `recepciones_compras_productos`
  ADD CONSTRAINT `fk_ordenes_compras_recepciones_compras_ordenes_compras_productos` FOREIGN KEY (`id_orden_compra`) REFERENCES `ordenes_compras` (`id_orden_compra`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  DROP FOREIGN KEY `fk_ordenes_compras_recepciones_compras_productos`;

ALTER TABLE `recepciones_compras_productos`
  ADD COLUMN `lote` VARCHAR (255) NULL AFTER `id_lote`;

ALTER TABLE `recepciones_compras`
  ADD COLUMN `observacion` TEXT NULL AFTER `total_costo`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 25/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `recepciones_compras_productos`
  ADD CONSTRAINT `fk_ordenes_compras_recepciones_compras_productos` FOREIGN KEY (`id_orden_compra`) REFERENCES `ordenes_compras` (`id_orden_compra`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  DROP FOREIGN KEY `fk_ordenes_compras_recepciones_compras_ordenes_compras_productos`;

DROP TABLE IF EXISTS stock;
CREATE TABLE `stock` (
  `id_stock` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `id_sucursal` INT,
  `id_lote` INT,
  `stock` INT,
  `fraccionado` INT DEFAULT 0,
  PRIMARY KEY (`id_stock`),
  UNIQUE INDEX `UNIQUE_PRODUCTO_SUCURSAL_LOTE` (
    `id_producto`,
    `id_sucursal`,
    `id_lote`
  )
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `stock`
  ADD CONSTRAINT `fk_productos_stock` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_lotes_stock` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_sucursales_stock` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT;

CREATE TABLE `stock_historial` (
  `id_stock_historial` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `id_sucursal` INT,
  `sucursal` VARCHAR (255),
  `id_lote` INT,
  `lote` VARCHAR (255),
  `stock` INT,
  `operacion` TINYINT COMMENT '1-Compra, 2-Compra Anulada',
  `detalles` VARCHAR (255),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_stock_historial`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `stock_historial`
  ADD CONSTRAINT `fk_productos_stock_historial` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_lotes_stock_historial` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_sucursales_stock_historial` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 27/05/2022
-- ----------------------------------------------------------------------
CREATE TABLE `recepciones_compras_archivos` (
  `id_recepcion_compra_archivo` INT NOT NULL AUTO_INCREMENT,
  `id_recepcion_compra` INT,
  `archivo` VARCHAR (255),
  PRIMARY KEY (`id_recepcion_compra_archivo`),
  CONSTRAINT `fk_recepciones_compras_recepciones_compras_archivos` FOREIGN KEY (`id_recepcion_compra`) REFERENCES `recepciones_compras` (`id_recepcion_compra`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 30/05/2022
-- ----------------------------------------------------------------------
DELIMITER //

CREATE FUNCTION `RedondearPlanilla`(normales_h FLOAT) RETURNS FLOAT(4,2)
BEGIN

 DECLARE normales_h_ FLOAT(4,2);
 DECLARE minutos FLOAT(4,2);
        
CASE 
  WHEN ROUND(DATE_FORMAT(normales_h, '%i'),2) <= 10 THEN SET minutos = 0;
  WHEN ROUND(normales_h,2) > 10 AND ROUND(normales_h,2) <= 20  THEN SET minutos = 25;
  WHEN ROUND(normales_h,2) > 20 AND ROUND(normales_h,2) <= 40  THEN SET minutos = 50;
  
  WHEN ROUND(normales_h,2) > 40 AND ROUND(normales_h,2) <= 50  THEN SET minutos = 75;
  WHEN ROUND(normales_h,2) > 50 AND ROUND(normales_h,2) <= 59  THEN SET minutos = 0;
     
END CASE;        

SET normales_h_ = minutos;
 -- Retornamos
 RETURN normales_h_;
END

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 30/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Stock','Stock','./stock','<i class=\"fas fa-boxes mt-1\"></i>','4.6','Habilitado');

ALTER TABLE `tipos_productos`
  ADD COLUMN `lote_automatico` TINYINT (1) DEFAULT 0 NULL COMMENT '0-No, 1-Si' AFTER `principios_activos`,
  ADD COLUMN `lote_prefijo` VARCHAR (5) NULL AFTER `lote_automatico`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 31/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(88,'Orden de Pagos','Orden de Pagos','./orden-pago','<i class=\"fas fa-cash-register mt-1\"></i>','6.2','Habilitado');

CREATE TABLE `orden_pagos` (
  `id_pago` INT NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME,
  `concepto` VARCHAR(100) NOT NULL,
  `id_banco` INT NOT NULL,
  `id_proveedor` INT(11) DEFAULT NULL,
  `id_funcionario` INT(11) DEFAULT NULL,
  `destino_pago` INT NOT NULL COMMENT '1-proveedor,2-funcionario',
  `forma_pago` INT NOT NULL COMMENT '1-transferencia,2-cheque',
  `nro_cuenta` VARCHAR(30) NOT NULL,
  `nro_cheque` VARCHAR(15) NOT NULL,
  `monto` INT NOT NULL,
  `observacion` VARCHAR(150) NOT NULL,
  `usuario` VARCHAR(45),
  `estado` TINYINT(1) DEFAULT '1' COMMENT '1-aprobado,2-anulado,3-pagado',
  PRIMARY KEY (`id_pago`) 
) ENGINE=INNODB;

CREATE TABLE `orden_pagos_proveedores` (
  `id_pago_proveedor` INT NOT NULL AUTO_INCREMENT,
  `id_pago` INT NOT NULL,
  `id_factura` INT(11) NOT NULL COMMENT 'ID DE LA FACTURA',
  `monto` INT NOT NULL,
  PRIMARY KEY (`id_pago_proveedor`) 
) ENGINE=INNODB;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 31/05/2022
-- ----------------------------------------------------------------------
ALTER TABLE `sucursales`
  ADD COLUMN `deposito` TINYINT (1) DEFAULT 0 NULL COMMENT '0-No, 1-Si' AFTER `fecha`;

ALTER TABLE `recepciones_compras`
  ADD COLUMN `id_sucursal` INT DEFAULT 1 NULL AFTER `observacion`,
  ADD CONSTRAINT `fk_sucursales_recepciones_compras` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 03/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  ADD UNIQUE INDEX `U_PRODUCTO_CODIGO` (`codigo`);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 31/05/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(88,'Administrar Pagos','Administrar Pagos','./administrar-pagos','<i class=\"fas fa-hand-holding-usd mt-1\"></i>','6.3','Habilitado');

ALTER TABLE orden_pagos ADD `id_moneda` INT(11) NOT NULL DEFAULT(1) AFTER monto;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 03/06/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Inventarios','Inventarios','./inventarios','<i class=\"fas fa-tags mt-1\"></i>','4.7','Habilitado');

CREATE TABLE `inventarios` (
  `id_inventario` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `descripcion` VARCHAR (255),
  `fecha_actualizacion` DATETIME,
  `total_precio` INT,
  `cantidad` INT,
  `estado` TINYINT (1) COMMENT '0-Pendiente, 1-Reemplazado',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_inventario`),
  CONSTRAINT `fk_sucursales_inventarios` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `inventarios_productos` (
  `id_inventario_producto` INT NOT NULL AUTO_INCREMENT,
  `id_inventario` INT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `id_lote` INT,
  `lote` VARCHAR (255),
  `cantidad` INT,
  `precio` INT,
  `stock_actual` INT,
  PRIMARY KEY (`id_inventario_producto`),
  CONSTRAINT `fk_inventarios_inventarios_productos` FOREIGN KEY (`id_inventario`) REFERENCES `inventarios` (`id_inventario`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_productos_inventarios_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_lotes_inventarios_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 07/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE funcionarios ADD `id_sucursal` INT NOT NULL DEFAULT(1);
ALTER TABLE funcionarios ADD `referencia` VARCHAR(255);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 07/06/2022
-- ----------------------------------------------------------------------
UPDATE productos SET controlado=0;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 08/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE funcionarios ADD `foto_perfil` TEXT NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 10/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE solicitudes_compras ADD numero VARCHAR(7) AFTER id_solicitud_compra;
ALTER TABLE ordenes_compras ADD numero VARCHAR(7) AFTER id_orden_compra;
ALTER TABLE recepciones_compras ADD numero VARCHAR(7) AFTER id_recepcion_compra;

UPDATE solicitudes_compras SET numero = LPAD(CAST(id_solicitud_compra AS CHAR CHARSET UTF8), 7, '0');
UPDATE ordenes_compras SET numero = LPAD(CAST(id_orden_compra AS CHAR CHARSET UTF8), 7, '0');
UPDATE recepciones_compras SET numero = LPAD(CAST(id_recepcion_compra AS CHAR CHARSET UTF8), 7, '0');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 10/06/2022
-- ----------------------------------------------------------------------
CREATE TABLE `recepciones_compras_vencimientos` (
  `id_recepcion_compra_vencimiento` INT NOT NULL AUTO_INCREMENT,
  `id_recepcion_compra` INT,
  `vencimiento` DATE,
  `monto` INT,
  PRIMARY KEY (
    `id_recepcion_compra_vencimiento`
  ),
  CONSTRAINT `fk_recepciones_compras_recepciones_compras_vencimientos` FOREIGN KEY (`id_recepcion_compra`) REFERENCES `recepciones_compras` (`id_recepcion_compra`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 10/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE orden_pagos ADD numero VARCHAR(7) AFTER id_pago;
UPDATE orden_pagos SET numero = LPAD(CAST(id_pago AS CHAR CHARSET UTF8), 7, '0');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 13/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `recepciones_compras`
  DROP COLUMN `vencimiento`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE orden_pagos_proveedores ADD id_recepcion_compra_vencimiento INT NULL AFTER id_factura;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 14/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE configuracion ADD numero_patronal VARCHAR(20) NULL AFTER moneda;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 14/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `solicitudes_compras_productos`
  ADD COLUMN `usuario_fin` VARCHAR (45) NULL AFTER `cantidad`,
  ADD COLUMN `fecha_fin` DATETIME NULL AFTER `usuario_fin`;

ALTER TABLE `clientes`
  ADD COLUMN `referencia` VARCHAR (350) NULL AFTER `fecha`,
  ADD COLUMN `longitud` VARCHAR (100) NULL AFTER `referencia`,
  ADD COLUMN `latitud` VARCHAR (100) NULL AFTER `longitud`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 15/06/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(74,'Reposos','Reposos','./reposos','<i class=\"fas fa-notes-medical mt-1\"></i>','80.2.4','Habilitado');

CREATE TABLE `reposos` (
  `id_reposo` INT(11) NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME NOT NULL,
  `id_funcionario` INT(11) DEFAULT NULL,
  `ci` VARCHAR(30) NOT NULL,
  `funcionario` VARCHAR(200) DEFAULT NULL,
  `fecha_desde` DATE NOT NULL,
  `fecha_hasta` DATE NOT NULL,
  `documento` TEXT NULL,
  `observacion` VARCHAR(250) DEFAULT NULL,
  `usuario` VARCHAR(45),
  `estado` TINYINT(1) DEFAULT '1' COMMENT '0-Inactivo, 1-Activo',
  PRIMARY KEY (`id_reposo`) 
) ENGINE=INNODB;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 15/06/2022
-- ----------------------------------------------------------------------
CREATE TABLE `orden_pagos_archivos` (
  `id_orden_pago_archivo` INT NOT NULL AUTO_INCREMENT,
  `id_pago` INT,
  `archivo` VARCHAR (255),
  PRIMARY KEY (`id_orden_pago_archivo`),
  CONSTRAINT `fk_orden_pagos_orden_pagos_archivos` FOREIGN KEY (`id_pago`) REFERENCES `orden_pagos` (`id_pago`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 16/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `stock_historial`
  CHANGE `stock` `cantidad` INT (11) NULL,
  CHANGE `operacion` `operacion` VARCHAR (100) NULL COMMENT 'ADD: Entrada, SUB: Salida',
  ADD COLUMN `origen` VARCHAR (100) NULL COMMENT 'REC: Recepción, INV: Inventario' AFTER `operacion`,
  ADD COLUMN `id_origen` INT NULL AFTER `origen`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 17/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE asistencias ADD observacion TEXT NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 20/06/2022
-- ----------------------------------------------------------------------
UPDATE menus SET orden='6' WHERE id_menu = '78';
UPDATE menus SET orden='6.1' WHERE id_menu = '76';
UPDATE menus SET orden='6.2' WHERE id_menu = '83';
UPDATE menus SET orden='6.3' WHERE id_menu = '85';
UPDATE menus SET orden='6.4' WHERE id_menu = '95';
UPDATE menus SET orden='7' WHERE id_menu = '88';
UPDATE menus SET orden='7.1' WHERE id_menu = '89';
UPDATE menus SET orden='7.2' WHERE id_menu = '94';
UPDATE menus SET orden='7.3' WHERE id_menu = '96';

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,'Ventas','Ventas','#','<i class=\\"fas fa-shopping-cart\\"></i>','5','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(100,'Facturación','Facturación','./facturacion','<i class=\\"fas fa-cash-register\\"></i>','5.1','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/06/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(6,'Configuración','Configuración','./configuracion','<i class=\"fas fa-cog mt-1\"></i>','90.05','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `asistencias`
  ADD COLUMN `total_trabajo` INT NULL AFTER `salida`,
  ADD COLUMN `normal` INT NULL AFTER `total_trabajo`,
  ADD COLUMN `extra` INT NULL AFTER `normal`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 24/06/2022
-- ----------------------------------------------------------------------
CREATE TABLE `facturas` (
  `id_factura` BIGINT (20) NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `numero` INT (7) UNSIGNED ZEROFILL,
  `fecha_venta` DATETIME,
  `condicion` TINYINT (1) COMMENT '1-Contado, 2-Crédito',
  `vencimiento` DATE,
  `id_cliente` INT,
  `ruc` VARCHAR (255),
  `razon_social` VARCHAR (255),
  `cantidad` INT,
  `descuento` INT,
  `total_costo` INT,
  `total_venta` INT,
  `exenta` INT,
  `gravada_5` INT,
  `gravada_10` INT,
  `saldo` INT,
  `usuario` VARCHAR (45),
  `estado` TINYINT (1) DEFAULT 1 COMMENT '0-Pendiente, 1-Pagada, 2-Anulada',
  `fecha` DATETIME,
  PRIMARY KEY (`id_factura`),
  CONSTRAINT `fk_sucursales_facturas` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_clientes_facturas` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `facturas_productos` (
  `id_factura_producto` BIGINT (20) NOT NULL AUTO_INCREMENT,
  `id_factura` BIGINT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `id_lote` INT,
  `lote` VARCHAR (255),
  `cantidad` INT,
  `precio` INT,
  `descuento` INT,
  `descuento_porc` INT,
  `total_venta` INT,
  `iva` INT,
  PRIMARY KEY (`id_factura_producto`),
  CONSTRAINT `fk_facturas_facturas_productos` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_facturas_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_lotes_facturas_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 28/06/2022
-- ----------------------------------------------------------------------
CREATE TABLE `cobros` (
  `id_cobro` INT NOT NULL AUTO_INCREMENT,
  `id_factura` BIGINT,
  `id_sucursal` INT,
  `id_metodo_pago` INT,
  `metodo_pago` VARCHAR (255),
  `monto` INT,
  `fecha` DATETIME,
  `usuario` VARCHAR (45),
  `estado` TINYINT DEFAULT 1 COMMENT '0-Anulado, 1-Pagado',
  PRIMARY KEY (`id_cobro`),
  CONSTRAINT `fk_facturas_cobros` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_sucursales_cobros` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_metodos_pagos_cobros` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pagos` (`id_metodo_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

UPDATE menus SET icono='<i class=\"fas fa-shopping-cart\"></i>' WHERE id_menu = '100';
UPDATE menus SET icono='<i class=\"fas fa-cash-register\"></i>' WHERE id_menu = '101';

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `stock_historial`
  ADD COLUMN `fraccionado` INT NULL AFTER `cantidad`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 29/06/2022
-- ----------------------------------------------------------------------
UPDATE menus SET id_menu_padre=55, menu='Ajustes de stock', titulo='Ajustes de stock', url='./inventarios', icono='<i class=\"fas fa-tags mt-1\"></i>', orden='4.7', estado='Habilitado' WHERE id_menu = '97';

CREATE TABLE `ajuste_stock` (
  `id_ajuste` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `descripcion` VARCHAR (255),
  `estado` TINYINT (1) COMMENT '0-Activo, 1-Inactivo',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_ajuste`),
  CONSTRAINT `fk_sucursales_ajustes` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `ajuste_stock_productos` (
  `id_ajuste_producto` INT NOT NULL AUTO_INCREMENT,
  `id_ajuste` INT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `id_lote` INT,
  `lote` VARCHAR (255),
  `tipo_ajuste` INT COMMENT '1-Positivo, 2-Negativo',
  `cantidad` INT,
  `fraccionado` INT,
  PRIMARY KEY (`id_ajuste_producto`),
  CONSTRAINT `fk_ajustes_ajustes_productos` FOREIGN KEY (`id_ajuste`) REFERENCES `ajuste_stock` (`id_ajuste`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_productos_ajuste_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_lotes_ajuste_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `ajuste_stock` ADD COLUMN `numero` VARCHAR(7) NULL AFTER `id_ajuste`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/06/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  ADD COLUMN `fraccionado` TINYINT NULL COMMENT '0-No, 1-Si' AFTER `lote`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 30/06/2022
-- ----------------------------------------------------------------------
UPDATE menus SET id_menu_padre=55, menu='Ajustes de stock', titulo='Ajustes de stock', url='./ajuste-stock', icono='<i class=\"fas fa-tags mt-1\"></i>', orden='4.7', estado='Habilitado' WHERE id_menu = '97';

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 01/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  DROP FOREIGN KEY `fk_facturas_facturas_productos`;
ALTER TABLE `facturas_productos`
  ADD CONSTRAINT `fk_facturas_facturas_productos` FOREIGN KEY (`id_factura`) REFERENCES `victoria_db`.`facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE CASCADE;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 04/07/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(55,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','4.8','Habilitado');

UPDATE menus SET id_menu_padre=102, menu='Stock', titulo='Stock', url='./stock', icono='<i class=\"fas fa-boxes mt-1\"></i>', orden='4.6', estado='Habilitado' WHERE id_menu = '93';

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 04/07/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(100,'Administrar Facturas','Administrar Facturas','./administrar-facturas','<i class=\"fas fa-dollar-sign\"></i>','5.2','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 05/07/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(65,'Ventas','Ventas','#','','80.03','Habilitado');
UPDATE menus SET orden='80.02.03' WHERE id_menu = '90';
UPDATE menus SET orden='80.02.04' WHERE id_menu = '99';
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(104,'Métodos De Pago','Métodos De Pago','./metodos-pagos','<i class=\"fas fa-dollar-sign\"></i>','80.03.01','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(104,'Entidades','Entidades','./entidades','<i class=\"fas fa-building\"></i>','80.03.02','Habilitado');

ALTER TABLE `metodos_pagos`
  ADD COLUMN `usuario` VARCHAR (45) NULL AFTER `estado`,
  ADD COLUMN `fecha` DATETIME NULL AFTER `usuario`;

UPDATE metodos_pagos SET usuario='admin', fecha=NOW();

CREATE TABLE `entidades` (
  `id_entidad` INT NOT NULL AUTO_INCREMENT,
  `ruc` VARCHAR (45),
  `entidad` VARCHAR (255),
  `estado` TINYINT (1) COMMENT '0-Inactivo, 1-Inactivo',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_entidad`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/07/2022
-- ----------------------------------------------------------------------
UPDATE menus SET id_menu_padre=102, menu='Stock', titulo='Stock', url='./stock', icono='<i class=\"fas fa-boxes mt-1\"></i>', orden='4.8.1', estado='Habilitado' WHERE id_menu = '93';

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(102,'Movimientos Stock','Movimientos Stock','./movimientos-stock','<i class=\"fas fa-clipboard-list mt-1\"></i>','4.8.2','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/07/2022
-- ----------------------------------------------------------------------
CREATE TABLE `clientes_tipos` (  
  `id_cliente_tipo` INT NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR(255),
  PRIMARY KEY (`id_cliente_tipo`) 
) ENGINE=INNODB;

INSERT INTO clientes_tipos(tipo) VALUES('Minorista');
INSERT INTO clientes_tipos(tipo) VALUES('Mayorista');

ALTER TABLE clientes ADD COLUMN id_tipo INT NOT NULL DEFAULT(1) AFTER email;
ALTER TABLE `clientes` CHANGE `id_tipo` `id_tipo` INT (11) NOT NULL;

-- ---------------------------------------------------------------------
-- Daniel Insaurralde - 06/07/2022
-- ----------------------------------------------------------------------
CREATE TABLE `cajas` (
  `id_caja` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `fecha_apertura` DATETIME,
  `fecha_cierre` DATETIME,
  `monto_apertura` INT,
  `monto_cierre` INT,
  `observacion` TEXT,
  `usuario` VARCHAR (45),
  `estado` TINYINT (1) COMMENT '0-Cerrada, 1-Abierta',
  PRIMARY KEY (`id_caja`),
  CONSTRAINT `fk_sucursales_cajas` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `cajas_detalles` (
  `id_caja_detalle` INT NOT NULL AUTO_INCREMENT,
  `id_caja` INT,
  `cantidad` INT,
  `valor` INT,
  `total` INT,
  `tipo` TINYINT (1) COMMENT '0-Cierre, 1-Apertura',
  PRIMARY KEY (`id_caja_detalle`),
  CONSTRAINT `cajas_cajas_detalles` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id_caja`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;


-- ----------------------------------------------------------------------
-- Francisco Gómez - 06/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `victoria_db`.`lotes`
  ADD COLUMN `id_proveedor` INT NULL AFTER `id_lote`,
  ADD CONSTRAINT `proveedores_lotes` FOREIGN KEY (`id_proveedor`) REFERENCES `victoria_db`.`proveedores` (`id_proveedor`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 07/07/2022
-- ----------------------------------------------------------------------

ALTER TABLE `facturas`
  ADD COLUMN `receta` INT DEFAULT 0 NULL AFTER `saldo`,
  ADD COLUMN `doctor` VARCHAR (50) NULL AFTER `receta`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 07/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `id_caja` INT NULL AFTER `id_sucursal`,
  ADD CONSTRAINT `fk_cajas_facturas` FOREIGN KEY (`id_caja`) REFERENCES `victoria_db`.`cajas` (`id_caja`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 11/07/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(100,'Administrar Cajas','Administrar Cajas','./administrar-cajas','<i class=\"fas fa-cash-register\"></i>','5.3','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 12/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `ajuste_stock_productos`
  ADD COLUMN `motivo` VARCHAR (50) NULL AFTER `tipo_ajuste`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE cobros ADD COLUMN detalles VARCHAR(50) NULL AFTER monto;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 14/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `delivery` TINYINT (1) DEFAULT 0 NULL COMMENT '0-No, 1-Si' AFTER `doctor`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 14/07/2022
-- ----------------------------------------------------------------------
CREATE TABLE `timbrados` (
  `id_timbrado` int(11) NOT NULL AUTO_INCREMENT,
  `ruc` varchar(45) DEFAULT NULL,
  `timbrado` int(8) NOT NULL COMMENT 'Número de Timbrado',
  `id_sucursal` int(3) NOT NULL COMMENT 'Establecimiento declarado en el RUC',
  `cod_establecimiento` int(3) unsigned zerofill DEFAULT NULL COMMENT 'Corresponde al numero del local y sucursales',
  `punto_de_expedicion` int(3) unsigned zerofill NOT NULL DEFAULT 001 COMMENT 'En un mismo establecimiento, corresponde al numero de caja, tipo de pago, entre otros',
  `inicio_vigencia` date NOT NULL COMMENT 'Inicio de vigencia de la factura',
  `fin_vigencia` date NOT NULL COMMENT 'Fin de vigencia de la factura',
  `desde` int(7) unsigned zerofill NOT NULL COMMENT 'Inicio de numero de factura',
  `hasta` int(7) unsigned zerofill NOT NULL COMMENT 'Fin de numero de factura',
  `estado` enum('ACTIVO','INACTIVO','CADUCADO') NOT NULL,
  `tipo` varchar(45) DEFAULT NULL COMMENT 'Factura, Nota de Remisión, Nota de Crédito',
  `membrete` text DEFAULT NULL,
  PRIMARY KEY (`id_timbrado`)
)

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES('109',6,'Timbrado','Timbrado','./timbrados','<i class="fas fa-window-restore mt-1"></i>','90.06','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 15/07/2022
-- ----------------------------------------------------------------------

ALTER TABLE `timbrados`
  CHANGE `estado` `estado` TINYINT  NOT NULL;

ALTER TABLE `timbrados`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 1 NOT NULL;




-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `metodos_pagos`
  ADD COLUMN `orden` INT NOT NULL AFTER `metodo_pago`;

UPDATE metodos_pagos SET orden = LPAD(CAST(id_metodo_pago AS CHAR CHARSET UTF8), 1, '0');

ALTER TABLE `metodos_pagos` ADD UNIQUE INDEX `orden_existente` (`orden`);

-- ----------------------------------------------------------------------
-- Francisco Gómez - 15/07/2022
-- ----------------------------------------------------------------------

ALTER TABLE `users`
  ENGINE = INNODB;

ALTER TABLE `funcionarios`
  ADD COLUMN `id_usuario` INT (10) UNSIGNED NULL AFTER `id_funcionario`,
  ADD UNIQUE INDEX `id_usuario_unico` (`id_usuario`),
  ADD CONSTRAINT `fk_users_funcionarios` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 18/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `id_delivery` INT NULL AFTER `delivery`;

ALTER TABLE `asistencias`
  CHANGE `funcionario` `funcionario` VARCHAR (250) NOT NULL;

-- ----------------------------------------------------------------------
-- Francisco Gómez - 18/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos_proveedores`
  ADD COLUMN `proveedor_principal` TINYINT (1) DEFAULT 0 NULL COMMENT 'Principal:1  No-Principal:0' AFTER `costo`;

-- ----------------------------------------------------------------------

-- Francisco Gómez - 19/07/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(66,'Descuento Proveedor','Descuento Proveedor','./descuentos-proveedores','<i class=\"fas fa-dollar-sign\"></i>','80.01.12','Habilitado');

-- Sebastian Alvarenga - 20/07/2022
-- ----------------------------------------------------------------------

ALTER TABLE `facturas`
  ADD COLUMN `id_timbrado` INT NULL AFTER `id_caja`;

CREATE TABLE `documentos_facturas` (
  `id_documento` INT (11) NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR (255),
  `id_factura` INT (11),
  `fecha` DATETIME,
  `documento` TEXT,
  PRIMARY KEY (`id_documento`)
);

ALTER TABLE `timbrados`
  ADD COLUMN `id_caja` INT NULL AFTER `id_sucursal`;

-- ----------------------------------------------------------------------
-- Francisco Gómez - 20/07/2022
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS descuentos_proveedores;
CREATE TABLE `descuentos_proveedores` (
  `id_descuento_proveedor` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `id_metodo_pago` INT,
  `id_origen` INT,
  `id_tipo_producto` INT,
  `id_laboratorio` INT,
  `id_marca` INT,
  `id_rubro` INT,
  `porcentaje` INT,
  `estado` TINYINT (1) DEFAULT 0 COMMENT 'Activo:1  Inactivo:0',
  `usuario` VARCHAR (50),
  `fecha` DATETIME,
  PRIMARY KEY (`id_descuento_proveedor`),
  CONSTRAINT `fk_sucursales_descuentos_proveedores` FOREIGN KEY (`id_sucursal`) REFERENCES `victoria_db`.`sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_metodos_pagos_descuentos_proveedores` FOREIGN KEY (`id_metodo_pago`) REFERENCES `victoria_db`.`metodos_pagos` (`id_metodo_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_origenes_descuentos_proveedores` FOREIGN KEY (`id_origen`) REFERENCES `victoria_db`.`origenes` (`id_origen`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_tipos_productos_descuentos_proveedores` FOREIGN KEY (`id_tipo_producto`) REFERENCES `victoria_db`.`tipos_productos` (`id_tipo_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_laboratorios_descuentos_proveedores` FOREIGN KEY (`id_laboratorio`) REFERENCES `victoria_db`.`laboratorios` (`id_laboratorio`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_marcas_descuentos_proveedores` FOREIGN KEY (`id_marca`) REFERENCES `victoria_db`.`marcas` (`id_marca`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_rubros_descuentos_proveedores` FOREIGN KEY (`id_rubro`) REFERENCES `victoria_db`.`rubros` (`id_rubro`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;


ALTER TABLE `descuentos_proveedores`
  ADD COLUMN `id_proveedor` INT NULL AFTER `id_sucursal`,
  ADD CONSTRAINT `fk_proveedores_descuentos_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE RESTRICT ON DELETE RESTRICT;

DROP TABLE IF EXISTS descuentos_proveedores_productos;
CREATE TABLE `descuentos_proveedores_productos` (
  `id_descuentos_proveedores_productos` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `id_producto` INT,
  `id_proveedor` INT,
  `porcentaje` INT,
  `fecha` DATETIME,
  PRIMARY KEY (
    `id_descuentos_proveedores_productos`
  ),
  CONSTRAINT `fk_sucursales_descuentos_proveedores_productos` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_descuentos_proveedores_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_proveedores_descuentos_proveedores_productos` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas`
  CHANGE `id_caja` `id_caja_horario` INT (11) NOT NULL AUTO_INCREMENT;

RENAME TABLE `cajas` TO `cajas_horarios`;

ALTER TABLE `cajas_detalles`
  CHANGE `id_caja` `id_caja_horario` INT (11) NULL;

ALTER TABLE `cajas_detalles`
  ADD CONSTRAINT `cajas_horarios_cajas_detalles` FOREIGN KEY (`id_caja_horario`) REFERENCES `cajas_horarios` (`id_caja_horario`) ON DELETE CASCADE,
  DROP FOREIGN KEY `cajas_cajas_detalles`;

CREATE TABLE `cajas` (  
  `id_caja` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(10),
  `id_sucursal` INT,
  `id_usuario` INT,
  `estado` TINYINT(1) COMMENT '0-Inactivo, 1-Activo',
  `observacion` VARCHAR(255),
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_caja`) 
) ENGINE=INNODB;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(100,'Cajas','Cajas','./cajas','<i class=\"fas fa-cash-register\"></i>','5.4','Habilitado');

INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar) VALUES(1, 111, 1, 1, 1, 1);

ALTER TABLE `victoria_db`.`facturas`
  CHANGE `id_caja` `id_caja_horario` INT (11) NULL;

ALTER TABLE `facturas`
  CHANGE `id_caja` `id_caja_horario` INT (11) NULL;



-- Sebastian Alvarenga - 21/07/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(111,66,'Descuentos','Descuentos','./descuentos-pagos','<i class="fas fa-cart-plus"></i>','80.01.13','Habilitado');

CREATE TABLE `descuentos_pagos` (
  `id_descuento_pago` INT NOT NULL AUTO_INCREMENT,
  `id_metodo_pago` INT,
  `id_entidad` INT,
  `descripcion` VARCHAR (80),
  `porcentaje` INT (11),
  `fecha_inicio` DATE,
  `fecha_fin` DATE,
  `estado` TINYINT (1),
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_descuento_pago`)
);

ALTER TABLE `descuentos_pagos`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 1 NULL;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 22/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos`
  CHANGE `fecha_inicio` `fecha_inicio` DATETIME NULL,
  CHANGE `fecha_fin` `fecha_fin` DATETIME NULL;

  ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_timbrados_facturas` FOREIGN KEY (`id_timbrado`) REFERENCES `victoria_db`.`timbrados` (`id_timbrado`) ON UPDATE RESTRICT ON DELETE RESTRICT;
  ADD CONSTRAINT `fk_funcionarios_facturas` FOREIGN KEY (`id_delivery`) REFERENCES `victoria_db`.`funcionarios` (`id_funcionario`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `descuentos_pagos`
  ADD CONSTRAINT `fk_metodos_pagos_descuentos_pagos` FOREIGN KEY (`id_metodo_pago`) REFERENCES `victoria_db`.`metodos_pagos` (`id_metodo_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_entidades_descuentos_pagos` FOREIGN KEY (`id_entidad`) REFERENCES `victoria_db`.`entidades` (`id_entidad`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `timbrados`
  ADD CONSTRAINT `fk_sucursales_timbrados` FOREIGN KEY (`id_sucursal`) REFERENCES `victoria_db`.`sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_cajas_timbrados` FOREIGN KEY (`id_caja`) REFERENCES `victoria_db`.`cajas` (`id_caja`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `documentos_facturas`
  CHANGE `id_factura` `id_factura` BIGINT (20) NULL;

ALTER TABLE `documentos_facturas`
  ADD CONSTRAINT `fk_documentos_facturas_facturas` FOREIGN KEY (`id_factura`) REFERENCES `victoria_db`.`facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE RESTRICT;



-- ----------------------------------------------------------------------
-- Angel Gimenez - 22/07/2022
-- ----------------------------------------------------------------------
CREATE TABLE `cajas_usuarios` (  
  `id_caja_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_caja` INT,
  `id_usuario` INT,
  `usuario` VARCHAR(45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_caja_usuario`) 
) ENGINE=INNODB;

ALTER TABLE `cajas` DROP COLUMN `id_usuario`;
ALTER TABLE `cajas_horarios` ADD COLUMN `id_caja` INT NULL AFTER `id_sucursal`;


ALTER TABLE `cajas_horarios`
  ADD CONSTRAINT `fk_cajas_cajas_horarios` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id_caja`);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 25/07/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas`
  ADD COLUMN `efectivo_inicial` INT DEFAULT 0 NULL AFTER `fecha`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 26/07/2022
-- ----------------------------------------------------------------------

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(88,'Informes','Informes','#','<i class="fas fa-file-alt mt-1"></i>','7.4','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(114,'Libro de compra','Libro de compra','./libro-compra','<i class="fas fa-file-invoice-dollar mt-1"></i>','7.4.1','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(114,'Libro de ventas','Libro de ventas','./libro-venta','<i class="fas fa-file-invoice-dollar mt-1"></i>','7.4.2','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 28/07/2022
-- ----------------------------------------------------------------------

INSERT INTO `roles` (`id_rol`, `rol`, `estado`)
VALUES(3, 'CONTADOR', 'Activo');

INSERT INTO`roles_menu` (`id_rol`,`id_menu`,`acceso`,`insertar`,`editar`,`eliminar`)
VALUES(3,117,1,1,1,1);

INSERT INTO`roles_menu` (`id_rol`,`id_menu`,`acceso`,`insertar`,`editar`,`eliminar`)
VALUES(3,118,1,1,1,1);

INSERT INTO`roles_menu` (`id_rol`,`id_menu`,`acceso`,`insertar`,`editar``eliminar`)
VALUES(3,119,1,1,1,1);

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(123,104,'Doctores','Doctores','./doctores','<i class="fas fa-user-md mt-1"></i>','80.03.03','Habilitado');

CREATE TABLE `doctores` (
  `id_doctor` INT NOT NULL AUTO_INCREMENT,
  `id_especialidad` INT,
  `nombre_apellido` VARCHAR (50),
  `registro_nro` VARCHAR (50),
  `estado` TINYINT DEFAULT 1,
  PRIMARY KEY (`id_doctor`)
);

CREATE TABLE `especialidades_doctores` (
  `id_especialidad` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR (50),
  `estado` TINYINT DEFAULT 1,
  PRIMARY KEY (`id_especialidad`)
);

ALTER TABLE `doctores`
  ADD CONSTRAINT `fk_doctores_especialidades_doctores` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades_doctores` (`id_especialidad`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 29/07/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(104,'Especialidades','Especialidades','./especialidades','<i class="fas fa-hospital-alt mt-1"></i>','80.03.04','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/07/2022
-- ----------------------------------------------------------------------
CREATE TABLE `tipos_puestos` (
  `id_tipo_puesto` INT NOT NULL AUTO_INCREMENT,
  `tipo_puesto` VARCHAR (255),
  PRIMARY KEY (`id_tipo_puesto`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `puestos`
  ADD COLUMN `id_tipo_puesto` INT NULL AFTER `comision`,
  ADD CONSTRAINT `fk_tipos_puestos_puestos` FOREIGN KEY (`id_tipo_puesto`) REFERENCES `tipos_puestos` (`id_tipo_puesto`) ON UPDATE RESTRICT ON DELETE RESTRICT;


INSERT INTO `tipos_puestos` (`id_tipo_puesto`, `tipo_puesto`) VALUES
(1, 'Cajero'),
(2, 'Delivery');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 01/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  DROP COLUMN `doctor`,
  ADD COLUMN `id_doctor` INT NULL AFTER `receta`,
  ADD CONSTRAINT `fk_doctores_facturas` FOREIGN KEY (`id_doctor`) REFERENCES `doctores` (`id_doctor`) ON UPDATE RESTRICT ON DELETE RESTRICT;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(74,'Tipos De Puesto','Tipos De Puesto','./tipos-puestos','<i class=\"fas fa-sitemap\"></i>','80.2.5','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 02/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `tipos_comprobantes` (
  `id_tipo_comprobante` INT NOT NULL AUTO_INCREMENT,
  `codigo` INT (11) NOT NULL,
  `nombre_comprobante` VARCHAR (50) NOT NULL,
  PRIMARY KEY (`id_tipo_comprobante`)
);
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 03/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `tipos_comprobantes`
  ADD COLUMN `estado` TINYINT (1) DEFAULT 1 NULL AFTER `nombre_comprobante`;

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(127,'Tipos de Comprobantes','Tipos de Comprobantes','./tipo-comprobante','<i class="fas fa-file mt-1"></i>','80.3.1','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(65,'Compras','Compras','#',' ','80.3','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 03/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `asistencias`
  ADD COLUMN `ci` VARCHAR (20) NULL AFTER `id_funcionario`;

ALTER TABLE `cajas`
  ADD COLUMN `tope_efectivo` INT DEFAULT 0 NULL AFTER `efectivo_inicial`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 03/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas_horarios`
  ADD COLUMN `total_venta` INT (11) NULL AFTER `monto_apertura`,
  ADD COLUMN `diferencia` INT (11) NULL AFTER `monto_cierre`;

ALTER TABLE `cajas_usuarios`
  ADD COLUMN `estado` INT (2) DEFAULT 0 NULL COMMENT '0=Habilitado, 1=Deshabilitado' AFTER `usuario`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 03/08/2022
-- ----------------------------------------------------------------------

CREATE TABLE `gastos` (
  `id_gasto` INT NOT NULL AUTO_INCREMENT,
  `id_tipo_gasto` INT,
  `id_sucursal` INT,
  `nro_gasto` INT,
  `timbrado` VARCHAR (50),
  `fecha_emision` DATE,
  `ruc` VARCHAR (20),
  `razon_social` VARCHAR (50),
  `condicion` TINYINT,
  `fecha_vencimiento` DATE,
  `documento` VARCHAR (50),
  `concepto` VARCHAR (50),
  `monto` INT,
  `iva` TINYINT,
  `iva_10` INT,
  `iva_5` INT,
  `extenta` INT,
  `observacion` VARCHAR (250),
  `estado` TINYINT,
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_gasto`)
);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 04/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `cajas_extracciones` (
  `id_extraccion` INT (11) NOT NULL AUTO_INCREMENT,
  `id_caja_horario` INT (11),
  `id_usuario` INT (11),
  `monto_extraccion` INT (10),
  `tota_venta` INT (10),
  `total_caja` INT (10),
  `total_venta_efectivo` INT (10),
  `total_caja_efectivo` INT (10),
  `monto_sin_extraer` INT (10),
  `fecha` DATETIME,
  `observacion` TEXT,
  PRIMARY KEY (`id_extraccion`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `cajas_extracciones`
  CHANGE `id_usuario` `usuario` VARCHAR (45) NULL;

ALTER TABLE `cajas_extracciones`
  CHANGE `tota_venta` `total_venta` INT (10) NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 04/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  ADD COLUMN `fuera_de_plaza` TINYINT (1) DEFAULT 0 NULL COMMENT '0:No; 1:Si' AFTER `indicaciones`;

-- Sebastian Alvarenga - 04/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `gastos`
  CHANGE `estado` `estado` TINYINT (4) DEFAULT 1 NULL;

ALTER TABLE `gastos`
  ADD COLUMN `id_tipo_comprobante` INT NULL AFTER `id_sucursal`;

CREATE TABLE `tipos_gastos` (
  `id_tipo_gasto` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR (50),
  `estado` TINYINT DEFAULT 1,
  PRIMARY KEY (`id_tipo_gasto`)
);

ALTER TABLE `gastos`
  ADD COLUMN `imputa_iva` VARCHAR (1) NULL AFTER `extenta`,
  ADD COLUMN `imputa_ire` VARCHAR (1) NULL AFTER `imputa_iva`,
  ADD COLUMN `imputa_irp` VARCHAR (1) NULL AFTER `imputa_ire`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 05/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Solicitud a Depósito','Solicitud a Depósito','./solicitud-deposito','<i class=\"fas fa-edit\"></i>','4.6','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Administrar Solicitudes','Administrar Solicitudes a Depósito','./solicitudes-depositos','<i class=\"fas fa-list\"></i>','4.6','Habilitado');

CREATE TABLE `solicitudes_depositos` (
  `id_solicitud_deposito` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `numero` VARCHAR (255),
  `observacion` TEXT,
  `estado` TINYINT (1) DEFAULT 0 COMMENT '0-Pendiente, 1-Aprobado, 2-Rechazado, 3-Parcial, 4-Total',
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_solicitud_deposito`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `solicitudes_depositos_productos` (
  `id_solicitud_deposito_producto` INT NOT NULL AUTO_INCREMENT,
  `id_solicitud_deposito` INT,
  `id_producto` INT,
  `cantidad` INT,
  `estado` TINYINT (1) DEFAULT 0 COMMENT '0-Pendiente, 1-Parcial, 2-Total',
  PRIMARY KEY (`id_solicitud_deposito_producto`),
  CONSTRAINT `fk_productos_solicitudes_depositos_productos` FOREIGN KEY (`id_solicitud_deposito`) REFERENCES `solicitudes_depositos` (`id_solicitud_deposito`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(108,'Extracciones','Extracciones','./extracciones','<i class=\"fas fa-angle-double-down mt-1\"></i>','5.5','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 03/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `sub_tipos_gastos` (
  `id_sub_tipo_gasto` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR (50),
  PRIMARY KEY (`id_sub_tipo_gasto`)
);

ALTER TABLE `tipos_gastos`
  ADD COLUMN `id_sub_tipo_gasto` INT NULL AFTER `id_tipo_gasto`;

ALTER TABLE `tipos_gastos`
  ADD CONSTRAINT `fk_tipos_gastos_sub_tipos_gastos` FOREIGN KEY (`id_sub_tipo_gasto`) REFERENCES `victoria_db`.`sub_tipos_gastos` (`id_sub_tipo_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `gastos`
  ADD CONSTRAINT `fk_gastos_tipos_gasto` FOREIGN KEY (`id_tipo_gasto`) REFERENCES `victoria_db`.`tipos_gastos` (`id_tipo_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_gastos_sucursales` FOREIGN KEY (`id_sucursal`) REFERENCES `victoria_db`.`sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_gastos_tipos_comprobantes` FOREIGN KEY (`id_tipo_comprobante`) REFERENCES `victoria_db`.`tipos_comprobantes` (`id_tipo_comprobante`) ON UPDATE RESTRICT ON DELETE RESTRICT;

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES (94,'Gastos','Gastos','./gastos','<i class="fas fa-clipboard-list mt-1"></i>','6.4','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES (65,'Gastos','Gastos','#','','80.4','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES (134,'Gastos','Gastos','./gasto','<i class="fas fa-clipboard-list mt-1"></i>','80.4.1','Habilitado');

INSERT INTO `sub_tipos_gastos` (`id_sub_tipo_gasto`, `nombre`)
VALUES (1, 'RECEPCION');

INSERT INTO `sub_tipos_gastos` (`id_sub_tipo_gasto`, `nombre`)
VALUES (2, 'SALIDA');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES (134,'Tipos de Gastos','Tipos de Gastos','./tipo-gasto','<i class="fab fa-elementor mt-1"></i>','80.4.2','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 08/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `gastos`
  CHANGE `nro_gasto` `nro_gasto` VARCHAR (7) NULL;

INSERT INTO `tipos_gastos` (`id_tipo_gasto`,`id_sub_tipo_gasto`,`nombre`)
VALUES(1,1,'PRODUCTOS');


ALTER TABLE `gastos`
  ADD COLUMN `id_sub_tipo_gasto` INT (11) NULL AFTER `id_tipo_comprobante`,
  ADD CONSTRAINT `fk_gastos_sub_tipo_gasto` FOREIGN KEY (`id_sub_tipo_gasto`) REFERENCES `victoria_db`.`sub_tipos_gastos` (`id_sub_tipo_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `gastos`
  ADD COLUMN `gravada_10` INT NULL AFTER `iva`,
  ADD COLUMN `gravada_5` INT NULL AFTER `gravada_10`;

ALTER TABLE `gastos`
  ADD COLUMN `no_imputa` VARCHAR (1) NULL AFTER `imputa_irp`;

INSERT INTO `tipos_comprobantes` (`codigo`,`nombre_comprobante`)
VALUES('101','AUTOFACTURA'),
      ('102','BOLETA DE TRANSPORTE PUBLLICO DE PASAJERO'),
      ('103', 'BOLETA DE VENTA'),
      ('104', 'BOLETA RESIMPLE'),
      ('105', 'BOLETOS DE LOTERIAS O JUEGOS DE AZAR'),
      ('106', 'BOLETO O TICKET DE TRANSPORTE AEREO'),
      ('107', 'DESPACHO DE IMPORTANCIA'),
      ('108', 'ENTRADAS A ESPECTACULOS PUBLICOS'),
      ('109', 'FACTURA'),
      ('110', 'NOTA DE CREDITO'),
      ('111', 'NOTA DE DEBITO'),
      ('112', 'TICKET MAQUINA REGISTRADORA'),
      ('201', 'COMPROBANTE DE EGRESO POR COMPRAS A CREDITO'),
      ('202', 'COMPROBANTE DEL EXTERIOR LEGALIZADO'),
      ('203', 'COMPROBANTE DE INGRESO POR VENTAS A CREDITO'),
      ('204', 'COMPROBANTE DE INGRESOS ENTIDADES PUBLICAS, RELIGIOSAS O DE BENEFICIO PUBLICO'),
      ('205', 'EXTRACTO DE CUENTA - BILLATAJE ELECTRONICO'),
      ('206', 'EXTRACTO DE CUENTA DE IPS'),
      ('207', 'EXTRACTO DE CEUNTA TC/TD'),
      ('208', 'LIQUIDACION DE SALARIO'),
      ('209', 'OTROS COMPROBANTES DE EGRESO'),
      ('210', 'OTROS COMPROBANTES DE INGRESOS'),
      ('211', 'TRANSFERENCIA O GIROS BANCARIOS/ BOLETA DE DEPOSITO')
-- ----------------------------------------------------------------------
-- Angel Gimenez - 08/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(104,'Comisiones','Comisiones','./comisiones','<i class=\"fas fa-hand-holding-usd mt-1\"></i>','80.03.05','Habilitado');

ALTER TABLE `productos`
  ADD COLUMN `comision` INT DEFAULT 0 NULL AFTER `descuento_fraccionado`;

---Funcion para traer el nombre del mes de una fecha en español----
DELIMITER $$
 CREATE FUNCTION mes(_d DATE, _locale VARCHAR(5)) RETURNS VARCHAR(22) CHARSET utf8
     DETERMINISTIC
 BEGIN
     SET @@lc_time_names = _locale;
     RETURN DATE_FORMAT(_d, '%M');
 END$$
 DELIMITER ;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 08/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `gastos`
  ADD COLUMN `nro_comprobante_venta_asoc` VARCHAR (50) NULL AFTER `no_imputa`,
  ADD COLUMN `timb_compro_venta_asoc` VARCHAR (50) NULL AFTER `nro_comprobante_venta_asoc`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 10/08/2022
-- ----------------------------------------------------------------------
UPDATE menus SET orden='4.05' WHERE id_menu = '71';
UPDATE menus SET orden='4.06' WHERE id_menu = '131';
UPDATE menus SET orden='4.07' WHERE id_menu = '133';
UPDATE menus SET orden='4.10' WHERE id_menu = '97';
UPDATE menus SET orden='4.11' WHERE id_menu = '102';
UPDATE menus SET orden='4.11.1' WHERE id_menu = '93';
UPDATE menus SET orden='4.11.2' WHERE id_menu = '107';

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Nota De Remisión','Nota De Remisión','./nota-remision','<i class=\"fas fa-edit\"></i>','4.08','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(55,'Notas De Remisión','Administrar Notas De Remisión','./administrar-notas-remision','<i class=\"fas fa-list\"></i>','4.09','Habilitado');

CREATE TABLE `notas_remision_motivos` (
  `id_nota_remision_motivo` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR (255),
  `nombre_corto` VARCHAR (100),
  PRIMARY KEY (`id_nota_remision_motivo`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO `notas_remision_motivos` (`descripcion`,`nombre_corto`) VALUES
('Traslado entre locales de la misma empresa', 'Traslado'),
('Venta', 'Venta'),
('Consignación', 'Consignación'),
('Devolución', 'Devolución'),
('Traslado de bienes para reparación', 'Reparación'),
('Exhibición, demostración', 'Exhibición, demostración'),
('Participación en ferias', 'Ferias'),
('Donación', 'Donación'),
('Canje', 'Canje');

CREATE TABLE `notas_remision` (
  `id_nota_remision` INT(11) NOT NULL AUTO_INCREMENT,
  `id_timbrado` INT(11) DEFAULT NULL,
  `id_sucursal_origen` INT(11) DEFAULT NULL,
  `id_sucursal_destino` INT(11) DEFAULT NULL,
  `numero` INT(7) UNSIGNED ZEROFILL DEFAULT NULL,
  `fecha_emision` DATETIME DEFAULT NULL,
  `ruc_rtte` VARCHAR(45) DEFAULT NULL,
  `razon_social_rtte` VARCHAR(255) DEFAULT NULL,
  `domicilio_rtte` VARCHAR(255) DEFAULT NULL,
  `ruc_destino` VARCHAR(45) DEFAULT NULL,
  `razon_social_destino` VARCHAR(255) DEFAULT NULL,
  `domicilio_destino` VARCHAR(255) DEFAULT NULL,
  `id_nota_remision_motivo` INT(11) DEFAULT NULL,
  `motivo` VARCHAR(255) DEFAULT NULL,
  `comprobante_venta` VARCHAR(45) DEFAULT NULL,
  `comprobante_nro` VARCHAR(45) DEFAULT NULL,
  `comprobante_timbrado` VARCHAR(45) DEFAULT NULL,
  `fecha_expedicion` DATE DEFAULT NULL,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin` DATE DEFAULT NULL,
  `km` VARCHAR(45) DEFAULT NULL,
  `marca_vehiculo` VARCHAR(150) DEFAULT NULL,
  `rua` VARCHAR(15) DEFAULT NULL,
  `rua_remolque` VARCHAR(45) DEFAULT NULL,
  `ruc_chofer` VARCHAR(45) DEFAULT NULL,
  `razon_social_chofer` VARCHAR(255) DEFAULT NULL,
  `domicilio_chofer` VARCHAR(255) DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT NULL COMMENT '1: En Tránsito; 2:Anulada; 3:Recibida',
  `observacion` TEXT DEFAULT NULL,
  `usuario` VARCHAR(45) DEFAULT NULL,
  `usuario_recepcion` VARCHAR(45) DEFAULT NULL,
  `fecha_actualizacion` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_nota_remision`),
  KEY `fk_notas_remision_motivos_notas_remision` (`id_nota_remision_motivo`),
  KEY `fk_sucursales_notas_remision_destino` (`id_sucursal_destino`),
  KEY `fk_sucursales_notas_remision_origen` (`id_sucursal_origen`),
  KEY `fk_timbrados_notas_remision` (`id_timbrado`),
  CONSTRAINT `fk_notas_remision_motivos_notas_remision` FOREIGN KEY (`id_nota_remision_motivo`) REFERENCES `notas_remision_motivos` (`id_nota_remision_motivo`),
  CONSTRAINT `fk_sucursales_notas_remision_destino` FOREIGN KEY (`id_sucursal_destino`) REFERENCES `sucursales` (`id_sucursal`),
  CONSTRAINT `fk_sucursales_notas_remision_origen` FOREIGN KEY (`id_sucursal_origen`) REFERENCES `sucursales` (`id_sucursal`),
  CONSTRAINT `fk_timbrados_notas_remision` FOREIGN KEY (`id_timbrado`) REFERENCES `timbrados` (`id_timbrado`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notas_remision_productos` (
  `id_nota_remision_producto` INT(11) NOT NULL AUTO_INCREMENT,
  `id_nota_remision` INT(11) DEFAULT NULL,
  `id_producto` INT(11) DEFAULT NULL,
  `codigo` BIGINT(20) DEFAULT NULL,
  `producto` VARCHAR(255) DEFAULT NULL,
  `id_lote` INT(11) DEFAULT NULL,
  `lote` VARCHAR(255) DEFAULT NULL,
  `cantidad` INT(11) DEFAULT NULL,
  `cantidad_recibida` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_nota_remision_producto`),
  KEY `fk_lotes_notas_remision_productos` (`id_lote`),
  KEY `fk_notas_remision_notas_remision_productos` (`id_nota_remision`),
  KEY `fk_productos_notas_remision_productos` (`id_producto`),
  CONSTRAINT `fk_lotes_notas_remision_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`),
  CONSTRAINT `fk_notas_remision_notas_remision_productos` FOREIGN KEY (`id_nota_remision`) REFERENCES `notas_remision` (`id_nota_remision`) ON DELETE CASCADE,
  CONSTRAINT `fk_productos_notas_remision_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `timbrados`
  CHANGE `tipo` `tipo` VARCHAR (45) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '0:Factura; 1:Nota de Crédito; 2:Nota de Remisión';

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 11/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `gastos`
  CHANGE `concepto` `concepto` VARCHAR (200) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 11/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `notas_remision`
  ADD COLUMN `fecha` DATETIME NULL AFTER `usuario`;
ALTER TABLE `notas_remision`
  CHANGE `estado` `estado` TINYINT (1) NULL COMMENT '1: En Tránsito; 2:Anulada; 3:Finalizado';

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas`
  ADD COLUMN `token` VARCHAR (250) NULL AFTER `tope_efectivo`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 16/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `notas_remision_productos`
  ADD COLUMN `observacion` VARCHAR (255) NULL AFTER `cantidad_recibida`;

UPDATE menus SET menu='Remisiones', titulo='Remisiones' WHERE id_menu = '139';

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 17/08/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(117,'Estado Proveedores','Estado Proveedores','./estado-proveedores','<i class="fas fa-file-invoice-dollar mt-1"></i>','7.4.3','Habilitado');

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(117,'Estado Clientes','Estado Clientes','./estado-clientes','<i class="fas fa-file-invoice-dollar mt-1"></i>','7.4.4','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 18/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `recibos` (
  `id_recibo` INT,
  `id_cliente` INT,
  `id_metodo_pago` INT,
  `numero` INT (7),
  `detalle_pago` VARCHAR (100),
  `total_pago` INT,
  `concepto` VARCHAR (100),
  `fecha_pago` DATE,
  `usuario` VARCHAR (50),
  `estado` TINYINT
);

ALTER TABLE `recibos`
  ADD CONSTRAINT `fk_recibos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `victoria_db`.`clientes` (`id_cliente`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_recibos_metodos_pagos` FOREIGN KEY (`id_metodo_pago`) REFERENCES `victoria_db`.`metodos_pagos` (`id_metodo_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `cobros`
  ADD COLUMN `id_recibo` INT NULL AFTER `id_metodo_pago`,
  ADD CONSTRAINT `fk_recibos_cobros` FOREIGN KEY (`id_recibo`) REFERENCES `victoria_db`.`recibos` (`id_cliente`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios`
  ADD COLUMN `aporte` INT DEFAULT 0 NULL COMMENT '0=ninguno, 1=ips, 2=factura' AFTER `foto_perfil`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 19/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos_proveedores`
  ADD COLUMN `costo_ultimo` INT (11) NULL AFTER `costo`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 19/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `recibos`
  CHANGE `id_recibo` `id_recibo` INT (11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (`id_recibo`);

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 22/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `recibos`
CHANGE `estado` `estado` TINYINT (4) DEFAULT 1 NULL;

ALTER TABLE `cobros`
ADD CONSTRAINT `fk_recibos_cobros` FOREIGN KEY (`id_recibo`) REFERENCES `victoria_db`.`recibos` (`id_recibo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 22/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(108,'Productos Controlados Vendidos','Productos Controlados Vendidos','./productos-controlados','<i class=\"fas fa-list\"></i>','5.6','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(108,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','5.7','Habilitado');

UPDATE menus SET id_menu_padre=143, menu='Controlados Vendidos', titulo='Productos Controlados Vendidos', url='./productos-controlados', icono='<i class=\"fas fa-list\"></i>', orden='5.7.1', estado='Habilitado' WHERE id_menu = '142'

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 22/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `stock_niveles` (
  `id_stock_nivel` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `id_producto` INT,
  `minimo` INT,
  `maximo` INT,
  PRIMARY KEY (`id_stock_nivel`),
  CONSTRAINT `fk_productos_stock_niveles` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_sucursales_stock_niveles` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(143,'Reporte de Cajas','Reporte de Cajas','./reportes-cajas','<i class=\"fas fa-list mt-1\"></i>','5.7.2','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 23/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cobros`
  ADD COLUMN `id_descuento_metodo_pago` INT NULL AFTER `id_metodo_pago`,
  ADD CONSTRAINT `fk_descuento_pagos_cobros` FOREIGN KEY (`id_descuento_metodo_pago`) REFERENCES `victoria_db`.`descuentos_pagos` (`id_descuento_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT;
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 24/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `metodos_pagos`
ADD COLUMN `entidad` INT (1) NULL DEFAULT 0 COMMENT '0- No tiene entidad, 1-Si cuenta con entidad' AFTER `orden` ;

UPDATE`metodos_pagos`
SET`entidad` = '1' WHERE `id_metodo_pago` = '2';

UPDATE`metodos_pagos`
SET`entidad` = '1' WHERE `id_metodo_pago` = '3';
-- ----------------------------------------------------------------------
-- Angel Gimenez - 24/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  ADD COLUMN `comision` INT (11) NULL AFTER `iva`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 24/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `notificaciones` (
  `id_notificacion` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR (255),
  `descripcion` VARCHAR (255),
  `fecha` DATETIME,
  PRIMARY KEY (`id_notificacion`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `notificaciones_usuarios` (
  `id_notificacion_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_notificacion` INT,
  `id_usuario` INT (10) UNSIGNED,
  `estado` TINYINT (1) DEFAULT 0 COMMENT '0:Pendiente; 1:Visto',
  PRIMARY KEY (`id_notificacion_usuario`),
  CONSTRAINT `fk_users_notificaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `fk_notificaciones_notificaciones_usuarios` FOREIGN KEY (`id_notificacion`) REFERENCES `notificaciones` (`id_notificacion`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_nopad_ci;

ALTER TABLE `notificaciones_usuarios`
  ADD UNIQUE INDEX `unique_id_usuario_id_notificacion` (`id_notificacion`, `id_usuario`);

ALTER TABLE `users`
  ADD COLUMN `notificaciones` TINYINT (1) DEFAULT 1 NULL COMMENT '0:No; 1:Si' AFTER `force_logout`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 24/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  CHANGE `comision` `comision` INT (11) DEFAULT 0 NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 25/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(78,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','5.5','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(145,'Reportes de Sueldos','Reportes de Sueldos','./reportes-sueldos','<i class=\"fas fa-list mt-1\"></i>','5.5.1','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 25/08/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(108,'Notas De Crédito','Notas De Crédito','./notas-credito','<i class=\"fas fa-reply\"></i>','5.5','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 26/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cobros`
  ADD COLUMN `id_entidad` INT NULL AFTER `id_descuento_metodo_pago`,
  ADD CONSTRAINT `fk_entidades_cobros` FOREIGN KEY (`id_entidad`) REFERENCES `victoria_db`.`entidades` (`id_entidad`) ON UPDATE RESTRICT ON DELETE RESTRICT;
  
INSERT INTO `menus` (`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES('Contabilidad','Contabilidad','#','<i class="fas fa-hand-holding-usd"></i>','6.01','Habilitado');

UPDATE `menus`SET `id_menu_padre` = 148, `orden` = '6.01.1' WHERE `id_menu` = 129;

UPDATE`menus`SET`id_menu_padre` = 148, `orden` = '6.01.3' WHERE `id_menu` = 117;

UPDATE `menus` SET `id_menu_padre` = 148, `orden` = '6.01.2' WHERE `id_menu` = 113;

UPDATE `menus` SET `id_menu_padre` = 145, `orden` = '5.5.2' WHERE `id_menu` = 137;

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(108,'Descuentos','Descuentos','#','<i class="fas fa-cash-register mt-1"></i>','5.4.1','Habilitado');

UPDATE `menus` SET `id_menu_padre` = 149, `menu` = 'Proveedor', `icono` = '<i class="fas fa-dollar-sign mt-1"></i>', `orden` = '5.4.1.1' WHERE `id_menu` = 114;

UPDATE `menus` SET `id_menu_padre` = 149, `menu` = 'Pagos', `titulo` = 'Descuentos Por Método de Pago', `icono` = '<i class="fas fa-cart-plus mt-1"></i>', `orden` = '5.4.1.2' WHERE `id_menu` = 121;

UPDATE `menus` SET `id_menu_padre` = 78, `orden` = '5.4.1' WHERE `id_menu` = 81;

UPDATE `menus` SET `id_menu_padre` = 78, `orden` = '5.4.2' WHERE `id_menu` = 91;

UPDATE `menus` SET `id_menu_padre` = 78, `orden` = '5.4.3' WHERE `id_menu` = 100;
-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 26/08/2022
-- ----------------------------------------------------------------------
CREATE TABLE `notas_credito` (
  `id_nota_credito` INT NOT NULL AUTO_INCREMENT,
  `id_factura_origen` BIGINT,
  `id_factura_destino` BIGINT,
  `id_timbrado` INT,
  `id_sucursal` INT,
  `numero` INT (7) UNSIGNED ZEROFILL,
  `cantidad` INT,
  `total` INT,
  `id_cliente` INT,
  `ruc` VARCHAR (45),
  `razon_social` VARCHAR (255),
  `usuario` VARCHAR (45),
  `estado` TINYINT (1) COMMENT '0:Pendiente; 1:Utilizada; 2:Anulada',
  `fecha` DATETIME,
  PRIMARY KEY (`id_nota_credito`),
  CONSTRAINT `fk_facturas_notas_credito_origen` FOREIGN KEY (`id_factura_origen`) REFERENCES `facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_facturas_notas_credito_destino` FOREIGN KEY (`id_factura_destino`) REFERENCES `facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_sucursales_notas_credito` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_timbrados_notas_credito` FOREIGN KEY (`id_timbrado`) REFERENCES `timbrados` (`id_timbrado`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_clientes_notas_credito` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `notas_credito_productos` (
  `id_nota_credito_producto` INT NOT NULL AUTO_INCREMENT,
  `id_nota_credito` INT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `id_lote` INT,
  `lote` VARCHAR (255),
  `cantidad` INT,
  `total_venta` INT,
  PRIMARY KEY (`id_nota_credito_producto`),
  CONSTRAINT `fk_notas_credito_notas_credito_productos` FOREIGN KEY (`id_nota_credito`) REFERENCES `notas_credito` (`id_nota_credito`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_notas_credito_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_lotes_notas_credito_productos` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/08/2022
-- ----------------------------------------------------------------------
ALTER TABLE `notas_credito_productos`
  ADD COLUMN `fraccionado` TINYINT (1) NULL COMMENT '0:No; 1:Si' AFTER `lote`;

DROP TABLE `descuentos_productos`;

ALTER TABLE `stock_niveles`
  DROP FOREIGN KEY `fk_productos_stock_niveles`;
ALTER TABLE `stock_niveles`
  ADD CONSTRAINT `fk_productos_stock_niveles` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;
ALTER TABLE `stock_niveles`
  DROP FOREIGN KEY `fk_sucursales_stock_niveles`;
ALTER TABLE `stock_niveles`
  ADD CONSTRAINT `fk_sucursales_stock_niveles` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 30/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `solicitudes_depositos`
  ADD COLUMN `id_deposito` INT (11) NULL AFTER `id_sucursal`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 31/08/2022
-- ----------------------------------------------------------------------

ALTER TABLE `notas_remision_productos`
  ADD COLUMN `id_solicitud_deposito` INT NULL AFTER `id_nota_remision`,
  ADD CONSTRAINT `fk_solicitud_deposito_notas_remision` FOREIGN KEY (`id_solicitud_deposito`) REFERENCES `victoria_db`.`solicitudes_depositos` (`id_solicitud_deposito`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 02/09/2022
-- ----------------------------------------------------------------------
CREATE TABLE `descuentos_pagos_productos` (
  `id_descuento_pago_producto` INT NOT NULL AUTO_INCREMENT,
  `id_descuento_pago` INT,
  `id_producto` INT,
  `porcentaje` INT,
  `fecha` DATETIME,
  PRIMARY KEY (`id_descuento_pago_producto`),
  CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_productos` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_descuentos_pagos_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `metodos_pagos`
  ADD COLUMN `sigla` VARCHAR (20) NULL AFTER `entidad`;

UPDATE metodos_pagos SET sigla = 'EF' WHERE `id_metodo_pago` = 1;
UPDATE metodos_pagos SET sigla = 'TC' WHERE `id_metodo_pago` = 2;
UPDATE metodos_pagos SET sigla = 'TD' WHERE `id_metodo_pago` = 3;
UPDATE metodos_pagos SET sigla = 'CH' WHERE `id_metodo_pago` = 4;
UPDATE metodos_pagos SET sigla = 'TR' WHERE `id_metodo_pago` = 5;
UPDATE metodos_pagos SET sigla = 'GC' WHERE `id_metodo_pago` = 6;
UPDATE metodos_pagos SET sigla = 'NC' WHERE `id_metodo_pago` = 7;
UPDATE metodos_pagos SET sigla = 'VA' WHERE `id_metodo_pago` = 8;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 05/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos_productos`
  DROP COLUMN `fecha`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_proveedores_productos`
  ADD COLUMN `id_metodo_pgo` INT (11) NULL AFTER `porcentaje`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 07/09/2022
-- ----------------------------------------------------------------------

ALTER TABLE `gastos`
  DROP COLUMN `iva`,
  DROP COLUMN `iva_10`,
  DROP COLUMN `iva_5`;

ALTER TABLE `gastos`
  CHANGE `extenta` `exenta` INT (11) NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 07/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos`
  DROP COLUMN `porcentaje`;

ALTER TABLE `descuentos_pagos_productos`
  DROP FOREIGN KEY `fk_descuentos_pagos_descuentos_pagos_productos`;
ALTER TABLE `descuentos_pagos_productos`
  ADD CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_productos` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`) ON DELETE CASCADE;

CREATE TABLE `descuentos_pagos_filtros` (
  `id_descuento_pago_filtro` INT NOT NULL AUTO_INCREMENT,
  `id_descuento_pago` INT,
  `id_origen` INT,
  `id_tipo_producto` INT,
  `id_laboratorio` INT,
  `id_marca` INT,
  `id_rubro` INT,
  `porcentaje` INT,
  PRIMARY KEY (`id_descuento_pago_filtro`),
  CONSTRAINT `descuentos_pagos_descuentos_pagos_filtros` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `origenes_descuentos_pagos_filtros` FOREIGN KEY (`id_origen`) REFERENCES `origenes` (`id_origen`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `tipos_productos_descuentos_pagos_filtros` FOREIGN KEY (`id_tipo_producto`) REFERENCES `tipos_productos` (`id_tipo_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `laboratorios_descuentos_pagos_filtros` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratorios` (`id_laboratorio`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `marcas_descuentos_pagos_filtros` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `rubros_descuentos_pagos_filtros` FOREIGN KEY (`id_rubro`) REFERENCES `rubros` (`id_rubro`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_proveedores_productos`
  CHANGE `id_metodo_pgo` `id_metodo_pago` INT (11) NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 08/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_filtros` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`) ON DELETE CASCADE,
  DROP FOREIGN KEY `descuentos_pagos_descuentos_pagos_filtros`;
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_laboratorios_descuentos_pagos_filtros` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratorios` (`id_laboratorio`),
  DROP FOREIGN KEY `laboratorios_descuentos_pagos_filtros`;
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_marcas_descuentos_pagos_filtros` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`),
  DROP FOREIGN KEY `marcas_descuentos_pagos_filtros`;
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_origenes_descuentos_pagos_filtros` FOREIGN KEY (`id_origen`) REFERENCES `origenes` (`id_origen`),
  DROP FOREIGN KEY `origenes_descuentos_pagos_filtros`;
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_rubros_descuentos_pagos_filtros` FOREIGN KEY (`id_rubro`) REFERENCES `rubros` (`id_rubro`),
  DROP FOREIGN KEY `rubros_descuentos_pagos_filtros`;
ALTER TABLE `descuentos_pagos_filtros`
  ADD CONSTRAINT `fk_tipos_productos_descuentos_pagos_filtros` FOREIGN KEY (`id_tipo_producto`) REFERENCES `tipos_productos` (`id_tipo_producto`),
  DROP FOREIGN KEY `tipos_productos_descuentos_pagos_filtros`;

ALTER TABLE `descuentos_pagos_filtros`
  ADD UNIQUE INDEX `u_filtros` (
    `id_descuento_pago`,
    `id_origen`,
    `id_tipo_producto`,
    `id_laboratorio`,
    `id_marca`,
    `id_rubro`
  );

ALTER TABLE `descuentos_pagos_productos`
  ADD UNIQUE INDEX `u_producto_descuento` (
    `id_descuento_pago`,
    `id_producto`
  );

UPDATE menus SET id_menu_padre=149, menu='Campañas', titulo='Campañas De Descuento', url='./descuentos-pagos', icono='<i class=\"fas fa-cart-plus mt-1\"></i>', orden='5.4.1.2', estado='Habilitado' WHERE id_menu = '121';

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 08/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `users`
  ADD COLUMN `usuario_carga` VARCHAR (50) NULL AFTER `notificaciones`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 09/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `documentos_facturas`
  ADD COLUMN `usuario` VARCHAR (45) NULL AFTER `id_factura`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 12/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos_filtros`
  ADD COLUMN `controlado` TINYINT (1) NULL COMMENT '0: No; 1: Si' AFTER `id_rubro`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `configuracion`
  ADD COLUMN `periodo_devolucion` INT (2) NULL COMMENT 'Campo para saber el periodo de dias que tiene para realizar una nota de credito' AFTER `numero_patronal`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/09/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(108,'Notas de Crédito','Notas de Crédito','./notas-credito-vendedor','<i class=\"fas fa-reply mt-1\"></i>','5.8','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 14/09/2022
-- ----------------------------------------------------------------------
CREATE TABLE `orden_pago_gastos` (
  `id_pago_gasto` INT (11) NOT NULL AUTO_INCREMENT,
  `id_pago` INT,
  `id_gasto` INT,
  `monto` INT,
  PRIMARY KEY (`id_pago_gasto`),
  CONSTRAINT `fk_orden_pago_gastos_orden_pago` FOREIGN KEY (`id_pago`) REFERENCES `victoria_db`.`orden_pagos` (`id_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_orden_pago_gastos_gastos` FOREIGN KEY (`id_gasto`) REFERENCES `victoria_db`.`gastos` (`id_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT
);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 14/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos`
  CHANGE `fecha_inicio` `fecha_inicio` DATE NULL,
  CHANGE `fecha_fin` `fecha_fin` DATE NULL,
  ADD COLUMN `tipo` TINYINT (1) NULL COMMENT '1: Rango de fechas, 2: Días' AFTER `descripcion`,
  ADD COLUMN `hora_inicio` TIME NULL AFTER `tipo`,
  ADD COLUMN `hora_fin` TIME NULL AFTER `hora_inicio`;

CREATE TABLE `descuentos_pagos_dias` (
  `id_descuento_pago_dia` INT NOT NULL AUTO_INCREMENT,
  `id_descuento_pago` INT,
  `dia` TINYINT COMMENT '1: domingo; 2: lunes; 3: miércoles; 4: jueves; 5: viernes; 6: sábado; 7: domingo',
  PRIMARY KEY (`id_descuento_pago_dia`),
  CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_dias` FOREIGN KEY (`id_descuento_pago`) REFERENCES `victoria_db`.`descuentos_pagos` (`id_descuento_pago`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `descuentos_pagos_dias`
  ADD UNIQUE INDEX `u_descuentos_pagos_dias_dia` (`id_descuento_pago`, `dia`);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 15/09/2022
-- ----------------------------------------------------------------------
UPDATE menus SET id_menu_padre=108, menu='Administrar Créditos', titulo='Administrar Notas De Crédito', url='./notas-credito', icono='<i class=\"fas fa-list mt-1\"></i>', orden='5.9', estado='Habilitado' WHERE id_menu = '147'
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 15/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `orden_pagos`
  ADD COLUMN `id_gasto` INT NULL AFTER `id_funcionario`;

ALTER TABLE `proveedores`
  ADD COLUMN `tipo_proveedor` INT NULL COMMENT '1-Productos 2-Gastos' AFTER `ruc`;

UPDATE `proveedores` SET `tipo_proveedor` = 1

ALTER TABLE `gastos`
  ADD COLUMN `tipo_proveedor` INT NULL COMMENT '1-Productos, 2-Gastos' AFTER `id_sub_tipo_gasto`;

ALTER TABLE `gastos`
  ADD COLUMN `id_proveedor` INT NOT NULL AFTER `id_sub_tipo_gasto`;

ALTER TABLE `orden_pagos`
  CHANGE `id_gasto` `id_proveedor_gasto` INT (11) NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 16/09/2022
-- ----------------------------------------------------------------------
-- Tabla calendario
SET lc_time_names = 'es_ES';
DROP TABLE IF EXISTS calendario;
CREATE TABLE calendario (
        id                      INTEGER PRIMARY KEY,  -- year*10000+month*100+day
        fecha_mysql                 DATE NOT NULL,
        ano                    INTEGER NOT NULL,
        mes_nro                   INTEGER NOT NULL, -- 1 to 12
        fecha                     INTEGER NOT NULL, -- 1 to 31
        dia                VARCHAR(9) NOT NULL, -- 'Monday', 'Tuesday'...
        mes              VARCHAR(9) NOT NULL, -- 'January', 'February'...
        feriado            TINYINT(1) DEFAULT '0' CHECK (feriado IN ('1', '0')),
        fin_semana            TINYINT(1) DEFAULT '0' CHECK (fin_semana IN ('1', '0')),
        evento                   VARCHAR(50),
        UNIQUE td_ymd_idx (ano,mes_nro,fecha),
        UNIQUE td_dbdate_idx (fecha_mysql)

) ENGINE=MYISAM;

DROP PROCEDURE IF EXISTS fill_date_dimension;
DELIMITER //
CREATE PROCEDURE fill_date_dimension(IN startdate DATE,IN stopdate DATE)
BEGIN
    DECLARE currentdate DATE;
    SET currentdate = startdate;
    WHILE currentdate < stopdate DO
        INSERT INTO calendario VALUES (
                        YEAR(currentdate)*10000+MONTH(currentdate)*100 + DAY(currentdate),
                        currentdate,
                        YEAR(currentdate),
                        MONTH(currentdate),
                        DAY(currentdate),
                        DATE_FORMAT(currentdate,'%W'),
                        DATE_FORMAT(currentdate,'%M'),
                        '0',
                        CASE DAYOFWEEK(currentdate) WHEN 1 THEN '1' WHEN 7 THEN '1' ELSE '0' END,
                        NULL);
        SET currentdate = ADDDATE(currentdate,INTERVAL 1 DAY);
    END WHILE;
END
//
DELIMITER ;

TRUNCATE TABLE calendario;

CALL fill_date_dimension('2022-01-01','2200-12-31');
OPTIMIZE TABLE calendario;
DROP PROCEDURE IF EXISTS fill_date_dimension;
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 16/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `gastos`
  CHANGE `estado` `estado` TINYINT (4) DEFAULT 0 NULL COMMENT '1-Pendiente,2-Pagado Parcial,3-Pagado Total,4-Anulado';

ALTER TABLE `gastos`
  ADD COLUMN `id_recepcion_compra` INT (11) NULL AFTER `id_proveedor`,
  ADD CONSTRAINT `fk_gastos_recepcion_compra` FOREIGN KEY (`id_recepcion_compra`) REFERENCES`recepciones_compras` (`id_recepcion_compra`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 19/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos`
  DROP COLUMN `id_metodo_pago`,
  DROP COLUMN `id_entidad`,
  DROP INDEX `fk_metodos_pagos_descuentos_pagos`,
  DROP INDEX `fk_entidades_descuentos_pagos`,
  DROP FOREIGN KEY `fk_entidades_descuentos_pagos`,
  DROP FOREIGN KEY `fk_metodos_pagos_descuentos_pagos`;

ALTER TABLE `descuentos_pagos`
  ADD COLUMN `fecha` DATETIME NULL AFTER `fecha_fin`;

ALTER TABLE `descuentos_pagos_filtros`
  ADD COLUMN `id_metodo_pago` INT NULL AFTER `id_descuento_pago`,
  ADD COLUMN `id_entidad` INT NULL AFTER `id_metodo_pago`,
  ADD CONSTRAINT `fk_metodos_pagos_descuentos_pagos_filtros` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pagos` (`id_metodo_pago`),
  ADD CONSTRAINT `fk_entidad_descuentos_pagos_filtros` FOREIGN KEY (`id_entidad`) REFERENCES `entidades` (`id_entidad`);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 20/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos_filtros`
  DROP INDEX `u_filtros`,
  ADD UNIQUE INDEX `u_filtros` (
    `id_descuento_pago`,
    `id_origen`,
    `id_tipo_producto`,
    `id_laboratorio`,
    `id_marca`,
    `id_rubro`,
    `id_metodo_pago`,
    `id_entidad`
  );
  

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 20/09/2022
-- ----------------------------------------------------------------------

CREATE TABLE `gastos_fijos` (
  `id_gasto_fijo` INT (11) NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT (11),
  `id_tipo_gasto_fijo` INT (11),
  `id_sub_tipo_gasto_fijo` INT (11),
  `monto` INT,
  `observacion` VARCHAR (150),
  `estado` TINYINT DEFAULT 1 COMMENT '0-Inactivo, 1-Activo',
  `fecha` DATE,
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_gasto_fijo`)
);

CREATE TABLE `gastos_fijos_sub_tipos` (
  `id_gasto_fijo_sub_tipo` INT (11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR (50),
  `estado` TINYINT,
  `fecha` DATE,
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_gasto_fijo_sub_tipo`)
);

CREATE TABLE `gastos_fijos_tipos` (
  `id_gasto_fijo_tipo` INT (11) NOT NULL AUTO_INCREMENT,
  `id_gasto_fijo_sub_tipo` INT (11),
  `nombre` VARCHAR (50),
  `estado` TINYINT,
  `fecha` DATE,
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_gasto_fijo_tipo`),
  CONSTRAINT `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos` FOREIGN KEY (`id_gasto_fijo_sub_tipo`) REFERENCES `gastos_fijos_sub_tipos` (`id_gasto_fijo_sub_tipo`) ON UPDATE RESTRICT ON DELETE RESTRICT
);

UPDATE notas_remision_motivos SET estado = 1, usuario='admin', fecha=NOW()

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(152,148,'Gatos Fijos','Gastos Fijos','./gastos-fijos','<i class="fas fa-clipboard-list mt-1"></i>','6.01.2','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(153,65,'Gastos Fijos','Gastos Fijos','#','','80.5','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(154,153,'Gastos Fijos','Gastos Fijos','./tipos-gastos-fijos','<i class="fas fa-clipboard-list mt-1"></i>','80.5.1','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(155,153,'Tipos de Gastos Fijos','Tipos de Gastos Fijos','./sub-tipos-gastos-fijos','<i class="fab fa-elementor mt-1"></i>','80.5.2','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(156,65,'Notas Remisión','Notas Remisión','#','','80.6','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(157,156,'Motivos','Motivos','./motivos','','80.6.1','Habilitado');

ALTER TABLE `gastos_fijos`
  ADD COLUMN `nro_gasto_fijo` VARCHAR (7) NULL AFTER `id_sub_tipo_gasto_fijo`;
  -- ----------------------------------------------------------------------
-- Angel Ojeda- 19/09/2022
-- ----------------------------------------------------------------------

ALTER TABLE `notas_remision_motivos` 
ADD COLUMN `fecha` DATE NULL AFTER `nombre_corto`,
ADD COLUMN `usuario` VARCHAR(45) NULL AFTER `fecha`,
ADD COLUMN `estado` TINYINT(1) NULL AFTER `usuario`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 20/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_proveedores`
  ADD COLUMN `controlado` TINYINT (1) NULL COMMENT '0: No; 1: Si' AFTER `id_rubro`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 20/09/2022
-- ----------------------------------------------------------------------
Executing:
ALTER TABLE `productos` 
ADD COLUMN `iva` INT(3) NOT NULL AFTER `fecha`, COMMENT = '1=exenta 3=5%iva 5=10%iva' ;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 21/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `gastos_fijos_tipos`
  DROP COLUMN `id_gasto_fijo_sub_tipo`,
  DROP INDEX `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos`,
  DROP FOREIGN KEY `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos`;

ALTER TABLE `gastos_fijos_sub_tipos`
  ADD COLUMN `id_gasto_fijo_tipo` INT NULL AFTER `id_gasto_fijo_sub_tipo`,
  ADD CONSTRAINT `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos` FOREIGN KEY (`id_gasto_fijo_tipo`) REFERENCES `gastos_fijos_tipos` (`id_gasto_fijo_tipo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/09/2022
-- ----------------------------------------------------------------------
CREATE TABLE `proveedores_tipos` (
  `id_tipo_proveedor` INT (11) NOT NULL AUTO_INCREMENT,
  `tipo_proveedor` INT (11),
  `id_proveedor` INT (11),
  PRIMARY KEY (`id_tipo_proveedor`),
  CONSTRAINT `fk_proveedores_proveedores_tipos` FOREIGN KEY (`id_proveedor`) REFERENCES `victoria_db`.`proveedores` (`id_proveedor`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 22/09/2022
-- ----------------------------------------------------------------------
----- Insert para registrar a todos los proveedores como tipo proveedor Productos ------
INSERT INTO proveedores_tipos (tipo_proveedor, id_proveedor) SELECT 1 AS tipo, id_proveedor FROM proveedores

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(65,'Insumos','Insumos','#','<i class=\"fas fa-list\"></i>','80.7','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(160,'Tipos','Tipos de Insumos','./tipos-insumos','<i class=\"fas fa-list\"></i>','80.7.1','Habilitado');

CREATE TABLE `tipos_insumos` (
  `id_tipo_insumo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `usuario` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_tipo_insumo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE menus SET id_menu_padre=65, menu='Insumos', titulo='Insumos', url='#', icono='', orden='80.7', estado='Habilitado' WHERE id_menu = '160'

-- ----------------------------------------------------------------------
-- Angel Ojeda - 22/09/2022
-- ----------------------------------------------------------------------

CREATE TABLE `plan_puntos` (
  `id_plan_puntos` INT NOT NULL AUTO_INCREMENT,
  `ventas_credito` TINYINT(1) NULL DEFAULT NULL,
  `configuracion` INT(45) NULL DEFAULT NULL,
  `puntos` INT(45) NULL DEFAULT NULL,
  `tipo` TINYINT(1) NULL DEFAULT NULL,
  `fecha` DATETIME NULL DEFAULT NULL,
  `usuario` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id_plan_puntos`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 22/09/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu`, `id_menu_padre`, `menu`, `titulo`, `url`, `icono`, `orden`, `estado`)VALUES(158, '', 'Reportes', 'Reportes', '#', '<i class="fas fa-file-contract"></i>', '8', 'Habilitado');

INSERT INTO `victoria_db`.`menus` (`id_menu`, `id_menu_padre`, `menu`, `titulo`, `url`, `icono`, `orden`, `estado`)VALUES(159, 158, 'Productos más vendidos', 'Productos más vendidos', './reporte-productos-mas-vendidos', '<i class="fas fa-file-alt mt-1"></i>', '8.1', 'Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/09/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(158,'Ranking Clientes','Ranking Clientes','./ranking-clientes','<i class=\"fas fa-file-alt mt-1\"></i>','8.3','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Ojeda - 22/09/2022
-- ----------------------------------------------------------------------

INSERT INTO `plan_puntos` (`id_plan_puntos`, `ventas_credito`, `configuracion`, `puntos`, `tipo`, `fecha`, `usuario`, `cantidad`) VALUES ('1', '0', '1', '1', '1', 'NOW()', 'admin', '100000');
INSERT INTO .`plan_puntos` (`id_plan_puntos`, `ventas_credito`, `configuracion`, `puntos`, `tipo`, `fecha`, `usuario`, `cantidad`) VALUES ('2', '0', '1', '1', '2', 'NOW()', 'admin', '100000');

-- ----------------------------------------------------------------------
-- Angel Ojeda - 26/09/2022
-- ----------------------------------------------------------------------


INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(157,'Servicios','Servicios','./servicios','','106.1','Habilitado');



CREATE TABLE `servicios` (
  `id_servicio` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NULL DEFAULT NULL,
  `estado` TINYINT(1) NULL DEFAULT NULL,
  `fecha` DATETIME NULL DEFAULT NULL,
  `usuario` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id_servicio`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(65,'Caja','Caja','./caja','<i class=\"fas fa-money-bill\"></i>','106','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(1,'Centro de Costo','Centro de Costo','','<i class=\"fas fa-bezier-curve\"></i>','7','Habilitado');

UPDATE menus SET id_menu_padre=NULL, menu='Centro de Costo', titulo='Centro de Costo', url='', icono='<i class=\"fas fa-bezier-curve\"></i>', orden='7', estado='Habilitado' WHERE id_menu = '169'

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(169,'Productos','Productos','','','7.01','Habilitado');

UPDATE menus SET id_menu_padre=169, menu='Productos', titulo='Productos', url='./productos-de-insumos', icono='', orden='7.01', estado='Habilitado' WHERE id_menu = '170'

CREATE TABLE `productos_insumo` (
  `id_producto_insumo` INT(11) NOT NULL,
  `producto` VARCHAR(255) NULL DEFAULT NULL,
  `costo` VARCHAR(255) NULL DEFAULT NULL,
  `codigo` BIGINT(20) NULL DEFAULT NULL,
  `id_tipo_insumo` INT(11) NULL,
  `fecha` DATETIME NULL DEFAULT NULL,
  `usuario` VARCHAR(45) NULL DEFAULT NULL,
  `estado` TINYINT(1) NULL DEFAULT 0,
  PRIMARY KEY (`id_producto_insumo`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 26/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `plan_puntos`
  ADD COLUMN `cantidad` INT (45) NULL AFTER `configuracion`;

ALTER TABLE `plan_puntos`
  CHANGE `tipo` `tipo` TINYINT (1) NULL COMMENT '1-Aculacion, 2-Canjeo';

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 27/09/2022
-- ----------------------------------------------------------------------
CREATE TABLE `cajas_horarios_servicios` (
  `id_caja_horario_servicio` INT NOT NULL AUTO_INCREMENT,
  `id_caja_horario` INT,
  `id_servicio` INT,
  `monto` INT,
  PRIMARY KEY (`id_caja_horario_servicio`),
  CONSTRAINT `fk_cajas_horarios_cajas_horarios_servicios` FOREIGN KEY (`id_caja_horario`) REFERENCES `cajas_horarios` (`id_caja_horario`) ON DELETE CASCADE,
  CONSTRAINT `kf_servicios_cajas_horarios_servicios` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `cajas_horarios`
  ADD COLUMN `monto_servicios` INT NULL AFTER `diferencia`;

  -- ----------------------------------------------------------------------
-- Angel Ojeda - 27/09/2022
-- ----------------------------------------------------------------------

ALTER TABLE `productos_insumo` 
CHANGE COLUMN `id_producto_insumo` `id_producto_insumo` INT(11) NOT NULL AUTO_INCREMENT ;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 27/09/2022
-- ----------------------------------------------------------------------

CREATE TABLE `plan_puntos_productos` (
  `id_plan_punto_producto` INT (11) NOT NULL AUTO_INCREMENT,
  `id_plan_punto` INT (11),
  `id_producto` INT (11),
  `puntos` INT (45),
  `fecha` DATETIME,
  `usuario` VARCHAR (50),
  PRIMARY KEY (`id_plan_punto_producto`),
  CONSTRAINT `fk_plan_puntos_plan_puntos_productos` FOREIGN KEY (`id_plan_punto`) REFERENCES `plan_puntos` (`id_plan_puntos`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_productos_plan_puntos_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `plan_puntos`
  ADD COLUMN `periodo_canje` INT (11) NULL AFTER `tipo`;

UPDATE plan_puntos SET periodo_canje = 10

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 28/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `id_nota_credito` INT NULL AFTER `id_timbrado`,
  ADD CONSTRAINT `fk_notas_credito_facturas` FOREIGN KEY (`id_nota_credito`) REFERENCES `notas_credito` (`id_nota_credito`);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/09/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos_insumo`
  ADD CONSTRAINT `fk_tipos_insumos_productos_insumo` FOREIGN KEY (`id_tipo_insumo`) REFERENCES `tipos_insumos` (`id_tipo_insumo`);



-- ----------------------------------------------------------------------
-- Angel Ojeda - 29/09/2022
-- ----------------------------------------------------------------------


INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(169,'Solicitud','Solicitud','./solicitud-de-insumos','','7.02','Habilitado');

UPDATE menus SET id_menu_padre=169, menu='Solicitud de Insumos', titulo='Solicitud de Insumos', url='./solicitud-insumos', icono='', orden='7.02', estado='Habilitado' WHERE id_menu = '171'

UPDATE menus SET id_menu_padre=169, menu='Carga de Insumos', titulo='Carga de Insumos', url='./carga-insumos', icono='', orden='7.02', estado='Habilitado' WHERE id_menu = '171'


-- ----------------------------------------------------------------------
-- Angel Ojeda - 05/10/2022
-- ----------------------------------------------------------------------

ALTER TABLE `cargas_insumos` 
ADD COLUMN `cantidad` INT(11) NULL DEFAULT NULL AFTER `fecha`,
ADD COLUMN `monto` VARCHAR(255) NULL AFTER `cantidad`;

ALTER TABLE `cargas_insumos` 
CHANGE COLUMN `cantidad` `cantidad` INT(11) NULL DEFAULT NULL AFTER `numero`,
CHANGE COLUMN `monto` `monto` VARCHAR(255) NULL DEFAULT NULL AFTER `cantidad`;

ALTER TABLE `cargas_insumos_productos` 
ADD COLUMN `monto` VARCHAR(245) NULL AFTER `vencimiento`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas_detalles`
  CHANGE `tipo` `tipo` TINYINT (1) NULL COMMENT '0-Cierre, 1-Apertura, 2-Sencillo';

ALTER TABLE `cajas_horarios`
  ADD COLUMN `monto_sencillo_cierre` INT (11) NULL AFTER `monto_servicios`,
  ADD COLUMN `diferencia_sencillo` INT (11) NULL AFTER `monto_sencillo_cierre`;
-- ----------------------------------------------------------------------
-- Angel Ojeda - 06/10/2022
-- ----------------------------------------------------------------------

  INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(169,'Administrar Carga Insumo','Administrar Carga Insumo','./administrar-carga-insumo','','7.03','Habilitado');

UPDATE menus SET id_menu_padre=169, menu='Administrar Cargas Insumos', titulo='Administrar Cargas Insumos', url='./administrar-carga-insumo', icono='', orden='7.03', estado='Habilitado' WHERE id_menu = '172'

UPDATE menus SET id_menu_padre=169, menu='Administrar Cargas Insumos', titulo='Administrar Cargas Insumos', url='./administrar-cargas-insumos', icono='', orden='7.03', estado='Habilitado' WHERE id_menu = '172'

-- ----------------------------------------------------------------------
-- Angel Gimenez - 07/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cargas_insumos_productos`
  CHANGE `vencimiento` `vencimiento` DATE NULL;


-- ----------------------------------------------------------------------
-- Angel Ojeda - 10/10/2022
-- ----------------------------------------------------------------------

UPDATE menus SET id_menu_padre=169, menu='Productos', titulo='Productos', url='./productos-de-insumos', icono='<i class=\"fas fa-box\"></i>', orden='7.01', estado='Habilitado' WHERE id_menu = '170'

UPDATE menus SET id_menu_padre=169, menu='Carga de Insumos', titulo='Carga de Insumos', url='./carga-insumos', icono='<i class=\"fas fa-edit mt-1\"></i>', orden='7.02', estado='Habilitado' WHERE id_menu = '171'

UPDATE menus SET id_menu_padre=169, menu='Administrar Cargas', titulo='Administrar Cargas Insumos', url='./administrar-cargas-insumos', icono='<i class=\"fas fa-list\"></i>', orden='7.03', estado='Habilitado' WHERE id_menu = '172'

-- ----------------------------------------------------------------------
-- sebastian Alvarenga - 10/10/2022
-- ----------------------------------------------------------------------
INSERT INTO `sub_tipos_gastos` (`id_sub_tipo_gasto`, `nombre`) VALUES (2, 'CARGA DE INSUMOS');



-- ----------------------------------------------------------------------
-- Angel Ojeda - 10/10/2022
-- ----------------------------------------------------------------------



UPDATE menus SET id_menu_padre=169, menu='Productos', titulo='Productos', url='./productos-de-insumos', icono='<i class=\"fas fa-box\"></i>', orden='7.01', estado='Habilitado' WHERE id_menu = '170'

UPDATE menus SET id_menu_padre=169, menu='Carga de Insumos', titulo='Carga de Insumos', url='./carga-insumos', icono='<i class=\"fas fa-edit mt-1\"></i>', orden='7.02', estado='Habilitado' WHERE id_menu = '171'

UPDATE menus SET id_menu_padre=169, menu='Administrar Cargas', titulo='Administrar Cargas Insumos', url='./administrar-cargas-insumos', icono='<i class=\"fas fa-list\"></i>', orden='7.03', estado='Habilitado' WHERE id_menu = '172'


;
ALTER TABLE `victoria_db`.`stock_insumos` 
ADD CONSTRAINT `fk_productos_insumo_stock_insumos`
  FOREIGN KEY (`id_producto_insumo`)
  REFERENCES `victoria_db`.`productos_insumo` (`id_producto_insumo`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

CREATE TABLE `victoria_db`.`stock_insumos_historial` (
  `id_stock_insumo_historial` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto_insumo` INT(11) NULL,
  `producto` VARCHAR(255) NULL,
  `vencimiento` DATE NULL,
  `operacion` VARCHAR(100) NULL,
  `origen` VARCHAR(100) NULL,
  `id_origen` INT(11) NULL,
  `detalles` VARCHAR(255) NULL,
  `usuario` VARCHAR(45) NULL,
  `fecha` DATETIME NULL,
  PRIMARY KEY (`id_stock_insumo_historial`),
  INDEX `fk_productos_insumo_stock_insumos_historial_idx` (`id_producto_insumo` ASC) VISIBLE,
  INDEX `fk_origen_stock_insumos_historial_idx` (`id_origen` ASC) VISIBLE,
  CONSTRAINT `fk_productos_insumo_stock_insumos_historial`
    FOREIGN KEY (`id_producto_insumo`)
    REFERENCES `victoria_db`.`productos_insumo` (`id_producto_insumo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_origen_stock_insumos_historial`
    FOREIGN KEY (`id_origen`)
    REFERENCES `victoria_db`.`origenes` (`id_origen`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(169,'Stock','Stock Insumos','./stock-insumos','<i class=\"fas fa-boxes\"></i>','7.0.4','Habilitado');

ALTER TABLE `stock_insumos_historial` 
DROP FOREIGN KEY `fk_origen_stock_insumos_historial`;
ALTER TABLE `stock_insumos_historial` 
DROP INDEX `fk_origen_stock_insumos_historial_idx` ;


;
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 11/10/2022
-- ----------------------------------------------------------------------
INSERT INTO `tipos_gastos` (`id_sub_tipo_gasto`,`nombre`,`estado`) VALUES(2,'INSUMOS',1);

ALTER TABLE `gastos`
  ADD COLUMN `id_carga_insumo` INT (11) NULL AFTER `id_recepcion_compra`,
  ADD CONSTRAINT `fk_gastos_cargas_insumos` FOREIGN KEY (`id_carga_insumo`) REFERENCES `cargas_insumos` (`id_carga_insumo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 11/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `orden_pagos_funcionarios` (
  `id_pago_funcionario` INT (11) NOT NULL AUTO_INCREMENT,
  `id_pago` INT (11),
  `id_liquidacion` INT (11),
  `monto` INT (11),
  PRIMARY KEY (`id_pago_funcionario`),
  CONSTRAINT `fk_orden_pago_funcionario_orden_pago` FOREIGN KEY (`id_pago`) REFERENCES `orden_pagos` (`id_pago`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_orden_pagos_liquidaciones_liquidaciones` FOREIGN KEY (`id_liquidacion`) REFERENCES `liquidacion_salarios` (`id_liquidacion`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB;

ALTER TABLE `liquidacion_salarios`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 0 NULL COMMENT '0=pendiente, 1=aprobado, 2=anulado, 3=pagado_parcial, 4=pagado total';



-- ----------------------------------------------------------------------
-- Angel Ojeda - 11/10/2022
-- ----------------------------------------------------------------------


CREATE TABLE `stock_insumos` (
  `id_stock_insumo` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto_insumo` INT(11) NULL DEFAULT NULL,
  `stock` INT(255) NULL DEFAULT NULL,
  `vencimiento` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id_stock_insumo`),
  INDEX `fk_id_producto_insumo_idx` (`id_producto_insumo` ASC) VISIBLE,
  CONSTRAINT `fk_id_producto_insumo`
    FOREIGN KEY (`id_producto_insumo`)
    REFERENCES `victoria_db`.`productos_insumo` (`id_producto_insumo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `cajas_horarios`
  ADD COLUMN `usuario_apertura` VARCHAR (45) NULL AFTER `estado`,
  ADD COLUMN `usuario_cierre` VARCHAR (45) NULL AFTER `usuario_apertura`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 13/10/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(158,'Stock Mínimo','Stock Mínimo','./reporte-stock-minimo','<i class="fas fa-file-alt mt-1"></i>','8.4','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 13/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `notas_credito`
  ADD COLUMN `devolucion_importe` INT (11) DEFAULT 0 NULL COMMENT '0=No, 1=Si' AFTER `fecha`;

ALTER TABLE `notas_credito`
  CHANGE `devolucion_importe` `devolucion_importe` TINYINT (1) DEFAULT 0 NULL COMMENT '0=No, 1=Si';

ALTER TABLE `notas_credito`
  ADD COLUMN `id_caja_horario` INT (11) NULL AFTER `devolucion_importe`;

ALTER TABLE `cajas_horarios`
  ADD COLUMN `devolucion_importe` INT (11) NULL AFTER `diferencia_sencillo`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 13/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `clientes_puntos` (
  `id_cliente_punto` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT,
  `id_factura` BIGINT (20),
  `fecha` DATE,
  `puntos` INT,
  PRIMARY KEY (`id_cliente_punto`),
  CONSTRAINT `fk_clientes_clientes_puntos` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_facturas_clientes_puntos` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 14/10/2022
-- ----------------------------------------------------------------------

INSERT INTO `menus` (`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(158,'Próximos a Vencer','Productos Próximos a Vencer','./reporte-productos-proximos-vencer','<i class="fas fa-file-alt mt-1"></i>','8.6','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 14/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `clientes_puntos`
  ADD COLUMN `utilizados` INT DEFAULT 0 NULL AFTER `puntos`,
  ADD COLUMN `estado` TINYINT DEFAULT 0 NULL COMMENT '0:Pendiente; 1:Utilizado; 2:Vencido' AFTER `utilizados`;

ALTER TABLE `clientes`
  ADD COLUMN `puntos` INT DEFAULT 0 NULL AFTER `latitud`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 14/10/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(143,'Reporte Puntos Clientes','Reporte Puntos Clientes','./reporte-puntos-clientes','<i class=\"fas fa-list mt-1\"></i>','5.7.4','Habilitado');

-- ----------------------------------------------------------------------
-- En producción - 14/10/2022
-- ----------------------------------------------------------------------
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 17/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  CHANGE `iva` `iva` INT (3) NOT NULL COMMENT '1-EXENTAS, 2-5%, 3-10%';

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 17/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `clientes_puntos`
  CHANGE `estado` `estado` TINYINT (4) DEFAULT 0 NULL COMMENT '0:Pendiente; 1:Utilizado; 2:Vencido; 3:Anulado';

  -- ----------------------------------------------------------------------
-- Angel Ojeda - 17/10/2022
-- ----------------------------------------------------------------------

ALTER TABLE `productos_insumo` 
CHANGE COLUMN `costo` `costo` INT(11) NULL DEFAULT NULL ;
ALTER TABLE `cargas_insumos_productos` 
CHANGE COLUMN `monto` `monto` INT(11) NULL DEFAULT NULL ;

ALTER TABLE `cargas_insumos` 
CHANGE COLUMN `monto` `monto` INT(11) NULL DEFAULT NULL ;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 17/10/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(169,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','7.05','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(179,'Stock Insumos','Reporte de Stock Insumos','./reporte-stock-insumos','<i class=\"fas fa-file-alt mt-1\"></i>','7.05.1','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 18/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `gastos_vencimientos` (
  `id_gasto_vencimiento` INT NOT NULL AUTO_INCREMENT,
  `id_gasto` INT,
  `vencimiento` DATE,
  `monto` INT,
  PRIMARY KEY (`id_gasto_vencimiento`),
  CONSTRAINT `fk_gastos_gastos_vencimientos` FOREIGN KEY (`id_gasto`) REFERENCES `gastos` (`id_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `orden_pago_gastos`
  ADD COLUMN `id_gasto_vencimiento` INT NULL AFTER `monto`,
  ADD CONSTRAINT `fk_orden_pago_gastos_gastos_vencimientos` FOREIGN KEY (`id_gasto_vencimiento`) REFERENCES `gastos_vencimientos` (`id_gasto_vencimiento`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/10/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(145,'Reporte de Despidos','Reporte de Despidos','./reporte-despidos','<i class=\"fas fa-file-alt mt-1\"></i>','5.5.3','Habilitado');

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 21/10/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(160,'Descuentos por Producto','Descuentos por Producto','./reporte-productos-descuentos','<i class=\"fas fa-file-alt mt-1\"></i>','9.9','Habilitado');
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 24/10/2022
-- ----------------------------------------------------------------------
INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(182,65,'Caja Chica','Caja Chica','#','','80.9','Habilitado');

INSERT INTO `menus` (`id_menu`,`id_menu_padre`,`menu`,`titulo`,`url`,`icono`,`orden`,`estado`)
VALUES(183,182,'Caja Chica','Nueva Caja Chica','./caja-chica','<i class="fas fa-cash-register mt-1"></i>','80.9.1','Habilitado');

CREATE TABLE `caja_chica` (
  `id_caja_chica` INT NOT NULL AUTO_INCREMENT,
  `id_sucursal` INT,
  `monto` INT,
  `monto_minimo` INT,
  `maximo_factura` INT,
  `fecha` DATETIME,
  `usuario` VARCHAR (50),
  `estado` TINYINT (1),
  PRIMARY KEY (`id_caja_chica`),
  CONSTRAINT `fk_caja_chica_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `caja_chica`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 1 NULL;
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 25/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `caja_chica_sucursal` (
  `id_caja_chica_sucursal` INT (11) NOT NULL AUTO_INCREMENT,
  `id_caja_chica` INT (11),
  `fecha_apertura` DATETIME,
  `fecha_rendicion` DATETIME,
  `usuario_apertura` VARCHAR (45),
  `usuario_rendicion` VARCHAR (45),
  `estado` TINYINT (1) DEFAULT 1 COMMENT '1-Abierto,2-Rendido',
  PRIMARY KEY (`id_caja_chica_sucursal`),
  CONSTRAINT `fk_caja_chica_caja_chica_sucursal` FOREIGN KEY (`id_caja_chica`) REFERENCES `caja_chica` (`id_caja_chica`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `caja_chica_sucursal`
  ADD COLUMN `cod_movimiento` VARCHAR (4) NULL AFTER `id_caja_chica`;

ALTER TABLE `orden_pagos`
  ADD COLUMN `id_caja_chica_sucursal` INT (11) NULL AFTER `id_proveedor_gasto`,
  ADD CONSTRAINT `fk_orden_pagos_caja_chica_sucursal` FOREIGN KEY (`id_caja_chica_sucursal`) REFERENCES `caja_chica_sucursal` (`id_caja_chica_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT;

INSERT INTO `tipos_puestos` (`id_tipo_puesto`, `tipo_puesto`) VALUES(3, 'Administrador de caja chica');

ALTER TABLE `caja_chica`
  ADD COLUMN `id_funcionario` INT (11) NULL AFTER `id_sucursal`;

ALTER TABLE `caja_chica_sucursal`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 0 NULL COMMENT '0-Pendiente,1-Abierto,2-Rendido';
-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 26/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `caja_chica_sucursal`
  ADD COLUMN `saldo` INT NULL AFTER `cod_movimiento`,
  ADD COLUMN `sobrante` INT NULL AFTER `saldo`;

ALTER TABLE `gastos`
  ADD COLUMN `id_caja_chica` INT (11) NULL AFTER `id_carga_insumo`,
  ADD CONSTRAINT `fk_gastos_caja_chica_sucursal` FOREIGN KEY (`id_caja_chica`) REFERENCES `caja_chica_sucursal` (`id_caja_chica_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `gastos`
  ADD COLUMN `deducible` TINYINT (1) NULL COMMENT '1-Deducible, 2-No deducible' AFTER `observacion`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 26/10/2022
-- ----------------------------------------------------------------------

  ALTER TABLE `configuracion` 
ADD COLUMN `utilidad` DECIMAL(10,2) NULL DEFAULT NULL AFTER `estado`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 26/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `lotes`
  ADD COLUMN `costo` INT (11) NULL AFTER `vencimiento_canje`;


-- ----------------------------------------------------------------------
-- Angel Ojeda - 27/10/2022
-- ----------------------------------------------------------------------

  INSERT INTO `sub_tipos_gastos` (`id_sub_tipo_gasto`, `nombre`) VALUES ('3', 'PREMIO');

  CREATE TABLE `premios` (
  `id_premio` INT NOT NULL AUTO_INCREMENT,
  `premio` VARCHAR(255) NULL DEFAULT NULL,
  `codigo` BIGINT(20) NULL DEFAULT NULL,
  `descripcion` VARCHAR(255) NULL DEFAULT NULL,
  `costo` INT(11) NULL DEFAULT NULL,
  `puntos` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_premio`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,'Premios','','','<i class=\"fas fa-trophy\"></i>','10','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(185,'Premios','Premios','./premios','','10.1','Habilitado');

ALTER TABLE `premios` 
ADD COLUMN `fecha` DATETIME NULL DEFAULT NULL AFTER `puntos`,
ADD COLUMN `usuario` VARCHAR(45) NULL DEFAULT NULL AFTER `fecha`,
ADD COLUMN `estado` TINYINT(1) NULL DEFAULT 0 AFTER `usuario`;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(185,'Carga de Premios','Carga de Premios','./cargas-premios','','10.2','Habilitado');

CREATE TABLE `cargas_premios` (
  `id_cargas_premios` INT(11) NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(245) NULL DEFAULT NULL,
  `cantidad` INT(11) NULL DEFAULT NULL,
  `monto` INT(11) NULL DEFAULT NULL,
  `observacion` VARCHAR(245) NULL DEFAULT NULL,
  `estado` TINYINT(1) NULL DEFAULT NULL,
  `usuario` VARCHAR(245) NULL DEFAULT NULL,
  `fecha` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id_cargas_premios`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 27/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `caja_chica_facturas` (
  `id_caja_chica_facturas` INT (11) NOT NULL AUTO_INCREMENT,
  `id_caja_chica_sucursal` INT (11),
  `id_gasto` INT (11),
  PRIMARY KEY (`id_caja_chica_facturas`),
  CONSTRAINT `fk_caja_chica_facturas_caja_chica_sucursales` FOREIGN KEY (`id_caja_chica_sucursal`) REFERENCES `caja_chica_sucursal` (`id_caja_chica_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_caja_chica_facturas_gastos` FOREIGN KEY (`id_gasto`) REFERENCES `gastos` (`id_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `gastos`
  CHANGE `id_proveedor` `id_proveedor` INT (11) NULL;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 28/10/2022
-- ----------------------------------------------------------------------

ALTER TABLE `cargas_premios` 
ADD COLUMN `puntos` INT(11) NULL DEFAULT NULL AFTER `fecha`;

CREATE TABLE `cargas_premios_productos` (
  `id_cargas_premios_productos` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cargas_premios` INT(11) NULL DEFAULT NULL,
  `id_premio` INT(11) NULL DEFAULT NULL,
  `cantidad` INT(11) NULL DEFAULT NULL,
  `monto` INT(11) NULL DEFAULT NULL,
  `puntos` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_cargas_premios_productos`),
  INDEX `fk_premios_cargas_premios_productos_idx` (`id_premio` ASC) VISIBLE,
  INDEX `fk_cargas_premios_cargas_premios_productos_idx` (`id_cargas_premios` ASC) VISIBLE,
  CONSTRAINT `fk_cargas_premios_cargas_premios_productos`
    FOREIGN KEY (`id_cargas_premios`)
    REFERENCES `cargas_premios` (`id_cargas_premios`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_premios_cargas_premios_productos`
    FOREIGN KEY (`id_premio`)
    REFERENCES `premios` (`id_premio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE `gastos` 
DROP FOREIGN KEY `fk_gastos_cargas_insumos`;
ALTER TABLE `gastos` 
ADD COLUMN `id_cargas_premios` INT(11) NULL DEFAULT NULL AFTER `usuario`,
ADD INDEX `fk_gastos_cargas_premios_idx` (`id_cargas_premios` ASC) VISIBLE;
;
ALTER TABLE `gastos` 
ADD CONSTRAINT `fk_gastos_cargas_insumos`
  FOREIGN KEY (`id_carga_insumo`)
  REFERENCES `cargas_insumos` (`id_carga_insumo`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_gastos_cargas_premios`
  FOREIGN KEY (`id_cargas_premios`)
  REFERENCES `cargas_premios` (`id_cargas_premios`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

  ALTER TABLE `cargas_premios` 
DROP COLUMN `puntos`;

ALTER TABLE `cargas_premios_productos` 
DROP COLUMN `puntos`;

CREATE TABLE `stock-premios` (
  `id_stock-premios` INT(11) NOT NULL AUTO_INCREMENT,
  `id_premio` INT(11) NULL,
  `stock` INT(11) NULL,
  PRIMARY KEY (`id_stock-premios`),
  INDEX `fk_premio_stock-premios_idx` (`id_premio` ASC) VISIBLE,
  CONSTRAINT `fk_premio_stock-premios`
    FOREIGN KEY (`id_premio`)
    REFERENCES `premios` (`id_premio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE `stock-premios` 
RENAME TO  `stock_premios` ;

CREATE TABLE `stock_premios_historial` (
  `id_stock_premios_historial` INT(11) NOT NULL AUTO_INCREMENT,
  `id_premio` INT(11) NULL DEFAULT NULL,
  `premio` VARCHAR(245) NULL DEFAULT NULL,
  `operacion` VARCHAR(100) NULL DEFAULT NULL,
  `origen` VARCHAR(100) NULL DEFAULT NULL,
  `id_origen` INT(11) NULL DEFAULT NULL,
  `detalles` VARCHAR(245) NULL DEFAULT NULL,
  `usuario` VARCHAR(45) NULL DEFAULT NULL,
  `fecha` DATETIME NULL,
  PRIMARY KEY (`id_stock_premios_historial`),
  INDEX `fk_premios_stock_premios_historial_idx` (`id_premio` ASC) VISIBLE,
  CONSTRAINT `fk_premios_stock_premios_historial`
    FOREIGN KEY (`id_premio`)
    REFERENCES `victoria_db`.`premios` (`id_premio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 31/10/2022
-- ----------------------------------------------------------------------
CREATE TABLE `caja_chica_depositos` (
  `id_caja_chica_deposito` INT (11) NOT NULL AUTO_INCREMENT,
  `id_caja_chica_sucursal` INT (11),
  `id_banco` INT (11),
  `fecha_deposito` DATE,
  `nro_cuenta` VARCHAR (45),
  `nro_comprobante` VARCHAR (45),
  `fecha_registro` DATETIME,
  `usuario` VARCHAR (45),
  PRIMARY KEY (`id_caja_chica_deposito`),
  CONSTRAINT `fk_caja_chica_sucursal_caja_chica_deposito` FOREIGN KEY (`id_caja_chica_sucursal`) REFERENCES `caja_chica_sucursal` (`id_caja_chica_sucursal`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_bancos_caja_chica_sucursal` FOREIGN KEY (`id_banco`) REFERENCES `bancos` (`id_banco`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `caja_chica_depositos`
  ADD COLUMN `estado` TINYINT (1) DEFAULT 1 NULL COMMENT '1-Pendiente , 2-Cargado' AFTER `usuario`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 31/10/2022
-- ----------------------------------------------------------------------
ALTER TABLE `ajuste_stock_productos`
  ADD COLUMN `cantidad_anterior` INT (11) NULL AFTER `fraccionado`,
  ADD COLUMN `fraccionado_anterior` INT (11) NULL AFTER `cantidad_anterior`;

ALTER TABLE `ajuste_stock_productos`
  CHANGE `tipo_ajuste` `tipo_ajuste` INT (11) NULL COMMENT '1-Positivo, 2-Negativo, 3-Reemplazo';

  
  -- ----------------------------------------------------------------------
-- Angel Ojeda - 31/10/2022
-- ----------------------------------------------------------------------

ALTER TABLE `stock_premios` 
CHANGE COLUMN `id_stock-premios` `id_stock_premio` INT(11) NOT NULL AUTO_INCREMENT ;

DROP TABLE stock_premios;

CREATE TABLE `stock_premios` (
  `id_stock_premio` INT(11) NOT NULL AUTO_INCREMENT,
  `id_premio` INT(11) NULL,
  `stock` INT(11) NULL,
  PRIMARY KEY (`id_stock_premio`),
  INDEX `fk_premio_stock_premios_idx` (`id_premio` ASC) VISIBLE,
  CONSTRAINT `fk_premio_stock_premios`
    FOREIGN KEY (`id_premio`)
    REFERENCES `premios` (`id_premio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 02/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `menus`
  CHANGE `orden` `orden` INT NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 03/11/2022
-- ----------------------------------------------------------------------
INSERT INTO `metodos_pagos` (`metodo_pago`, `orden`, `sigla`, `usuario`, `fecha`) 
VALUES ('Puntos', '9', 'PT', 'admin', '2022-11-01 00:00:00');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 03/11/2022
-- ----------------------------------------------------------------------
CREATE TABLE `bancos_cuentas` (
  `id_cuenta` INT (11) NOT NULL AUTO_INCREMENT,
  `id_banco` INT (11),
  `cuenta` VARCHAR (25),
  `estado` TINYINT (1),
  PRIMARY KEY (`id_cuenta`)
) ENGINE = INNODB;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 04/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `bancos_cuentas`
  ADD CONSTRAINT `fk_bancos_bancos_cuentas` FOREIGN KEY (`id_banco`) REFERENCES `victoria_db`.`bancos` (`id_banco`) ON UPDATE CASCADE ON DELETE CASCADE;

-- ----------------------------------------------------------------------
-- Fabio Areco - 08/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `rubros`
  ADD COLUMN `icono` VARCHAR (250) NULL AFTER `rubro`,
  ADD COLUMN `estado_web` TINYINT (0) DEFAULT 1 NULL COMMENT '0:Inactivo, 1:Activo' AFTER `estado`,
  ADD COLUMN `foto` LONGTEXT  AFTER `usuario`;

-- ----------------------------------------------------------------------
-- Francisco Gómez - 10/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE rubros
  ADD COLUMN `orden` INT NULL AFTER `rubro`;
ALTER TABLE productos
  ADD COLUMN `destacar` TINYINT (1) DEFAULT 2 NULL AFTER `iva`;
ALTER TABLE configuracion
  ADD COLUMN `limite_producto` INT NULL AFTER `utilidad`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 10/11/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(94,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','7.4','Habilitado');

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(191,'Facturas a Vencer','Facturas a Vencer','./reporte-facturas-a-vencer','<i class=\"fas fa-file mt-1\"></i>','1','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Ojeda - 10/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `cajas` 
ADD COLUMN `descripcion` VARCHAR(245) NULL DEFAULT NULL AFTER `observacion`;

-- ----------------------------------------------------------------------
-- Fabio Areco - 11/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `clientes`
ADD COLUMN `ip` VARCHAR (100) NULL AFTER `puntos`,
ADD COLUMN `token` TEXT NULL AFTER `ip`,
ADD COLUMN `fecha_login` DATETIME NULL AFTER `token`,
ADD COLUMN `hora_fecha_reset` DATETIME NULL AFTER `fecha_login`,
ADD COLUMN `estado` TINYINT (1) DEFAULT 2 NULL COMMENT '1:activo 2:pendiente 3:deshabilitado' AFTER `hora_fecha_reset`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 11/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `courier` TINYINT (1) DEFAULT 0 NULL COMMENT '0:No; 1:Si' AFTER `id_delivery`;

ALTER TABLE `documentos_facturas`
  ADD COLUMN `tipo` TINYINT (1) NULL COMMENT '1:Receta; 2:Courier' AFTER `documento`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 11/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `cajas` 
ADD COLUMN `ultima_conexion` DATETIME NULL AFTER `descripcion`;

-- ----------------------------------------------------------------------
-- Fabio Areco - 11/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `clientes`
ADD COLUMN `password` VARCHAR (255) NULL AFTER `estado`,
ADD COLUMN `password_admin` VARCHAR (255) NULL AFTER `password`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 11/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `configuracion` 
ADD COLUMN `limite_caja` INT NULL DEFAULT NULL AFTER `limite_producto`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 11/11/2022
-- ----------------------------------------------------------------------
CREATE TABLE banner (
  `id_banner` INT NOT NULL AUTO_INCREMENT,
  `foto` VARCHAR (250),
  `creacion` DATETIME,
  `estado` TINYINT (1) COMMENT '0:Inactivo 1:Activo',
  PRIMARY KEY (`id_banner`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,'WEB','WEB','#','<i class=\"fas fa-desktop\"></i>','70','Habilitado');
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(193,'Banner','Banner','./banner','<i class=\"fas fa-list\"></i>','10','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Ojeda - 14/11/2022
-- ----------------------------------------------------------------------

CREATE TABLE `canjes_puntos` (
  `id_canje_punto` INT NOT NULL AUTO_INCREMENT,
  `id_cliente_punto` INT NULL,
  `id_premio` INT NULL,
  `numero` VARCHAR(245) NULL,
  `cantidad` INT NULL,
  `puntos` INT NULL,
  `ruc` VARCHAR(45) NULL,
  `razon_social` VARCHAR(245) NULL,
  `estado` TINYINT(1) NULL,
  `fecha` DATETIME NULL,
  `usuario` VARCHAR(245) NULL,
  PRIMARY KEY (`id_canje_punto`),
  INDEX `fk_clientes_puntos_canjes_puntos_idx` (`id_cliente_punto` ASC) VISIBLE,
  INDEX `fk_premios_canjes_puntos_idx` (`id_premio` ASC) VISIBLE,
  CONSTRAINT `fk_clientes_puntos_canjes_puntos`
    FOREIGN KEY (`id_cliente_punto`)
    REFERENCES `victoria_db`.`clientes_puntos` (`id_cliente_punto`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_premios_canjes_puntos`
    FOREIGN KEY (`id_premio`)
    REFERENCES `victoria_db`.`premios` (`id_premio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(185,'Canjes Premios','Canjes Premios','./canje-premios','<i class=\"fas fa-hands-helping\"></i>','11.5','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 15/11/2022
-- ----------------------------------------------------------------------
UPDATE `notas_remision_motivos` SET `descripcion` = 'TRASLADO ENTRE LOCALES DE LA MISMA EMPRESA', `nombre_corto` = 'TRASLADO' WHERE `id_nota_remision_motivo` = 1;

UPDATE `notas_remision_motivos` SET `descripcion` = 'VENTA', `nombre_corto` = 'VENTA' WHERE `id_nota_remision_motivo` = 2;

UPDATE `notas_remision_motivos` SET `descripcion` = 'CONSIGNACIÓN', `nombre_corto` = 'CONSIGNACIÓN' WHERE `id_nota_remision_motivo` = 3;

UPDATE `notas_remision_motivos` SET `descripcion` = 'DEVOLUCIÓN', `nombre_corto` = 'DEVOLUCIÓN' WHERE `id_nota_remision_motivo` = 4;

UPDATE `notas_remision_motivos` SET `descripcion` = 'TRASLADO DE BIENES PARA REPARACIÓN', `nombre_corto` = 'REPARACIÓN' WHERE `id_nota_remision_motivo` = 5;

UPDATE `notas_remision_motivos` SET `descripcion` = 'EXHIBICIÓN, DEMOSTRACIÓN', `nombre_corto` = 'EXHIBICIÓN, DEMOSTRACIÓN' WHERE `id_nota_remision_motivo` = 6;

UPDATE `notas_remision_motivos` SET `descripcion` = 'PARTICIPACIÓN EN FERIAS', `nombre_corto` = 'PARTICIPACIÓN EN FERIAS' WHERE `id_nota_remision_motivo` = 7;

UPDATE `notas_remision_motivos` SET `descripcion` = 'DONACIÓN', `nombre_corto` = 'DONACIÓN' WHERE `id_nota_remision_motivo` = 8;

UPDATE `notas_remision_motivos` SET `descripcion` = 'CANJE', `nombre_corto` = 'CANJE' WHERE `id_nota_remision_motivo` = 9;

ALTER TABLE `documentos_facturas`
  ADD COLUMN `id_doctor` INT (11) NULL AFTER `tipo`,
  ADD CONSTRAINT `fk_documentos_facturas_doctores` FOREIGN KEY (`id_doctor`) REFERENCES `doctores` (`id_doctor`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 16/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `entidades`
  ADD COLUMN `tipo` VARCHAR (45) NULL AFTER `entidad`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 17/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `configuracion`
  ADD COLUMN `limite_egreso` INT (11) NULL AFTER `limite_caja`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 17/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `configuracion`
  ADD COLUMN `alerta_nro_timbrado` INT NULL DEFAULT 0 COMMENT 'Se notifica al usuario cuando se alcanza este número' AFTER `limite_egreso`;

  -- Angel Ojeda - 17/11/2022
-- ----------------------------------------------------------------------
  ALTER TABLE `doctores` 
ADD COLUMN `ruc` VARCHAR(45) NULL AFTER `nombre_apellido`;

-- ----------------------------------------------------------------------
-- Francisco Gómez - 17/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE descuentos_proveedores
  CHANGE `porcentaje` `porcentaje` DECIMAL (12, 2) NULL;
  
ALTER TABLE descuentos_proveedores_productos
  CHANGE `porcentaje` `porcentaje` DECIMAL (12, 2) NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `orden_pagos_archivos`
  ADD COLUMN `nro_recibo` VARCHAR (45) NULL AFTER `archivo`;

-- ----------------------------------------------------------------------
-- Francisco Gómez - 18/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE descuentos_pagos_filtros
  CHANGE `porcentaje` `porcentaje` DECIMAL (12, 2) NULL;

ALTER TABLE descuentos_pagos_productos
  CHANGE `porcentaje` `porcentaje` DECIMAL (12, 2) NULL;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 21/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `solicitudes_depositos`
  ADD COLUMN `id_proveedor` INT NULL AFTER `id_sucursal`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/11/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(71,'Stock Valorizado','Stock Valorizado','./stock-valorizado','<i class=\"fas fa-calculator mt-1\"></i>','3','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Gimenez - 22/11/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(NULL,'Notas De Remisión','Notas De Remisión','#','<i class=\"fas fa-edit mt-1\"></i>','11','Habilitado');

UPDATE menus SET id_menu_padre=197, menu='Productos', titulo='Productos', url='./nota-remision', icono='<i class=\"fas fa-edit mt-1\"></i>', orden='1', estado='Habilitado' WHERE id_menu = '136';

UPDATE menus SET id_menu_padre=197, menu='Remisiones', titulo='Remisiones', url='./administrar-notas-remision', icono='<i class=\"fas fa-list\"></i>', orden='3', estado='Habilitado' WHERE id_menu = '148';

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(197,'Insumos','Insumos','./remision-insumos','<i class=\"fas fa-edit mt-1\"></i>','2','Habilitado');

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 21/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `caja_chica_depositos`
  CHANGE `nro_cuenta` `id_cuenta` INT (11) NULL AFTER `id_banco`;
  
ALTER TABLE `productos`
  ADD COLUMN `comision_concepto` VARCHAR (45) NULL AFTER `comision`;

ALTER TABLE `facturas_productos`
  ADD COLUMN `comision_concepto` VARCHAR (45) NULL AFTER `comision`;

  
-- Angel Ojeda - 21/11/2022
-- ----------------------------------------------------------------------

  INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(185,'Canje Premios','Canje Premios','./canje-premios','<i class=\"fas fa-exchange-alt\"></i>','11.5','Habilitado');

-- ----------------------------------------------------------------------
-- Angel Ojeda - 22/11/2022
-- ----------------------------------------------------------------------
  INSERT INTO `tipos_gastos` (`id_tipo_gasto`, `id_sub_tipo_gasto`, `nombre`, `estado`) VALUES ('15', '3', 'PREMIO', '1');
 
-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/11/2022
-- ----------------------------------------------------------------------
CREATE TABLE `notas_remision_insumo` (
  `id_nota_remision_insumo` INT (11) NOT NULL AUTO_INCREMENT,
  `id_nota_remision` INT (11),
  `id_producto_insumo` INT (11),
  `codigo` BIGINT (20),
  `producto` VARCHAR (255),
  `cantidad` INT (11),
  `cantidad_recibida` INT (11),
  `observacion` VARCHAR (255),
  PRIMARY KEY (`id_nota_remision_insumo`),
  KEY `fk_notas_remision_notas_remision_insumos` (`id_nota_remision`),
  KEY `fk_productos_notas_remision_insumos` (`id_producto_insumo`),
  CONSTRAINT `fk_notas_remision_notas_remision_insumos` FOREIGN KEY (`id_nota_remision`) REFERENCES `notas_remision` (`id_nota_remision`) ON DELETE CASCADE,
  CONSTRAINT `fk_productos_notas_remision_insumos` FOREIGN KEY (`id_producto_insumo`) REFERENCES `productos_insumo` (`id_producto_insumo`) ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `notas_remision`
  ADD COLUMN `tipo_remision` TINYINT (1) NULL COMMENT '0=producto, 1=insumo' AFTER `fecha_actualizacion`;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 21/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `configuracion`
  ADD COLUMN `logo_close` VARCHAR (100) NULL AFTER `logo_horizontal`;

UPDATE `configuracion` SET `logo_close` = 'dist/images/logo-close.png' WHERE `id_configuracion` = 1;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 24/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `canjes_puntos` 
ADD COLUMN `observacion` VARCHAR(245) NULL AFTER `razon_social`;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 24/11/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(185,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','13','Habilitado');

ALTER TABLE `canjes_puntos`
  DROP COLUMN `id_cliente_punto`,
  DROP COLUMN `id_premio`,
  ADD COLUMN `id_cliente` INT NULL AFTER `puntos`,
  CHANGE `estado` `estado` TINYINT (1) NULL COMMENT '1:Procesado; 2:Anulado',
  DROP INDEX `fk_clientes_puntos_canjes_puntos_idx`,
  DROP INDEX `fk_premios_canjes_puntos_idx`,
  DROP FOREIGN KEY `fk_clientes_puntos_canjes_puntos`,
  DROP FOREIGN KEY `fk_premios_canjes_puntos`,
  ADD CONSTRAINT `fk_clientes_canjes_puntos` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);
CREATE TABLE `canjes_puntos_premios` (
  `id_canje_punto_premio` INT NOT NULL AUTO_INCREMENT,
  `id_canje_punto` INT,
  `id_premio` INT,
  `costo` INT,
  `cantidad` INT,
  `puntos` INT,
  `total` INT,
  PRIMARY KEY (`id_canje_punto_premio`),
  CONSTRAINT `fk_canjes_puntos_canjes_puntos_premios` FOREIGN KEY (`id_canje_punto`) REFERENCES `canjes_puntos` (`id_canje_punto`) ON DELETE CASCADE,
  CONSTRAINT `fk_premios_canjes_puntos_premios` FOREIGN KEY (`id_premio`) REFERENCES `premios` (`id_premio`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
CREATE TABLE `canjes_puntos_utilizados` (
  `id_canje_punto_utilizado` INT NOT NULL AUTO_INCREMENT,
  `id_canje_punto` INT,
  `id_cliente_punto` INT,
  `utilizados` INT,
  PRIMARY KEY (`id_canje_punto_utilizado`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
ALTER TABLE `canjes_puntos_utilizados` 
ADD INDEX `fk_canjes_punto_canjes_puntos_utilizados_idx` (`id_canje_punto` ASC) VISIBLE,
ADD INDEX `fk_clientes_puntos_canjes_puntos_utilizados_idx` (`id_cliente_punto` ASC) VISIBLE;
;
ALTER TABLE `canjes_puntos_utilizados` 
ADD CONSTRAINT `fk_canjes_punto_canjes_puntos_utilizados`
  FOREIGN KEY (`id_canje_punto`)
  REFERENCES `canjes_puntos` (`id_canje_punto`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_clientes_puntos_canjes_puntos_utilizados`
  FOREIGN KEY (`id_cliente_punto`)
  REFERENCES `canjes_puntos` (`id_canje_punto`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

-- ----------------------------------------------------------------------
-- Angel Ojeda - 25/11/2022
-- ----------------------------------------------------------------------

ALTER TABLE `canjes_puntos_utilizados` 
DROP FOREIGN KEY `fk_clientes_puntos_canjes_puntos_utilizados`;
ALTER TABLE `canjes_puntos_utilizados` 
ADD INDEX `fk_clientes_puntos_canjes_puntos_utilizados_idx` (`id_cliente_punto` ASC) VISIBLE,
DROP INDEX `fk_clientes_puntos_canjes_puntos_utilizados_idx` ;
;
ALTER TABLE `canjes_puntos_utilizados` 
ADD CONSTRAINT `fk_clientes_puntos_canjes_puntos_utilizados`
  FOREIGN KEY (`id_cliente_punto`)
  REFERENCES `clientes_puntos` (`id_cliente_punto`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;
  
-- ----------------------------------------------------------------------
-- En DEV - 25/11/2022
-- ----------------------------------------------------------------------

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 28/11/2022
-- ----------------------------------------------------------------------
CREATE TABLE `descuentos_pagos_remates` (
  `id_descuento_pago_remate` INT NOT NULL AUTO_INCREMENT,
  `id_descuento_pago` INT,
  `id_producto` INT,
  `porcentaje` DECIMAL (12, 2),
  PRIMARY KEY (`id_descuento_pago_remate`),
  CONSTRAINT `fk_productos_descuentos_remates_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_remates` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 29/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  ADD COLUMN `remate` TINYINT (1) NULL COMMENT '0:No; 1:Si' AFTER `precio`,
  ADD COLUMN `tipo_descuento` VARCHAR (45) NULL COMMENT 'DPP: Descuentos Proveedor Producto; DCR: Descuentos Campañas Remates; DCP: Descuentos Campañas Productos; DCF:Descuentos Campañas Filtros; DP:Descuentos Proveedor;' AFTER `remate`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 01/12/2022
-- ----------------------------------------------------------------------
UPDATE `rubros` SET `rubro` = 'HIGIENE ÍNTIMA' WHERE `id_rubro` = '8'; 

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 02/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `reimpreso` TINYINT (1) DEFAULT 0 NULL COMMENT '0:No; 1:Si' AFTER `courier`,
  ADD COLUMN `usuario_reimpresion` VARCHAR (45) NULL AFTER `reimpreso`,
  ADD COLUMN `fecha_reimpresion` DATETIME NULL AFTER `usuario_reimpresion`;
ALTER TABLE `facturas`
  CHANGE `reimpreso` `impresiones` INT DEFAULT 0 NULL,
  CHANGE `usuario_reimpresion` `usuario_impresion` VARCHAR (45) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `fecha_reimpresion` `fecha_impresion` DATETIME NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/11/2022
-- ----------------------------------------------------------------------
ALTER TABLE `orden_pagos`
  ADD COLUMN `cuenta_destino` VARCHAR (30) DEFAULT '0' NULL AFTER `nro_cheque`;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 02/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `editar_cobros` TINYINT (1) DEFAULT 0 NULL COMMENT '0:No; 1:Si' AFTER `fecha_impresion`;

-- ----------------------------------------------------------------------
-- En DEV - 02/12/2022
-- ----------------------------------------------------------------------

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 05/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `descuentos_pagos_remates`
  ADD COLUMN `id_lote` INT NULL AFTER `id_producto`,
  ADD CONSTRAINT `fk_lotes_descuentos_pagos_remates` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`);

ALTER TABLE `descuentos_pagos_remates`
  DROP FOREIGN KEY `fk_descuentos_pagos_descuentos_pagos_remates`;
ALTER TABLE `descuentos_pagos_remates`
  ADD CONSTRAINT `fk_descuentos_pagos_descuentos_pagos_remates` FOREIGN KEY (`id_descuento_pago`) REFERENCES `descuentos_pagos` (`id_descuento_pago`) ON DELETE CASCADE;


  -- ----------------------------------------------------------------------
-- Angel Ojeda - 05/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `stock_premios_historial` 
ADD COLUMN `cantidad` INT NULL DEFAULT NULL AFTER `premio`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 07/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `orden_pagos`
  ADD COLUMN `fecha_pago` DATE NULL AFTER `fecha`;

ALTER TABLE `orden_pagos_archivos`
  CHANGE `nro_recibo` `nro_documento` VARCHAR (45) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  ADD COLUMN `tipo_documento` INT NULL COMMENT '1= Recibo, 2=Comprobante' AFTER `nro_documento`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 09/12/2022
-- ----------------------------------------------------------------------
INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) 
VALUES(197,'Informes','Informes','#','<i class=\"fas fa-file-alt mt-1\"></i>','4','Habilitado');

UPDATE menus SET id_menu_padre=176, menu='Costo de Insumos', titulo='Costo de Insumos', url='./reporte-costo-insumos', icono='<i class=\"fas fa-file-alt mt-1\"></i>', orden='2', estado='Habilitado' WHERE id_menu = '202';

-- ----------------------------------------------------------------------
-- En DEV - 14/12/2022
-- ----------------------------------------------------------------------

-- ----------------------------------------------------------------------
-- Angel Gimenez - 19/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `notas_remision_insumo`
  ADD COLUMN `costo` INT (11) NULL AFTER `cantidad_recibida`;

  -----------------------------------------------------------------------
--Ruben Britos 20/12/2022
ALTER TABLE `productos`    
  ADD COLUMN  `fraccion` TINYINT (1);

 -----------------------------------------------------------------------
  UPDATE productos SET fraccion=IF(cantidad_fracciones > 1, 1, 0);

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 21/12/2022
-- ----------------------------------------------------------------------
INSERT INTO `sub_tipos_gastos` (`id_sub_tipo_gasto`, `nombre`) VALUES(4, 'CAJA CHICA');

INSERT INTO `tipos_gastos` (`id_sub_tipo_gasto`,`nombre`,`estado`) VALUES(4,'CAJA CHICA',1);

ALTER TABLE `gastos` 
  CHANGE `deducible` `deducible` TINYINT (1) DEFAULT 1 NULL COMMENT '1-Deducible, 2-No deducible';

UPDATE `gastos` SET `deducible` = 1 WHERE `deducible` IS NULL;

UPDATE `gastos` SET `id_tipo_gasto` = (SELECT id_tipo_gasto FROM tipos_gastos WHERE id_sub_tipo_gasto=4) WHERE `id_tipo_gasto` IS NULL;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 23/12/2022
-- ----------------------------------------------------------------------
CREATE TABLE `facturas_puntos_utilizados` (
  `id_factura_punto_utilizado` INT NOT NULL AUTO_INCREMENT,
  `id_factura` BIGINT(20),
  `id_cliente_punto` INT,
  `utilizados` INT,
  PRIMARY KEY (`id_factura_punto_utilizado`),
  CONSTRAINT `fk_facturas_facturas_puntos_utilizados` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`),
  CONSTRAINT `fk_clientes_puntos_facturas_puntos_utilizados` FOREIGN KEY (`id_cliente_punto`) REFERENCES `clientes_puntos` (`id_cliente_punto`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- En DEV - 23/12/2022
-- ----------------------------------------------------------------------


------------------------------------------------------------------------
-- Ruben Britos - 26/12/2022
-- ----------------------------------------------------------------------

CREATE TABLE `productos_clasificaciones` (
  `id_producto_clasificacion` INT NOT NULL AUTO_INCREMENT,
  `id_clasificacion` INT,
  `id_producto` INT,
  PRIMARY KEY (`id_producto_clasificacion`),
  CONSTRAINT `fk_prodcutos_productos_clasificaciones` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_clasificaciones_productos_productos_clasificaciones` FOREIGN KEY (`id_clasificacion`) REFERENCES`clasificaciones_productos` (`id_clasificacion_producto`) ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_estonian_ci;


INSERT INTO productos_clasificaciones (id_producto,id_clasificacion)
SELECT id_producto, id_clasificacion FROM productos WHERE id_clasificacion IS NOT NULL;



ALTER TABLE `productos`
  DROP COLUMN `id_clasificacion`,
  DROP INDEX `fk_id_clasificacion`,
  DROP FOREIGN KEY `fk_id_clasificacion`;

------------------------------------------------------------------------
--Angel Ojeda - 26/12/2022
-- ----------------------------------------------------------------------

  ALTER TABLE `clientes_puntos`   
  ADD COLUMN `fecha_actualizacion` DATETIME NULL  COMMENT 'sirve para tomar de referencia al momento de actualizar por las fechas y dar de baja los que pasen los dias de la configuracion' AFTER `estado`;

------------------------------------------------------------------------
--Sebastian Alvarenga - 26/12/2022
-- ----------------------------------------------------------------------

ALTER TABLE `gastos_fijos_sub_tipos`
  ADD COLUMN `id_proveedor` INT NULL AFTER `id_gasto_fijo_tipo`,
  ADD CONSTRAINT `fk_proveedores_gastos_fijos_sub_tipos` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 27/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `gastos_fijos_sub_tipos`
  DROP FOREIGN KEY `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos`;

DROP TABLE gastos_fijos_tipos

ALTER TABLE `gastos_fijos_sub_tipos`
  DROP FOREIGN KEY `fk_proveedores_gastos_fijos_sub_tipos`;

DROP TABLE gastos_fijos

RENAME TABLE `gastos_fijos_sub_tipos` TO `gastos_fijos`;

ALTER TABLE `gastos`
  ADD COLUMN `id_gastos_fijos` INT NULL AFTER `id_caja_chica`,
  ADD CONSTRAINT `fk_gastos_gastos_fijos` FOREIGN KEY (`id_gastos_fijos`) REFERENCES `gastos_fijos` (`id_gasto_fijo_sub_tipo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 28/12/2022
-- ----------------------------------------------------------------------
CREATE TABLE `clientes_carrito` (
  `id_cliente_carrito` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT,
  `id_producto` INT,
  `cantidad` INT,
  `fecha` DATETIME,
  `fecha_actualizacion` DATETIME,
  PRIMARY KEY (`id_cliente_carrito`),
  CONSTRAINT `fk_clientes_clientes_carrito` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `fk_productos_clientes_carrito` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
ALTER TABLE `clientes_carrito`
  ADD UNIQUE INDEX `u_clientes_carrito_id_clientte_id_producto` (`id_cliente`, `id_producto`);

CREATE TABLE `ordenes` (
  `id_orden` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT,
  `ruc` VARCHAR (255),
  `razon_social` VARCHAR (255),
  `direccion` VARCHAR (255),
  `referencia` VARCHAR (255),
  `telefono` VARCHAR (45),
  `email` VARCHAR (255),
  `total` INT,
  `cantidad` INT,
  `fecha` DATETIME,
  `fecha_actualizacion` DATETIME,
  `usuario_actualizacion` VARCHAR (45),
  `estado` TINYINT (1) COMMENT '0: Pendiente; 1: Procesado; 2: Anulado',
  PRIMARY KEY (`id_orden`),
  CONSTRAINT `fk_clientes_ordenes` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

------------------------------------------------------------------------
--Sebastian Alvarenga - 29/12/2022
-- ----------------------------------------------------------------------
ALTER TABLE `gastos_fijos`
  CHANGE `id_gasto_fijo_tipo` `id_tipo_gasto` INT (11) NULL,
  ADD CONSTRAINT `fk_gstos_fijos_tipos_gastos` FOREIGN KEY (`id_tipo_gasto`) REFERENCES `tipos_gastos` (`id_tipo_gasto`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `gastos`
  CHANGE `id_gastos_fijos` `id_gasto_fijo` INT (11) NULL;



------------------------------------------------------------------------
--Ruben Britos - 02/01/2023
-- ----------------------------------------------------------------------

  ALTER TABLE `gastos_fijos`
  DROP COLUMN `id_tipo_gasto`,

  DROP INDEX `fk_gastos_fijos_tipos_gastos_fijos_sub_tipos`;
ALTER TABLE `gastos_fijos`
  DROP COLUMN `id_proveedor`,

  DROP INDEX `fk_proveedores_gastos_fijos_sub_tipos`;
ALTER TABLE `gastos`
  DROP COLUMN `id_gasto_fijo`,

  DROP INDEX `fk_gastos_gastos_fijos`,
  DROP FOREIGN KEY `fk_gastos_gastos_fijos`;
DROP TABLE gastos_fijos

------------------------------------------------------------------------
--Sebastian Alvarenga - 04/01/2023
-- ----------------------------------------------------------------------
CREATE TABLE `delivery` (
  `id_delivery` int(11) NOT NULL AUTO_INCREMENT,
  `id_distrito` int(11) DEFAULT NULL,
  `precio` int(11) DEFAULT NULL,
  `fecha_carga` datetime DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_delivery`),
  KEY `fk_delivery_distritos` (`id_distrito`),
  CONSTRAINT `fk_delivery_distritos` FOREIGN KEY (`id_distrito`) REFERENCES `distritos` (`id_distrito`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4


ALTER TABLE `ordenes`
  ADD COLUMN `metodo_pago` TINYINT (1) NULL COMMENT '1: Tarjeta de Credito, 2:Contraentrega' AFTER `cantidad`,
  ADD COLUMN `delivery` TINYINT (1) DEFAULT 1 NULL COMMENT '0: no, 1:si' AFTER `usuario_actualizacion`,
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 0 NULL COMMENT '0: Pendiente; 1: Procesado; 2: Anulado';

CREATE TABLE `ordenes_detalles` (
  `id_orden_detalle` INT NOT NULL AUTO_INCREMENT,
  `id_orden` INT,
  `id_producto` INT,
  `producto` VARCHAR (255),
  `cantidad` INT,
  `precio` INT,
  `total_orden` INT,
  PRIMARY KEY (`id_orden_detalle`),
  CONSTRAINT `fk_ordenes_detalles_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON UPDATE RESTRICT ON DELETE RESTRICT
);

ALTER TABLE `ordenes`
  ADD COLUMN `id_delivery` INT NULL AFTER `delivery`,
  ADD COLUMN `total_delivery` INT NULL AFTER `id_delivery`,
  ADD CONSTRAINT `fk_delivery_ordenes` FOREIGN KEY (`id_delivery`) REFERENCES `victoria_db`.`delivery` (`id_delivery`) ON UPDATE RESTRICT ON DELETE RESTRICT;

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 06/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `ordenes_detalles`
  ADD CONSTRAINT `fk_productos_ordenes_detalles` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

-- ----------------------------------------------------------------------
-- Angel Gimenez - 09/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `banner`
  ADD COLUMN `link` VARCHAR (250) NULL AFTER `foto`;
------------------------------------------------------------------------
--Sebastian Alvarenga - 09/01/2023
-- ----------------------------------------------------------------------
CREATE TABLE `libro_cuentas` (
  `id_libro_cuenta` int(11) NOT NULL AUTO_INCREMENT,
  `id_padre` int(11) DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `cuenta` int(11) DEFAULT NULL,
  `denominacion` varchar(55) DEFAULT NULL,
  `saldo` int(11) DEFAULT NULL,
  `usuario` varchar(55) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id_libro_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4

ALTER TABLE `libro_cuentas`
  CHANGE `saldo` `saldo` INT (11) DEFAULT 0 NULL;
------------------------------------------------------------------------
--Sebastian Alvarenga - 10/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `libro_cuentas`
  ADD COLUMN `tipo_cuenta` TINYINT (1) NULL COMMENT '1: Activo, 2: Pasivo, 3:Ingresos, 4:Egresos' AFTER `cuenta`;

INSERT INTO `libro_cuentas` (`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(1,1,1,'ACTIVO','admin',NOW());

INSERT INTO `libro_cuentas` (`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(1,2,2,'PASIVO','admin',NOW());

INSERT INTO `libro_cuentas` (`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(1,3,5,'PATRIMONIO NETO','admin',NOW());

INSERT INTO `libro_cuentas` (`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(1,4,3,'INGRESOS','admin',NOW());

INSERT INTO `libro_cuentas` (`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(1,5,4,'EGRESOS','admin',NOW());


CREATE TABLE `libro_diario` (
  `id_libro_diario` INT NOT NULL AUTO_INCREMENT,
  `fecha` DATE,
  `nro_asiento` VARCHAR (255),
  `importe` DECIMAL (10, 2),
  `descripcion` TEXT,
  `usuario` VARCHAR (55),
  `fecha_creacion` DATETIME,
  `estado` TINYINT (1) DEFAULT 1 COMMENT '0:Anulado, 1:Activo',
  PRIMARY KEY (`id_libro_diario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `libro_diario_detalles` (
  `id_libro_detalle` INT NOT NULL AUTO_INCREMENT,
  `id_libro_diario` INT,
  `id_libro_cuento` INT,
  `concepto` TEXT,
  `debe` DECIMAL (10, 2),
  `haber` DECIMAL (10, 2),
  PRIMARY KEY (`id_libro_detalle`),
  CONSTRAINT `fk_libro_diario_detalle_libro_diario` FOREIGN KEY (`id_libro_diario`) REFERENCES `libro_diario` (`id_libro_diario`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `fk_libro_diario_detalles_libro_cuentas` FOREIGN KEY (`id_libro_cuento`) REFERENCES `libro_cuentas` (`id_libro_cuenta`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `libro_diario_periodo` (
  `id_libro_diario_periodo` INT NOT NULL AUTO_INCREMENT,
  `desde` DATE,
  `hasta` DATE,
  `usuario` VARCHAR (55),
  `fecha` DATETIME,
  `estado` TINYINT (1) DEFAULT 1 COMMENT '0:Inactivo, 1:Activo',
  PRIMARY KEY (`id_libro_diario_periodo`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `libro_diario`
  ADD COLUMN `id_libro_diario_periodo` INT NULL AFTER `id_libro_diario`,
  ADD CONSTRAINT `fk_libro_diario_libro_diario_periodo` FOREIGN KEY (`id_libro_diario_periodo`) REFERENCES `libro_diario_periodo` (`id_libro_diario_periodo`) ON UPDATE RESTRICT ON DELETE RESTRICT;

------------------------------------------------------------------------
--Angel Ojeda- 11/01/2023
-- ----------------------------------------------------------------------

  ALTER TABLE `ordenes` 
ADD COLUMN `contraentrega_monto` INT NULL AFTER `metodo_pago`;

CREATE TABLE `clientes_direcciones` (
  `id_cliente_direccion` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NULL,
  `direccion` VARCHAR(255) NULL,
  `longitud` VARCHAR(255) NULL,
  `latitud` VARCHAR(255) NULL,
  `referencia` VARCHAR(255) NULL,
  `fecha` DATETIME NULL,
  PRIMARY KEY (`id_cliente_direccion`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE `clientes_direcciones` 
ADD INDEX `fk_clientes_clientes_direcciones_idx` (`id_cliente` ASC) VISIBLE;
;
ALTER TABLE `clientes_direcciones` 
ADD CONSTRAINT `fk_clientes_clientes_direcciones`
  FOREIGN KEY (`id_cliente`)
  REFERENCES `clientes` (`id_cliente`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES(NULL,'Motivo Asiento','Motivo Asiento','./motivo-asiento','<i class=\"fas fa-book-open\"></i>','80.11.2','Habilitado');
UPDATE menus SET id_menu_padre=213, menu='Motivo Asiento', titulo='Motivo Asiento', url='./motivos-asientos', icono='<i class=\"fas fa-book-open\"></i>', orden='80.12', estado='Habilitado' WHERE id_menu = '215';

------------------------------------------------------------------------
--Sebastian Alvarenga - 11/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `libro_diario_periodo`
  ADD COLUMN `nombre` VARCHAR (255) NULL AFTER `id_libro_diario_periodo`;

CREATE TABLE `motivos_asiento` (
  `id_motivo_asiento` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR (255),
  `usuario` VARCHAR (255),
  `fecha` DATETIME,
  `estado` TINYINT (1) COMMENT '0:Inactivo, 1:Activo',
  PRIMARY KEY (`id_motivo_asiento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `motivos_asiento`
  CHANGE `estado` `estado` TINYINT (1) DEFAULT 1 NULL COMMENT '0:Inactivo, 1:Activo';

------------------------------------------------------------------------
--Sebastian Alvarenga - 12/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `clientes`
  DROP COLUMN `direccion`,
  DROP COLUMN `referencia`,
  DROP COLUMN `longitud`,
  DROP COLUMN `latitud`;

------------------------------------------------------------------------
--Angel Ojeda - 13/01/2023
-- ----------------------------------------------------------------------

  ALTER TABLE `ordenes` 
ADD COLUMN `nota_orden` VARCHAR(255) NULL AFTER `contraentrega_monto`;
------------------------------------------------------------------------
--Sebastian Alvarenga - 17/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `id_orden` INT NULL COMMENT 'Si tiene un id asignado entonces es una venta de la pagina web' AFTER `id_nota_credito`;

ALTER TABLE `ordenes`
  ADD COLUMN `observacion` VARCHAR (255) NULL AFTER `estado`;

  ------------------------------------------------------------------------
--Ruben Britos - 18/01/2023
-- ----------------------------------------------------------------------

  ALTER TABLE `calendario`
  CHANGE `mes` `mes` VARCHAR (10) CHARSET utf8 COLLATE utf8_general_ci NOT NULL;
  
UPDATE calendario SET mes='septiembre' WHERE mes_nro = 9;

------------------------------------------------------------------------
--Sebastian Alvarenga - 18/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `sucursales`
  ADD COLUMN `tipo_web` TINYINT DEFAULT 0 NULL COMMENT '0-No, 1-Si' AFTER `deposito`;

INSERT INTO `sucursales` (`nombre_empresa`,`ruc`,`razon_social`,`sucursal`,`direccion`,`id_distrito`,`id_pais`,`telefono`,`email`,`id_moneda`,`fecha`,`deposito`,`tipo_web`,`usuario`,`estado`)
VALUES('SANTA VICTORIA','','SANTA VICTORIA','SUCURSAL WEB','',71,172,'','',1,NOW(),0,1,'admin',1);

------------------------------------------------------------------------
--Sebastian Alvarenga - 24/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `libro_diario`
  ADD COLUMN `id_comprobante` INT NULL AFTER `id_libro_diario_periodo`,
  ADD CONSTRAINT `fk_libro_diario_motivos_asiento` FOREIGN KEY (`id_comprobante`) REFERENCES `motivos_asiento` (`id_motivo_asiento`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `libro_diario_detalles`
  CHANGE `id_libro_cuento` `id_libro_cuenta` INT (11) NULL;

------------------------------------------------------------------------
--Sebastian Alvarenga - 25/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `libro_diario_detalles`
  CHANGE `debe` `debe` INT NULL,
  CHANGE `haber` `haber` INT NULL;
------------------------------------------------------------------------
--Sebastian Alvarenga - 26/01/2023
-- ----------------------------------------------------------------------
ALTER TABLE `libro_cuentas`
  DROP COLUMN `saldo`;
  
ALTER TABLE `libro_diario`
  ADD COLUMN `contraasiento` VARCHAR (255) NULL COMMENT 'Se guarda el nro de asiento del cual se realiza el contra asiento' AFTER `descripcion`;
------------------------------------------------------------------------
--Sebastian Alvarenga - 09/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  ADD COLUMN `tipo` TINYINT (1) NULL COMMENT '1-Producto, 2-Servicio' AFTER `fraccion`;

ALTER TABLE `productos`
  CHANGE `tipo` `tipo` TINYINT (1) DEFAULT 1 NULL COMMENT '1-Producto, 2-Servicio';
------------------------------------------------------------------------
--Sebastian Alvarenga - 10/02/2023
-- ----------------------------------------------------------------------
CREATE TABLE `movimientos_bancarios` (
  `id_movimiento_bancario` INT NOT NULL AUTO_INCREMENT,
  `id_banco` INT,
  `fecha_comprobante` DATE,
  `tipo_movimiento` INT,
  `nro_comprobante` VARCHAR (250),
  `importe` INT,
  `concepto` VARCHAR (250),
  `observacion` VARCHAR (250),
  `fecha_creacion` DATETIME,
  `usuario` VARCHAR (250),
  `estado` TINYINT (1) DEFAULT 1 COMMENT '0-Inactivo, 1-Activo',
  PRIMARY KEY (`id_movimiento_bancario`),
  CONSTRAINT `fk_movimientos_bancarios_bancos` FOREIGN KEY (`id_banco`) REFERENCES `bancos` (`id_banco`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

UPDATE `productos` SET `tipo` = 1 WHERE `tipo` IS NULL;
------------------------------------------------------------------------
--Sebastian Alvarenga - 13/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `facturas`
  ADD COLUMN `id_asiento` INT NULL AFTER `fecha`,
  ADD CONSTRAINT `fk_libro_diario_facturas` FOREIGN KEY (`id_asiento`) REFERENCES `libro_diario` (`id_libro_diario`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 15/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `gastos`
  ADD COLUMN `id_asiento` INT NULL AFTER `id_caja_chica`,
  ADD CONSTRAINT `fk_gastos_libro_diario` FOREIGN KEY (`id_asiento`) REFERENCES `libro_diario` (`id_libro_diario`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 17/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `gastos`
  ADD COLUMN `id_libro_cuentas` INT NULL AFTER `id_asiento`,
  ADD CONSTRAINT `fk_gastos_libros_cuentas` FOREIGN KEY (`id_asiento`) REFERENCES `libro_cuentas` (`id_libro_cuenta`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 20/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `orden_pagos`
  ADD COLUMN `id_libro_diario` INT NULL AFTER `id_caja_chica_sucursal`,
  ADD CONSTRAINT `fk_orden_pagos_libro_diario` FOREIGN KEY (`id_libro_diario`) REFERENCES `libro_diario` (`id_libro_diario`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 28/02/2023
-- ----------------------------------------------------------------------
ALTER TABLE `liquidacion_salarios`
  ADD COLUMN `id_asiento` INT NULL AFTER `id_liquidacion`,
  ADD CONSTRAINT `fk_liquidacion_salarios_libro_diario` FOREIGN KEY (`id_asiento`) REFERENCES `libro_diario` (`id_libro_diario`) ON UPDATE RESTRICT ON DELETE RESTRICT;
------------------------------------------------------------------------
--Sebastian Alvarenga - 24/03/2023
-- ----------------------------------------------------------------------

INSERT INTO `libro_cuentas` (`id_libro_cuenta`,`id_padre`,`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(292,7,(NULL),111400,1,'BANCO ITAU CAJA DE AHORRO','admin',NOW());

INSERT INTO `libro_cuentas` (`id_libro_cuenta`,`id_padre`,`nivel`,`cuenta`,`tipo_cuenta`,`denominacion`,`usuario`,`fecha`)
VALUES(293,7,(NULL),111700,1,'BANCO FAMILIAR','admin',NOW());

INSERT INTO `bancos` (`id_banco`,`ruc`,`banco`,`estado`,`usuario`,`fecha`)
VALUES(6,'80002201-7','BANCO ITAU CAJA DE AHORRO',1,'admin',NOW());

INSERT INTO `bancos_cuentas` (`id_cuenta`,`id_banco`,`cuenta`,`estado`)
VALUES(6,6,'1111111111',0);

-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 04/04/2023
-- ----------------------------------------------------------------------
ALTER TABLE `gastos`
  CHARSET = utf8mb4,
  COLLATE = utf8mb4_general_ci;

ALTER TABLE `gastos`
  DROP FOREIGN KEY `fk_gastos_libros_cuentas`;
ALTER TABLE `gastos`
  ADD CONSTRAINT `fk_gastos_libro_cuentas` FOREIGN KEY (`id_libro_cuentas`) REFERENCES `victoria_db`.`libro_cuentas` (`id_libro_cuenta`);


-- ----------------------------------------------------------------------
-- Daniel Insaurralde - 17/04/2023
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  CHANGE `comision` `comision` DECIMAL (13, 2) DEFAULT 0 NULL;

-- ----------------------------------------------------------------------
-- Angel gimenez - 17/04/2023
-- ----------------------------------------------------------------------
ALTER TABLE `facturas_productos`
  CHANGE `comision` `comision` DECIMAL (12, 2) DEFAULT 0 NULL;

-- ----------------------------------------------------------------------
-- Sebastian Alvarenga - 27/04/2023
-- ----------------------------------------------------------------------
ALTER TABLE `productos`
  CHANGE `codigo` `codigo` VARCHAR (255) NULL;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/05/2023
-- ----------------------------------------------------------------------
ALTER TABLE `lotes`
  DROP INDEX `LOTE_UNICO`;


-- Angel Ojeda - 18/05/2023
-- ----------------------------------------------------------------------

--Realizar la siguiente consulta en produccion para poner todos los nombres en mayusculas
UPDATE clientes SET razon_social = UPPER(razon_social);

--Verificar esa tabla si cuenta con auto-increment 
ALTER TABLE `orden_pagos` 
CHANGE COLUMN `id_pago` `id_pago` INT(11) NOT NULL AUTO_INCREMENT ;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 19/05/2023
-- ----------------------------------------------------------------------
UPDATE productos_proveedores SET proveedor_principal = 1 WHERE id_producto = 29357;

-- ----------------------------------------------------------------------
-- Robert Romero - 19/05/2023
-- ----------------------------------------------------------------------

-- Agrega el costo total para el caso en que recepcione por completo el producto
ALTER TABLE ordenes_compras_productos ADD COLUMN total_costo INT;

-- Actualiza registro antigüos para evitar nulos
UPDATE ordenes_compras_productos SET total_costo = (costo * cantidad);

-- ----------------------------------------------------------------------
-- Arturo Nuñez - 03/07/2023
-- ----------------------------------------------------------------------
ALTER TABLE `productos` ADD COLUMN `usuario_modifica` VARCHAR(45) NULL AFTER `usuario`; 