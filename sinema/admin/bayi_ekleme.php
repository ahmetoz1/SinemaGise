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

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bayi_adi = $conn->real_escape_string($_POST['bayi_adi']);
    $telefon = $conn->real_escape_string($_POST['telefon']);
    $adres = $conn->real_escape_string($_POST['adres']);
    $calisan_sayisi = (int)$_POST['calisan_sayisi'];
    $musteri_kapasitesi = (int)$_POST['musteri_kapasitesi'];
    $ort_degerlendirme = 0; // Yeni bayi için başlangıç değeri
    $degerlendirme_puani = 0.0; // Yeni bayi için başlangıç değeri

    // Bayi adı kontrolü
    $bayi_check = $conn->query("SELECT COUNT(*) FROM bayi WHERE bayi_adi = '$bayi_adi'")->fetch_row()[0];
    if ($bayi_check > 0) {
        $_SESSION['hata'] = "Bu bayi adı zaten kullanılıyor!";
        header("Location: bayi_ekleme.php");
        exit();
    }

    $query = "INSERT INTO bayi (bayi_adi, telefon, adres, calisan_sayisi, musteri_kapasitesi, ort_degerlendirme, degerlendirme_puani) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssiiid", $bayi_adi, $telefon, $adres, $calisan_sayisi, $musteri_kapasitesi, $ort_degerlendirme, $degerlendirme_puani);

    if ($stmt->execute()) {
        $_SESSION['basarili'] = "Bayi başarıyla eklendi!";
        header("Location: bayiler.php");
        exit();
    } else {
        $_SESSION['hata'] = "Bayi eklenirken hata oluştu: " . $stmt->error;
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-building"></i> Yeni Bayi Ekle</h1>
    </div>

    <?php if(isset($_SESSION['hata'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['hata'] ?>
        </div>
        <?php unset($_SESSION['hata']); ?>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="">
            <div class="form-group">
                <label for="bayi_adi">Bayi Adı</label>
                <input type="text" id="bayi_adi" name="bayi_adi" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="tel" id="telefon" name="telefon" required>
                </div>
                <div class="form-group">
                    <label for="adres">Adres</label>
                    <input type="text" id="adres" name="adres" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="calisan_sayisi">Çalışan Sayısı</label>
                    <input type="number" id="calisan_sayisi" name="calisan_sayisi" min="1" required>
                </div>
                <div class="form-group">
                    <label for="musteri_kapasitesi">Müşteri Kapasitesi</label>
                    <input type="number" id="musteri_kapasitesi" name="musteri_kapasitesi" min="1" required>
                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ekle
                </button>
            </div>
        </form>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?> 