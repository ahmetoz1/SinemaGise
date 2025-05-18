<?php
// Admin paneli kontrolü
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

// Müşteri ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz müşteri ID!";
    header("Location: musteriler.php");
    exit();
}

$musteri_id = (int)$_GET['id'];

// Müşteri bilgilerini veritabanından çek
$musteri = $conn->query("
    SELECT *, CONCAT(ad, ' ', soyad) AS ad_soyad 
    FROM musteri 
    WHERE musteri_id = $musteri_id
")->fetch_assoc();

// Müşteri bulunamadıysa hata ver
if(!$musteri) {
    $_SESSION['hata'] = "Müşteri bulunamadı!";
    header("Location: musteriler.php");
    exit();
}

// Müşterinin bilet geçmişini çek
$biletler = $conn->query("
    SELECT b.*, f.ad AS film_adi, s.tarih, sa.salon_no
    FROM bilet b
    JOIN seans s ON b.seans_id = s.seans_id
    JOIN film f ON s.film_id = f.film_id
    JOIN salon sa ON s.salon_id = sa.salon_id
    WHERE b.musteri_id = $musteri_id
    ORDER BY b.satis_tarihi DESC
");

// Admin panel header'ını dahil et
include(__DIR__.'/includes/header.php');
?>

<!-- Sayfa başlığı -->
<div class="admin-header">
    <h1>Müşteri Detayları: <?= htmlspecialchars($musteri['ad_soyad']) ?></h1>
</div>

<!-- Müşteri bilgileri formu -->
<div class="form-container">
    <!-- Kişisel bilgiler -->
    <div class="form-row">
        <div class="form-group">
            <label>Ad Soyad:</label>
            <p><?= htmlspecialchars($musteri['ad_soyad']) ?></p>
        </div>
        <div class="form-group">
            <label>E-posta:</label>
            <p><?= htmlspecialchars($musteri['e_posta']) ?></p>
        </div>
    </div>
    
    <!-- İletişim bilgileri -->
    <div class="form-row">
        <div class="form-group">
            <label>Telefon:</label>
            <p><?= htmlspecialchars($musteri['tel_no']) ?></p>
        </div>
        <div class="form-group">
            <label>Doğum Tarihi:</label>
            <p><?= date('d.m.Y', strtotime($musteri['dogum_tarihi'])) ?></p>
        </div>
    </div>
    
    <!-- Puan bilgisi -->
    <div class="form-row">
        <div class="form-group">
            <label>Puan:</label>
            <p><?= $musteri['puan'] ?></p>
        </div>
    </div>
    
    <!-- Bilet geçmişi tablosu -->
    <h3>Bilet Geçmişi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Film</th>
                <th>Tarih</th>
                <th>Salon</th>
                <th>Fiyat</th>
                <th>Satış Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($bilet = $biletler->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($bilet['film_adi']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($bilet['tarih'])) ?></td>
                <td><?= $bilet['salon_no'] ?></td>
                <td><?= $bilet['fiyat'] ?> TL</td>
                <td><?= date('d.m.Y H:i', strtotime($bilet['satis_tarihi'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>