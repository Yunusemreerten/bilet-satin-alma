<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt_companies = $db->query("SELECT id, name FROM Bus_Company ORDER BY name ASC");
$firmalar = $stmt_companies->fetchAll();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];

    if (empty($full_name) || empty($email) || empty($password) || empty($company_id)) {
        $error_message = 'Lütfen tüm alanları doldurun.';
    } else {
                $stmt_check = $db->prepare("SELECT id FROM User WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $error_message = 'Bu e-posta adresi zaten kullanılıyor.';
        } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_id = uniqid('user_', true);
            $role = 'company_admin';             
                        $stmt_insert = $db->prepare(
                "INSERT INTO User (id, full_name, email, password, role, company_id) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $result = $stmt_insert->execute([$user_id, $full_name, $email, $hashed_password, $role, $company_id]);

            if ($result) {
                                header('Location: manage_company_admins.php?success=admin_added');
                exit;
            } else {
                $error_message = 'Firma admini eklenirken bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Firma Admini Ekle - Admin Paneli</title>
    <link href="https:</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>

    <div class="container mt-5">
        <h1>Yeni Firma Admini Ekle</h1>
        <hr>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_company_admin.php" method="POST">
            <div class="mb-3">
                <label for="full_name" class="form-label">Ad Soyad</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-posta Adresi</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Geçici Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="company_id" class="form-label">Atanacak Firma</label>
                <select class="form-select" id="company_id" name="company_id" required>
                    <option value="" selected disabled>Lütfen bir firma seçin...</option>
                    <?php foreach ($firmalar as $firma): ?>
                        <option value="<?php echo htmlspecialchars($firma['id']); ?>">
                            <?php echo htmlspecialchars($firma['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="manage_company_admins.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Admini Ekle</button>
        </form>
    </div>
</body>
</html>