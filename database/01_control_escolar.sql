-- Sistema de Control Escolar - Script de inicialización para MariaDB
-- Se ejecuta automáticamente la primera vez que se crea el volumen (docker-entrypoint-initdb.d).
-- No incluye CREATE DATABASE ni USE: la imagen ya crea y selecciona la base con MARIADB_DATABASE.
SET NAMES utf8mb4;

-- ---------- Estructura (el orden respeta las llaves foráneas) ----------
CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alumnos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  matricula VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(80) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  fecha_nacimiento DATE NOT NULL,
  grado TINYINT UNSIGNED NOT NULL,
  grupo CHAR(1) NOT NULL,
  email VARCHAR(120) NULL,
  telefono VARCHAR(20) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  grado TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calificaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alumno_id INT UNSIGNED NOT NULL,
  materia_id INT UNSIGNED NOT NULL,
  parcial TINYINT UNSIGNED NOT NULL,
  calificacion DECIMAL(4,1) NOT NULL,
  UNIQUE KEY uq_cal (alumno_id, materia_id, parcial),
  CONSTRAINT fk_cal_alumno  FOREIGN KEY (alumno_id)  REFERENCES alumnos(id)  ON DELETE CASCADE,
  CONSTRAINT fk_cal_materia FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Datos iniciales ----------
-- Usuario administrador: admin / admin123 (hash SHA-512 crypt, compatible con password_verify)
INSERT INTO usuarios (usuario, nombre, password_hash) VALUES
('admin', 'Administrador', '$6$ctrlEsc01$AtKn8HoEzn1i48/xnXvNRm/lVbSEALdHNClbWeoL/Fk8DZA9MEA64neEvSn6JCxcUPxGnoHNpYJ280r4mioyr.');

INSERT INTO alumnos (matricula, nombre, apellidos, fecha_nacimiento, grado, grupo, email, telefono) VALUES
('2026001', 'Ana',    'Pérez López',    '2012-04-15', 3, 'A', 'ana.perez@example.com',      '2221000001'),
('2026002', 'Luis',   'Hernández Ruiz', '2012-08-02', 3, 'A', 'luis.hernandez@example.com', '2221000002'),
('2026003', 'María',  'García Torres',  '2012-11-23', 3, 'B', 'maria.garcia@example.com',   NULL),
('2026004', 'Carlos', 'Sánchez Mora',   '2011-01-30', 3, 'B', NULL,                         '2221000004');

INSERT INTO materias (clave, nombre, grado) VALUES
('MAT3', 'Matemáticas', 3),
('ESP3', 'Español',     3),
('CIE3', 'Ciencias',    3);

INSERT INTO calificaciones (alumno_id, materia_id, parcial, calificacion) VALUES
(1, 1, 1, 9.5), (1, 2, 1, 8.7), (1, 3, 1, 9.0),
(2, 1, 1, 7.2), (2, 2, 1, 6.8), (2, 3, 1, 5.5),
(3, 1, 1, 8.0), (3, 2, 1, 9.1),
(4, 1, 1, 6.0), (4, 3, 1, 7.4);
