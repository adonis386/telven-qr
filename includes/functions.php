<?php
require_once 'config.php';

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getDescuentosDisponibles($cupon_code) {
        try {
            // Primero verificamos si existe la tabla
            $checkTable = $this->conn->query("SHOW TABLES LIKE 'descuentos_tipos'");
            if ($checkTable->rowCount() == 0) {
                // Si la tabla no existe, la creamos junto con los datos iniciales
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS descuentos_tipos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nombre VARCHAR(100) NOT NULL,
                        descripcion TEXT,
                        monto DECIMAL(10,2) NOT NULL,
                        condiciones TEXT
                    )
                ");

                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS cupones_descuentos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        cupon_id VARCHAR(50) NOT NULL,
                        tipo_descuento_id INT NOT NULL,
                        usado BOOLEAN DEFAULT FALSE,
                        fecha_uso DATETIME,
                        FOREIGN KEY (tipo_descuento_id) REFERENCES descuentos_tipos(id)
                    )
                ");

                // Insertar los tipos de descuentos predefinidos con condiciones específicas
                $this->conn->exec("
                    INSERT INTO descuentos_tipos (nombre, descripcion, monto, condiciones) VALUES
                    ('Chip Gratuito', 'Chip completamente gratis para tu línea telefónica', 0, 'No aplican condiciones adicionales'),
                    
                    ('Accesorios', 'Descuento en línea de accesorios (excepto productos Cubitt)', 3, 'Válido para todos los accesorios excepto productos de marca Cubitt'),
                    
                    ('Productos Cubitt Seleccionados', 'Descuento exclusivo en productos seleccionados de la marca Cubitt', 5, 'Aplica únicamente para:\n- Relojes Cubitt\n- Cornetas Cubitt\n- Balanza Cubitt'),
                    
                    ('Equipos Internet', 'Descuento en equipos de internet', 5, 'Válido para todos los equipos de internet disponibles'),
                    
                    ('Teléfonos Gama Baja', 'Descuento en teléfonos gama baja', 3, 'Aplica para teléfonos con precio menor a $100'),
                    
                    ('Teléfonos Gama Alta', 'Descuento en teléfonos gama alta seleccionados', 5, 'Condiciones:\n- Solo aplica en compras mayores a $200\n- No acumulable con otras promociones\n- No aplica para iPhone\n- Consultar modelos participantes en tienda')
                ");
            }

            // Obtener los descuentos y su estado para el cupón
            $stmt = $this->conn->prepare("
                SELECT 
                    dt.*,
                    COALESCE(cd.usado, FALSE) as usado,
                    cd.fecha_uso
                FROM descuentos_tipos dt
                LEFT JOIN cupones_descuentos cd 
                    ON dt.id = cd.tipo_descuento_id 
                    AND cd.cupon_id = :cupon_code
                ORDER BY dt.id ASC
            ");
            
            $stmt->bindParam(':cupon_code', $cupon_code);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function usarDescuento($cupon_code, $tipo_descuento_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO cupones_descuentos (cupon_id, tipo_descuento_id, usado, fecha_uso)
                VALUES (:cupon_code, :tipo_descuento_id, TRUE, NOW())
            ");
            $stmt->bindParam(':cupon_code', $cupon_code);
            $stmt->bindParam(':tipo_descuento_id', $tipo_descuento_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            throw new Exception("Error al usar descuento: " . $e->getMessage());
        }
    }
    
    public function registerClientWithUUID($uuid, $nombre, $email, $telefono, $tipo_documento, $numero_documento, $selfie_path, $cedula_path) {
        $couponCode = substr(uniqid(), 0, 8);
        $fecha_registro = date('Y-m-d H:i:s');
        
        try {
            $stmt = $this->conn->prepare("INSERT INTO clientes (uuid, nombre, email, telefono, tipo_documento, numero_documento, foto_selfie, foto_cedula, coupon_code, fecha_registro, estado) 
                                        VALUES (:uuid, :nombre, :email, :telefono, :tipo_documento, :numero_documento, :selfie_path, :cedula_path, :coupon_code, :fecha_registro, 'activo')");
            
            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':tipo_documento', $tipo_documento);
            $stmt->bindParam(':numero_documento', $numero_documento);
            $stmt->bindParam(':selfie_path', $selfie_path);
            $stmt->bindParam(':cedula_path', $cedula_path);
            $stmt->bindParam(':coupon_code', $couponCode);
            $stmt->bindParam(':fecha_registro', $fecha_registro);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            die("Error al registrar cliente: " . $e->getMessage());
        }
    }

    public function documentoExiste($tipo, $numero) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM clientes WHERE tipo_documento = :tipo AND numero_documento = :numero");
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':numero', $numero);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            die("Error al verificar documento: " . $e->getMessage());
        }
    }

    public function verifyClient($uuid) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE uuid = :uuid AND estado = 'activo'");
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error al verificar cliente: " . $e->getMessage());
        }
    }

    public function useCoupon($uuid) {
        try {
            $stmt = $this->conn->prepare("UPDATE clientes SET estado = 'usado' WHERE uuid = :uuid");
            $stmt->bindParam(':uuid', $uuid);
            return $stmt->execute();
        } catch(PDOException $e) {
            die("Error al usar cupón: " . $e->getMessage());
        }
    }

    public function getAllClients() {
        try {
            $stmt = $this->conn->query("SELECT * FROM clientes ORDER BY fecha_registro DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error al obtener clientes: " . $e->getMessage());
        }
    }

    public function deleteClient($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            die("Error al eliminar cliente: " . $e->getMessage());
        }
    }

    public function getClientById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error al obtener cliente: " . $e->getMessage());
        }
    }

    public function getClientByCoupon($coupon_code) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE coupon_code = :coupon_code");
            $stmt->bindParam(':coupon_code', $coupon_code);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Error al buscar cupón: " . $e->getMessage());
        }
    }

    public function useCouponByCode($coupon_code) {
        try {
            $stmt = $this->conn->prepare("UPDATE clientes SET estado = 'usado' WHERE coupon_code = :coupon_code AND estado = 'activo'");
            $stmt->bindParam(':coupon_code', $coupon_code);
            return $stmt->execute();
        } catch(PDOException $e) {
            throw new Exception("Error al usar cupón: " . $e->getMessage());
        }
    }

    public function revertirDescuento($cupon_code, $tipo_descuento_id) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM cupones_descuentos 
                WHERE cupon_id = :cupon_code 
                AND tipo_descuento_id = :tipo_descuento_id
            ");
            
            $stmt->bindParam(':cupon_code', $cupon_code);
            $stmt->bindParam(':tipo_descuento_id', $tipo_descuento_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            throw new Exception("Error al revertir descuento: " . $e->getMessage());
        }
    }
}

// Función para generar código QR
function generateQRCode($data) {
    require_once 'phpqrcode/qrlib.php';
    $filename = 'assets/qr/' . $data['uuid'] . '.png';
    QRcode::png($data['uuid'], $filename, QR_ECLEVEL_L, 10);
    return $filename;
}
?>
