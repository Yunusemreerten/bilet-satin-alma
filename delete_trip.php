<?php
session_start();
require_once 'includes/db.php';

// --- YETKİ KONTROLÜ ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company_admin') {
    header('Location: index.php');
    exit;
}

// Silinecek seferin ID'si URL ile geldi mi?
if (!isset($_GET['trip_id'])) {
    header('Location: company_admin_panel.php?error=missing_id');
    exit;
}

$trip_id_to_delete = $_GET['trip_id'];
$admin_user_id = $_SESSION['user_id'];

try {
    // Önce adminin firma ID'sini alalım
    $stmt_company = $db->prepare("SELECT company_id FROM User WHERE id = ?");
    $stmt_company->execute([$admin_user_id]);
    $company_id = $stmt_company->fetchColumn();

    // Ardından, silinmek istenen seferin gerçekten bu firmaya ait olup olmadığını kontrol edelim.
    // Bu, başka bir firmanın admininin, URL'yi değiştirerek sizin seferinizi silmesini engeller. ÇOK ÖNEMLİ!
    $stmt_trip = $db->prepare("SELECT id FROM Trips WHERE id = ? AND company_id = ?");
    $stmt_trip->execute([$trip_id_to_delete, $company_id]);
    $trip = $stmt_trip->fetch();

    if ($trip) {
        // Sefer bu firmaya aitse, silme işlemini yap.
        // TODO: İleri seviye bir kontrol olarak, bu sefere bilet satılmışsa silinmesini engelleyebilirsiniz.
        // Şimdilik direkt siliyoruz.
        $stmt_delete = $db->prepare("DELETE FROM Trips WHERE id = ?");
        $stmt_delete->execute([$trip_id_to_delete]);

        // Başarı mesajıyla panele yönlendir.
        header('Location: company_admin_panel.php?success=trip_deleted');
        exit;
    } else {
        // Sefer bulunamadı veya bu firmaya ait değilse, yetkisiz işlem hatası ver.
        throw new Exception("Yetkisiz silme işlemi veya geçersiz sefer ID'si.");
    }

} catch (Exception $e) {
    // Herhangi bir hata olursa, hata mesajıyla panele yönlendir.
    header('Location: company_admin_panel.php?error=delete_failed');
    exit;
}
?>