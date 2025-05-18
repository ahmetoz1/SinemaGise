<?php
require_once __DIR__.'/../config.php';
checkAdminSession();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz müşteri ID!";
    header("Location: musteriler.php");
    exit();
}

$musteri_id = (int)$_GET['id'];

// Müşterinin bilet sayısını kontrol et
$bilet_sayisi = $conn->query("
    SELECT COUNT(*) 
    FROM bilet 
    WHERE musteri_id = $musteri_id
")->fetch_row()[0];

// İşlemi başlat
$conn->begin_transaction();

try {
    // Değerlendirmeleri sil
    $conn->query("DELETE FROM degerlendirme WHERE musteri_id = $musteri_id");
    
    // Biletleri sil (koltuk durumu trigger ile güncellenecek)
    if($bilet_sayisi > 0) {
        $conn->query("DELETE FROM bilet WHERE musteri_id = $musteri_id");
    }
    
    // Müşteriyi sil
    $conn->query("DELETE FROM musteri WHERE musteri_id = $musteri_id");
    
    $conn->commit();
    $_SESSION['basarili'] = "Müşteri ve tüm ilişkili verileri başarıyla silindi! Koltuklar tekrar satışa açıldı.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['hata'] = "Silme işlemi sırasında hata oluştu: " . $e->getMessage();
}

header("Location: musteriler.php");
exit();
?>