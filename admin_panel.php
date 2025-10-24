<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Bilet Platformu</title>
    <link href="https:</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin_panel.php">Admin Paneli</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">Merhaba, <?php echo htmlspecialchars($_SESSION['user_full_name']); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Yönetim Paneli</h1>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Firma Yönetimi</h5>
                        <p class="card-text">Yeni otobüs firmaları ekleyin, mevcutları düzenleyin veya silin.</p>
                        <a href="manage_companies.php" class="btn btn-primary">Firmaları Yönet</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Firma Admin Yönetimi</h5>
                        <p class="card-text">Yeni firma adminleri oluşturun ve firmalara atayın.</p>
                        <a href="manage_company_admins.php" class="btn btn-primary">Adminleri Yönet</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Kupon Yönetimi</h5>
                        <p class="card-text">Tüm sistem için indirim kuponları oluşturun.</p>
                        <a href="manage_coupons.php" class="btn btn-primary">Kuponları Yönet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>