<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

// Personel ID'sini güvenli şekilde al
$personel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$personel_id) {
    $_SESSION['hata'] = "Geçersiz personel ID!";
    header("Location: personeller.php");
    exit();
}

// Veritabanından personel bilgilerini çek
$sql = "SELECT * FROM personel WHERE personel_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['hata'] = "Sorgu hatası: " . $conn->error;
    header("Location: personeller.php");
    exit();
}
$stmt->bind_param("i", $personel_id);
$stmt->execute();
$result = $stmt->get_result();
$personel = $result->fetch_assoc();

if (!$personel) {
    $_SESSION['hata'] = "Personel bulunamadı!";
    header("Location: personeller.php");
    exit();
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = $conn->real_escape_string($_POST['ad']);
    $soyad = $conn->real_escape_string($_POST['soyad']);
    $e_posta = $conn->real_escape_string($_POST['e_posta']);
    $pozisyon = $conn->real_escape_string($_POST['pozisyon']);
    $bayi_id = (int)$_POST['bayi_id'];
    $tel_no = $conn->real_escape_string($_POST['tel_no']);
    $dogum_tarihi = $conn->real_escape_string($_POST['dogum_tarihi']);
    $adres = $conn->real_escape_string($_POST['adres']);

    // E-posta kontrolü (kendi e-postası hariç)
    $email_check = $conn->query("SELECT COUNT(*) FROM personel WHERE e_posta = '$e_posta' AND personel_id != $personel_id")->fetch_row()[0];
    if ($email_check > 0) {
        $_SESSION['hata'] = "Bu e-posta adresi başka bir personel tarafından kullanılıyor!";
        header("Location: personel_duzenle.php?id=$personel_id");
        exit();
    }

    // Şifre değiştirilecek mi?
    if (!empty($_POST['sifre'])) {
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
        $query = "UPDATE personel SET ad=?, soyad=?, e_posta=?, pozisyon=?, bayi_id=?, tel_no=?, dogum_tarihi=?, adres=?, sifre=? WHERE personel_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssissssi", $ad, $soyad, $e_posta, $pozisyon, $bayi_id, $tel_no, $dogum_tarihi, $adres, $sifre, $personel_id);
    } else {
        $query = "UPDATE personel SET ad=?, soyad=?, e_posta=?, pozisyon=?, bayi_id=?, tel_no=?, dogum_tarihi=?, adres=? WHERE personel_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssisssi", $ad, $soyad, $e_posta, $pozisyon, $bayi_id, $tel_no, $dogum_tarihi, $adres, $personel_id);
    }

    if ($stmt->execute()) {
        $_SESSION['basarili'] = "Personel bilgileri başarıyla güncellendi!";
        header("Location: personeller.php");
        exit();
    } else {
        $_SESSION['hata'] = "Güncelleme sırasında hata oluştu: " . $stmt->error;
    }
}

// Bayileri çek
$bayiler = $conn->query("SELECT * FROM bayi");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-user-edit"></i> Personel Düzenle: <?= htmlspecialchars($personel['ad'] . ' ' . $personel['soyad']) ?></h1>
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
                    <input type="text" id="ad" name="ad" value="<?= htmlspecialchars($personel['ad']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="soyad">Soyad</label>
                    <input type="text" id="soyad" name="soyad" value="<?= htmlspecialchars($personel['soyad']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="e_posta">E-posta</label>
                <input type="email" id="e_posta" name="e_posta" value="<?= htmlspecialchars($personel['e_posta']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pozisyon">Pozisyon</label>
                    <select id="pozisyon" name="pozisyon" required>
                        <option value="Gişe Görevlisi" <?= $personel['pozisyon'] == 'Gişe Görevlisi' ? 'selected' : '' ?>>Gişe Görevlisi</option>
                        <option value="Temizlik Görevlisi" <?= $personel['pozisyon'] == 'Temizlik Görevlisi' ? 'selected' : '' ?>>Temizlik Görevlisi</option>
                        <option value="Güvenlik" <?= $personel['pozisyon'] == 'Güvenlik' ? 'selected' : '' ?>>Güvenlik</option>
                        <option value="Yönetici" <?= $personel['pozisyon'] == 'Yönetici' ? 'selected' : '' ?>>Yönetici</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bayi_id">Bağlı Olduğu Sinema</label>
                    <select id="bayi_id" name="bayi_id" required>
                        <option value="">Seçiniz</option>
                        <?php while($bayi = $bayiler->fetch_assoc()): ?>
                            <option value="<?= $bayi['bayi_id'] ?>" <?= $bayi['bayi_id'] == $personel['bayi_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bayi['bayi_adi']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tel_no">Telefon</label>
                    <input type="tel" id="tel_no" name="tel_no" value="<?= htmlspecialchars($personel['tel_no']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="dogum_tarihi">Doğum Tarihi</label>
                    <input type="date" id="dogum_tarihi" name="dogum_tarihi" value="<?= $personel['dogum_tarihi'] ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="adres">Adres</label>
                <textarea id="adres" name="adres" rows="3" required><?= htmlspecialchars($personel['adres']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sifre">Yeni Şifre</label>
                    <input type="password" id="sifre" name="sifre" minlength="6">
                    <p class="password-note">Şifreyi değiştirmek istemiyorsanız boş bırakın</p>
                </div>
                <div class="form-group">
                    <label for="sifre_tekrar">Yeni Şifre Tekrar</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" minlength="6">
                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Güncelle
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
    
    if(sifre && sifre !== sifreTekrar) {
        e.preventDefault();
        alert('Şifreler uyuşmuyor!');
        return false;
    }
    
    return true;
});
</script>