<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();
require_once 'includes/db.php';

// ... (Güvenlik ve Kontrol Adımları aynı kalıyor) ...
if (!isset($_SESSION['user_id'])) die("Giriş yapmalısınız.");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Geçersiz istek.");
if (!isset($_POST['trip_id'], $_POST['selected_seat'], $_POST['price'])) die("Eksik bilgi.");

$trip_id = $_POST['trip_id'];
$selected_seat = (int)$_POST['selected_seat'];
$price = (float)$_POST['price'];
$user_id = $_SESSION['user_id'];
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$final_price = $price; // Başlangıçta son fiyat, bilet fiyatına eşit.
$coupon_id = null;

try {
    $db->beginTransaction();

    // KUPON KONTROLÜ (YENİ EKLENDİ)
    if (!empty($coupon_code)) {
        $stmt_coupon = $db->prepare("SELECT * FROM Coupons WHERE code = ? AND expire_date > date('now')");
        $stmt_coupon->execute([$coupon_code]);
        $coupon = $stmt_coupon->fetch();

        if ($coupon) {
            // Kuponun kullanım limitini kontrol et
            $stmt_usage = $db->prepare("SELECT COUNT(id) FROM User_Coupons WHERE coupon_id = ?");
            $stmt_usage->execute([$coupon['id']]);
            $usage_count = $stmt_usage->fetchColumn();

            if ($usage_count < $coupon['usage_limit']) {
                // İndirimi uygula
                $final_price = $price - ($price * ($coupon['discount'] / 100.0));
                $coupon_id = $coupon['id']; // Kupon kullanıldıysa ID'sini sakla
            }
        }
    }

    // KULLANICI BAKİYESİNİ KONTROL ET (final_price ile)
    $stmt = $db->prepare("SELECT balance FROM User WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_balance = $stmt->fetchColumn();

    if ($user_balance < $final_price) {
        $db->rollBack();
        header('Location: buy_ticket.php?trip_id=' . $trip_id . '&error=insufficient_balance');
        exit;
    }

    // ... (Koltuk kontrolü aynı kalıyor) ...
    $stmt_check_seat = $db->prepare("SELECT bs.id FROM Booked_Seats bs JOIN Tickets t ON bs.ticket_id = t.id WHERE t.trip_id = ? AND bs.seat_number = ? AND t.status = 'active'");
    $stmt_check_seat->execute([$trip_id, $selected_seat]);
    if ($stmt_check_seat->fetch()) {
        $db->rollBack();
        header('Location: buy_ticket.php?trip_id=' . $trip_id . '&error=seat_taken');
        exit;
    }
    
    // YENİ BİR BİLET OLUŞTUR (final_price ile)
    $ticket_id = uniqid('ticket_', true);
    $stmt_ticket = $db->prepare("INSERT INTO Tickets (id, trip_id, user_id, status, total_price) VALUES (?, ?, ?, 'active', ?)");
    $stmt_ticket->execute([$ticket_id, $trip_id, $user_id, $final_price]);

    // SEÇİLEN KOLTUĞU REZERVE ET
    $booked_seat_id = uniqid('seat_', true);
    $stmt_seat = $db->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)");
    $stmt_seat->execute([$booked_seat_id, $ticket_id, $selected_seat]);

    // KULLANICININ BAKİYESİNİ DÜŞÜR (final_price ile)
    $new_balance = $user_balance - $final_price;
    $stmt_balance = $db->prepare("UPDATE User SET balance = ? WHERE id = ?");
    $stmt_balance->execute([$new_balance, $user_id]);

    // EĞER KUPON KULLANILDIYSA, KULLANIMI KAYDET (YENİ EKLENDİ)
    if ($coupon_id) {
        $user_coupon_id = uniqid('uc_', true);
        $stmt_log_coupon = $db->prepare("INSERT INTO User_Coupons (id, coupon_id, user_id) VALUES (?, ?, ?)");
        $stmt_log_coupon->execute([$user_coupon_id, $coupon_id, $user_id]);
    }
    
    $db->commit();
    ob_end_clean();
    header('Location: my_tickets.php?success=purchase_complete');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    ob_end_clean();
    die("İşlem sırasında bir hata oluştu: " . $e->getMessage());
}
?>