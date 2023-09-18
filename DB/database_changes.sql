-- ----------------------------------------------------------------------
-- Angel Gimenez - 21/08/2023
-- ----------------------------------------------------------------------
CREATE TABLE `carreras` (
  `id_carrera` INT NOT NULL AUTO_INCREMENT,
  `carrera` VARCHAR (250),
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_carrera`)
) ENGINE = INNODB;

CREATE TABLE `areas` (
  `id_area` INT NOT NULL AUTO_INCREMENT,
  `id_area_superior` INT,
  `area` VARCHAR (255),
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_area`)
) ENGINE = INNODB;

CREATE TABLE `categorias` (
  `id_categoria` INT NOT NULL AUTO_INCREMENT,
  `id_categoria_superior` INT,
  `categoria` VARCHAR (10),
  `cargo` VARCHAR (255),
  `salario` INT,
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_categoria`)
) ENGINE = INNODB;

CREATE TABLE `tipo_organizacion` (
  `id_tipo` INT NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR (250),
  PRIMARY KEY (`id_tipo`)
) ENGINE = INNODB;

INSERT INTO `tipo_organizacion` (`tipo`) VALUES
('UNIVERSIDAD'),
('ESCUELA/COLEGIO'),
('INSTITUTO'),
('EMPRESA'),
('INSTITUCION');

CREATE TABLE `sector` (
  `id_sector` INT NOT NULL AUTO_INCREMENT,
  `sector` VARCHAR (250),
  PRIMARY KEY (`id_sector`)
) ENGINE = INNODB;

INSERT INTO `sector` (`sector`) VALUES
('PRIVADA'),
('PUBLICA'),
('ONG');

CREATE TABLE `organizacion` (
  `id_organizacion` INT NOT NULL AUTO_INCREMENT,
  `id_tipo` INT,
  `id_sector` INT,
  `id_pais` INT,
  `organizacion` VARCHAR (255),
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_organizacion`)
) ENGINE = INNODB;

