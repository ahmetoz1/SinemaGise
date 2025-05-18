<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

// Yönetici kontrolü
if($_SESSION['personel_pozisyon'] != 'Yönetici') {
    $_SESSION['hata'] = "Bu işlem için yetkiniz yok!";
    header("Location: personeller.php");
    exit();
}

// Personel ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz personel ID!";
    header("Location: personeller.php");
    exit();
}

$personel_id = (int)$_GET['id'];

// Personelin kendisini silmesini engelle
if($personel_id == $_SESSION['personel_id']) {
    $_SESSION['hata'] = "Kendi hesabınızı silemezsiniz!";
    header("Location: personeller.php");
    exit();
}

// Personeli sil
$query = "DELETE FROM personel WHERE personel_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $personel_id);

if($stmt->execute()) {
    $_SESSION['basarili'] = "Personel başarıyla silindi!";
} else {
    $_SESSION['hata'] = "Silme işlemi sırasında hata oluştu: " . $stmt->error;
}

header("Location: personeller.php");
exit();
?>