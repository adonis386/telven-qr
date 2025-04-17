<?php
require_once 'phpqrcode/qrlib.php';

// Obtener el protocolo (http o https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";

// URL base de la aplicación con nuevo_registro=true
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . "/tiendaqr/procesar.php?nuevo_registro=true";

// Asegurarse de que existe el directorio para el QR
if (!file_exists('assets/qr')) {
    mkdir('assets/qr', 0777, true);
}

// Nombre del archivo QR
$qrFile = 'assets/qr/registro_milenium.png';

// Generar el código QR
QRcode::png($baseUrl, $qrFile, QR_ECLEVEL_L, 10);

// Mostrar el QR para imprimir
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Registro - Tienda Milenium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .qr-container {
            text-align: center;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
        }
        .qr-image {
            width: 300px;
            height: 300px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="qr-container">
            <h2>Tienda Milenium</h2>
            <p>Escanea este código QR para registrarte y obtener beneficios exclusivos</p>
            <img src="<?php echo $qrFile; ?>" alt="Código QR" class="qr-image">
            <p class="mt-3"><small>URL generada: <?php echo $baseUrl; ?></small></p>
        </div>
    </div>
</body>
</html> 