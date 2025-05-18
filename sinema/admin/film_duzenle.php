<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();
include('functions.php');

$bayiler = getBayiler();

// Film ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz film ID!";
    header("Location: filmler.php");
    exit();
}

$film_id = (int)$_GET['id'];

// Film bilgilerini çek
$film = $conn->query("SELECT * FROM film WHERE film_id = $film_id")->fetch_assoc();
if(!$film) {
    $_SESSION['hata'] = "Film bulunamadı!";
    header("Location: filmler.php");
    exit();
}

// Film güncelleme işlemi
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['film_guncelle'])) {
    // POST verilerini kontrol et
    error_log("POST verileri: " . print_r($_POST, true));
    
    // Form verilerini al ve kontrol et
    $bayi_id = isset($_POST['bayi_id']) ? (int)$_POST['bayi_id'] : 0;
    $ad = isset($_POST['ad']) ? $conn->real_escape_string($_POST['ad']) : '';
    $gise_baslangıc = isset($_POST['gise_baslangıc']) ? $conn->real_escape_string($_POST['gise_baslangıc']) : '';
    $gise_bitis = isset($_POST['gise_bitis']) ? $conn->real_escape_string($_POST['gise_bitis']) : '';
    $sure = isset($_POST['sure']) ? (int)$_POST['sure'] : 0;
    // Süreyi saat:dakika formatına çevir
    $sure_format = sprintf("%02d:%02d", floor($sure / 60), $sure % 60);
    $yas_siniri = isset($_POST['yas_siniri']) ? (int)$_POST['yas_siniri'] : 0;
    $kategori = isset($_POST['kategori']) ? $conn->real_escape_string($_POST['kategori']) : '';
    $yonetmen = isset($_POST['yonetmen']) ? $conn->real_escape_string($_POST['yonetmen']) : '';
    $ozet = isset($_POST['ozet']) ? $conn->real_escape_string($_POST['ozet']) : '';
    
    error_log("İşlenmiş veriler:");
    error_log("Film ID: $film_id");
    error_log("Özet: $ozet");
    
    // Veritabanı güncelleme sorgusu
    $query = "UPDATE film SET 
                bayi_id = ?,
                ad = ?,
                gise_baslangıc = ?,
                gise_bitis = ?,
                sure = ?,
                yas_siniri = ?,
                kategori = ?,
                yonetmen = ?,
                ozet = ?
              WHERE film_id = ?";
    
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare hatası: " . $conn->error);
        }
        
        $stmt->bind_param("issssisssi", 
            $bayi_id, 
            $ad, 
            $gise_baslangıc, 
            $gise_bitis, 
            $sure_format, 
            $yas_siniri, 
            $kategori, 
            $yonetmen, 
            $ozet, 
            $film_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute hatası: " . $stmt->error);
        }
        
        error_log("Güncelleme başarılı. Etkilenen satır: " . $stmt->affected_rows);
        
        // Güncelleme sonrası kontrol
        $kontrol = $conn->query("SELECT * FROM film WHERE film_id = $film_id")->fetch_assoc();
        error_log("Güncelleme sonrası veri: " . print_r($kontrol, true));
        
        // Dosya yükleme
        if(isset($_FILES['afis']) && $_FILES['afis']['error'] == 0) {
            $hedef = "../uploads/film_" . $film_id . ".jpg";
            
            // Dosya türü kontrolü
            $izin_verilen_turler = ['image/jpeg', 'image/jpg'];
            if(!in_array($_FILES['afis']['type'], $izin_verilen_turler)) {
                throw new Exception("Sadece JPEG formatında fotoğraf yükleyebilirsiniz!");
            }
            
            // Dosya boyutu kontrolü (2MB)
            if($_FILES['afis']['size'] > 2 * 1024 * 1024) {
                throw new Exception("Fotoğraf boyutu 2MB'dan büyük olamaz!");
            }
            
            // Yükleme işlemi
            if(!move_uploaded_file($_FILES['afis']['tmp_name'], $hedef)) {
                throw new Exception("Fotoğraf yüklenirken bir hata oluştu!");
            }
            
            error_log("Fotoğraf başarıyla yüklendi: " . $hedef);
        }
        
        $_SESSION['basarili'] = "Film başarıyla güncellendi!";
        header("Location: filmler.php");
        exit();
        
    } catch (Exception $e) {
        error_log("Hata: " . $e->getMessage());
        $_SESSION['hata'] = "Film güncellenirken hata oluştu: " . $e->getMessage();
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Film Düzenle: <?= htmlspecialchars($film['ad']) ?></h1>
    <div class="admin-user">
        <i class="fas fa-user-circle"></i>
        <span><?= $_SESSION['personel_pozisyon'] ?></span>
    </div>
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
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="bayi_id">Sinema Salonu</label>
            <select id="bayi_id" name="bayi_id" required>
                <option value="">Seçiniz</option>
                <?php foreach($bayiler as $id => $adi): ?>
                    <option value="<?= $id ?>" <?= $id == $film['bayi_id'] ? 'selected' : '' ?>><?= $adi ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="ad">Film Adı</label>
            <input type="text" id="ad" name="ad" value="<?= htmlspecialchars($film['ad']) ?>" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="gise_baslangıc">Gişe Başlangıç</label>
                <input type="datetime-local" id="gise_baslangıc" name="gise_baslangıc" 
                       value="<?= date('Y-m-d\TH:i', strtotime($film['gise_baslangıc'])) ?>" required>
            </div>
            <div class="form-group">
                <label for="gise_bitis">Gişe Bitiş</label>
                <input type="datetime-local" id="gise_bitis" name="gise_bitis" 
                       value="<?= date('Y-m-d\TH:i', strtotime($film['gise_bitis'])) ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="sure">Süre (dk)</label>
                <?php
                // Veritabanından gelen süreyi dakikaya çevir
                $mevcut_sure = 0;
                if(!empty($film['sure'])) {
                    $parcalar = explode(':', $film['sure']);
                    if(count($parcalar) >= 2) {
                        $mevcut_sure = (int)$parcalar[0] * 60 + (int)$parcalar[1];
                    }
                }
                ?>
                <input type="number" id="sure" name="sure" min="1" value="<?= $mevcut_sure ?>" required>
                <small>Dakika cinsinden giriniz (örn: 120 dakika = 02:00)</small>
            </div>
            <div class="form-group">
                <label for="yas_siniri">Yaş Sınırı</label>
                <select id="yas_siniri" name="yas_siniri" required>
                    <option value="0" <?= $film['yas_siniri'] == 0 ? 'selected' : '' ?>>Genel İzleyici</option>
                    <option value="7" <?= $film['yas_siniri'] == 7 ? 'selected' : '' ?>>7+</option>
                    <option value="13" <?= $film['yas_siniri'] == 13 ? 'selected' : '' ?>>13+</option>
                    <option value="18" <?= $film['yas_siniri'] == 18 ? 'selected' : '' ?>>18+</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Seçiniz</option>
                    <option value="Aksiyon" <?= $film['kategori'] == 'Aksiyon' ? 'selected' : '' ?>>Aksiyon</option>
                    <option value="Komedi" <?= $film['kategori'] == 'Komedi' ? 'selected' : '' ?>>Komedi</option>
                    <option value="Drama" <?= $film['kategori'] == 'Drama' ? 'selected' : '' ?>>Drama</option>
                    <option value="Korku" <?= $film['kategori'] == 'Korku' ? 'selected' : '' ?>>Korku</option>
                    <option value="Bilim Kurgu" <?= $film['kategori'] == 'Bilim Kurgu' ? 'selected' : '' ?>>Bilim Kurgu</option>
                    <option value="Fantastik" <?= $film['kategori'] == 'Fantastik' ? 'selected' : '' ?>>Fantastik</option>
                    <option value="Romantik" <?= $film['kategori'] == 'Romantik' ? 'selected' : '' ?>>Romantik</option>
                    <option value="Animasyon" <?= $film['kategori'] == 'Animasyon' ? 'selected' : '' ?>>Animasyon</option>
                </select>
            </div>
            <div class="form-group">
                <label for="yonetmen">Yönetmen</label>
                <input type="text" id="yonetmen" name="yonetmen" value="<?= htmlspecialchars($film['yonetmen']) ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="ozet">Film Özeti</label>
            <textarea id="ozet" name="ozet" rows="5" style="width: 100%;"><?= htmlspecialchars($film['ozet'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="afis">Film Afişi (JPEG, max 2MB)</label>
            <input type="file" id="afis" name="afis" accept="image/jpeg">
            <div class="current-image">
                <p>Mevcut Afiş:</p>
                <img src="../uploads/film_<?= $film_id ?>.jpg" alt="<?= htmlspecialchars($film['ad']) ?>" 
                     onerror="this.src='../uploads/no_image.jpg'" style="max-width: 200px; margin-top: 10px;">
            </div>
        </div>
        
        <div class="form-submit">
            <button type="submit" name="film_guncelle" class="btn btn-primary">
                <i class="fas fa-save"></i> Güncelle
            </button>
            <a href="filmler.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> İptal
            </a>
        </div>
    </form>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>