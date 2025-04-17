<?php
session_start();
require_once '../includes/functions.php';

// Verificar si el admin está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$mensaje = '';
$tipo_mensaje = '';
$cliente = null;
$descuentos = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['coupon_code'])) {
        $coupon_code = trim($_POST['coupon_code']);
        try {
            $cliente = $db->getClientByCoupon($coupon_code);
            if ($cliente) {
                $descuentos = $db->getDescuentosDisponibles($coupon_code);
            }
        } catch (Exception $e) {
            $tipo_mensaje = 'danger';
            $mensaje = 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['usar_descuento'])) {
        // Procesar el uso del descuento
        $coupon_code = $_POST['usar_descuento_coupon'];
        $tipo_descuento_id = $_POST['usar_descuento_tipo'];
        
        try {
            if ($db->usarDescuento($coupon_code, $tipo_descuento_id)) {
                $tipo_mensaje = 'success';
                $mensaje = '¡Descuento aplicado exitosamente!';
            } else {
                $tipo_mensaje = 'danger';
                $mensaje = 'Error al aplicar el descuento.';
            }
            
            // Recargar la información del cliente
            $cliente = $db->getClientByCoupon($coupon_code);
            if ($cliente) {
                $descuentos = $db->getDescuentosDisponibles($coupon_code);
            }
        } catch (Exception $e) {
            $tipo_mensaje = 'danger';
            $mensaje = 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['revertir_descuento'])) {
        // Procesar la reversión del descuento
        $coupon_code = $_POST['revertir_descuento_coupon'];
        $tipo_descuento_id = $_POST['revertir_descuento_tipo'];
        
        try {
            if ($db->revertirDescuento($coupon_code, $tipo_descuento_id)) {
                $tipo_mensaje = 'success';
                $mensaje = '¡Descuento revertido exitosamente!';
            } else {
                $tipo_mensaje = 'danger';
                $mensaje = 'Error al revertir el descuento.';
            }
            
            // Recargar la información del cliente
            $cliente = $db->getClientByCoupon($coupon_code);
            if ($cliente) {
                $descuentos = $db->getDescuentosDisponibles($coupon_code);
            }
        } catch (Exception $e) {
            $tipo_mensaje = 'danger';
            $mensaje = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Cupón</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Panel Administrativo</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Validar Cupón</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                                <?php echo $mensaje; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" 
                                       name="coupon_code" placeholder="Ingrese el código del cupón"
                                       required autofocus>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>

                        <?php if ($cliente): ?>
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h4 class="mb-0">Información del Cliente</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre']); ?></p>
                                        <p><strong>Documento:</strong> <?php echo $cliente['tipo_documento'] . '-' . $cliente['numero_documento']; ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
                                        <p><strong>Fecha Registro:</strong> <?php echo $cliente['fecha_registro']; ?></p>
                                    </div>
                                </div>

                                <?php if ($descuentos): ?>
                                <div class="mt-4">
                                    <h5>Descuentos Disponibles:</h5>
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
                                                        <small class="text-muted"><?php echo htmlspecialchars($descuento['condiciones']); ?></small>
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
                                                        <form method="POST" action="" onsubmit="return confirmarUso('<?php echo htmlspecialchars($descuento['nombre']); ?>')">
                                                            <input type="hidden" name="usar_descuento_coupon" value="<?php echo $cliente['coupon_code']; ?>">
                                                            <input type="hidden" name="usar_descuento_tipo" value="<?php echo $descuento['id']; ?>">
                                                            <button type="submit" name="usar_descuento" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i> Validar
                                                            </button>
                                                        </form>
                                                        <?php else: ?>
                                                        <form method="POST" action="" onsubmit="return confirmarReversion('<?php echo htmlspecialchars($descuento['nombre']); ?>')">
                                                            <input type="hidden" name="revertir_descuento_coupon" value="<?php echo $cliente['coupon_code']; ?>">
                                                            <input type="hidden" name="revertir_descuento_tipo" value="<?php echo $descuento['id']; ?>">
                                                            <button type="submit" name="revertir_descuento" class="btn btn-sm btn-warning">
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
                                </div>
                                <?php endif; ?>
                            </div>
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