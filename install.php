<?php
require_once 'includes/config.php';

function createTables($mysqli) {
    $tables = [
        // Tabla clientes
        "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(36) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            tipo_documento ENUM('V', 'E', 'P') NOT NULL,
            numero_documento VARCHAR(20) NOT NULL,
            foto_selfie VARCHAR(255) NOT NULL,
            foto_cedula VARCHAR(255) NOT NULL,
            coupon_code VARCHAR(8) NOT NULL,
            fecha_registro DATETIME NOT NULL,
            estado ENUM('activo', 'usado') NOT NULL DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Tabla descuentos_tipos
        "CREATE TABLE IF NOT EXISTS descuentos_tipos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            monto DECIMAL(10,2) NOT NULL,
            condiciones TEXT
        )",

        // Tabla cupones_descuentos
        "CREATE TABLE IF NOT EXISTS cupones_descuentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cupon_id VARCHAR(50) NOT NULL,
            tipo_descuento_id INT NOT NULL,
            usado BOOLEAN DEFAULT FALSE,
            fecha_uso DATETIME,
            FOREIGN KEY (tipo_descuento_id) REFERENCES descuentos_tipos(id)
        )"
    ];

    foreach ($tables as $sql) {
        if (!$mysqli->query($sql)) {
            throw new Exception("Error al crear tabla: " . $mysqli->error);
        }
    }
}

function insertInitialData($mysqli) {
    // Insertar tipos de descuentos iniciales
    $sql = "INSERT INTO descuentos_tipos (nombre, descripcion, monto, condiciones) VALUES
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
         'CONDICIONES IMPORTANTES:\n- Solo aplica en compras mayores a $200\n- No acumulable con otras promociones\n- Consultar modelos participantes en tienda\n- Sujeto a disponibilidad')";

    if (!$mysqli->query($sql)) {
        throw new Exception("Error al insertar datos iniciales: " . $mysqli->error);
    }
}

function createDirectories() {
    $directories = [
        'uploads/selfies',
        'uploads/cedulas',
        'assets/qr'
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

// Ejecutar la instalación
try {
    echo "<h1>Instalación del Sistema de Cupones - Tienda Milenium</h1>";
    echo "<pre>";
    
    echo "Conectando a MySQL...\n";
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($mysqli->connect_error) {
        throw new Exception("Error de conexión: " . $mysqli->connect_error);
    }
    echo "✓ Conexión exitosa\n\n";

    echo "Creando base de datos...\n";
    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
        throw new Exception("Error al crear la base de datos: " . $mysqli->error);
    }
    echo "✓ Base de datos creada\n\n";

    echo "Seleccionando base de datos...\n";
    if (!$mysqli->select_db(DB_NAME)) {
        throw new Exception("Error al seleccionar la base de datos: " . $mysqli->error);
    }
    echo "✓ Base de datos seleccionada\n\n";

    echo "Creando tablas...\n";
    createTables($mysqli);
    echo "✓ Tablas creadas\n\n";

    echo "Insertando datos iniciales...\n";
    insertInitialData($mysqli);
    echo "✓ Datos iniciales insertados\n\n";

    echo "Creando directorios necesarios...\n";
    createDirectories();
    echo "✓ Directorios creados\n\n";

    $mysqli->close();
    
    echo "¡Instalación completada con éxito!\n";
    echo "\nPasos siguientes:\n";
    echo "1. Verifica que puedas acceder a: http://localhost/tiendaqr/\n";
    echo "2. Genera el QR de registro en: http://localhost/tiendaqr/generate_qr.php\n";
    echo "3. Accede al panel admin en: http://localhost/tiendaqr/admin/\n";
    
} catch(Exception $e) {
    echo "Error durante la instalación:\n";
    echo $e->getMessage() . "\n\n";
    echo "Por favor verifica:\n";
    echo "1. Que MySQL esté corriendo en XAMPP\n";
    echo "2. Que las credenciales en includes/config.php sean correctas\n";
    echo "3. Que el servicio MySQL esté activo\n";
}
echo "</pre>";
?> 