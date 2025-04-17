<?php
require_once 'includes/functions.php';

// Configuración de límites de subida
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', '30');

// Verificar si es un nuevo registro o si viene con UUID
if (isset($_GET['nuevo_registro'])) {
    // Generar un nuevo UUID
    $uuid = uniqid('', true);
    // Redirigir a la misma página pero con el UUID generado
    header("Location: procesar.php?uuid=" . $uuid);
    exit;
}

// Verificar si se recibió un UUID
if (!isset($_GET['uuid'])) {
    header('Location: index.php');
    exit;
}

$uuid = $_GET['uuid'];
$error = '';
$db = new Database();

// Notificar a index.php que este código fue escaneado
@file_get_contents("http://{$_SERVER['HTTP_HOST']}/tiendaqr/index.php?scanned={$uuid}");

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar tamaño de archivos (máximo 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB en bytes
        
        if ($_FILES['selfie']['size'] > $max_size || $_FILES['foto_cedula']['size'] > $max_size) {
            throw new Exception('Las imágenes no deben superar los 5MB cada una.');
        }

        // Validar documento
        if ($db->documentoExiste($_POST['tipo_documento'], $_POST['numero_documento'])) {
            throw new Exception('Este documento ya está registrado en el sistema.');
        }

        // Procesar las imágenes
        $selfie_path = '';
        $cedula_path = '';
        
        // Validar y guardar selfie
        if (!isset($_FILES['selfie']) || $_FILES['selfie']['error'] !== 0) {
            throw new Exception('Error al subir la selfie: ' . getUploadErrorMessage($_FILES['selfie']['error']));
        }
        
        // Validar y guardar foto de cédula
        if (!isset($_FILES['foto_cedula']) || $_FILES['foto_cedula']['error'] !== 0) {
            throw new Exception('Error al subir la foto de la cédula: ' . getUploadErrorMessage($_FILES['foto_cedula']['error']));
        }

        // Procesar imágenes
        $selfie_path = processImage($_FILES['selfie'], 'selfies');
        $cedula_path = processImage($_FILES['foto_cedula'], 'cedulas');

        // Registrar el cliente
        $result = $db->registerClientWithUUID(
            $uuid,
            $_POST['nombre'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['tipo_documento'],
            $_POST['numero_documento'],
            $selfie_path,
            $cedula_path
        );

        if ($result) {
            header("Location: gracias.php?uuid={$uuid}");
    exit;
        } else {
            throw new Exception('Error al registrar en la base de datos.');
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Función para procesar y guardar imágenes
function processImage($file, $folder) {
    $target_dir = "uploads/" . $folder . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png');
    
    if (!in_array($extension, $allowed)) {
        throw new Exception('Solo se permiten archivos JPG, JPEG y PNG.');
    }
    
    // Generar nombre único
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_file = $target_dir . $filename;
    
    // Intentar optimizar la imagen antes de guardar
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('El archivo no es una imagen válida.');
    }

    // Mover el archivo
    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception('Error al guardar la imagen.');
    }

    return $target_file;
}

// Función para obtener mensajes de error de subida
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'La imagen excede el tamaño máximo permitido por el servidor.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'La imagen excede el tamaño máximo permitido por el formulario.';
        case UPLOAD_ERR_PARTIAL:
            return 'La imagen se subió parcialmente.';
        case UPLOAD_ERR_NO_FILE:
            return 'No se subió ninguna imagen.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta la carpeta temporal del servidor.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Error al escribir el archivo en el servidor.';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extensión de PHP detuvo la subida.';
        default:
            return 'Error desconocido al subir la imagen.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/css.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="text-center">Registro para Obtener Beneficios</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data" id="registroForm">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="tipo_documento" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                        <option value="P">P</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="numero_documento" class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                            </div>

                            <div class="mb-3">
                                <label for="selfie" class="form-label">Selfie</label>
                                <input type="file" class="form-control" id="selfie" name="selfie" accept="image/*" capture="user" required>
                                <small class="text-muted">Toma una selfie con la cámara frontal (máx. 5MB)</small>
                            </div>

                            <div class="mb-3">
                                <label for="foto_cedula" class="form-label">Foto de la Cédula</label>
                                <input type="file" class="form-control" id="foto_cedula" name="foto_cedula" accept="image/*" capture="environment" required>
                                <small class="text-muted">Toma una foto de tu cédula (máx. 5MB)</small>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    Registrar y Obtener Beneficios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        });
    </script>
</body>
</html>
