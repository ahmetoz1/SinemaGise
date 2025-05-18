<?php
// Veritabanı bağlantısı
include('config.php');

// Hata ayıklama modunu aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Temel güvenlik kontrolleri
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['musteri_id'])) {
    $_SESSION['hata'] = "Geçersiz bilet satın alma isteği!";
    header("Location: index.php");
    exit();
}

// Form verilerini al ve güvenli hale getir
$seans_id = intval($_POST['seans_id'] ?? 0);
$koltuk_sayisi = intval($_POST['koltuk_sayisi'] ?? 0);
$koltuklar = $_POST['koltuklar'] ?? [];
$musteri_id = $_SESSION['musteri_id'];

// Koltuklar dizisini kontrol et
if(!is_array($koltuklar)) {
    $koltuklar = [];
}

// Veri doğrulama
if($seans_id <= 0 || $koltuk_sayisi <= 0 || count($koltuklar) != $koltuk_sayisi) {
    $_SESSION['hata'] = "Geçersiz bilet bilgileri!";
    header("Location: film_liste.php");
    exit();
}

// Transaction başlat - tüm işlemler ya hep birlikte başarılı olacak ya da hiçbiri olmayacak
$conn->begin_transaction();

try {
    // Seans bilgilerini kontrol et
    $seansQuery = "SELECT bilet_fiyati, tarih FROM seans WHERE seans_id = ? AND tarih > NOW()";
    $stmt = $conn->prepare($seansQuery);
    $stmt->bind_param("i", $seans_id);
    $stmt->execute();
    $seansResult = $stmt->get_result();
    
    if($seansResult->num_rows == 0) {
        throw new Exception("Seans bulunamadı veya tarihi geçmiş!");
    }
    
    $seans = $seansResult->fetch_assoc();
    $bilet_fiyati = $seans['bilet_fiyati'];
    
    // Her koltuk için işlem yap
    foreach($koltuklar as $koltuk_no) {
        $koltuk_no = intval($koltuk_no);
        
        // Koltuk müsaitlik kontrolü
        $koltukKontrol = "SELECT koltuk_id, durum FROM koltuk WHERE seans_id = ? AND koltuk_no = ?";
        $stmt = $conn->prepare($koltukKontrol);
        $stmt->bind_param("ii", $seans_id, $koltuk_no);
        $stmt->execute();
        $koltukKontrolResult = $stmt->get_result();
        
        if($koltukKontrolResult->num_rows == 0) {
            // Yeni koltuk oluştur
            $koltukInsert = "INSERT INTO koltuk (seans_id, koltuk_no, durum) VALUES (?, ?, 'Dolu')";
            $stmt = $conn->prepare($koltukInsert);
            $stmt->bind_param("ii", $seans_id, $koltuk_no);
            if(!$stmt->execute()) {
                throw new Exception("Koltuk oluşturulamadı!");
            }
            $koltuk_id = $conn->insert_id;
        } else {
            $koltukData = $koltukKontrolResult->fetch_assoc();
            if($koltukData['durum'] == 'Dolu') {
                throw new Exception("$koltuk_no numaralı koltuk zaten dolu!");
            }
            $koltuk_id = $koltukData['koltuk_id'];
            
            // Koltuk durumunu güncelle
            $koltukUpdate = "UPDATE koltuk SET durum = 'Dolu' WHERE koltuk_id = ?";
            $stmt = $conn->prepare($koltukUpdate);
            $stmt->bind_param("i", $koltuk_id);
            if(!$stmt->execute()) {
                throw new Exception("Koltuk durumu güncellenemedi!");
            }
        }
        
        // Bilet kaydı oluştur
        $biletInsert = "INSERT INTO bilet (seans_id, musteri_id, koltuk_id, satis_tarihi, fiyat)
                       VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($biletInsert);
        $stmt->bind_param("iiid", $seans_id, $musteri_id, $koltuk_id, $bilet_fiyati);
        if(!$stmt->execute()) {
            throw new Exception("Bilet oluşturulamadı!");
        }
    }
    
    // Müşteri puanını güncelle (her bilet için 10 puan)
    $eklenecekPuan = $koltuk_sayisi * 10;
    $puanEkle = "UPDATE musteri SET puan = puan + ? WHERE musteri_id = ?";
    $stmt = $conn->prepare($puanEkle);
    $stmt->bind_param("ii", $eklenecekPuan, $musteri_id);
    if(!$stmt->execute()) {
        throw new Exception("Puan güncellenemedi!");
    }
    
    // Tüm işlemler başarılı, transaction'ı onayla
    $conn->commit();
    
    $_SESSION['basarili'] = "Biletleriniz başarıyla alındı! Toplam $koltuk_sayisi koltuk satın aldınız.";
    header("Location: hesabim.php");
    exit();
    
} catch(Exception $e) {
    // Hata durumunda tüm işlemleri geri al
    $conn->rollback();
    
    $_SESSION['hata'] = "Bilet alınırken hata oluştu: " . $e->getMessage();
    header("Location: bilet_al.php?seans=".$seans_id);
    exit();
}
?>