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

// Bayi ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz bayi ID!";
    header("Location: bayiler.php");
    exit();
}

$bayi_id = (int)$_GET['id'];

// Bayi bilgilerini çek
$query = "SELECT * FROM bayi WHERE bayi_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bayi_id);
$stmt->execute();
$result = $stmt->get_result();
$bayi = $result->fetch_assoc();

if(!$bayi) {
    $_SESSION['hata'] = "Bayi bulunamadı!";
    header("Location: bayiler.php");
    exit();
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bayi_adi = $conn->real_escape_string($_POST['bayi_adi']);
    $telefon = $conn->real_escape_string($_POST['telefon']);
    $adres = $conn->real_escape_string($_POST['adres']);
    $calisan_sayisi = (int)$_POST['calisan_sayisi'];
    $musteri_kapasitesi = (int)$_POST['musteri_kapasitesi'];

    // Bayi adı kontrolü (kendi adı hariç)
    $name_check = $conn->query("SELECT COUNT(*) FROM bayi WHERE bayi_adi = '$bayi_adi' AND bayi_id != $bayi_id")->fetch_row()[0];
    if ($name_check > 0) {
        $_SESSION['hata'] = "Bu bayi adı zaten kullanılıyor!";
        header("Location: bayi_duzenle.php?id=$bayi_id");
        exit();
    }

    $query = "UPDATE bayi SET 
              bayi_adi = ?, 
              telefon = ?, 
              adres = ?, 
              calisan_sayisi = ?, 
              musteri_kapasitesi = ? 
              WHERE bayi_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssiii", $bayi_adi, $telefon, $adres, $calisan_sayisi, $musteri_kapasitesi, $bayi_id);

    if ($stmt->execute()) {
        $_SESSION['basarili'] = "Bayi bilgileri başarıyla güncellendi!";
        header("Location: bayiler.php");
        exit();
    } else {
        $_SESSION['hata'] = "Güncelleme sırasında hata oluştu: " . $stmt->error;
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-edit"></i> Bayi Düzenle: <?= htmlspecialchars($bayi['bayi_adi']) ?></h1>
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
                <input type="text" id="bayi_adi" name="bayi_adi" value="<?= htmlspecialchars($bayi['bayi_adi']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="tel" id="telefon" name="telefon" value="<?= htmlspecialchars($bayi['telefon']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="adres">Adres</label>
                    <input type="text" id="adres" name="adres" value="<?= htmlspecialchars($bayi['adres']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="calisan_sayisi">Çalışan Sayısı</label>
                    <input type="number" id="calisan_sayisi" name="calisan_sayisi" min="1" value="<?= $bayi['calisan_sayisi'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="musteri_kapasitesi">Müşteri Kapasitesi</label>
                    <input type="number" id="musteri_kapasitesi" name="musteri_kapasitesi" min="1" value="<?= $bayi['musteri_kapasitesi'] ?>" required>
                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Güncelle
                </button>
                <a href="bayiler.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?> 