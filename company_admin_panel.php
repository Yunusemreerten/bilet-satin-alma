<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company_admin') {
    header('Location: index.php');
    exit;
}



$admin_user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT company_id FROM User WHERE id = ?");
$stmt->execute([$admin_user_id]);
$company_id = $stmt->fetchColumn();

$stmt_trips = $db->prepare("SELECT * FROM Trips WHERE company_id = ? ORDER BY created_date DESC");
$stmt_trips->execute([$company_id]);
$seferler = $stmt_trips->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Paneli - Bilet Platformu</title>
    <link href="https:</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">OtobüsBilet</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Merhaba, <?php echo htmlspecialchars($_SESSION['user_full_name']); ?></a>
                    </li>

                    <?php                     <?php if (isset($_SESSION['user_role'])): ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin_panel.php">Admin Paneli</a></li>
                        <?php elseif ($_SESSION['user_role'] == 'company_admin'): ?>
                            <li class="nav-item"><a class="nav-link active" href="company_admin_panel.php">Firma Paneli</a></li>
                        <?php else:                             <li class="nav-item"><a class="nav-link" href="my_tickets.php">Biletlerim</a></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                <?php else:                     <li class="nav-item">
                        <a class="nav-link" href="login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
            </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
    <?php
            if (isset($_GET['success']) && $_GET['success'] == 'trip_updated') {
        echo '<div class="alert alert-success">Sefer başarıyla güncellendi.</div>';
    }

        if (isset($_GET['success']) && $_GET['success'] == 'trip_added') {
        echo '<div class="alert alert-success">Yeni sefer başarıyla eklendi.</div>';
    }
        if (isset($_GET['success']) && $_GET['success'] == 'trip_deleted') {
        echo '<div class="alert alert-success">Sefer başarıyla silindi.</div>';
    }
        if (isset($_GET['error']) && $_GET['error'] == 'delete_failed') {
        echo '<div class="alert alert-danger">Sefer silinirken bir hata oluştu.</div>';
    }
    ?>
    <!-- ... sayfanın geri kalanı ... -->

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Sefer Yönetimi</h1>
            <a href="add_trip.php" class="btn btn-primary">Yeni Sefer Ekle</a>
        </div>

   

        <!-- TODO: Buraya seferleri listeleyeceğimiz bir tablo gelecek -->

        <?php if (empty($seferler)): ?>
            <div class="alert alert-info">
                Henüz firmanıza ait bir sefer bulunmamaktadır.
            </div>
        <?php else: ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Kalkış</th>
                        <th>Varış</th>
                        <th>Kalkış Zamanı</th>
                        <th>Fiyat</th>
                        <th>Kapasite</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seferler as $sefer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sefer['departure_city']); ?></td>
                            <td><?php echo htmlspecialchars($sefer['destination_city']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($sefer['departure_time'])); ?></td>
                            <td><?php echo htmlspecialchars($sefer['price']); ?> TL</td>
                            <td><?php echo htmlspecialchars($sefer['capacity']); ?></td>
                            <td>
                                <!-- TODO: Bu butonlar düzenleme ve silme sayfalarına yönlendirecek -->
                                <a href="edit_trip.php?trip_id=<?php echo $sefer['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                <a href="delete_trip.php?trip_id=<?php echo $sefer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu seferi kalıcı olarak silmek istediğinizden emin misiniz?');">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>