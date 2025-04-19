-- Eliminar tablas existentes
DROP TABLE IF EXISTS cupones_descuentos;
DROP TABLE IF EXISTS descuentos_tipos;

-- Crear tabla de tipos de descuentos
CREATE TABLE descuentos_tipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    condiciones TEXT
);

-- Crear tabla de cupones_descuentos
CREATE TABLE cupones_descuentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cupon_id VARCHAR(50) NOT NULL,
    tipo_descuento_id INT NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    fecha_uso DATETIME,
    FOREIGN KEY (tipo_descuento_id) REFERENCES descuentos_tipos(id),
    UNIQUE KEY unique_cupon_descuento (cupon_id, tipo_descuento_id)
);

-- Insertar los tipos de descuentos predefinidos
INSERT INTO descuentos_tipos (nombre, descripcion, monto, condiciones) VALUES
('Chip Gratuito', 'Chip completamente gratis para tu línea telefónica', 0, 'No aplican condiciones adicionales'),
('Accesorios', 'Descuento en línea de accesorios (excepto productos Cubitt)', 3, 'Válido para todos los accesorios excepto productos de marca Cubitt'),
('Productos Cubitt Seleccionados', 'Descuento exclusivo en productos seleccionados de la marca Cubitt', 5, 'Aplica únicamente para:\n- Relojes Cubitt\n- Cornetas Cubitt\n- Balanza Cubitt'),
('Equipos Internet', 'Descuento en equipos de internet', 5, 'Válido para todos los equipos de internet disponibles'),
('Teléfonos Gama Baja', 'Descuento en teléfonos gama baja', 3, 'Aplica para teléfonos con precio menor a $100'),
('Teléfonos Gama Alta', 'Descuento en teléfonos gama alta seleccionados', 5, 'Condiciones:\n- Solo aplica en compras mayores a $200\n- No acumulable con otras promociones\n- No aplica para iPhone\n- Consultar modelos participantes en tienda'); 