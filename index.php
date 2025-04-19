<?php
require_once 'phpqrcode/qrlib.php';
require_once 'includes/functions.php';

// IP fija del servidor
$server_ip = '192.168.0.169';

// Verificar si hay un UUID en uso
$last_uuid_file = 'temp/last_uuid.txt';
$current_uuid = '';

if (file_exists($last_uuid_file)) {
    $stored_data = json_decode(file_get_contents($last_uuid_file), true);
    
    // Si el UUID anterior fue escaneado o han pasado más de 5 minutos, generar uno nuevo
    if (isset($stored_data['last_check']) && 
        (time() - $stored_data['last_check'] > 300 || // 5 minutos
        isset($_GET['scanned']) && $_GET['scanned'] == $stored_data['uuid'])) {
        $current_uuid = substr(uniqid(), 0, 8);
    } else {
        $current_uuid = $stored_data['uuid'];
    }
} else {
    $current_uuid = substr(uniqid(), 0, 8);
}

// Guardar el UUID actual y el timestamp
$data_to_store = [
    'uuid' => $current_uuid,
    'last_check' => time()
];
if (!file_exists('temp')) {
    mkdir('temp');
}
file_put_contents($last_uuid_file, json_encode($data_to_store));

// Construir la URL completa
$qr_url = "http://{$server_ip}/tiendaqr/procesar.php?uuid={$current_uuid}";

// Generar el archivo QR
$qr_file = 'temp/qr_' . $current_uuid . '.png';
QRcode::png($qr_url, $qr_file, QR_ECLEVEL_L, 10);

// Agregar JavaScript para actualizar la página
$refresh_script = "";
if (isset($_GET['scanned'])) {
    $refresh_script = "
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1000);
        </script>
    ";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Nuestra Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/css.css" rel="stylesheet">
    <!-- Auto refresh cada 5 minutos -->
    <meta http-equiv="refresh" content="300">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="welcome-container text-center">
                    <h1 class="display-4 mb-4">¡Bienvenido a Nuestra Tienda!</h1>
                    <p class="lead mb-4">Escanea el código QR para obtener beneficios exclusivos</p>
                    
                    <div class="qr-container">
                        <div id="qr-code" class="mb-3">
                            <img src="<?php echo $qr_file; ?>?v=<?php echo time(); ?>" 
                                 alt="Código QR" 
                                 class="img-fluid" 
                                 style="max-width: 200px;">
                            <p class="mt-2 small text-muted">Servidor: <?php echo $server_ip; ?></p>
                            <!-- Debug info -->
                            <p class="mt-2 small">Código único: <?php echo $current_uuid; ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="mb-3">Beneficios al escanear:</h3>
                        <ul class="benefits-list text-start">
                            <li>Línea telefónica gratuita por tiempo limitado</li>
                            <li>Cupón de descuento de $2 a $8 en productos seleccionados</li>
                            <li>Acceso a ofertas exclusivas</li>
                            <li>Registro automático en nuestro programa de fidelidad</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $refresh_script; ?>
</body>
</html>