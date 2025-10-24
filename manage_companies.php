<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $db->query("SELECT * FROM Bus_Company ORDER BY name ASC");
$firmalar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Yönetimi - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Firma Yönetimi</h1>
            <a href="add_company.php" class="btn btn-primary">Yeni Firma Ekle</a>
        </div>

        <?php
        if (isset($_GET['success'])) {
            $message = '';
            if ($_GET['success'] == 'company_added') $message = 'Yeni firma başarıyla eklendi.';
            if ($_GET['success'] == 'company_deleted') $message = 'Firma başarıyla silindi.';
            if ($_GET['success'] == 'company_updated') $message = 'Firma başarıyla güncellendi.';
            if ($message) echo '<div class="alert alert-success">' . $message . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">İşlem sırasında bir hata oluştu.</div>';
        }
        ?>
        
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Firma Adı</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($firmalar)): ?>
                    <tr><td colspan="2" class="text-center">Sistemde kayıtlı firma bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($firmalar as $firma): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($firma['name']); ?></td>
                            <td>
                                <a href="edit_company.php?company_id=<?php echo $firma['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                <a href="delete_company.php?company_id=<?php echo $firma['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu firmayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="admin_panel.php" class="btn btn-secondary mt-3">Geri Dön</a>
    </div>
</body>
</html>