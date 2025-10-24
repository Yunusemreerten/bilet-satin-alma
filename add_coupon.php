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
    $code = strtoupper(trim($_POST['code']));     $discount = (float)$_POST['discount'];
    $usage_limit = (int)$_POST['usage_limit'];
    $expire_date = $_POST['expire_date'];

    if (empty($code) || empty($discount) || empty($usage_limit) || empty($expire_date)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } elseif ($discount <= 0 || $discount > 100) {
        $error_message = 'İndirim oranı 1 ile 100 arasında olmalıdır.';
    } else {
                $stmt_check = $db->prepare("SELECT id FROM Coupons WHERE code = ?");
        $stmt_check->execute([$code]);
        if ($stmt_check->fetch()) {
            $error_message = 'Bu kupon kodu zaten mevcut.';
        } else {
            $coupon_id = uniqid('coupon_', true);
            
            $stmt_insert = $db->prepare(
                "INSERT INTO Coupons (id, code, discount, usage_limit, expire_date) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $result = $stmt_insert->execute([$coupon_id, $code, $discount, $usage_limit, $expire_date]);

            if ($result) {
                header('Location: manage_coupons.php?success=coupon_added');
                exit;
            } else {
                $error_message = 'Kupon eklenirken bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Kupon Ekle - Admin Paneli</title>
    <link href="https:</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>
    <div class="container mt-5">
        <h1>Yeni Kupon Ekle</h1>
        <p class="text-muted">Bu kuponlar tüm firmalarda geçerli olacaktır.</p>
        <hr>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_coupon.php" method="POST">
            <div class="mb-3">
                <label for="code" class="form-label">Kupon Kodu</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="mb-3">
                <label for="discount" class="form-label">İndirim Oranı (%)</label>
                <input type="number" class="form-control" id="discount" name="discount" step="1" min="1" max="100" required>
            </div>
            <div class="mb-3">
                <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" required>
            </div>
            <div class="mb-3">
                <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                <input type="date" class="form-control" id="expire_date" name="expire_date" required>
            </div>
            <a href="manage_coupons.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Kuponu Ekle</button>
        </form>
    </div>
</body>
</html>