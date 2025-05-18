<?php
// config.php'yi include et (session kontrolü burada yapılıyor)
include(__DIR__.'/config.php');

// Oturum kontrolü
$isLoggedIn = isset($_SESSION['musteri_id']);
$isPersonelLoggedIn = isset($_SESSION['personel_id']);
$userName = $isLoggedIn ? $_SESSION['ad'] . ' ' . $_SESSION['soyad'] : '';
$personelName = $isPersonelLoggedIn ? $_SESSION['personel_adi'] : '';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinema Bilet Sistemi</title>
    <link rel="stylesheet" href="style_1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 class="site-title">Marjinal Sinema</h1>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="film_liste.php"><i class="fas fa-film"></i> Filmler</a></li>
                    <li><a href="bilet_al.php"><i class="fas fa-ticket-alt"></i> Bilet Al</a></li>
                    
                    <?php if($isPersonelLoggedIn): ?>
                        <!-- Personel giriş yaptığında gösterilecek menü -->
                        <li><a href="admin/dashboard.php"><i class="fas fa-user-shield"></i> Personel Paneli</a></li>
                        <li><a href="admin/logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    <?php elseif($isLoggedIn): ?>
                        <!-- Müşteri giriş yaptığında gösterilecek menü -->
                        <li><a href="hesabim.php"><i class="fas fa-user"></i> Hesabım</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    <?php else: ?>
                        <!-- Giriş yapılmadığında gösterilecek menü -->
                        <li><a href="uye_ol.php"><i class="fas fa-user-plus"></i> Kayıt Ol</a></li>
                        <li><a href="giris_yap.php"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a></li>
                        <li><a href="/sinema/admin/login.php"><i class="fas fa-user-tie"></i> Personel Girişi</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>