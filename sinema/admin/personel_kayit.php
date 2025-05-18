<?php
define('IN_ADMIN', true);
include('functions.php');

require_once __DIR__.'/../config.php';
checkAdminSession(); // Bu satırı her admin sayfasının EN BAŞINA ekleyin


// Sadece yöneticiler kayıt anahtarı oluşturabilir
if($_SESSION['personel_pozisyon'] != 'Yönetici') {
    $_SESSION['hata'] = "Bu işlem için yetkiniz yok!";
    header("Location: dashboard.php");
    exit();
}

// Yeni kayıt anahtarı oluştur
if(isset($_POST['anahtar_olustur'])) {
    $anahtar = bin2hex(random_bytes(16)); // 32 karakterlik rastgele anahtar
    
    $query = "INSERT INTO personel_kayit_anahtarlari (anahtar) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $anahtar);
    
    if($stmt->execute()) {
        $_SESSION['basarili'] = "Yeni kayıt anahtarı oluşturuldu: " . $anahtar;
    } else {
        $_SESSION['hata'] = "Anahtar oluşturulurken hata oluştu!";
    }
    
    header("Location: personel_kayit.php");
    exit();
}

// Anahtar listesini getir
$anahtarlar = $conn->query("SELECT * FROM personel_kayit_anahtarlari ORDER BY olusturulma_tarihi DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Kayıt Anahtarları - Marjinal Sinema</title>
    <link rel="stylesheet" href="style_1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .admin-content {
            flex: 1;
            padding: 30px;
            background: #f5f5f5;
        }
        .admin-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .admin-header h1 {
            margin: 0;
            color: #1a1a1a;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-submit {
            margin-top: 15px;
        }
        .form-submit button {
            padding: 10px 15px;
            background-color: #ffcc00;
            color: #1a1a1a;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .kullanildi {
            color: #4CAF50;
            font-weight: bold;
        }
        .kullanilmadi {
            color: #f44336;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include(__DIR__.'/includes/sidebar.php'); ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Personel Kayıt Anahtarları</h1>
        </div>
        
        <?php if(isset($_SESSION['basarili'])): ?>
            <div class="alert success"><?= $_SESSION['basarili'] ?></div>
            <?php unset($_SESSION['basarili']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['hata'])): ?>
            <div class="alert error"><?= $_SESSION['hata'] ?></div>
            <?php unset($_SESSION['hata']); ?>
        <?php endif; ?>
        
        <div class="form-container">
            <h2>Yeni Kayıt Anahtarı Oluştur</h2>
            <form method="POST" action="">
                <div class="form-submit">
                    <button type="submit" name="anahtar_olustur"><i class="fas fa-key"></i> Anahtar Oluştur</button>
                </div>
            </form>
        </div>
        
        <h2>Anahtar Listesi</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Anahtar</th>
                    <th>Durum</th>
                    <th>Oluşturulma Tarihi</th>
                    <th>Kullanılma Tarihi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($anahtar = $anahtarlar->fetch_assoc()): ?>
                <tr>
                    <td><?= $anahtar['anahtar'] ?></td>
                    <td>
                        <?php if($anahtar['kullanildi']): ?>
                            <span class="kullanildi"><i class="fas fa-check-circle"></i> Kullanıldı</span>
                        <?php else: ?>
                            <span class="kullanilmadi"><i class="fas fa-times-circle"></i> Kullanılmadı</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d.m.Y H:i', strtotime($anahtar['olusturulma_tarihi'])) ?></td>
                    <td>
                        <?= $anahtar['kullanilma_tarihi'] ? date('d.m.Y H:i', strtotime($anahtar['kullanilma_tarihi'])) : '-' ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>