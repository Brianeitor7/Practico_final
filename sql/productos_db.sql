-- ============================================================
-- productos_db.sql — Esquema y datos iniciales de la aplicación
-- Base de datos: productos_db (MariaDB)
-- ============================================================

CREATE DATABASE IF NOT EXISTS productos_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE productos_db;

-- ------------------------------------------------------------
-- Tabla principal de productos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre            VARCHAR(100)     NOT NULL,
    descripcion       TEXT             NULL,
    categoria         VARCHAR(50)      NOT NULL DEFAULT 'General',
    precio            DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    cantidad          INT UNSIGNED     NOT NULL DEFAULT 0,
    fecha_creacion    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                       ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_categoria (categoria)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- Datos de ejemplo (solo si la tabla está vacía)
-- ------------------------------------------------------------
INSERT INTO productos (nombre, descripcion, categoria, precio, cantidad)
SELECT * FROM (
    SELECT 'Notebook Lenovo ThinkPad E16'  AS nombre,
           'Portátil 16" Ryzen 7, 16 GB RAM, SSD 512 GB' AS descripcion,
           'Informática' AS categoria, 749.99 AS precio, 12 AS cantidad
    UNION ALL SELECT 'Monitor LG 24" Full HD',
           'Monitor IPS 75 Hz con entrada HDMI y VGA',
           'Informática', 139.90, 25
    UNION ALL SELECT 'Teclado mecánico RGB',
           'Switches rojos, formato 80%, retroiluminado',
           'Accesorios', 59.50, 8
    UNION ALL SELECT 'Mouse inalámbrico Logitech M185',
           'Conexión USB, batería de 12 meses',
           'Accesorios', 18.75, 40
    UNION ALL SELECT 'Silla ergonómica de oficina',
           'Respaldo malla, regulable en altura',
           'Oficina', 189.00, 5
    UNION ALL SELECT 'Auriculares Bluetooth JBL T450BT',
           'Plegables, 11 h de autonomía',
           'Electrónica', 45.00, 0
) AS nuevos
WHERE NOT EXISTS (SELECT 1 FROM productos);

-- ------------------------------------------------------------
-- Nota: los usuarios de la base de datos (appuser / bckuser)
-- se crean en scripts/setup_bd.sh, NO aquí, para que las
-- contraseñas nunca queden en el repositorio Git.
-- ------------------------------------------------------------
