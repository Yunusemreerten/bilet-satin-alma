<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

// --- YETKİ KONTROLÜ ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company_admin') {
    header('Location: index.php');
    exit;
}

// Düzenlenecek seferin ID'si URL ile gelmediyse panele geri yolla.
if (!isset($_GET['trip_id'])) {
    header('Location: company_admin_panel.php');
    exit;
}

$trip_id_to_edit = $_GET['trip_id'];
$error_message = '';

// --- Form Gönderildiyse (GÜNCELLEME İŞLEMİ) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen yeni verileri al
    $departure_city = $_POST['departure_city'];
    $destination_city = $_POST['destination_city'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

    if (empty($departure_city) || empty($destination_city) || empty($departure_time) || empty($arrival_time) || empty($price) || empty($capacity)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } else {
        // Güvenlik: Adminin firma ID'sini alarak bu seferi düzenleme yetkisi var mı kontrol et
        $stmt_company = $db->prepare("SELECT company_id FROM User WHERE id = ?");
        $stmt_company->execute([$_SESSION['user_id']]);
        $company_id = $stmt_company->fetchColumn();

        $stmt_update = $db->prepare("
            UPDATE Trips 
            SET departure_city = ?, destination_city = ?, departure_time = ?, arrival_time = ?, price = ?, capacity = ?
            WHERE id = ? AND company_id = ?
        ");
        $result = $stmt_update->execute([$departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity, $trip_id_to_edit, $company_id]);

        if ($result) {
            header('Location: company_admin_panel.php?success=trip_updated');
            exit;
        } else {
            $error_message = 'Sefer güncellenirken bir hata oluştu.';
        }
    }
}

// --- Sayfa İlk Yüklendiğinde (MEVCUT BİLGİLERİ ÇEKME) ---
$stmt = $db->prepare("SELECT * FROM Trips WHERE id = ?");
$stmt->execute([$trip_id_to_edit]);
$sefer = $stmt->fetch();

// Eğer sefer bulunamazsa panele geri yolla.
if (!$sefer) {
    header('Location: company_admin_panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seferi Düzenle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="company_admin_panel.php">Firma Admin Paneli</a></div>
    </nav>

    <div class="container mt-5">
        <h1>Seferi Düzenle</h1>
        <hr>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="edit_trip.php?trip_id=<?php echo $sefer['id']; ?>" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="departure_city" class="form-label">Kalkış Şehri</label>
                    <input type="text" class="form-control" id="departure_city" name="departure_city" value="<?php echo htmlspecialchars($sefer['departure_city']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="destination_city" class="form-label">Varış Şehri</label>
                    <input type="text" class="form-control" id="destination_city" name="destination_city" value="<?php echo htmlspecialchars($sefer['destination_city']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="departure_time" class="form-label">Kalkış Zamanı</label>
                    <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" value="<?php echo date('Y-m-d\TH:i', strtotime($sefer['departure_time'])); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="arrival_time" class="form-label">Varış Zamanı</label>
                    <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" value="<?php echo date('Y-m-d\TH:i', strtotime($sefer['arrival_time'])); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Fiyat (TL)</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($sefer['price']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label">Otobüs Kapasitesi</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo htmlspecialchars($sefer['capacity']); ?>" required>
                </div>
            </div>
            <a href="company_admin_panel.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
        </form>
    </div>
</body>
</html>