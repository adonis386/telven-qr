<?php
// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'admin');
define('DB_NAME', 'telvenqr');

// Función para verificar la conexión a la base de datos
function testDatabaseConnection() {
    try {
        // Conectar al servidor MySQL
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error de conexión: " . $mysqli->connect_error);
        }
        
        // Crear la base de datos si no existe
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
            throw new Exception("Error al crear la base de datos: " . $mysqli->error);
        }
        
        // Seleccionar la base de datos
        if (!$mysqli->select_db(DB_NAME)) {
            throw new Exception("Error al seleccionar la base de datos: " . $mysqli->error);
        }
        
        // Crear la tabla clientes
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(36) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            coupon_code VARCHAR(8) NOT NULL,
            fecha_registro DATETIME NOT NULL,
            estado ENUM('activo', 'usado') NOT NULL DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$mysqli->query($sql)) {
            throw new Exception("Error al crear la tabla: " . $mysqli->error);
        }
        
        // Crear índices
        if (!$mysqli->query("CREATE INDEX IF NOT EXISTS idx_uuid ON clientes(uuid)")) {
            throw new Exception("Error al crear índice uuid: " . $mysqli->error);
        }
        
        if (!$mysqli->query("CREATE INDEX IF NOT EXISTS idx_coupon_code ON clientes(coupon_code)")) {
            throw new Exception("Error al crear índice coupon_code: " . $mysqli->error);
        }
        
        $mysqli->close();
        return "Base de datos y tabla creadas exitosamente";
        
    } catch(Exception $e) {
        return "Error: " . $e->getMessage() . 
               "<br>Verifica que:<br>" .
               "1. MySQL esté corriendo en XAMPP<br>" .
               "2. Las credenciales sean correctas<br>" .
               "3. El servicio MySQL esté activo";
    }
}

// Función para generar UUID único
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Función para generar código de cupón
function generateCouponCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
?>
