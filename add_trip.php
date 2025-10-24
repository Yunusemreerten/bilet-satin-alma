<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company_admin') {
    header('Location: index.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $departure_city = $_POST['departure_city'];
    $destination_city = $_POST['destination_city'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

        if (empty($departure_city) || empty($destination_city) || empty($departure_time) || empty($arrival_time) || empty($price) || empty($capacity)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } else {
                $stmt_company = $db->prepare("SELECT company_id FROM User WHERE id = ?");
        $stmt_company->execute([$_SESSION['user_id']]);
        $company_id = $stmt_company->fetchColumn();

        if ($company_id) {
                        $trip_id = uniqid('trip_', true);
            
                        $stmt = $db->prepare("
                INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$trip_id, $company_id, $departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity]);

            if ($result) {
                                header('Location: company_admin_panel.php?success=trip_added');
                exit;
            } else {
                $error_message = 'Sefer eklenirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        } else {
            $error_message = 'Firma bilgisi alınamadı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Sefer Ekle - Firma Paneli</title>
    <link href="https:</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="company_admin_panel.php">Firma Admin Paneli</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Yeni Sefer Ekle</h1>
        <hr>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_trip.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="departure_city" class="form-label">Kalkış Şehri</label>
                    <input type="text" class="form-control" id="departure_city" name="departure_city" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="destination_city" class="form-label">Varış Şehri</label>
                    <input type="text" class="form-control" id="destination_city" name="destination_city" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="departure_time" class="form-label">Kalkış Zamanı</label>
                    <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="arrival_time" class="form-label">Varış Zamanı</label>
                    <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Fiyat (TL)</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label">Otobüs Kapasitesi</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" required>
                </div>
            </div>
            <a href="company_admin_panel.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Seferi Ekle</button>
        </form>
    </div>

</body>
</html>