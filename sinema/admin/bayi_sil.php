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

// Bayi ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz bayi ID!";
    header("Location: bayiler.php");
    exit();
}

$bayi_id = (int)$_GET['id'];

// Bayinin bağlı olduğu personel ve salonları kontrol et
$check_query = "SELECT 
    (SELECT COUNT(*) FROM personel WHERE bayi_id = ?) as personel_count,
    (SELECT COUNT(*) FROM salon WHERE bayi_id = ?) as salon_count,
    (SELECT COUNT(*) FROM film WHERE bayi_id = ?) as film_count";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("iii", $bayi_id, $bayi_id, $bayi_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$counts = $check_result->fetch_assoc();

if($counts['personel_count'] > 0 || $counts['salon_count'] > 0 || $counts['film_count'] > 0) {
    $_SESSION['hata'] = "Bu bayiyi silemezsiniz! Bayiye bağlı personel, salon veya film bulunmaktadır.";
    header("Location: bayiler.php");
    exit();
}

// Bayiyi sil
$query = "DELETE FROM bayi WHERE bayi_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bayi_id);

if($stmt->execute()) {
    $_SESSION['basarili'] = "Bayi başarıyla silindi!";
} else {
    $_SESSION['hata'] = "Silme işlemi sırasında hata oluştu: " . $stmt->error;
}

header("Location: bayiler.php");
exit(); 