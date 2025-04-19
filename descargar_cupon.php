<?php
require_once 'includes/functions.php';
require_once 'tcpdf/tcpdf.php'; // Necesitarás instalar TCPDF

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

// Crear nuevo documento PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Tienda Milenium');
$pdf->SetAuthor('Tienda Milenium');
$pdf->SetTitle('Cupón de Beneficios - ' . $cliente['coupon_code']);

// Eliminar encabezado y pie de página predeterminados
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Agregar página
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont('helvetica', 'B', 20);

// Título
$pdf->Cell(0, 10, 'Tienda Milenium', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 16);
$pdf->Cell(0, 10, 'Cupón de Beneficios', 0, 1, 'C');
$pdf->Ln(10);

// Código del cupón
$pdf->SetFont('helvetica', 'B', 24);
$pdf->Cell(0, 15, 'Código: ' . $cliente['coupon_code'], 1, 1, 'C');
$pdf->Ln(10);

// Información del cliente
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Información del Cliente:', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, 'Nombre: ' . $cliente['nombre'], 0, 1);
$pdf->Cell(0, 8, 'Documento: ' . $cliente['tipo_documento'] . '-' . $cliente['numero_documento'], 0, 1);
$pdf->Cell(0, 8, 'Fecha de Registro: ' . $cliente['fecha_registro'], 0, 1);
$pdf->Ln(10);

// Beneficios
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Beneficios Disponibles:', 0, 1);
$pdf->Ln(5);

foreach ($descuentos as $descuento) {
    // Título del beneficio
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, $descuento['nombre'], 0, 1);
    
    // Descripción
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 6, $descuento['descripcion'], 0, 'L');
    
    // Monto del descuento
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'Descuento: $' . number_format($descuento['monto'], 2), 0, 1);
    
    // Condiciones
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->MultiCell(0, 6, 'Condiciones: ' . $descuento['condiciones'], 0, 'L');
    
    $pdf->Ln(5);
}

// Notas importantes
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'Importante:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, "• Este cupón es de uso personal e intransferible\n• Cada beneficio puede ser utilizado una sola vez\n• Presenta este documento en la tienda para redimir tus beneficios\n• Válido hasta que todos los beneficios sean utilizados", 0, 'L');

// Generar el PDF
$pdf->Output('cupon_' . $cliente['coupon_code'] . '.pdf', 'D'); 