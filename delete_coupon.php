<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['coupon_id'])) {
    header('Location: manage_coupons.php');
    exit;
}

$coupon_id_to_delete = $_GET['coupon_id'];

try {
    // İlişkili User_Coupons kayıtlarını da silmek iyi bir pratiktir.
    $stmt_rel = $db->prepare("DELETE FROM User_Coupons WHERE coupon_id = ?");
    $stmt_rel->execute([$coupon_id_to_delete]);

    // Kuponu sil
    $stmt = $db->prepare("DELETE FROM Coupons WHERE id = ?");
    $stmt->execute([$coupon_id_to_delete]);

    header('Location: manage_coupons.php?success=coupon_deleted');
    exit;

} catch (Exception $e) {
    header('Location: manage_coupons.php?error=delete_failed');
    exit;
}
?>```

---

### **Adım 3: `manage_coupons.php` Sayfasını Son Haline Getirme**

Şimdi kupon listeleme sayfasındaki butonları bu yeni dosyalara yönlendirelim ve mesajları ekleyelim.

Aşağıda `manage_coupons.php` dosyasının **güncellenmiş son halini** veriyorum. Lütfen mevcut dosyanın içeriğini bununla tamamen değiştir.

```php
<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $db->query("SELECT * FROM Coupons WHERE company_id IS NULL ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kupon Yönetimi - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="admin_panel.php">Admin Paneli</a></div>
    </nav>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kupon Yönetimi</h1>
            <a href="add_coupon.php" class="btn btn-primary">Yeni Kupon Ekle</a>
        </div>
        
        <?php
        if (isset($_GET['success'])) {
            $message = '';
            if ($_GET['success'] == 'coupon_added') $message = 'Yeni kupon başarıyla eklendi.';
            if ($_GET['success'] == 'coupon_deleted') $message = 'Kupon başarıyla silindi.';
            if ($message) echo '<div class="alert alert-success">' . $message . '</div>';
        }
        ?>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Kod</th>
                    <th>İndirim Oranı (%)</th>
                    <th>Kullanım Limiti</th>
                    <th>Son Kullanma Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                    <tr><td colspan="5" class="text-center">Sistemde genel kupon bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                            <td><?php echo htmlspecialchars($coupon['discount']); ?></td>
                            <td><?php echo htmlspecialchars($coupon['usage_limit']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($coupon['expire_date'])); ?></td>
                            <td>
                                <a href="delete_coupon.php?coupon_id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">Sil</a>
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