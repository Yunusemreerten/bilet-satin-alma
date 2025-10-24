<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

// Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_balance = 0;
$stmt_balance = $db->prepare("SELECT balance FROM User WHERE id = ?");
$stmt_balance->execute([$_SESSION['user_id']]);
$user_balance = $stmt_balance->fetchColumn();

$user_id = $_SESSION['user_id'];

// Veritabanından kullanıcıya ait biletleri çekiyoruz.
// En güncel biletin en üstte görünmesi için tarihe göre tersten sıralıyoruz.
// Bize seferin tüm detayları da lazım olduğu için birçok tabloyu birleştiriyoruz (JOIN).
$stmt = $db->prepare("
    SELECT
        t.id AS ticket_id,
        t.status AS ticket_status,
        t.total_price,
        tr.departure_city,
        tr.destination_city,
        tr.departure_time,
        tr.arrival_time,
        bc.name AS company_name
    FROM Tickets t
    JOIN Trips tr ON t.trip_id = tr.id
    JOIN Bus_Company bc ON tr.company_id = bc.id
    WHERE t.user_id = ?
    ORDER BY tr.departure_time DESC
");
$stmt->execute([$user_id]);
$biletler = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">OtobüsBilet</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                         <a class="nav-link" href="#">
                            Merhaba, <?php echo htmlspecialchars($_SESSION['user_full_name']); ?> (Bakiye: <?php echo number_format($user_balance, 2, ',', '.'); ?> TL)
                        </a>
<                   /li>

                    <?php // Kullanıcı rolüne göre farklı linkler göster ?>
                    <?php if (isset($_SESSION['user_role'])): ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin_panel.php">Admin Paneli</a></li>
                        <?php elseif ($_SESSION['user_role'] == 'company_admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="company_admin_panel.php">Firma Paneli</a></li>
                        <?php else: // 'user' rolü için ?>
                            <li class="nav-item"><a class="nav-link active" href="my_tickets.php">Biletlerim</a></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                <?php else: // Giriş yapmamış kullanıcı için ?>
                    <li class="nav-item">
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
        <h1 class="mb-4">Biletlerim</h1>

        <?php
        // Satın alma işleminden sonra gelen başarı mesajını göster
        if (isset($_GET['success']) && $_GET['success'] == 'purchase_complete') {
            echo '<div class="alert alert-success">Biletiniz başarıyla satın alındı!</div>';
        }
        // Bilet iptal işleminden sonra gelen başarı mesajını göster
        if (isset($_GET['success']) && $_GET['success'] == 'cancellation_complete') {
            echo '<div class="alert alert-success">Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.</div>';
        }
        // Bilet iptal işleminden sonra gelen hata mesajını göster
        if (isset($_GET['error']) && $_GET['error'] == 'cancellation_failed') {
            echo '<div class="alert alert-danger">Bilet iptal edilemedi. Sefer saatine 1 saatten az kalmış olabilir.</div>';
        }
        ?>

        <?php if (empty($biletler)): ?>
            <div class="alert alert-info">Henüz satın alınmış bir biletiniz bulunmamaktadır.</div>
        <?php else: ?>
            <?php foreach ($biletler as $bilet): ?>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong><?php echo htmlspecialchars($bilet['departure_city']); ?> &rarr; <?php echo htmlspecialchars($bilet['destination_city']); ?></strong>
                        <span>
                            Durum:
                            <?php if ($bilet['ticket_status'] == 'active'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php elseif ($bilet['ticket_status'] == 'canceled'): ?>
                                <span class="badge bg-danger">İptal Edilmiş</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Geçmiş</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>Firma:</strong> <?php echo htmlspecialchars($bilet['company_name']); ?></p>
                        <p><strong>Kalkış Tarihi:</strong> <?php echo date('d F Y, H:i', strtotime($bilet['departure_time'])); ?></p>
                        <p><strong>Fiyat:</strong> <?php echo htmlspecialchars($bilet['total_price']); ?> TL</p>
                        
                        <?php
                        // İptal etme koşulunu kontrol edelim:
                        // 1. Bilet 'aktif' olmalı.
                        // 2. Seferin kalkış zamanı, şu anki zamandan en az 1 saat (3600 saniye) sonra olmalı.
                        $su_anki_zaman = time();
                        $sefer_zamani = strtotime($bilet['departure_time']);
                        $iptal_edilebilir = ($bilet['ticket_status'] == 'active' && ($sefer_zamani - $su_anki_zaman) > 3600);
                        
                        // Eğer bilet iptal edilebilir durumdaysa, İptal Et butonunu göster.
                        if ($iptal_edilebilir):
                        ?>
                            <a href="cancel_ticket.php?ticket_id=<?php echo $bilet['ticket_id']; ?>" class="btn btn-danger" onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ücret hesabınıza iade edilecektir.');">Bileti İptal Et</a>
                        <?php endif; ?>

                        <!-- TODO: PDF indirme linki eklenecek -->
                        <a href="download_pdf.php?ticket_id=<?php echo $bilet['ticket_id']; ?>" class="btn btn-info">PDF Olarak İndir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>