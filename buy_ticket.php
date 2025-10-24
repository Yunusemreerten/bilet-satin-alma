<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=login_required');
    exit;
}

$user_balance = 0;
$stmt_balance = $db->prepare("SELECT balance FROM User WHERE id = ?");
$stmt_balance->execute([$_SESSION['user_id']]);
$user_balance = $stmt_balance->fetchColumn();

if (!isset($_GET['trip_id'])) {
    header('Location: index.php');
    exit;
}
$trip_id = $_GET['trip_id'];

$stmt = $db->prepare("
    SELECT Trips.*, Bus_Company.name as company_name 
    FROM Trips 
    JOIN Bus_Company ON Trips.company_id = Bus_Company.id 
    WHERE Trips.id = ?
");
$stmt->execute([$trip_id]);
$sefer = $stmt->fetch();

if (!$sefer) {
    header('Location: index.php');
    exit;
}

$stmt_seats = $db->prepare("
    SELECT bs.seat_number 
    FROM Booked_Seats bs
    JOIN Tickets t ON bs.ticket_id = t.id
    WHERE t.trip_id = ? AND t.status = 'active'
");
$stmt_seats->execute([$trip_id]);
$dolu_koltuklar = $stmt_seats->fetchAll(PDO::FETCH_COLUMN, 0);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al - <?php echo htmlspecialchars($sefer['departure_city'] . ' - ' . $sefer['destination_city']); ?></title>
    <link href="https:    <style>
        .seat { width: 40px; height: 40px; border: 1px solid #ccc; margin: 5px; display: flex; justify-content: center; align-items: center; cursor: pointer; border-radius: 5px; }
        .seat.disabled { background-color: #f0f0f0; color: #999; cursor: not-allowed; }
        .seat.selected { background-color: #28a745; color: white; }
    </style>
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
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
            </div>
        </div>
    </nav>

   <div class="container mt-5">
    <?php
    if (isset($_GET['error'])) {
        $error_message = '';
        if ($_GET['error'] == 'insufficient_balance') $error_message = 'Bilet almak için yeterli bakiyeniz bulunmamaktadır.';
        if ($_GET['error'] == 'seat_taken') $error_message = 'Üzgünüz, siz işlem yaparken seçtiğiniz koltuk başka bir kullanıcı tarafından satın alındı. Lütfen başka bir koltuk seçin.';
        if ($error_message) echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    ?>
    <div class="row">
        <div class="col-md-4">
            <h3>Sefer Bilgileri</h3>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($sefer['company_name']); ?></h5>
                    <p class="card-text">
                        <strong>Nereden:</strong> <?php echo htmlspecialchars($sefer['departure_city']); ?><br>
                        <strong>Nereye:</strong> <?php echo htmlspecialchars($sefer['destination_city']); ?><br>
                        <strong>Kalkış:</strong> <?php echo date('d.m.Y H:i', strtotime($sefer['departure_time'])); ?><br>
                        <strong>Varış:</strong> <?php echo date('d.m.Y H:i', strtotime($sefer['arrival_time'])); ?><br>
                        <strong>Fiyat:</strong> <?php echo htmlspecialchars($sefer['price']); ?> TL
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <h3>Koltuk Seçimi</h3>
            <form action="process_purchase.php" method="POST">
                <div class="card p-3 d-flex flex-wrap flex-row">
                    <?php for ($i = 1; $i <= $sefer['capacity']; $i++): ?>
                        <?php $is_disabled = in_array($i, $dolu_koltuklar); ?>
                        <div class="seat <?php if ($is_disabled) echo 'disabled'; ?>">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="row justify-content-end align-items-center mt-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="coupon_code" placeholder="İndirim Kuponu Kodu">
                            <button class="btn btn-outline-secondary" type="button" id="apply_coupon_btn">Uygula</button>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($sefer['id']); ?>">
                <input type="hidden" name="selected_seat" id="selected_seat_input">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($sefer['price']); ?>">
                
                <div class="d-grid mt-3">
                    <button type="submit" id="buy_button" class="btn btn-primary" disabled>Satın Al</button>
                </div>
            </form>
        </div>
        <!-- FAZLADAN OLAN BUTONUN VE DIV'LERİN OLDUĞU BÖLÜM SİLİNDİ -->
    </div>
</div>
</body>
<script>
    const seats = document.querySelectorAll('.seat');
    const selectedSeatInput = document.getElementById('selected_seat_input');
    const buyButton = document.getElementById('buy_button');
    let currentSelected = null;
    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            if (seat.classList.contains('disabled')) { return; }
            if (currentSelected) { currentSelected.classList.remove('selected'); }
            seat.classList.add('selected');
            selectedSeatInput.value = seat.innerText;
            buyButton.disabled = false;
            currentSelected = seat;
        });
    });
</script>
</html>