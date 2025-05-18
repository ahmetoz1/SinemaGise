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

// Salon ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz salon ID!";
    header("Location: salonlar.php");
    exit();
}

$salon_id = (int)$_GET['id'];

// Salonun bağlı olduğu seansları kontrol et
$check_query = "SELECT COUNT(*) as seans_count FROM seans WHERE salon_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $salon_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$counts = $check_result->fetch_assoc();

if($counts['seans_count'] > 0) {
    $_SESSION['hata'] = "Bu salonu silemezsiniz! Salona bağlı seanslar bulunmaktadır.";
    header("Location: salonlar.php");
    exit();
}

// Salonu sil
$query = "DELETE FROM salon WHERE salon_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $salon_id);

if($stmt->execute()) {
    $_SESSION['basarili'] = "Salon başarıyla silindi!";
} else {
    $_SESSION['hata'] = "Silme işlemi sırasında hata oluştu: " . $stmt->error;
}

header("Location: salonlar.php");
exit(); 