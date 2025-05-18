<?php
// Oturumu başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tüm oturum verilerini temizle
$_SESSION = array();

// Oturum çerezi sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Oturumu yok et
session_destroy();

// Ana sayfaya yönlendir (mutlak yol kullanarak)
header("Location: http://" . $_SERVER['HTTP_HOST'] . "/sinema/index.php");
exit();
?>