CREATE TABLE `bonificaciones` (
  `id_bonificacion` INT NOT NULL AUTO_INCREMENT,
  `concepto` VARCHAR (255),
  `porcentaje` INT,
  `observacion` TEXT,
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_bonificacion`)
) ENGINE = INNODB;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 22/08/2023
-- ----------------------------------------------------------------------
CREATE TABLE `organigrama` (
  `id_cargo` INT NOT NULL AUTO_INCREMENT,
  `id_area` INT,
  `id_jefe` INT,
  `cargo` VARCHAR (255),
  `confianza` TINYINT (1) DEFAULT 0,
  `multiple_personas` TINYINT (1) DEFAULT 0,
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_cargo`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/08/2023
-- ----------------------------------------------------------------------
CREATE TABLE `permisos` (
  `id_permiso` INT NOT NULL AUTO_INCREMENT,
  `concepto` VARCHAR (255),
  `cantidad` INT,
  `unidad` TINYINT (1) DEFAULT 0 COMMENT '0=Dias, 1=Horas',
  `periodo` VARCHAR (10),
  `relacionado_a` TINYINT (1) COMMENT '1=Entrada, 2=Salida, 3=Intermedia, 4=Sin Marcacion, 5=Vacaciones',
  `goce_sueldo` TINYINT (1) DEFAULT 0 COMMENT '0=SI, 1=NO',
  `autenticada` TINYINT COMMENT '0=SI, 1=NO',
  `validez` INT,
  `documentos_requeridos` VARCHAR (255),
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_permiso`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `permisos`
  ADD COLUMN `observacion` TEXT NULL AFTER `documentos_requeridos`;

ALTER TABLE `permisos`
  DROP COLUMN `documentos_requeridos`;

CREATE TABLE `permisos_documentos` (
  `id_permiso_documento` INT NOT NULL AUTO_INCREMENT,
  `id_permiso` INT,
  `id_documento` INT,
  PRIMARY KEY (`id_permiso_documento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 23/08/2023
-- ----------------------------------------------------------------------
CREATE TABLE `grupo_sanguineo` (
  `id_grupo` INT NOT NULL AUTO_INCREMENT,
  `grupo_sanguineo` VARCHAR (10),
  PRIMARY KEY (`id_grupo`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO `grupo_sanguineo` (`grupo_sanguineo`) VALUES
('A+'),
('A-'),
('B+'),
('B-'),
('AB+'),
('AB-'),
('O+'),
('O-');

CREATE TABLE `barrios` (
  `id_barrio` INT NOT NULL AUTO_INCREMENT,
  `id_localidad` INT,
  `barrio` VARCHAR (255),
  PRIMARY KEY (`id_barrio`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `localidades` (
  `id_localidad` INT NOT NULL AUTO_INCREMENT,
  `id_distrito` INT,
  `localidad` VARCHAR (255),
  PRIMARY KEY (`id_localidad`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 24/08/2023
-- ----------------------------------------------------------------------
ALTER TABLE `funcionarios`
  DROP COLUMN `salario_nominal`,
  DROP COLUMN `comision`,
  DROP COLUMN `id_ciudad`,
  DROP COLUMN `id_puesto`,
  DROP COLUMN `id_estado`,
  DROP COLUMN `id_banco`,
  DROP COLUMN `nro_cuenta`,
  DROP COLUMN `fecha_baja`,
  DROP COLUMN `fecha_alta`,
  DROP COLUMN `imagen`,
  DROP COLUMN `curriculum`,
  DROP COLUMN `antecedente`,
  DROP COLUMN `cantidad_hijos`,
  DROP COLUMN `aporte`,
  DROP COLUMN `id_sucursal`,
  DROP COLUMN `referencia`,
  ADD COLUMN `fecha_nacimiento` DATE NULL AFTER `ci`,
  ADD COLUMN `nacionalidad` TINYINT (1) NULL AFTER `fecha_nacimiento`,
  ADD COLUMN `id_departamento` INT NULL AFTER `nacionalidad`,
  ADD COLUMN `id_distrito` INT NULL AFTER `id_departamento`,
  ADD COLUMN `sexo` TINYINT (1) NULL COMMENT '1=Femenino, 2=Masculino' AFTER `id_distrito`,
  ADD COLUMN `id_grupo_sanguineo` INT NULL AFTER `sexo`,
  ADD COLUMN `id_estado` INT NULL AFTER `id_grupo_sanguineo`,
  CHANGE `telefono` `telefono` VARCHAR (15) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `id_estado`,
  CHANGE `celular` `celular` VARCHAR (15) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `telefono`,
  ADD COLUMN `email` VARCHAR (100) NULL AFTER `celular`,
  ADD COLUMN `numero` VARCHAR (10) NULL AFTER `email`,
  CHANGE `direccion` `direccion` VARCHAR (200) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `numero`,
  ADD COLUMN `id_area` INT NULL AFTER `direccion`,
  ADD COLUMN `id_cargo` INT NULL AFTER `id_area`,
  ADD COLUMN `vinculo` TINYINT (1) NULL COMMENT '1=Permanente, 2=Contratado, 3=Comisionado, 4=Por producto' AFTER `id_cargo`,
  ADD COLUMN `fecha_ingreso` DATE NULL AFTER `vinculo`,
  ADD COLUMN `fecha_asuncion` DATE NULL AFTER `fecha_ingreso`,
  CHANGE `salario_real` `salario` INT (11) NOT NULL,
  ADD COLUMN `telefono_interno` VARCHAR (15) NULL AFTER `salario`,
  ADD COLUMN `id_distrito_contacto` INT NULL AFTER `telefono_interno`,
  ADD COLUMN `id_localidad` INT NULL AFTER `id_distrito_contacto`,
  ADD COLUMN `id_barrio` INT NULL AFTER `id_localidad`,
  ADD COLUMN `persona_contacto` VARCHAR (250) NULL AFTER `id_barrio`,
  ADD COLUMN `celular_contacto` VARCHAR (15) NULL AFTER `persona_contacto`,
  DROP INDEX `fk_id_puesto`,
  DROP FOREIGN KEY `fk_id_puesto`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 01/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `idiomas` (
  `id_idioma` INT NOT NULL AUTO_INCREMENT,
  `idioma` VARCHAR (50),
  PRIMARY KEY (`id_idioma`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO idiomas (idioma) VALUES
('GUARANI'),
('ESPAÑOL'),
('INGLÉS'),
('PORTUGUES'),
('ALEMÁN'),
('ITALIANO'),
('FRANCES');

CREATE TABLE `escuelas_funcionarios` (
  `id_escuela_funcionario` INT NOT NULL AUTO_INCREMENT,
  `id_funcionario` INT,
  `escuela` VARCHAR (255),
  `estado` TINYINT,
  `nivel` TINYINT,
  `anho_ingreso` INT (4),
  `anho_egreso` INT (4),
  `observacion` TEXT,
  PRIMARY KEY (`id_escuela_funcionario`),
  CONSTRAINT `fk_funcionarios_escuela` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `escuelas_funcionarios`
  ADD COLUMN `documento` VARCHAR (255) NULL AFTER `anho_egreso`;

CREATE TABLE `funcionarios_idiomas` (
  `id_funcionario_idioma` INT NOT NULL AUTO_INCREMENT,
  `id_funcionario` INT,
  `id_idioma` INT,
  `lee` TINYINT,
  `habla` TINYINT,
  `escribe` TINYINT,
  PRIMARY KEY (`id_funcionario_idioma`),
  CONSTRAINT `fk_funcionarios_funcionarios_idioma` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 04/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `funcionarios_universidad` (
  `id_funcionario_universidad` int(11) NOT NULL AUTO_INCREMENT,
  `id_funcionario` int(11) DEFAULT NULL,
  `id_universidad` int(11) DEFAULT NULL,
  `id_carrera` int(11) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `nivel` tinyint(1) DEFAULT NULL,
  `anho_ingreso` int(4) DEFAULT NULL,
  `anho_egreso` int(4) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_funcionario_universidad`),
  KEY `fk_funcionarios_funcionarios_univ` (`id_funcionario`),
  CONSTRAINT `fk_funcionarios_funcionarios_univ` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4

CREATE TABLE `funcionarios_cursos` (
  `id_funcionario_curso` INT NOT NULL AUTO_INCREMENT,
  `id_funcionario` INT,
  `id_instituto` INT,
  `curso` VARCHAR (255),
  `tipo` TINYINT (1),
  `fecha` DATE,
  `duracion` INT,
  `documento` VARCHAR (255),
  `observacion` TEXT,
  PRIMARY KEY (`id_funcionario_curso`),
  CONSTRAINT `fk_funcionarios_funcionarios_curso` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `funcionarios_experiencias` (
  `id_funcionario_experiencia` INT NOT NULL AUTO_INCREMENT,
  `id_funcionario` INT,
  `id_instituto` INT,
  `cargo` VARCHAR (255),
  `vinculo` TINYINT (1),
  `fecha_desde` DATE,
  `fecha_hasta` DATE,
  `documento` VARCHAR (255),
  `observacion` TEXT,
  PRIMARY KEY (`id_funcionario_experiencia`),
  CONSTRAINT `fk_funcionarios_funcionarios_exp` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `vinculos_familiares` (
  `id_vinculo_familiar` INT NOT NULL AUTO_INCREMENT,
  `vinculo` VARCHAR (250),
  `estado` TINYINT (1) DEFAULT 0,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_vinculo_familiar`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `funcionarios_familias` (
  `id_funcionario_familia` INT NOT NULL AUTO_INCREMENT,
  `id_funcionario` INT,
  `vinculo` TINYINT (1),
  `ci` VARCHAR (15),
  `nombre` VARCHAR (255),
  `apellido` VARCHAR (255),
  `fecha_nacimiento` DATE,
  `sexo` TINYINT (1),
  PRIMARY KEY (`id_funcionario_familia`),
  CONSTRAINT `fk_funcionarios_funcionarios_familia` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 05/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `resoluciones` (
  `id_resolucion` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR (10),
  `fecha_resolucion` DATE,
  `acapite` TEXT,
  `id_representante` INT,
  `documento` VARCHAR (255),
  `usuario` VARCHAR (45),
  `fecha` DATE,
  `estado` TINYINT (1),
  PRIMARY KEY (`id_resolucion`),
  CONSTRAINT `fk_funcionarios_resoluciones` FOREIGN KEY (`id_representante`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `resoluciones`
  CHANGE `fecha` `fecha` DATETIME NULL;

ALTER TABLE `resoluciones`
  ADD COLUMN `nombre_documento` VARCHAR (255) NULL AFTER `id_representante`;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 06/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `objeto_gasto` (
  `id_objeto_gasto` INT NOT NULL AUTO_INCREMENT,
  `objeto_gasto` VARCHAR (255),
  PRIMARY KEY (`id_objeto_gasto`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `objeto_gasto`
  ADD COLUMN `codigo` INT NULL AFTER `id_objeto_gasto`;

INSERT INTO objeto_gasto (codigo, objeto_gasto) VALUES
(111,'111-SUELDOS'),
(113,'113-GASTOS DE REPRESENTACION'),
(133,'133-BONIFICACIONES Y GRATIFICACIONES'),
(137,'137-GRATIFICACIONES POR SERVICIOS ESPECIALES'),
(141,'141-CONTRATACION DE PERSONAL TECNICO'),
(144,'144-JORNALES'),
(145,'145-HONORARIOS PROFESIONALES');

CREATE TABLE `fuente_financiamiento` (
  `id_fuente_financiamiento` INT NOT NULL AUTO_INCREMENT,
  `codigo` INT,
  `fuente` VARCHAR (255),
  PRIMARY KEY (`id_fuente_financiamiento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO fuente_financiamiento (codigo, fuente) VALUES
(10,'RECURSOS DEL TESORO'),
(20,'RECURSOS DEL CREDITO PUBLICO'),
(30,'RECURSOS INSTITUCIONALES');

CREATE TABLE `funcionarios_contratos` (
  `id_funcionario_contrato` INT NOT NULL AUTO_INCREMENT,
  `numero_contrato` VARCHAR (10),
  `id_resolucion` INT,
  `id_area` INT,
  `id_funcionario` INT,
  `anho` INT,
  `id_cargo` INT,
  `id_representante` INT,
  `fecha_vigencia` DATE,
  `fecha_hasta` DATE,
  `salario` INT,
  `fecha_acto` DATE,
  `id_objeto_gasto` INT,
  `id_categoria` INT,
  `id_fuente` INT,
  `documento_nombre` VARCHAR (255),
  `documento` VARCHAR (255),
  `estado` TINYINT (1),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_funcionario_contrato`),
  CONSTRAINT `fk_resolucion_contrato` FOREIGN KEY (`id_resolucion`) REFERENCES `resoluciones` (`id_resolucion`),
  CONSTRAINT `fk_funcionarios_contratos` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_areas_contrato` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  CONSTRAINT `fk_cargos_contratos` FOREIGN KEY (`id_cargo`) REFERENCES `organigrama` (`id_cargo`),
  CONSTRAINT `fk_representantes_contratos` FOREIGN KEY (`id_representante`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_objeto_gasto_contrato` FOREIGN KEY (`id_objeto_gasto`) REFERENCES `objeto_gasto` (`id_objeto_gasto`),
  CONSTRAINT `fk_fuente_contrato` FOREIGN KEY (`id_fuente`) REFERENCES `fuente_financiamiento` (`id_fuente_financiamiento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 07/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `remuneracion_complementaria` (
  `id_remuneracion` INT NOT NULL AUTO_INCREMENT,
  `anho` INT,
  `numero_resolucion` VARCHAR (20),
  `fecha_acto` DATE,
  `id_representante` INT,
  `acapite` TEXT,
  `documento_nombre` VARCHAR (255),
  `documento` VARCHAR (255),
  `estado` TINYINT (1),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_remuneracion`),
  CONSTRAINT `fk_funcionarios_remuneracion` FOREIGN KEY (`id_representante`) REFERENCES `funcionarios` (`id_funcionario`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 08/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `nombramientos` (
  `id_nombramiento` INT NOT NULL AUTO_INCREMENT,
  `id_area` INT,
  `id_servidor` INT,
  `decreto` VARCHAR (45),
  `fecha_acto` DATE,
  `asignacion` INT,
  `id_fuente` INT,
  `id_categoria` INT,
  `cargo` VARCHAR (255),
  `estado` TINYINT (1),
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_nombramiento`),
  CONSTRAINT `fk_funcionarios_nombramientos` FOREIGN KEY (`id_servidor`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_fuente_nombramiento` FOREIGN KEY (`id_fuente`) REFERENCES `fuente_financiamiento` (`id_fuente_financiamiento`),
  CONSTRAINT `fk_categorias_nombramientos` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 11/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `funcionarios_justificativos` (
  `id_justificativo` INT NOT NULL AUTO_INCREMENT,
  `tipo` TINYINT (1),
  `anho` INT (4),
  `mes` INT,
  `id_area` INT,
  `id_servidor` INT,
  `fecha_justificativo` DATE,
  `id_permiso` INT,
  `id_autorizante` INT,
  `vehiculo` TINYINT (1),
  `estado` TINYINT (1),
  `fecha_desde` DATE,
  `fecha_hasta` DATE,
  `documento_nombre` VARCHAR (255),
  `documento` VARCHAR (255),
  `observacion` TEXT,
  `usuario` VARCHAR (45),
  `fecha` DATETIME,
  PRIMARY KEY (`id_justificativo`),
  CONSTRAINT `fk_area_justificativos` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_funcionarios_justificativos` FOREIGN KEY (`id_servidor`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_funcionarios_autorizante` FOREIGN KEY (`id_autorizante`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_permisos_justificativos` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `justificativos_documentos` (
  `id_justificativo_documento` INT NOT NULL AUTO_INCREMENT,
  `id_justificativo` INT,
  `id_documento` INT,
  PRIMARY KEY (`id_justificativo_documento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 12/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `funcionarios_comisiones` (
  `id_comision` INT NOT NULL AUTO_INCREMENT,
  `anho` INT (4),
  `mes` INT,
  `id_area` INT,
  `id_autorizante` INT,
  `numero` VARCHAR (10),
  `vehiculo` TINYINT (1),
  `fecha_desde` DATE,
  `fecha_hasta` DATE,
  `hora_desde` TIME,
  `hora_hasta` TIME,
  `id_permiso` INT,
  `id_lugar` INT,
  `tipo_actividad` TINYINT (1),
  `observacion` TEXT,
  `usuario` VARCHAR (255),
  `estado` TINYINT (1),
  `fecha` DATETIME,
  PRIMARY KEY (`id_comision`),
  CONSTRAINT `fk_areas_comisiones` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  CONSTRAINT `fk_funcionarios_comision` FOREIGN KEY (`id_autorizante`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_permisos_comisiones` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `servidores_comisiones` (
  `id_servidor_comision` INT NOT NULL AUTO_INCREMENT,
  `id_servidor` INT,
  `id_comision` INT,
  PRIMARY KEY (`id_servidor_comision`),
  CONSTRAINT `fk_servidor_comision` FOREIGN KEY (`id_servidor`) REFERENCES `funcionarios` (`id_funcionario`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_comision_ser` FOREIGN KEY (`id_comision`) REFERENCES `funcionarios_comisiones` (`id_comision`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `traslado_temporal` (
  `id_traslado` INT NOT NULL AUTO_INCREMENT,
  `anho` INT (4),
  `id_area` INT,
  `id_servidor` INT,
  `id_institucion` INT,
  `fecha_desde` DATE,
  `fecha_hasta` DATE,
  `fecha_acto` DATE,
  `id_categoria` INT,
  `id_fuente` INT,
  `documento_nombre` VARCHAR (255),
  `documento` VARCHAR (255),
  `observacion` TEXT,
  `usuario` VARCHAR (45),
  `estado` TINYINT (1),
  `fecha` DATETIME,
  PRIMARY KEY (`id_traslado`),
  CONSTRAINT `fk_areas_traslado` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  CONSTRAINT `fk_funcionarios_traslado` FOREIGN KEY (`id_servidor`) REFERENCES `funcionarios` (`id_funcionario`),
  CONSTRAINT `fk_organizacion_traslado` FOREIGN KEY (`id_institucion`) REFERENCES `organizacion` (`id_organizacion`),
  CONSTRAINT `fk_categorias_traslado` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  CONSTRAINT `fk_fuente_traslado` FOREIGN KEY (`id_fuente`) REFERENCES `fuente_financiamiento` (`id_fuente_financiamiento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Angel Gimenez - 14/09/2023
-- ----------------------------------------------------------------------
CREATE TABLE `descuentos` (
  `id_descuento` INT NOT NULL AUTO_INCREMENT,
  `vinculo` TINYINT (1),
  `concepto` VARCHAR (255),
  `porcentaje` DECIMAL (10, 2),
  `nro_salario` INT,
  `factor` DECIMAL (10, 2),
  `retencion` DECIMAL (10, 2),
  PRIMARY KEY (`id_descuento`)
) ENGINE = INNODB CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

ALTER TABLE `descuentos`
  ADD COLUMN `usuario` VARCHAR (45) NULL AFTER `retencion`,
  ADD COLUMN `estado` TINYINT (1) NULL AFTER `usuario`,
  ADD COLUMN `fecha` DATETIME NULL AFTER `estado`;
-- ----------------------------------------------------------------------
-- Angel Gimenez - 18/09/2023
-- ----------------------------------------------------------------------
ALTER TABLE `liquidacion_salarios`
  DROP COLUMN `periodo`,
  DROP COLUMN `forma_pago`,
  DROP COLUMN `nro_cheque`,
  DROP COLUMN `nro_cuenta`,
  ADD COLUMN `anho` INT (4) NULL AFTER `neto_cobrar`,
  ADD COLUMN `mes` INT NULL AFTER `anho`,
  ADD COLUMN `tipo` INT NULL AFTER `mes`,
  ADD COLUMN `calculo` INT NULL AFTER `tipo`,
  ADD COLUMN `nro_factura` VARCHAR (255) NULL AFTER `calculo`,
  ADD COLUMN `documento_nombre` VARCHAR (255) NULL AFTER `nro_factura`,
  ADD COLUMN `documento` VARCHAR (255) NULL AFTER `documento_nombre`;

ALTER TABLE `descuentos_funcionarios`
  DROP COLUMN `fecha`,
  ADD COLUMN `id_area` INT NULL AFTER `funcionario`,
  ADD COLUMN `anho` INT (4) NULL AFTER `id_area`,
  ADD COLUMN `mes` INT NULL AFTER `anho`;

ALTER TABLE `descuentos_funcionarios`
  CHANGE `descuento` `multa` TINYINT (1) NULL;

ALTER TABLE `anticipos`
  DROP COLUMN `fecha`,
  ADD COLUMN `id_area` INT NULL AFTER `funcionario`,
  ADD COLUMN `anho` INT (4) NULL AFTER `id_area`,
  ADD COLUMN `mes` INT NULL AFTER `anho`;

ALTER TABLE `prestamos`
  DROP COLUMN `fecha`,
  ADD COLUMN `id_area` INT NULL AFTER `funcionario`,
  ADD COLUMN `anho` INT (4) NULL AFTER `id_area`,
  ADD COLUMN `mes` INT NULL AFTER `anho`;

