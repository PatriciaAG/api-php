CREATE DATABASE IF NOT EXISTS gestion_servicios
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_servicios;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS transaccion_detalle;
DROP TABLE IF EXISTS transaccion;
DROP TABLE IF EXISTS servicio;
DROP TABLE IF EXISTS persona;
DROP TABLE IF EXISTS servicio_tipo;
DROP TABLE IF EXISTS rol;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE rol (
  id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_nombre VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE persona (
  id_persona INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  persona_nombre VARCHAR(100) NOT NULL,
  persona_apellido VARCHAR(100) NOT NULL,
  persona_ci VARCHAR(30) NOT NULL UNIQUE,
  id_rol INT UNSIGNED NOT NULL,
  CONSTRAINT fk_persona_rol FOREIGN KEY (id_rol)
    REFERENCES rol(id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE servicio_tipo (
  id_servicio_tipo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  servicio_tipo_descripcion VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE servicio (
  id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  servicios_nombre VARCHAR(150) NOT NULL,
  servicios_descripcion TEXT NULL,
  servicios_imagen VARCHAR(500) NULL,
  servicios_precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  id_servicio_tipo INT UNSIGNED NOT NULL,
  CONSTRAINT fk_servicio_tipo FOREIGN KEY (id_servicio_tipo)
    REFERENCES servicio_tipo(id_servicio_tipo) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_servicio_precio CHECK (servicios_precio_unitario >= 0)
) ENGINE=InnoDB;

CREATE TABLE transaccion (
  id_transaccion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tr_fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tr_total_pagar DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  id_persona INT UNSIGNED NOT NULL,
  CONSTRAINT fk_transaccion_persona FOREIGN KEY (id_persona)
    REFERENCES persona(id_persona) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_transaccion_total CHECK (tr_total_pagar >= 0)
) ENGINE=InnoDB;

CREATE TABLE transaccion_detalle (
  id_transaccion_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_transaccion INT UNSIGNED NOT NULL,
  id_servicio INT UNSIGNED NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_detalle_transaccion FOREIGN KEY (id_transaccion)
    REFERENCES transaccion(id_transaccion) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detalle_servicio FOREIGN KEY (id_servicio)
    REFERENCES servicio(id_servicio) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_detalle_cantidad CHECK (cantidad > 0),
  CONSTRAINT chk_detalle_precio CHECK (precio_unitario >= 0 AND subtotal >= 0)
) ENGINE=InnoDB;

INSERT INTO rol (rol_nombre) VALUES ('Administrador'), ('Cliente');
INSERT INTO servicio_tipo (servicio_tipo_descripcion) VALUES ('Mantenimiento'), ('Reparación');
INSERT INTO persona (persona_nombre, persona_apellido, persona_ci, id_rol)
VALUES ('Juan', 'Pérez', '1234567', 2);
INSERT INTO servicio (servicios_nombre, servicios_descripcion, servicios_precio_unitario, id_servicio_tipo)
VALUES ('Mantenimiento básico', 'Servicio de mantenimiento preventivo', 250.00, 1),
       ('Diagnóstico', 'Diagnóstico general del equipo', 100.00, 2);
