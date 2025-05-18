<?php
include('config.php');

if(isset($_SESSION['personel_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $personel_id = $conn->real_escape_string($_POST['personel_id']);
    $sifre = $_POST['sifre'];
    
    $query = "SELECT * FROM personel WHERE personel_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $personel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $personel = $result->fetch_assoc();
        if(password_verify($sifre, $personel['sifre'])) {
            $_SESSION['personel_id'] = $personel['personel_id'];
            $_SESSION['personel_adi'] = $personel['ad'] . ' ' . $personel['soyad'];
            $_SESSION['personel_pozisyon'] = $personel['pozisyon'];
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $hata = "Geçersiz şifre!";
        }
    } else {
        $hata = "Geçersiz personel ID!";
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
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        .login-links a {
            color: #1a1a1a;
            text-decoration: none;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <main>
        <div class="login-container">
            <h2><i class="fas fa-user-tie"></i> Personel Girişi</h2>
            
            <?php if(isset($hata)): ?>
                <div class="error-message"><?= $hata ?></div>
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
            
            <div class="login-links">
                <a href="giris_yap.php"><i class="fas fa-user"></i> Müşteri Girişi</a>
            </div>
        </div>
    </main>
    
    <?php include('footer.php'); ?>
</body>
</html>