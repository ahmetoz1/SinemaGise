<?php
// Dosyanın zaten yüklenip yüklenmediğini kontrol et
if (!defined('SINEMA_CONFIG_LOADED')) {
    define('SINEMA_CONFIG_LOADED', true);

    // Veritabanı Bağlantısı
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "sinema";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // Oturum Ayarları
    ini_set('session.cookie_lifetime', 86400);
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Oturum Kontrol Fonksiyonu
    if (!function_exists('checkAdminSession')) {
        function checkAdminSession() {
            if (!isset($_SESSION['personel_id'])) {
                $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                header("Location: /personel_giris.php");
                exit();
            }
        }
    }

    // Yardımcı Fonksiyonlar
    if (!function_exists('getAdminPageTitle')) {
        function getAdminPageTitle() {
            $titles = [
                'dashboard' => 'Yönetim Paneli',
                'filmler' => 'Film Yönetimi',
                'personeller' => 'Personel Yönetimi',
                'seanslar' => 'Seans Yönetimi',
                'biletler' => 'Bilet Yönetimi',
                'musteriler' => 'Müşteri Yönetimi'
            ];
            
            $current = basename($_SERVER['SCRIPT_NAME'], '.php');
            return $titles[$current] ?? 'Admin Paneli';
        }
    }

    if (!function_exists('fetchAll')) {
        function fetchAll($table, $order = 'id DESC') {
            global $conn;
            $result = $conn->query("SELECT * FROM $table ORDER BY $order");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>