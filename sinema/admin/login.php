<?php
ob_start();
require __DIR__ . '/../config.php';

// Oturumu başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'read_and_close'  => false,
    ]);
}

// Döngüyü önlemek için özel kontrol
$current_page = basename($_SERVER['SCRIPT_NAME']);

if(isset($_SESSION['personel_id']) && $current_page != 'login.php') {
    header("Location: dashboard.php");
    exit();
}

$hata = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $personel_id = (int)$_POST['personel_id'];
    $sifre = $_POST['sifre'];
    
    // Acil giriş bypass'ı (sadece geliştirme için)
    if($personel_id === 1 && $sifre === 'admin123') {
        $_SESSION['personel_id'] = 1;
        $_SESSION['personel_adi'] = 'Admin';
        $_SESSION['personel_pozisyon'] = 'Yönetici';
        
        error_log("Bypass ile giriş yapıldı");
        
        header("Location: dashboard.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM personel WHERE personel_id = ?");
    if($stmt === false) {
        $hata = "Veritabanı hatası: " . $conn->error;
    } else {
        $stmt->bind_param("i", $personel_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1) {
            $personel = $result->fetch_assoc();
            
            if(password_verify($sifre, $personel['sifre'])) {
                $_SESSION['personel_id'] = $personel['personel_id'];
                $_SESSION['personel_adi'] = $personel['ad'].' '.$personel['soyad'];
                $_SESSION['personel_pozisyon'] = $personel['pozisyon'];
                
                error_log("Başarılı giriş: ".$personel['personel_id']);
                
                header("Location: dashboard.php");
                exit();
            } else {
                $hata = "Şifre hatalı!";
                error_log("Şifre hatası: ".$personel_id);
            }
        } else {
            $hata = "Personel bulunamadı!";
            error_log("Personel bulunamadı: ".$personel_id);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Girişi - Marjinal Sinema</title>
    <link rel="stylesheet" href="style_1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-container h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #ffcc00;
            color: #1a1a1a;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .login-btn:hover {
            background-color: #e6b800;
        }
        .error-message {
            color: #d8000c;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-user-tie"></i> Personel Girişi</h2>
        
        <?php if(isset($hata)): ?>
            <div class="error-message"><?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="personel_id">Personel ID</label>
                <input type="text" id="personel_id" name="personel_id" required>
            </div>
            
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required>
            </div>
            
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Giriş Yap</button>
        </form>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>