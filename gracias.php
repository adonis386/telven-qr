<?php
require_once 'includes/functions.php';

if (!isset($_GET['uuid'])) {
    header('Location: index.php');
    exit;
}

$uuid = $_GET['uuid'];
$db = new Database();
$cliente = $db->verifyClient($uuid);

if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Obtener todos los descuentos disponibles
$descuentos = $db->getDescuentosDisponibles($cliente['coupon_code']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Gracias por Registrarte! - Tienda Milenium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .benefit-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .coupon-code {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            padding: 10px 20px;
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            display: inline-block;
            background-color: #fff;
        }
        .icon-benefit {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .discount-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: #198754;
        }
        .conditions-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #dee2e6;
        }
        .conditions-list {
            margin: 0;
            padding-left: 20px;
            list-style-type: none;
        }
        .conditions-list li {
            position: relative;
            padding-left: 15px;
            margin-bottom: 4px;
        }
        .conditions-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5 shadow">
                    <div class="card-body text-center">
                        <h1 class="mb-3">Tienda Milenium</h1>
                        
                        <h2 class="card-title mb-4">
                            <i class="fas fa-check-circle text-success"></i>
                            ¡Gracias por Registrarte!
                        </h2>
                        
                        <div class="mb-4">
                            <p class="lead">Tu código de cupón es:</p>
                            <div class="coupon-code mb-3">
                                <?php echo $cliente['coupon_code']; ?>
                            </div>
                            <p class="text-muted small">Guarda este código, lo necesitarás para reclamar tus beneficios en Tienda Milenium</p>
                            
                            <!-- Botón de descarga opcional -->
                            <div class="mt-4">
                                <a href="descargar_cupon.php?uuid=<?php echo $uuid; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> Descargar mis beneficios en PDF
                                </a>
                                <div class="text-muted small mt-2">
                                    <i class="fas fa-info-circle"></i> 
                                    Puedes descargar tus beneficios para consultarlos sin conexión a internet
                                </div>
                            </div>
                        </div>

                        <h3 class="mb-4">Tus Beneficios Exclusivos:</h3>
                        
                        <!-- Chip Gratuito -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-sim-card icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Chip Gratuito</h4>
                                    <p class="mb-0">Obtén un chip completamente gratis para tu línea telefónica</p>
                                    <div class="conditions-text">
                                        <strong>Condiciones:</strong> No aplican condiciones adicionales
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accesorios -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-headphones icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Accesorios</h4>
                                    <p class="mb-0">Descuento de <span class="discount-amount">$3</span> en accesorios</p>
                                    <div class="conditions-text">
                                        <strong>Importante:</strong> No válido para productos de marca Cubitt
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cubitt -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-mobile-alt icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Productos Cubitt Seleccionados</h4>
                                    <p class="mb-0">Descuento de <span class="discount-amount">$5</span> en productos Cubitt específicos</p>
                                    <div class="conditions-text">
                                        <strong>Válido únicamente para:</strong>
                                        <ul class="conditions-list">
                                            <li>Relojes Cubitt</li>
                                            <li>Cornetas Cubitt</li>
                                            <li>Balanza Cubitt</li>
                                        </ul>
                                        <strong>Nota:</strong> No válido para otros productos de la marca
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipos Internet -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-wifi icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Equipos de Internet</h4>
                                    <p class="mb-0">Descuento de <span class="discount-amount">$5</span> en equipos de internet</p>
                                    <div class="conditions-text">
                                        <strong>Condiciones:</strong> Válido para todos los equipos de internet disponibles
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Teléfonos Gama Baja -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-mobile icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Teléfonos Gama Baja</h4>
                                    <p class="mb-0">Descuento de <span class="discount-amount">$3</span> en teléfonos gama baja</p>
                                    <div class="conditions-text">
                                        <strong>Condiciones:</strong> Aplica para teléfonos con precio menor a $200
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Teléfonos Gama Alta -->
                        <div class="benefit-card text-start">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-mobile-alt icon-benefit"></i>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">Teléfonos Gama Alta</h4>
                                    <p class="mb-0">Descuento de <span class="discount-amount">$5</span> en teléfonos gama alta</p>
                                    <div class="conditions-text">
                                        <strong>Condiciones importantes:</strong>
                                        <ul class="conditions-list">
                                            <li>Solo aplica en compras mayores a $200</li>
                                            <li>No acumulable con otras promociones</li>
                                            <li>Consultar modelos participantes en tienda</li>
                                            <li>Sujeto a disponibilidad</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Importante:</strong> Puedes usar cada descuento una vez. El cupón seguirá activo hasta que uses todos los beneficios disponibles.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
