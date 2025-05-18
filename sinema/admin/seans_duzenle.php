<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();
include('functions.php');

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz seans ID!";
    header("Location: seanslar.php");
    exit();
}

$seans_id = (int)$_GET['id'];

// Seans bilgilerini getir
$seans = $conn->query("
    SELECT s.*, f.ad AS film_adi, sa.salon_no, f.bayi_id 
    FROM seans s
    JOIN film f ON s.film_id = f.film_id
    JOIN salon sa ON s.salon_id = sa.salon_id
    WHERE s.seans_id = $seans_id
");

if(!$seans || $seans->num_rows == 0) {
    $_SESSION['hata'] = "Seans bulunamadı!";
    header("Location: seanslar.php");
    exit();
}

$seans = $seans->fetch_assoc();

// Form gönderildiğinde
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seans_guncelle'])) {
    $salon_id = (int)$_POST['salon_id'];
    $film_id = (int)$_POST['film_id'];
    $tarih = $conn->real_escape_string($_POST['tarih']);
    $bilet_fiyati = (int)$_POST['bilet_fiyati'];
    $goruntu = $conn->real_escape_string($_POST['goruntu']);
    $dil = $conn->real_escape_string($_POST['dil']);

    $query = "UPDATE seans SET 
              salon_id = ?, film_id = ?, tarih = ?, bilet_fiyati = ?, goruntu = ?, dil = ?
              WHERE seans_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisisii", $salon_id, $film_id, $tarih, $bilet_fiyati, $goruntu, $dil, $seans_id);
    
    if($stmt->execute()) {
        $_SESSION['basarili'] = "Seans bilgileri güncellendi!";
        header("Location: seanslar.php");
        exit();
    } else {
        $_SESSION['hata'] = "Güncelleme sırasında hata oluştu: " . $stmt->error;
    }
}

// Film ve salon listelerini getir
$filmler = $conn->query("SELECT film_id, ad FROM film WHERE bayi_id = ".$seans['bayi_id']." ORDER BY ad");
$salonlar = $conn->query("SELECT salon_id, salon_no FROM salon WHERE bayi_id = ".$seans['bayi_id']." ORDER BY salon_no");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Seans Düzenle: <?= htmlspecialchars($seans['film_adi']) ?></h1>
</div>

<div class="form-container">
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="film_id">Film</label>
                <select id="film_id" name="film_id" class="form-control" required>
                    <?php while($film = $filmler->fetch_assoc()): ?>
                        <option value="<?= $film['film_id'] ?>" <?= $film['film_id'] == $seans['film_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($film['ad']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="salon_id">Salon</label>
                <select id="salon_id" name="salon_id" class="form-control" required>
                    <?php while($salon = $salonlar->fetch_assoc()): ?>
                        <option value="<?= $salon['salon_id'] ?>" <?= $salon['salon_id'] == $seans['salon_id'] ? 'selected' : '' ?>>
                            Salon <?= $salon['salon_no'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tarih">Tarih ve Saat</label>
                <input type="datetime-local" id="tarih" name="tarih" class="form-control"
                       value="<?= date('Y-m-d\TH:i', strtotime($seans['tarih'])) ?>" required>
            </div>
            <div class="form-group">
                <label for="bilet_fiyati">Bilet Fiyatı (₺)</label>
                <input type="number" id="bilet_fiyati" name="bilet_fiyati" class="form-control"
                       value="<?= $seans['bilet_fiyati'] ?>" min="0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="goruntu">Görüntü Formatı</label>
                <select id="goruntu" name="goruntu" class="form-control" required>
                    <option value="2D" <?= $seans['goruntu'] == '2D' ? 'selected' : '' ?>>2D</option>
                    <option value="3D" <?= $seans['goruntu'] == '3D' ? 'selected' : '' ?>>3D</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dil">Dil</label>
                <select id="dil" name="dil" class="form-control" required>
                    <option value="Türkçe Dublaj" <?= $seans['dil'] == 'Türkçe Dublaj' ? 'selected' : '' ?>>Türkçe Dublaj</option>
                    <option value="Orijinal-Altyazılı" <?= $seans['dil'] == 'Orijinal-Altyazılı' ? 'selected' : '' ?>>Orijinal-Altyazılı</option>
                </select>
            </div>
        </div>

        <div class="form-submit">
            <button type="submit" name="seans_guncelle" class="btn btn-primary">
                <i class="fas fa-save"></i> Güncelle
            </button>
            <a href="seanslar.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> İptal
            </a>
        </div>
    </form>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>