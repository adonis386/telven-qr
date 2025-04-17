<?php
session_start();
require_once '../includes/functions.php';

// Verificar si el admin está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$db = new Database();
$cliente = $db->getClientById($_GET['id']);
$descuentos = $db->getDescuentosDisponibles($cliente['coupon_code']);

// Si se está marcando un descuento como usado o revertiendo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['usar_descuento'])) {
        try {
            $db->usarDescuento($cliente['coupon_code'], $_POST['descuento_id']);
            $mensaje = "Descuento marcado como usado correctamente.";
            $tipo_mensaje = "success";
        } catch (Exception $e) {
            $mensaje = "Error al marcar el descuento: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } elseif (isset($_POST['revertir_descuento'])) {
        try {
            $db->revertirDescuento($cliente['coupon_code'], $_POST['descuento_id']);
            $mensaje = "Descuento revertido correctamente.";
            $tipo_mensaje = "success";
        } catch (Exception $e) {
            $mensaje = "Error al revertir el descuento: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
    // Recargar los descuentos después de cualquier acción
    $descuentos = $db->getDescuentosDisponibles($cliente['coupon_code']);
}

// Verificar si todos los descuentos están usados
$todos_usados = true;
foreach ($descuentos as $descuento) {
    if (!$descuento['usado']) {
        $todos_usados = false;
        break;
    }
}

// Si todos los descuentos están usados, marcar el cupón como completamente usado
if ($todos_usados && $cliente['estado'] === 'activo') {
    $db->useCouponByCode($cliente['coupon_code']);
    $cliente = $db->getClientById($_GET['id']); // Recargar cliente
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Cliente</title>
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
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Nombre:</th>
                                <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            </tr>
                            <tr>
                                <th>Documento:</th>
                                <td><?php echo $cliente['tipo_documento'] . '-' . $cliente['numero_documento']; ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                            </tr>
                            <tr>
                                <th>Cupón:</th>
                                <td>
                                    <span class="badge bg-primary"><?php echo $cliente['coupon_code']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Estado General:</th>
                                <td>
                                    <span class="badge <?php echo $cliente['estado'] === 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $cliente['estado']; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Registro:</th>
                                <td><?php echo $cliente['fecha_registro']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Documentos</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Selfie:</label>
                            <img src="<?php echo '../' . $cliente['foto_selfie']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Cédula:</label>
                            <img src="<?php echo '../' . $cliente['foto_cedula']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Estado de Descuentos</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Descuento</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Fecha Uso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($descuentos as $descuento): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($descuento['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo nl2br(htmlspecialchars($descuento['condiciones'])); ?></small>
                                        </td>
                                        <td>$<?php echo number_format($descuento['monto'], 2); ?></td>
                                        <td>
                                            <?php if ($descuento['usado']): ?>
                                                <span class="badge bg-secondary">Usado</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $descuento['fecha_uso'] ? date('d/m/Y H:i', strtotime($descuento['fecha_uso'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if (!$descuento['usado']): ?>
                                            <form method="POST" onsubmit="return confirmarUso('<?php echo htmlspecialchars($descuento['nombre']); ?>')">
                                                <input type="hidden" name="descuento_id" value="<?php echo $descuento['id']; ?>">
                                                <button type="submit" name="usar_descuento" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Validar
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <form method="POST" onsubmit="return confirmarReversion('<?php echo htmlspecialchars($descuento['nombre']); ?>')">
                                                <input type="hidden" name="descuento_id" value="<?php echo $descuento['id']; ?>">
                                                <button type="submit" name="revertir_descuento" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-undo"></i> Revertir
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($todos_usados): ?>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                Todos los descuentos han sido utilizados.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarUso(nombreDescuento) {
        return confirm(`¿Estás seguro que deseas validar el descuento "${nombreDescuento}"?\n\nEsta acción registrará la fecha y hora de uso.`);
    }

    function confirmarReversion(nombreDescuento) {
        return confirm(`¿Estás seguro que deseas REVERTIR la validación del descuento "${nombreDescuento}"?\n\nEsto eliminará el registro de uso.`);
    }
    </script>
</body>
</html> 