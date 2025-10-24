<?php
// Veritabanı dosyasının yolu
// Bu dosya, ana proje dizininde olduğu için yol bu şekilde.
$db_file = __DIR__ . '/../bilet_platformu.db';

try {
    // PDO (PHP Data Objects) ile SQLite veritabanına bağlanıyoruz.
    // Bu, modern ve güvenli bir bağlantı yöntemidir.
    $db = new PDO('sqlite:' . $db_file);

    // Hata modunu ayarlıyoruz. Eğer bir sorguda hata olursa, ekrana yazdırması için.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanından veri çekerken sonuçların bir "ilişkisel dizi" olarak gelmesini sağlıyoruz.
    // Bu sayede $row['price'] gibi sütun isimleriyle verilere ulaşabiliriz.
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Eğer bağlantı kurulamazsa, bir hata mesajı yazdır ve programı durdur.
    // Gerçek bir projede bu kadar detaylı hata gösterilmez ama geliştirme aşamasında çok faydalıdır.
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}
?>