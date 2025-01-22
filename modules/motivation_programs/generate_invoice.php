<?php
require_once '../../db/db.php';
require '../../../vendor/autoload.php';
require 'fpdf.php'; // Ensure you have FPDF installed

session_start();

// Check if program ID is passed
if (!isset($_GET['program_id']) || empty($_GET['program_id'])) {
    die('Program not found!');
}

$programId = intval($_GET['program_id']);
$userId = $_SESSION['user_id'];

// Fetch program and payment details
$programSql = "SELECT title, description, date, time, location, price FROM programs WHERE id = ?";
$stmt = $conn->prepare($programSql);
$stmt->bind_param("i", $programId);
$stmt->execute();
$programResult = $stmt->get_result();
$program = $programResult->fetch_assoc();
$stmt->close();

$paymentSql = "SELECT payment_intent_id, amount, created_at FROM payments WHERE user_id = ? AND program_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($paymentSql);
$stmt->bind_param("ii", $userId, $programId);
$stmt->execute();
$paymentResult = $stmt->get_result();
$payment = $paymentResult->fetch_assoc();
$stmt->close();

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'Invoice', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Program Title:', 0, 0);
$pdf->Cell(100, 10, $program['title'], 0, 1);

$pdf->Cell(50, 10, 'Description:', 0, 0);
$pdf->MultiCell(100, 10, $program['description'], 0, 1);

$pdf->Cell(50, 10, 'Date:', 0, 0);
$pdf->Cell(100, 10, $program['date'] . ' at ' . $program['time'], 0, 1);

$pdf->Cell(50, 10, 'Location:', 0, 0);
$pdf->Cell(100, 10, $program['location'], 0, 1);

$pdf->Cell(50, 10, 'Payment Amount:', 0, 0);
$pdf->Cell(100, 10, 'RM ' . number_format($payment['amount'], 2), 0, 1);

$pdf->Cell(50, 10, 'Payment ID:', 0, 0);
$pdf->Cell(100, 10, $payment['payment_intent_id'], 0, 1);

$pdf->Cell(50, 10, 'Payment Date:', 0, 0);
$pdf->Cell(100, 10, $payment['created_at'], 0, 1);

$pdf->Ln(10);
$pdf->Cell(0, 10, 'Thank you for your payment!', 0, 1, 'C');

// Output PDF
$pdf->Output('D', 'invoice_' . $programId . '.pdf'); // Forces download with dynamic name
