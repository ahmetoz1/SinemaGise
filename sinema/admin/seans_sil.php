<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

// Yönetici kontrolü
if($_SESSION['personel_pozisyon'] != 'Yönetici') {
    $_SESSION['hata'] = "Bu işlem için yetkiniz yok!";
    header("Location: index.php");
    exit();
}

// Seans ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz seans ID!";
    header("Location: seanslar.php");
    exit();
}

$seans_id = (int)$_GET['id'];

// Önce biletleri sil
$bilet_query = "DELETE FROM bilet WHERE seans_id = ?";
$stmt = $conn->prepare($bilet_query);
$stmt->bind_param("i", $seans_id);
$stmt->execute();

// Sonra koltuk kayıtlarını sil
$koltuk_query = "DELETE FROM koltuk WHERE seans_id = ?";
$stmt = $conn->prepare($koltuk_query);
$stmt->bind_param("i", $seans_id);
$stmt->execute();

// Sonra seansı sil
$seans_query = "DELETE FROM seans WHERE seans_id = ?";
$stmt = $conn->prepare($seans_query);
$stmt->bind_param("i", $seans_id);

if ($stmt->execute()) {
    $_SESSION['basarili'] = "Seans ve ilgili biletler başarıyla silindi!";
} else {
    $_SESSION['hata'] = "Seans silinirken bir hata oluştu: " . $stmt->error;
}

header("Location: seanslar.php");
exit();
?>