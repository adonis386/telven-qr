<?php
require_once '../includes/functions.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Desactivar temporalmente la verificación de claves foráneas
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Eliminar las tablas existentes
    $conn->exec("DROP TABLE IF EXISTS cupones_descuentos");
    $conn->exec("DROP TABLE IF EXISTS descuentos_tipos");

    // Crear las tablas con la estructura correcta
    $conn->exec("
        CREATE TABLE descuentos_tipos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            monto DECIMAL(10,2) NOT NULL,
            condiciones TEXT
        )
    ");

    $conn->exec("
        CREATE TABLE cupones_descuentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cupon_id VARCHAR(50) NOT NULL,
            tipo_descuento_id INT NOT NULL,
            usado BOOLEAN DEFAULT FALSE,
            fecha_uso DATETIME,
            FOREIGN KEY (tipo_descuento_id) REFERENCES descuentos_tipos(id)
        )
    ");

    // Reactivar la verificación de claves foráneas
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Insertar los nuevos registros actualizados
    $conn->exec("
        INSERT INTO descuentos_tipos (nombre, descripcion, monto, condiciones) VALUES
        ('Chip Gratuito', 
         'Chip completamente gratis para tu línea telefónica', 
         0, 
         'No aplican condiciones adicionales'),
        
        ('Accesorios', 
         'Descuento en línea de accesorios (excepto productos Cubitt)', 
         3, 
         'Válido para todos los accesorios excepto productos de marca Cubitt'),
        
        ('Productos Cubitt Seleccionados', 
         'Descuento exclusivo solo en: Relojes, Cornetas y Balanza marca Cubitt', 
         5, 
         'Aplica únicamente para:\n- Relojes Cubitt\n- Cornetas Cubitt\n- Balanza Cubitt\n\nNo válido para otros productos de la marca'),
        
        ('Equipos Internet', 
         'Descuento en equipos de internet', 
         5, 
         'Válido para todos los equipos de internet disponibles'),
        
        ('Teléfonos Gama Baja', 
         'Descuento en teléfonos gama baja', 
         3, 
         'Aplica para teléfonos con precio menor a $200'),
        
        ('Teléfonos Gama Alta', 
         'Descuento en teléfonos gama alta', 
         5, 
         'CONDICIONES IMPORTANTES:\n- Solo aplica en compras mayores a $200\n- No acumulable con otras promociones\n- Consultar modelos participantes en tienda\n- Sujeto a disponibilidad')
    ");

    echo "Descuentos actualizados correctamente.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 