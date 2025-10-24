<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'includes/db.php';

// --- YETKİ KONTROLÜ ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['admin_id'])) {
    header('Location: manage_company_admins.php');
    exit;
}
$admin_id_to_edit = $_GET['admin_id'];

// Firma seçimi için tüm firmaları çek
$stmt_companies = $db->query("SELECT id, name FROM Bus_Company ORDER BY name ASC");
$firmalar = $stmt_companies->fetchAll();

// Form gönderildiyse (Güncelleme işlemi)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $company_id = $_POST['company_id'];

    $stmt_update = $db->prepare("UPDATE User SET full_name = ?, email = ?, company_id = ? WHERE id = ? AND role = 'company_admin'");
    $result = $stmt_update->execute([$full_name, $email, $company_id, $admin_id_to_edit]);

    if ($result) {
        header('Location: manage_company_admins.php?success=admin_updated');
        exit;
    }
}

// Sayfa ilk yüklendiğinde adminin mevcut bilgilerini çek
$stmt_fetch = $db->prepare("SELECT full_name, email, company_id FROM User WHERE id = ?");
$stmt_fetch->execute([$admin_id_to_edit]);
$admin = $stmt_fetch->fetch();

if (!$admin) {
    header('Location: manage_company_admins.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Adminini Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>
    <div class="container mt-5">
        <h1>Firma Adminini Düzenle</h1>
        <hr>
        <form action="edit_company_admin.php?admin_id=<?php echo htmlspecialchars($admin_id_to_edit); ?>" method="POST">
            <div class="mb-3">
                <label>Ad Soyad</label>
                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label>E-posta</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Atanacak Firma</label>
                <select class="form-select" name="company_id" required>
                    <?php foreach ($firmalar as $firma): ?>
                        <option value="<?php echo $firma['id']; ?>" <?php if ($firma['id'] == $admin['company_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($firma['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="manage_company_admins.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </div>
</body>
</html>