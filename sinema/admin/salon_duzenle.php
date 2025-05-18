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

// Salon ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['hata'] = "Geçersiz salon ID!";
    header("Location: salonlar.php");
    exit();
}

$salon_id = (int)$_GET['id'];

// Salon bilgilerini çek
$query = "SELECT * FROM salon WHERE salon_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$result = $stmt->get_result();
$salon = $result->fetch_assoc();

if(!$salon) {
    $_SESSION['hata'] = "Salon bulunamadı!";
    header("Location: salonlar.php");
    exit();
}

// Bayileri çek
$bayiler_query = "SELECT bayi_id, bayi_adi FROM bayi ORDER BY bayi_adi";
$bayiler_result = $conn->query($bayiler_query);

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salon_no = $conn->real_escape_string($_POST['salon_no']);
    $bayi_id = (int)$_POST['bayi_id'];
    $kapasite = (int)$_POST['kapasite'];

    // Salon no kontrolü (kendi no'su hariç)
    $no_check = $conn->query("SELECT COUNT(*) FROM salon WHERE salon_no = '$salon_no' AND bayi_id = $bayi_id AND salon_id != $salon_id")->fetch_row()[0];
    if ($no_check > 0) {
        $_SESSION['hata'] = "Bu bayi için bu salon numarası zaten kullanılıyor!";
        header("Location: salon_duzenle.php?id=$salon_id");
        exit();
    }

    $query = "UPDATE salon SET 
              salon_no = ?, 
              bayi_id = ?, 
              kapasite = ? 
              WHERE salon_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siii", $salon_no, $bayi_id, $kapasite, $salon_id);

    if ($stmt->execute()) {
        $_SESSION['basarili'] = "Salon bilgileri başarıyla güncellendi!";
        header("Location: salonlar.php");
        exit();
    } else {
        $_SESSION['hata'] = "Güncelleme sırasında hata oluştu: " . $stmt->error;
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-edit"></i> Salon Düzenle: Salon <?= htmlspecialchars($salon['salon_no']) ?></h1>
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
                <label for="salon_no">Salon No</label>
                <input type="text" id="salon_no" name="salon_no" value="<?= htmlspecialchars($salon['salon_no']) ?>" required>
            </div>

            <div class="form-group">
                <label for="bayi_id">Bayi</label>
                <select id="bayi_id" name="bayi_id" required>
                    <option value="">Bayi Seçin</option>
                    <?php while($bayi = $bayiler_result->fetch_assoc()): ?>
                        <option value="<?= $bayi['bayi_id'] ?>" <?= ($bayi['bayi_id'] == $salon['bayi_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bayi['bayi_adi']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="kapasite">Kapasite</label>
                <input type="number" id="kapasite" name="kapasite" min="1" value="<?= $salon['kapasite'] ?>" required>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Güncelle
                </button>
                <a href="salonlar.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?> 