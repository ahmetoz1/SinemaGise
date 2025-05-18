<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();
include('functions.php');

// Film ve salon listelerini çek
$filmler = $conn->query("SELECT film_id, ad FROM film ORDER BY ad ASC");
$salonlar = $conn->query("SELECT salon_id, salon_no FROM salon ORDER BY salon_no ASC");

// SEANS EKLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $film_id = intval($_POST['film_id']);
    $salon_id = intval($_POST['salon_id']);
    $tarih = $conn->real_escape_string($_POST['tarih']);
    $bilet_fiyati = floatval($_POST['bilet_fiyati']);
    $goruntu = $conn->real_escape_string($_POST['goruntu']);
    $dil = $conn->real_escape_string($_POST['dil']);

    $sql = "INSERT INTO seans (film_id, salon_id, tarih, bilet_fiyati, goruntu, dil) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisdss", $film_id, $salon_id, $tarih, $bilet_fiyati, $goruntu, $dil);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Seans başarıyla eklendi!";
        header("Location: seanslar.php");
        exit();
    } else {
        $_SESSION['error'] = "Seans eklenirken hata oluştu: " . $conn->error;
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Yeni Seans Ekle</h1>
    <div class="admin-user">
        <i class="fas fa-user-circle"></i>
        <span><?= $_SESSION['personel_pozisyon'] ?></span>
    </div>
</div>

<div class="add-form-container">
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="film_id">Film:</label>
                <select id="film_id" name="film_id" required class="form-control">
                    <option value="">Film Seçiniz</option>
                    <?php while($film = $filmler->fetch_assoc()): ?>
                        <option value="<?= $film['film_id'] ?>"><?= htmlspecialchars($film['ad']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="salon_id">Salon:</label>
                <select id="salon_id" name="salon_id" required class="form-control">
                    <option value="">Salon Seçiniz</option>
                    <?php while($salon = $salonlar->fetch_assoc()): ?>
                        <option value="<?= $salon['salon_id'] ?>">Salon <?= $salon['salon_no'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tarih">Tarih ve Saat:</label>
                <input type="datetime-local" id="tarih" name="tarih" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="bilet_fiyati">Bilet Fiyatı (₺):</label>
                <input type="number" id="bilet_fiyati" name="bilet_fiyati" step="0.01" min="0" required class="form-control">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="goruntu">Görüntü Formatı:</label>
                <select id="goruntu" name="goruntu" required class="form-control">
                    <option value="2D">2D</option>
                    <option value="3D">3D</option>
                    <option value="4DX">4DX</option>
                    <option value="IMAX">IMAX</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="dil">Dil:</label>
                <select id="dil" name="dil" required class="form-control">
                    <option value="Türkçe Dublaj">Türkçe Dublaj</option>
                    <option value="Türkçe Altyazı">Türkçe Altyazı</option>
                    <option value="Orjinal Dil">Orjinal Dil</option>
                </select>
            </div>
        </div>

        <div class="form-submit">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Seansı Kaydet
            </button>
            <a href="seanslar.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> İptal
            </a>
        </div>
    </form>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>