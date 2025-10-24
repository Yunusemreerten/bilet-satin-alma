<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);

    if (empty($company_name)) {
        $error_message = 'Firma adı boş bırakılamaz.';
    } else {
                $stmt = $db->prepare("SELECT id FROM Bus_Company WHERE name = ?");
        $stmt->execute([$company_name]);
        if ($stmt->fetch()) {
            $error_message = 'Bu isimde bir firma zaten mevcut.';
        } else {
                        $company_id = uniqid('company_', true);
            
                        $stmt_insert = $db->prepare("INSERT INTO Bus_Company (id, name) VALUES (?, ?)");
            $result = $stmt_insert->execute([$company_id, $company_name]);

            if ($result) {
                                header('Location: manage_companies.php?success=company_added');
                exit;
            } else {
                $error_message = 'Firma eklenirken bir veritabanı hatası oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Firma Ekle - Admin Paneli</title>
    <link href="https:</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>

    <div class="container mt-5">
        <h1>Yeni Firma Ekle</h1>
        <hr>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_company.php" method="POST">
            <div class="mb-3">
                <label for="company_name" class="form-label">Firma Adı</label>
                <input type="text" class="form-control" id="company_name" name="company_name" required>
            </div>
            <a href="manage_companies.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Firmayı Ekle</button>
        </form>
    </div>

</body>
</html>