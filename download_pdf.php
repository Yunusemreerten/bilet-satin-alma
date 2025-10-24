<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/tFPDF.php'; 

if (!isset($_SESSION['user_id']) || !isset($_GET['ticket_id'])) {
    die("Geçersiz istek.");
}

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT
        t.total_price, u.full_name AS passenger_name, tr.departure_city,
        tr.destination_city, tr.departure_time, tr.arrival_time,
        bc.name AS company_name, bs.seat_number
    FROM Tickets t
    JOIN User u ON t.user_id = u.id
    JOIN Trips tr ON t.trip_id = tr.id
    JOIN Bus_Company bc ON tr.company_id = bc.id
    JOIN Booked_Seats bs ON bs.ticket_id = t.id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$ticket_id, $user_id]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet) {
    die("Bilet bulunamadı veya yetkiniz yok.");
}


$pdf = new tFPDF();
$pdf->AddPage();

$pdf->AddFont('DejaVu', '', 'DejaVuSans.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);

$pdf->SetFont('DejaVu', 'B', 16);
$pdf->Cell(0, 10, 'Otobüs Biletiniz', 0, 1, 'C');
$pdf->Ln(10); 

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Yolcu Adı Soyadı:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, $bilet['passenger_name']);
$pdf->Ln();

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Firma:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, $bilet['company_name']);
$pdf->Ln();

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Güzergah:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, $bilet['departure_city'] . ' -> ' . $bilet['destination_city']);
$pdf->Ln();

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Kalkış Zamanı:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, date('d.m.Y H:i', strtotime($bilet['departure_time'])));
$pdf->Ln();

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Koltuk No:');
$pdf->SetFont('DejaVu', 'B', 14);
$pdf->Cell(0, 10, $bilet['seat_number']);
$pdf->Ln();

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 10, 'Fiyat:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, $bilet['total_price'] . ' TL');
$pdf->Ln(20);

$pdf->SetFont('DejaVu', '', 10); 
$pdf->Cell(0, 10, 'İyi yolculuklar dileriz!', 0, 1, 'C');

$pdf->Output('D', 'bilet.pdf');
?>