<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

// Bakiye çekme kodu
$user_balance = 0;
if (isset($_SESSION['user_id'])) {
    $stmt_balance = $db->prepare("SELECT balance FROM User WHERE id = ?");
    $stmt_balance->execute([$_SESSION['user_id']]);
    $user_balance = $stmt_balance->fetchColumn();
}

$seferler = [];
// Arama yapıldıysa, sadece arama sonuçlarını getir
if (isset($_GET['departure_city']) && isset($_GET['destination_city']) && !empty($_GET['departure_city']) && !empty($_GET['destination_city'])) {
    $kalkis_yeri = $_GET['departure_city'];
    $varis_yeri = $_GET['destination_city'];

    $query = "SELECT 
                    Trips.*, 
                    Bus_Company.name AS company_name 
                FROM Trips 
                JOIN Bus_Company ON Trips.company_id = Bus_Company.id
                WHERE departure_city LIKE ? AND destination_city LIKE ?
                ORDER BY departure_time ASC";
    
    $stmt = $db->prepare($query);
    // LIKE sorgusu için joker karakterler ekliyoruz (%)
    $stmt->execute(['%'.$kalkis_yeri.'%', '%'.$varis_yeri.'%']);
    $seferler = $stmt->fetchAll();
} else {
    // --- YENİ EKLENEN KISIM ---
    // Sayfa ilk açıldığında veya arama yapılmadığında, tüm seferleri listele
    // Yaklaşan seferlerin en üstte görünmesi için tarihe göre sırala
    $query = "SELECT 
                    Trips.*, 
                    Bus_Company.name AS company_name 
                FROM Trips 
                JOIN Bus_Company ON Trips.company_id = Bus_Company.id
                WHERE departure_time > datetime('now', 'localtime')
                ORDER BY departure_time ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $seferler = $stmt->fetchAll();
    // --- BİTTİ ---
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Satın Alma Platformu</title>
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
                    </li>
                    <?php if (isset($_SESSION['user_role'])): ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin_panel.php">Admin Paneli</a></li>
                        <?php elseif ($_SESSION['user_role'] == 'company_admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="company_admin_panel.php">Firma Paneli</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="my_tickets.php">Biletlerim</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="text-center"><h1>Sefer Arama</h1></div>
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card p-4">
                <form action="index.php" method="GET">
                    <div class="row">
                        <div class="col-md-5 mb-2"><input type="text" class="form-control" name="departure_city" placeholder="Kalkış Şehri"></div>
                        <div class="col-md-5 mb-2"><input type="text" class="form-control" name="destination_city" placeholder="Varış Şehri"></div>
                        <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary">Sefer Bul</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h2 class="text-center mb-4">Yaklaşan Seferler</h2>
        <?php if (count($seferler) > 0): ?>
            <?php foreach ($seferler as $sefer): ?>
                <div class="card mb-3">
                    <div class="card-body row align-items-center">
                        <div class="col-md-2"><strong><?php echo htmlspecialchars($sefer['company_name']); ?></strong></div>
                        <div class="col-md-3">Kalkış: <strong><?php echo htmlspecialchars($sefer['departure_city']); ?></strong><br>Saat: <strong><?php echo date('H:i', strtotime($sefer['departure_time'])); ?></strong></div>
                        <div class="col-md-3">Varış: <strong><?php echo htmlspecialchars($sefer['destination_city']); ?></strong><br>Tarih: <strong><?php echo date('d.m.Y', strtotime($sefer['departure_time'])); ?></strong></div>
                        <div class="col-md-2 text-center">Fiyat: <h3><?php echo htmlspecialchars($sefer['price']); ?> TL</h3></div>
                        <div class="col-md-2 d-grid"><a href="buy_ticket.php?trip_id=<?php echo $sefer['id']; ?>" class="btn btn-success">Bilet Al</a></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center">Aradığınız kriterlere uygun sefer bulunamadı.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>