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

// Bayileri çek
$bayiler_query = "SELECT bayi_id, bayi_adi FROM bayi ORDER BY bayi_adi";
$bayiler_result = $conn->query($bayiler_query);

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bayi_id = (int)$_POST['bayi_id'];
    $salonlar = $_POST['salonlar'];
    $basarili = true;
    $hata_mesaji = "";

    // Her bir salon için işlem yap
    foreach ($salonlar as $salon) {
        $salon_no = $conn->real_escape_string($salon['salon_no']);
        $kapasite = (int)$salon['kapasite'];

        // Salon no kontrolü
        $no_check = $conn->query("SELECT COUNT(*) FROM salon WHERE salon_no = '$salon_no' AND bayi_id = $bayi_id")->fetch_row()[0];
        if ($no_check > 0) {
            $basarili = false;
            $hata_mesaji = "Bayi için salon no $salon_no zaten kullanılıyor!";
            break;
        }

        $query = "INSERT INTO salon (salon_no, bayi_id, kapasite) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $salon_no, $bayi_id, $kapasite);

        if (!$stmt->execute()) {
            $basarili = false;
            $hata_mesaji = "Salon eklenirken bir hata oluştu: " . $stmt->error;
            break;
        }
    }

    if ($basarili) {
        $_SESSION['basarili'] = "Salonlar başarıyla eklendi!";
        header("Location: salonlar.php");
        exit();
    } else {
        $_SESSION['hata'] = $hata_mesaji;
    }
}

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-plus"></i> Yeni Salon Ekle</h1>
    </div>

    <?php if(isset($_SESSION['hata'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['hata'] ?>
        </div>
        <?php unset($_SESSION['hata']); ?>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="" id="salonForm">
            <div class="form-group">
                <label for="bayi_id">Bayi</label>
                <select id="bayi_id" name="bayi_id" required>
                    <option value="">Bayi Seçin</option>
                    <?php while($bayi = $bayiler_result->fetch_assoc()): ?>
                        <option value="<?= $bayi['bayi_id'] ?>"><?= htmlspecialchars($bayi['bayi_adi']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div id="salonlarContainer">
                <div class="salon-item">
                    <h3>Salon 1</h3>
                    <div class="form-group">
                        <label for="salon_no_1">Salon No</label>
                        <input type="text" id="salon_no_1" name="salonlar[0][salon_no]" required>
                    </div>

                    <div class="form-group">
                        <label for="kapasite_1">Kapasite</label>
                        <input type="number" id="kapasite_1" name="salonlar[0][kapasite]" min="1" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-secondary" onclick="salonEkle()">
                    <i class="fas fa-plus"></i> Salon Ekle
                </button>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="salonlar.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let salonSayisi = 1;

function salonEkle() {
    salonSayisi++;
    const container = document.getElementById('salonlarContainer');
    const yeniSalon = document.createElement('div');
    yeniSalon.className = 'salon-item';
    yeniSalon.innerHTML = `
        <h3>Salon ${salonSayisi}</h3>
        <div class="form-group">
            <label for="salon_no_${salonSayisi}">Salon No</label>
            <input type="text" id="salon_no_${salonSayisi}" name="salonlar[${salonSayisi-1}][salon_no]" required>
        </div>

        <div class="form-group">
            <label for="kapasite_${salonSayisi}">Kapasite</label>
            <input type="number" id="kapasite_${salonSayisi}" name="salonlar[${salonSayisi-1}][kapasite]" min="1" required>
        </div>

        <button type="button" class="btn btn-danger btn-sm" onclick="salonSil(this)">
            <i class="fas fa-trash"></i> Salonu Sil
        </button>
    `;
    container.appendChild(yeniSalon);
}

function salonSil(button) {
    const salonItem = button.parentElement;
    salonItem.remove();
    // Salon numaralarını güncelle
    const salonlar = document.querySelectorAll('.salon-item');
    salonlar.forEach((salon, index) => {
        salon.querySelector('h3').textContent = `Salon ${index + 1}`;
        const inputs = salon.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
        });
    });
    salonSayisi--;
}
</script>

<style>
.salon-item {
    background: #f8f9fa;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.salon-item h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
}

.btn-danger {
    margin-top: 10px;
}
</style>

<?php include(__DIR__.'/includes/footer.php'); ?> 