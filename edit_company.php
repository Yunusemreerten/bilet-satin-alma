<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'includes/db.php';

// --- YETKİ KONTROLÜ ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Düzenlenecek firmanın ID'si geldi mi?
if (!isset($_GET['company_id'])) {
    header('Location: manage_companies.php');
    exit;
}
$company_id_to_edit = $_GET['company_id'];
$error_message = '';

// Form gönderildiyse (Güncelleme işlemi)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_company_name = trim($_POST['company_name']);

    if (empty($new_company_name)) {
        $error_message = 'Firma adı boş bırakılamaz.';
    } else {
        $stmt = $db->prepare("UPDATE Bus_Company SET name = ? WHERE id = ?");
        $result = $stmt->execute([$new_company_name, $company_id_to_edit]);

        if ($result) {
            header('Location: manage_companies.php?success=company_updated');
            exit;
        } else {
            $error_message = 'Firma güncellenirken bir hata oluştu.';
        }
    }
}

// Sayfa ilk yüklendiğinde mevcut firma adını çek
$stmt_fetch = $db->prepare("SELECT name FROM Bus_Company WHERE id = ?");
$stmt_fetch->execute([$company_id_to_edit]);
$company = $stmt_fetch->fetch();

if (!$company) {
    // Firma bulunamazsa listeye geri dön
    header('Location: manage_companies.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firmayı Düzenle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>

    <div class="container mt-5">
        <h1>Firmayı Düzenle</h1>
        <hr>
        <form action="edit_company.php?company_id=<?php echo htmlspecialchars($company_id_to_edit); ?>" method="POST">
            <div class="mb-3">
                <label for="company_name" class="form-label">Firma Adı</label>
                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['name']); ?>" required>
            </div>
            <a href="manage_companies.php" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </div>
</body>
</html>