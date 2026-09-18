-- 1. Crear base de datos (DML: CREATE DATABASE)
CREATE DATABASE IF NOT EXISTS TIENDA
  CHARACTER SET utf8mb4              
  COLLATE utf8mb4_unicode_ci;        -
-- 2. Seleccionar la base de datos
USE TIENDA;
-- 3. Crear tablas (DDL: CREATE TABLE)
CREATE TABLE PRODUCTO (
  id_producto INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nombre      VARCHAR(50)  NOT NULL,
  descripcion VARCHAR(200),
  precio      DECIMAL(10,2) NOT NULL,     
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  categoria   VARCHAR(30)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE CLIENTE (
  id_cliente INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nombre     VARCHAR(50)  NOT NULL,
  email      VARCHAR(50)  NOT NULL UNIQUE, 
  direccion  VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE COMPRA (
  id_compra    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  cantidad     INT UNSIGNED NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  fecha        DATE NOT NULL,
  id_producto  INT UNSIGNED NOT NULL,
  id_cliente   INT UNSIGNED NOT NULL,
  estado       ENUM('Enviado', 'Recibido', 'Entregado') DEFAULT 'Enviado',
  FOREIGN KEY (id_producto) REFERENCES PRODUCTO(id_producto),
  FOREIGN KEY (id_cliente)  REFERENCES CLIENTE(id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- REGISTROS DE PRODUCTOS (10 items)
INSERT INTO PRODUCTO (nombre, descripcion, precio, stock, categoria) VALUES
('Laptop HP Pavilion',       'Notebook 16 GB RAM',              489990, 10, 'Notebooks'),
('Nintendo Switch 2',        'Consola híbrida',                599990,  8, 'Consolas'),
('Auriculares Sony',         'Cancelación de ruido',            32990, 25, 'Periféricos'),
('AMD Ryzen 7 9800X3D',      'Procesador gaming',              599900, 12, 'Procesadores'),
('Monitor Samsung 24"',      'IPS Full HD',                   179990, 15, 'Periféricos'),
('MSI GeForce RTX 5070 Ti',  'Tarjeta gráfica gaming',        1135990,  5, 'Tarjetas de video'),
('Teclado Redragon',         'Mecánico RGB',                   29990, 30, 'Periféricos'),
('Samsung Galaxy Watch9',    'Smartwatch fitness',            312990, 18, 'Smartwatches'),
('Mouse Microsoft',          'Inalámbrico ergonómico',         19990, 40, 'Periféricos'),
('Silla Gamer XRacer',       'Ergonómica con soporte lumbar', 149990,  8, 'Periféricos');

-- Insertar Clientes
INSERT INTO CLIENTE (nombre, email, direccion) VALUES
('Ana Rojas',    'ana.rojas@mail.com',  'Av. Providencia 1234, Santiago'),
('Luis Soto',    'luis.soto@mail.com',  'Calle Las Heras 456, Valparaíso'),
('María Torres', 'maria.t@mail.com',    'Av. Colón 789, Viña del Mar');

-- Registrar 10 Operaciones de Compra
INSERT INTO COMPRA (cantidad, total, fecha, id_producto, id_cliente, estado) VALUES
-- Cliente 1: Ana Rojas 
(1, 489990.00, '2026-09-01', 1, 1, 'Enviado'),
(2,  65980.00, '2026-09-02', 3, 1, 'Enviado'),
(1, 32990.00,  '2026-09-08', 3, 1, 'Enviado'),
(1, 599990.00, '2026-09-09', 2, 1, 'Enviado'),

-- Cliente 2: Luis Soto 
(1,  29990.00, '2026-09-04', 7, 2, 'Enviado'),
(3,  98970.00, '2026-09-05', 3, 2, 'Enviado'),
(2, 359980.00, '2026-09-06', 2, 2, 'Enviado'),

-- Cliente 3: María Torres
(1, 489990.00, '2026-09-07', 1, 3, 'Enviado'),
(1, 179990.00, '2026-09-10', 5, 3, 'Enviado'),

-- Extra: Cliente 1 compra más
(2, 119998.00, '2026-09-11', 9, 1, 'Enviado');