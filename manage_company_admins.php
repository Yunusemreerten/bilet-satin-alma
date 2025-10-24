<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $db->query("
    SELECT u.id, u.full_name, u.email, COALESCE(bc.name, 'Atanmamış') AS company_name 
    FROM User u 
    LEFT JOIN Bus_Company bc ON u.company_id = bc.id 
    WHERE u.role = 'company_admin' 
    ORDER BY u.full_name ASC
");
$company_admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Admin Yönetimi - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Firma Admin Yönetimi</h1>
            <a href="add_company_admin.php" class="btn btn-primary">Yeni Admin Ekle</a>
        </div>

        <?php
        if (isset($_GET['success'])) {
            $message = '';
            if ($_GET['success'] == 'admin_added') $message = 'Yeni firma admini başarıyla eklendi.';
            if ($_GET['success'] == 'admin_deleted') $message = 'Firma admininin rolü normale çevrildi.';
            if ($_GET['success'] == 'admin_updated') $message = 'Firma admini başarıyla güncellendi.';
            if ($message) echo '<div class="alert alert-success">' . $message . '</div>';
        }
        ?>
        
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Atandığı Firma</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($company_admins)): ?>
                    <tr><td colspan="4" class="text-center">Sistemde kayıtlı firma admini bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($company_admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['company_name']); ?></td>
                            <td>
                                <a href="edit_company_admin.php?admin_id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                <a href="delete_company_admin.php?admin_id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcının firma admini yetkisini kaldırmak istediğinizden emin misiniz? Kullanıcı silinmeyecek, rolü normale dönecektir.');">Yetkiyi Kaldır</a>
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