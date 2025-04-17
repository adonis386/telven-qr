<?php
session_start();
require_once '../includes/functions.php';

// Verificar si el admin está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$error = '';
$success = '';

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = $_GET['id'];
$cliente = $db->getClientById($id);

// Si no existe el cliente, redirigir
if (!$cliente) {
    header('Location: dashboard.php');
    exit;
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $db->updateClient(
            $id,
            $_POST['nombre'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['tipo_documento'],
            $_POST['numero_documento'],
            $_POST['estado']
        );
        
        if ($result) {
            $success = 'Cliente actualizado correctamente';
            $cliente = $db->getClientById($id); // Recargar datos
        }
    } catch (Exception $e) {
        $error = 'Error al actualizar: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Panel Administrativo</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Editar Cliente</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="tipo_documento" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                        <option value="V" <?php echo $cliente['tipo_documento'] === 'V' ? 'selected' : ''; ?>>V</option>
                                        <option value="E" <?php echo $cliente['tipo_documento'] === 'E' ? 'selected' : ''; ?>>E</option>
                                        <option value="P" <?php echo $cliente['tipo_documento'] === 'P' ? 'selected' : ''; ?>>P</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="numero_documento" class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                           value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="activo" <?php echo $cliente['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="usado" <?php echo $cliente['estado'] === 'usado' ? 'selected' : ''; ?>>Usado</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h4>Documentos Actuales</h4>
                            <div class="mb-3">
                                <label class="form-label">Selfie actual:</label>
                                <img src="<?php echo '../' . $cliente['foto_selfie']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto Cédula actual:</label>
                                <img src="<?php echo '../' . $cliente['foto_cedula']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 