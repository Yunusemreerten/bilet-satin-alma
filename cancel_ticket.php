<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

// --- GÜVENLİK VE KONTROL ADIMLARI ---

// 1. Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['user_id'])) {
    die("Bu işlemi yapmak için giriş yapmalısınız.");
}

// 2. İptal edilecek biletin ID'si URL ile geldi mi?
if (!isset($_GET['ticket_id'])) {
    header('Location: my_tickets.php');
    exit;
}

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

try {
    // SATIN ALMADA OLDUĞU GİBİ, GÜVENLİ İŞLEM İÇİN TRANSACTION BAŞLATIYORUZ
    $db->beginTransaction();

    // 3. BİLET BİLGİLERİNİ VE SEFER SAATİNİ ÇEK
    // Biletin gerçekten bu kullanıcıya ait olup olmadığını, fiyatını ve durumunu kontrol etmeliyiz.
    $stmt = $db->prepare("
        SELECT t.id, t.status, t.total_price, t.user_id, tr.departure_time
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    $bilet = $stmt->fetch();

    // 4. GEREKLİ KONTROLLERİ YAP
    // Bilet bulunamadıysa VEYA bilet bu kullanıcıya ait değilse, işlemi durdur.
    if (!$bilet || $bilet['user_id'] !== $user_id) {
        throw new Exception("Geçersiz bilet veya yetkisiz işlem.");
    }

    // Biletin durumu 'active' değilse, iptal edilemez.
    if ($bilet['status'] !== 'active') {
        throw new Exception("Bu bilet zaten iptal edilmiş veya geçmiş bir sefere aittir.");
    }
    
    // Sefer saatine 1 saatten az kalmışsa, iptal edilemez.
    $su_anki_zaman = time();
    $sefer_zamani = strtotime($bilet['departure_time']);
    if (($sefer_zamani - $su_anki_zaman) <= 3600) {
        throw new Exception("Sefer saatine 1 saatten az kaldığı için bilet iptal edilemez.");
    }

    // --- İPTAL İŞLEMLERİ ---

    // 5. Biletin durumunu 'canceled' olarak güncelle.
    $stmt = $db->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = ?");
    $stmt->execute([$ticket_id]);

    // 6. Bu bilete ait satılmış koltuk kaydını Booked_Seats tablosundan sil.
    // Bu sayede koltuk tekrar boşa çıkar.
    $stmt = $db->prepare("DELETE FROM Booked_Seats WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);

    // 7. Kullanıcının bakiyesine bilet ücretini geri iade et.
    $stmt = $db->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$bilet['total_price'], $user_id]);

    // Tüm işlemler başarılı olduysa, değişiklikleri kalıcı olarak kaydet.
    $db->commit();

    // Kullanıcıyı başarı mesajıyla Biletlerim sayfasına yönlendir.
    header('Location: my_tickets.php?success=cancellation_complete');
    exit;

} catch (Exception $e) {
    // Eğer yukarıdaki işlemler sırasında herhangi bir hata oluşursa...
    // Yapılan tüm değişiklikleri geri al.
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    // Kullanıcıyı hata mesajıyla Biletlerim sayfasına yönlendir.
    header('Location: my_tickets.php?error=cancellation_failed');
    // die($e->getMessage()); // Hatanın ne olduğunu görmek için bu satırı kullanabilirsin.
    exit;
}
?>