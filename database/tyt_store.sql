CREATE DATABASE IF NOT EXISTS tyt_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tyt_store;

CREATE TABLE IF NOT EXISTS categorias (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL UNIQUE, descripcion VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS marcas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL UNIQUE, descripcion VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS tallas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, etiqueta VARCHAR(20) NOT NULL, tipo ENUM('Ropa','Calzado') NOT NULL, UNIQUE KEY uq_talla (etiqueta,tipo)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS proveedores (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150) NOT NULL, contacto VARCHAR(120) NOT NULL, telefono VARCHAR(40) NOT NULL, correo VARCHAR(150) NOT NULL, ciudad VARCHAR(100) NOT NULL, nit VARCHAR(40) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS roles (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(30) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS usuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, correo VARCHAR(190) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, rol_id INT UNSIGNED NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (rol_id) REFERENCES roles(id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS productos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sku VARCHAR(40) NOT NULL UNIQUE, nombre VARCHAR(150) NOT NULL, descripcion TEXT NOT NULL, categoria_id INT UNSIGNED NULL, marca_id INT UNSIGNED NULL, proveedor_id INT UNSIGNED NULL, precio_compra INT UNSIGNED NOT NULL DEFAULT 0, precio_venta INT UNSIGNED NOT NULL DEFAULT 0, activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL, FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL, FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS variantes (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, producto_id INT UNSIGNED NOT NULL, talla VARCHAR(20) NOT NULL, color VARCHAR(60) NOT NULL, stock INT NOT NULL DEFAULT 0, stock_minimo INT UNSIGNED NOT NULL DEFAULT 0, UNIQUE KEY uq_variante (producto_id,talla,color), FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS movimientos_inventario (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, variante_id INT UNSIGNED NOT NULL, tipo ENUM('entrada','salida','ajuste') NOT NULL, cantidad INT UNSIGNED NOT NULL, nota VARCHAR(255) NOT NULL DEFAULT '', fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, usuario_id INT UNSIGNED NULL, FOREIGN KEY (variante_id) REFERENCES variantes(id) ON DELETE CASCADE, FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ventas (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(30) NOT NULL UNIQUE, fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, cliente VARCHAR(150) NOT NULL, vendedor VARCHAR(150) NOT NULL, items_count INT UNSIGNED NOT NULL, descuento INT UNSIGNED NOT NULL DEFAULT 0, total INT UNSIGNED NOT NULL, metodo_pago ENUM('tarjeta','efectivo','transferencia') NOT NULL, usuario_id INT UNSIGNED NULL, FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL, INDEX idx_ventas_fecha (fecha)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS detalle_venta (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, venta_id BIGINT UNSIGNED NOT NULL, variante_id INT UNSIGNED NOT NULL, cantidad INT UNSIGNED NOT NULL, precio_unitario INT UNSIGNED NOT NULL, subtotal INT UNSIGNED NOT NULL, FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE, FOREIGN KEY (variante_id) REFERENCES variantes(id), INDEX idx_detalle_variante (variante_id)) ENGINE=InnoDB;

INSERT IGNORE INTO roles(nombre) VALUES ('administrador'),('vendedor');

INSERT IGNORE INTO categorias(nombre,descripcion) VALUES
('Camisetas','Camisetas y tops para hombre y mujer'),('Pantalones','Jeans, leggings y pantalones formales'),('Calzado','Zapatos, tenis y sandalias'),('Chaquetas','Chaquetas, abrigos y buzos'),('Accesorios','Bolsos, cinturones y gorras'),('Ropa Interior','Ropa interior y medias');
INSERT IGNORE INTO marcas(nombre,descripcion) VALUES
('Nike','Ropa y calzado deportivo'),('Adidas','Calzado y artículos deportivos'),('Levi''s','Jeans y prendas denim'),('Puma','Moda y calzado deportivo'),('Chevignon','Moda casual y prendas urbanas'),('Arturo Calle','Moda masculina y accesorios'),('Studio F','Moda femenina contemporánea'),('Vélez','Accesorios y marroquinería');
INSERT IGNORE INTO tallas(etiqueta,tipo) VALUES ('XS','Ropa'),('S','Ropa'),('M','Ropa'),('L','Ropa'),('XL','Ropa'),('XXL','Ropa'),('35','Calzado'),('36','Calzado'),('37','Calzado'),('38','Calzado'),('39','Calzado'),('40','Calzado'),('41','Calzado'),('42','Calzado'),('43','Calzado'),('44','Calzado');
INSERT IGNORE INTO proveedores(nombre,contacto,telefono,correo,ciudad,nit) VALUES
('Distribuidora Moda & Estilo','Carlos Ramírez','310 456 7890','ventas@modaestilo.com','Medellín','900.123.456-1'),
('Nike Colombia SAS','Ana Gómez','601 234 5678','distribuciones@nikeco.com','Bogotá','800.987.654-3'),
('Calzatodo Mayorista','Luis Herrera','350 789 0123','pedidos@calzatodo.com.co','Bucaramanga','900.345.678-9'),
('Textiles del Valle','María Ospina','318 901 2345','contacto@textilesdelValle.co','Cali','830.456.789-2'),
('Fashion Import Ltda','Jorge Cifuentes','314 567 8901','jorge@fashionimport.co','Barranquilla','860.789.012-7');

INSERT IGNORE INTO productos(sku,nombre,descripcion,categoria_id,marca_id,proveedor_id,precio_compra,precio_venta) VALUES
('CAM-OVR-001','Camiseta Básica Oversize','Camiseta oversize 100% algodón',(SELECT id FROM categorias WHERE nombre='Camisetas'),(SELECT id FROM marcas WHERE nombre='Chevignon'),1,35000,65000),
('CAM-POL-002','Camiseta Polo Clásica','Polo de algodón piqué',(SELECT id FROM categorias WHERE nombre='Camisetas'),(SELECT id FROM marcas WHERE nombre='Arturo Calle'),1,55000,98000),
('PAN-JSK-003','Jean Skinny Mujer','Jean elastizado tiro alto',(SELECT id FROM categorias WHERE nombre='Pantalones'),(SELECT id FROM marcas WHERE nombre='Levi''s'),4,70000,135000),
('PAN-JSH-004','Jean Slim Hombre','Jean slim fit lavado oscuro',(SELECT id FROM categorias WHERE nombre='Pantalones'),(SELECT id FROM marcas WHERE nombre='Levi''s'),4,68000,129000),
('CAL-AIR-005','Tenis Air Max Running','Tenis para correr',(SELECT id FROM categorias WHERE nombre='Calzado'),(SELECT id FROM marcas WHERE nombre='Nike'),2,180000,320000),
('CAL-ULT-006','Tenis Ultraboost','Tenis de alto rendimiento',(SELECT id FROM categorias WHERE nombre='Calzado'),(SELECT id FROM marcas WHERE nombre='Adidas'),2,195000,345000),
('CHA-DEP-007','Chaqueta Deportiva','Chaqueta deportiva con capucha',(SELECT id FROM categorias WHERE nombre='Chaquetas'),(SELECT id FROM marcas WHERE nombre='Puma'),2,95000,175000),
('CAM-BLU-008','Blusa Lino Mujer','Blusa de lino',(SELECT id FROM categorias WHERE nombre='Camisetas'),(SELECT id FROM marcas WHERE nombre='Studio F'),4,45000,89000),
('PAN-LEG-009','Legging Deportivo','Legging de compresión',(SELECT id FROM categorias WHERE nombre='Pantalones'),(SELECT id FROM marcas WHERE nombre='Nike'),2,65000,115000),
('CAL-OXF-010','Zapato Cuero Oxford','Zapato formal de cuero',(SELECT id FROM categorias WHERE nombre='Calzado'),(SELECT id FROM marcas WHERE nombre='Vélez'),3,140000,250000);

INSERT IGNORE INTO variantes(producto_id,talla,color,stock,stock_minimo)
SELECT p.id,v.talla,v.color,v.stock,v.minimo FROM productos p JOIN (
 SELECT 'CAM-OVR-001' sku,'S' talla,'Blanco' color,15 stock,5 minimo UNION ALL SELECT 'CAM-OVR-001','M','Blanco',15,5 UNION ALL SELECT 'CAM-OVR-001','L','Blanco',3,5 UNION ALL SELECT 'CAM-OVR-001','M','Negro',18,5 UNION ALL
 SELECT 'CAM-POL-002','M','Azul Marino',8,3 UNION ALL SELECT 'CAM-POL-002','L','Azul Marino',6,3 UNION ALL SELECT 'CAM-POL-002','XL','Blanco',4,3 UNION ALL SELECT
 'PAN-JSK-003','S','Azul Oscuro',2,4 UNION ALL SELECT 'PAN-JSK-003','M','Azul Oscuro',10,4 UNION ALL SELECT 'PAN-JSK-003','L','Azul Oscuro',9,4 UNION ALL SELECT
 'PAN-JSH-004','M','Negro',9,3 UNION ALL SELECT 'PAN-JSH-004','L','Negro',7,3 UNION ALL SELECT 'PAN-JSH-004','XL','Negro',5,3 UNION ALL SELECT
 'CAL-AIR-005','37','Negro/Blanco',9,2 UNION ALL SELECT 'CAL-AIR-005','38','Negro/Blanco',2,3 UNION ALL SELECT 'CAL-AIR-005','39','Negro/Blanco',6,2 UNION ALL SELECT
 'CAL-ULT-006','39','Negro',5,2 UNION ALL SELECT 'CAL-ULT-006','40','Negro',5,2 UNION ALL SELECT
 'CHA-DEP-007','M','Gris',8,3 UNION ALL SELECT 'CHA-DEP-007','L','Negro',2,3 UNION ALL SELECT
 'CAM-BLU-008','S','Crema',11,3 UNION ALL SELECT 'CAM-BLU-008','M','Crema',9,3 UNION ALL SELECT
 'PAN-LEG-009','S','Negro',14,5 UNION ALL SELECT 'PAN-LEG-009','M','Morado',7,5 UNION ALL
 'CAL-OXF-010','39','Café',3,2 UNION ALL SELECT 'CAL-OXF-010','40','Negro',4,2
) v ON v.sku=p.sku;

INSERT IGNORE INTO ventas(codigo,fecha,cliente,vendedor,items_count,descuento,total,metodo_pago) VALUES
('V2','2026-09-29 11:40:00','Andrés Ospina','Carlos Vega',1,20000,300000,'tarjeta'),
('V1','2026-09-29 09:15:00','Laura Martínez','Andrea Torres',2,0,265000,'efectivo'),
('V4','2026-09-28 16:05:00','Camila Ríos','Natalia Cano',2,19000,300000,'efectivo'),
('V3','2026-09-28 14:20:00','Pedro Salcedo','Andrea Torres',2,0,273000,'transferencia'),
('V5','2026-09-27 10:30:00','Roberto Muñoz','Carlos Vega',1,0,250000,'tarjeta');
