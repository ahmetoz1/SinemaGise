<?php
include('config.php');

if(isset($_SESSION['personel_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al
    $anahtar = $conn->real_escape_string($_POST['anahtar']);
    $ad = $conn->real_escape_string($_POST['ad']);
    $soyad = $conn->real_escape_string($_POST['soyad']);
    $pozisyon = $conn->real_escape_string($_POST['pozisyon']);
    $tel_no = $conn->real_escape_string($_POST['tel_no']);
    $adres = $conn->real_escape_string($_POST['adres']);
    $dogum_tarihi = $conn->real_escape_string($_POST['dogum_tarihi']);
    $sifre = password_hash($_POST['sifre'], PASSWORD_BCRYPT);
    
    // Anahtarı kontrol et
    $anahtarKontrol = $conn->query("SELECT * FROM personel_kayit_anahtarlari WHERE anahtar = '$anahtar' AND kullanildi = 0");
    
    if($anahtarKontrol->num_rows == 1) {
        // Personel kaydını oluştur
        $query = "INSERT INTO personel (ad, soyad, pozisyon, tel_no, adres, dogum_tarihi, sifre) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $ad, $soyad, $pozisyon, $tel_no, $adres, $dogum_tarihi, $sifre);
        
        if($stmt->execute()) {
            // Anahtarı kullanıldı olarak işaretle
            $conn->query("UPDATE personel_kayit_anahtarlari SET kullanildi = 1, kullanilma_tarihi = NOW() WHERE anahtar = '$anahtar'");
            
            $_SESSION['basarili'] = "Kayıt başarılı! Giriş yapabilirsiniz.";
            header("Location: personel_giris.php");
            exit();
        } else {
            $hata = "Kayıt sırasında hata oluştu: " . $stmt->error;
        }
    } else {
        $hata = "Geçersiz veya kullanılmış kayıt anahtarı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Kayıt - Marjinal Sinema</title>
    <link rel="stylesheet" href="style_1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .register-container h2 {
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .register-btn {
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
        .register-btn:hover {
            background-color: #e6b800;
        }
        .error-message {
            color: #d8000c;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #1a1a1a;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <main>
        <div class="register-container">
            <h2><i class="fas fa-user-tie"></i> Personel Kayıt Formu</h2>
            
            <?php if(isset($hata)): ?>
                <div class="error-message"><?= $hata ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="anahtar">Kayıt Anahtarı</label>
                    <input type="text" id="anahtar" name="anahtar" required placeholder="Yöneticinizden temin ediniz">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ad">Ad</label>
                        <input type="text" id="ad" name="ad" required>
                    </div>
                    <div class="form-group">
                        <label for="soyad">Soyad</label>
                        <input type="text" id="soyad" name="soyad" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pozisyon">Pozisyon</label>
                        <select id="pozisyon" name="pozisyon" required>
                            <option value="">Seçiniz</option>
                            <option value="Gişe Görevlisi">Gişe Görevlisi</option>
                            <option value="Temizlik Görevlisi">Temizlik Görevlisi</option>
                            <option value="Güvenlik">Güvenlik</option>
                            <option value="Yönetici">Yönetici</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tel_no">Telefon</label>
                        <input type="tel" id="tel_no" name="tel_no" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adres">Adres</label>
                    <input type="text" id="adres" name="adres" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dogum_tarihi">Doğum Tarihi</label>
                        <input type="date" id="dogum_tarihi" name="dogum_tarihi" required>
                    </div>
                    <div class="form-group">
                        <label for="sifre">Şifre</label>
                        <input type="password" id="sifre" name="sifre" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="register-btn"><i class="fas fa-user-plus"></i> Kayıt Ol</button>
            </form>
            
            <div class="login-link">
                <a href="personel_giris.php"><i class="fas fa-sign-in-alt"></i> Personel Girişi Yap</a>
            </div>
        </div>
    </main>
    
    <?php include('footer.php'); ?>
</body>
</html>