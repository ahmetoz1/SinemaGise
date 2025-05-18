<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
require_once __DIR__.'/functions.php';
checkAdminSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = $conn->real_escape_string($_POST['ad']);
    $soyad = $conn->real_escape_string($_POST['soyad']);
    $e_posta = $conn->real_escape_string($_POST['e_posta']);
    $pozisyon = $conn->real_escape_string($_POST['pozisyon']);
    $bayi_id = (int)$_POST['bayi_id'];
    $tel_no = $conn->real_escape_string($_POST['tel_no']);
    $dogum_tarihi = $conn->real_escape_string($_POST['dogum_tarihi']);
    $adres = $conn->real_escape_string($_POST['adres']);
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

    // E-posta kontrolü
    $email_check = $conn->query("SELECT COUNT(*) FROM personel WHERE e_posta = '$e_posta'")->fetch_row()[0];
    if ($email_check > 0) {
        $_SESSION['hata'] = "Bu e-posta adresi zaten kullanılıyor!";
        header("Location: personel_ekle.php");
        exit();
    }

    $query = "INSERT INTO personel (ad, soyad, e_posta, pozisyon, bayi_id, tel_no, dogum_tarihi, adres, sifre) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssissss", $ad, $soyad, $e_posta, $pozisyon, $bayi_id, $tel_no, $dogum_tarihi, $adres, $sifre);

    if ($stmt->execute()) {
        $_SESSION['basarili'] = "Personel başarıyla eklendi!";
        header("Location: personeller.php");
        exit();
    } else {
        $_SESSION['hata'] = "Personel eklenirken hata oluştu: " . $stmt->error;
    }
}

// Bayileri çek
$bayiler = $conn->query("SELECT * FROM bayi");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-user-plus"></i> Yeni Personel Ekle</h1>
    </div>

    <?php if(isset($_SESSION['hata'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['hata'] ?>
        </div>
        <?php unset($_SESSION['hata']); ?>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="">
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

            <div class="form-group">
                <label for="e_posta">E-posta</label>
                <input type="email" id="e_posta" name="e_posta" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pozisyon">Pozisyon</label>
                    <select id="pozisyon" name="pozisyon" required>
                        <option value="Gişe Görevlisi">Gişe Görevlisi</option>
                        <option value="Temizlik Görevlisi">Temizlik Görevlisi</option>
                        <option value="Güvenlik">Güvenlik</option>
                        <option value="Yönetici">Yönetici</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bayi_id">Bağlı Olduğu Sinema</label>
                    <select id="bayi_id" name="bayi_id" required>
                        <option value="">Seçiniz</option>
                        <?php while($bayi = $bayiler->fetch_assoc()): ?>
                            <option value="<?= $bayi['bayi_id'] ?>"><?= htmlspecialchars($bayi['bayi_adi']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tel_no">Telefon</label>
                    <input type="tel" id="tel_no" name="tel_no" required>
                </div>
                <div class="form-group">
                    <label for="dogum_tarihi">Doğum Tarihi</label>
                    <input type="date" id="dogum_tarihi" name="dogum_tarihi" required>
                </div>
            </div>

            <div class="form-group">
                <label for="adres">Adres</label>
                <textarea id="adres" name="adres" rows="3" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="sifre_tekrar">Şifre Tekrar</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" minlength="6" required>
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

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const sifre = document.getElementById('sifre').value;
    const sifreTekrar = document.getElementById('sifre_tekrar').value;
    
    if(sifre !== sifreTekrar) {
        e.preventDefault();
        alert('Şifreler uyuşmuyor!');
        return false;
    }
    
    return true;
});
</script>