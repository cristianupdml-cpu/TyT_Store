USE tyt_store;

CREATE TABLE IF NOT EXISTS roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT IGNORE INTO roles(nombre) VALUES ('administrador'),('vendedor');

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol_id INT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

ALTER TABLE movimientos_inventario
  ADD COLUMN usuario_id INT UNSIGNED NULL,
  ADD CONSTRAINT fk_movimientos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE ventas
  ADD COLUMN usuario_id INT UNSIGNED NULL,
  ADD INDEX idx_ventas_fecha (fecha),
  ADD CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS detalle_venta (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  venta_id BIGINT UNSIGNED NOT NULL,
  variante_id INT UNSIGNED NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario INT UNSIGNED NOT NULL,
  subtotal INT UNSIGNED NOT NULL,
  FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (variante_id) REFERENCES variantes(id),
  INDEX idx_detalle_variante (variante_id)
) ENGINE=InnoDB;
