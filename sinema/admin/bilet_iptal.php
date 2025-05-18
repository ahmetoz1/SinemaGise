<?php
// Gerekli dosyaları dahil et ve admin kontrolü yap
require_once __DIR__.'/../config.php';
checkAdminSession();

// Bilet ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz bilet ID!";
    header("Location: biletler.php");
    exit();
}

$bilet_id = (int)$_GET['id'];

// Bilet ve ilgili bilgileri veritabanından çek
$bilet = $conn->query("
    SELECT b.*, k.koltuk_id, k.salon_id, k.seans_id 
    FROM bilet b
    JOIN koltuk k ON b.koltuk_id = k.koltuk_id
    WHERE b.bilet_id = $bilet_id
")->fetch_assoc();

// Bilet bulunamadıysa hata ver
if(!$bilet) {
    $_SESSION['hata'] = "Bilet bulunamadı!";
    header("Location: biletler.php");
    exit();
}

// Transaction başlat - tüm işlemler ya hep birlikte başarılı olacak ya da hiçbiri olmayacak
$conn->begin_transaction();

try {
    // Koltuk durumunu boş olarak güncelle
    $conn->query("UPDATE koltuk SET durum = 'Bos' WHERE koltuk_id = ".$bilet['koltuk_id']);
    
    // Bileti veritabanından sil
    $conn->query("DELETE FROM bilet WHERE bilet_id = $bilet_id");
    
    // Müşteri puanını güncelle (eğer kayıtlı müşteriyse)
    if($bilet['musteri_id']) {
        // Bilet fiyatının 10'da 1'i kadar puan düş
        $conn->query("UPDATE musteri SET puan = puan - FLOOR($bilet[fiyat]/10) WHERE musteri_id = $bilet[musteri_id]");
    }
    
    // Tüm işlemler başarılı, transaction'ı onayla
    $conn->commit();
    $_SESSION['basarili'] = "Bilet başarıyla iptal edildi!";
} catch (Exception $e) {
    // Hata durumunda tüm işlemleri geri al
    $conn->rollback();
    $_SESSION['hata'] = "İptal işlemi sırasında hata oluştu: " . $e->getMessage();
}

// İşlem sonrası biletler sayfasına yönlendir
header("Location: biletler.php");
exit();
?>