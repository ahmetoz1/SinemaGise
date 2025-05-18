<?php 
include('header.php');
include('config.php');

// Hata ayıklama modunu aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Müşteri giriş kontrolü
if(!isset($_SESSION['musteri_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['uyari'] = "Bilet almak için giriş yapmalısınız!";
    header("Location: giris_yap.php");
    exit();
}

// Film veya seans ID kontrolü
if(!isset($_GET['film']) && !isset($_GET['seans'])) {
    $_SESSION['hata'] = "Geçersiz bilet alma isteği!";
    header("Location: film_liste.php");
    exit();
}

try {
    // Film bilgilerini çek
    if(isset($_GET['film'])) {
        $film_id = intval($_GET['film']);
        
        // Film detaylarını al
        $filmQuery = "SELECT * FROM film WHERE film_id = ?";
        $stmt = $conn->prepare($filmQuery);
        $stmt->bind_param("i", $film_id);
        $stmt->execute();
        $film = $stmt->get_result()->fetch_assoc();
        
        if(!$film) {
            throw new Exception("Film bulunamadı!");
        }
        
        // Bu film için tüm seansları al
        $seanslarQuery = "SELECT s.*, sa.salon_no, sa.kapasite, 
                         COALESCE(sdo.doluluk_orani, 0) as doluluk_orani
                         FROM seans s
                         JOIN salon sa ON s.salon_id = sa.salon_id
                         LEFT JOIN salon_doluluk_orani sdo ON s.seans_id = sdo.seans_id
                         WHERE s.film_id = ? AND s.tarih > NOW()
                         ORDER BY s.tarih ASC";
        $stmt = $conn->prepare($seanslarQuery);
        $stmt->bind_param("i", $film_id);
        $stmt->execute();
        $seanslar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if(count($seanslar) == 0) {
            $_SESSION['hata'] = "Bu film için uygun seans bulunamadı!";
            header("Location: film_detay.php?id=".$film_id);
            exit();
        }
        
        // Varsayılan olarak ilk seansı seç
        $seans = $seanslar[0];
        $seans_id = $seans['seans_id'];
        $kapasite = $seans['kapasite'];
    } else {
        $seans_id = intval($_GET['seans']);
        
        // Seans bilgilerini al
        $seansQuery = "SELECT s.*, f.*, sa.salon_no, sa.kapasite 
                      FROM seans s
                      JOIN film f ON s.film_id = f.film_id
                      JOIN salon sa ON s.salon_id = sa.salon_id
                      WHERE s.seans_id = ? AND s.tarih > NOW()";
        $stmt = $conn->prepare($seansQuery);
        $stmt->bind_param("i", $seans_id);
        $stmt->execute();
        $seans = $stmt->get_result()->fetch_assoc();
        
        if(!$seans) {
            throw new Exception("Seans bulunamadı veya tarihi geçmiş!");
        }
        
        $film_id = $seans['film_id'];
        $film = $seans;
        $kapasite = $seans['kapasite'];
        
        // Bu film için tüm seansları al (seans değiştirme seçeneği için)
        $seanslarQuery = "SELECT s.*, sa.salon_no,
                         COALESCE(sdo.doluluk_orani, 0) as doluluk_orani
                         FROM seans s
                         JOIN salon sa ON s.salon_id = sa.salon_id
                         LEFT JOIN salon_doluluk_orani sdo ON s.seans_id = sdo.seans_id
                         WHERE s.film_id = ? AND s.tarih > NOW()
                         ORDER BY s.tarih ASC";
        $stmt = $conn->prepare($seanslarQuery);
        $stmt->bind_param("i", $film_id);
        $stmt->execute();
        $seanslar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Koltuk durumlarını çek
    $koltuklarQuery = "SELECT koltuk_no, durum FROM koltuk 
                      WHERE seans_id = ? 
                      ORDER BY koltuk_no ASC";
    $stmt = $conn->prepare($koltuklarQuery);
    $stmt->bind_param("i", $seans_id);
    $stmt->execute();
    $koltuklarResult = $stmt->get_result();

    $koltukDurumlari = [];
    while($koltuk = $koltuklarResult->fetch_assoc()) {
        $koltukDurumlari[$koltuk['koltuk_no']] = $koltuk['durum'];
    }

} catch(Exception $e) {
    $_SESSION['hata'] = "Hata: " . $e->getMessage();
    header("Location: film_liste.php");
    exit();
}
?>

<main>
    <section class="booking-section">
        <div class="booking-container">
            <?php if(isset($_SESSION['hata'])): ?>
                <div class="alert error"><?= $_SESSION['hata'] ?></div>
                <?php unset($_SESSION['hata']); ?>
            <?php endif; ?>
            
            <div class="movie-selection">
                <h2>Bilet Al: <?= htmlspecialchars($film['ad']) ?></h2>
                <div class="selected-movie">
                    <img src="uploads/film_<?= $film_id ?>.jpg" alt="<?= htmlspecialchars($film['ad']) ?>" 
                         onerror="this.src='uploads/no_image.jpg'">
                    <div class="movie-details">
                        <h3><?= htmlspecialchars($film['ad']) ?></h3>
                        <p><i class="fas fa-clock"></i> <?= substr($film['sure'], 0, 5) ?></p>
                        <p><i class="fas fa-film"></i> <?= $seans['goruntu'] ?> - <?= $seans['dil'] ?></p>
                    </div>
                </div>
                
                <!-- Seans Seçim Bölümü -->
                <div class="session-selection">
                    <h3>Seans Seçiniz</h3>
                    <div class="session-options">
                        <?php foreach($seanslar as $s): ?>
                            <?php
                                $doluluk = round($s['doluluk_orani']);
                                if($doluluk < 25) {
                                    $dolulukClass = 'low';
                                } elseif($doluluk < 50) {
                                    $dolulukClass = 'midlow';
                                } elseif($doluluk < 75) {
                                    $dolulukClass = 'midhigh';
                                } else {
                                    $dolulukClass = 'high';
                                }
                            ?>
                            <div class="session-card <?= $s['seans_id'] == $seans_id ? 'active' : '' ?>">
                                <a href="bilet_al.php?seans=<?= $s['seans_id'] ?>">
                                    <p class="session-time"><?= date('H:i', strtotime($s['tarih'])) ?></p>
                                    <p class="session-hall">Salon <?= $s['salon_no'] ?></p>
                                    <p class="session-price"><?= $s['bilet_fiyati'] ?> TL</p>
                                    <p class="session-occupancy <?= $dolulukClass ?>">Doluluk: %<?= $doluluk ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="booking-form">
                <form action="bilet_islem.php" method="POST" id="biletForm">
                    <input type="hidden" name="seans_id" value="<?= $seans_id ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="koltuk_sayisi">Koltuk Sayısı:</label>
                            <select id="koltuk_sayisi" name="koltuk_sayisi" required class="form-control">
                                <?php for($i=1; $i<=6; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Koltuk</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="toplam_fiyat">Toplam Fiyat:</label>
                            <input type="text" id="toplam_fiyat" name="toplam_fiyat" 
                                   value="<?= $seans['bilet_fiyati'] ?> TL" readonly class="form-control">
                        </div>
                    </div>

                    <div class="seat-selection">
                        <h3>Koltuk Seçimi <small>(Maksimum 6 koltuk seçebilirsiniz)</small></h3>
                        <div class="screen">S A H N E</div>
                        <div class="seats-grid">
                            <?php
                            $sutun_sayisi = 10;
                            $satir_sayisi = ceil($kapasite / $sutun_sayisi);
                            
                            for($i=1; $i<=$satir_sayisi; $i++):
                                echo '<div class="seat-row">';
                                for($j=1; $j<=$sutun_sayisi; $j++):
                                    $koltuk_no = ($i-1)*$sutun_sayisi + $j;
                                    if($koltuk_no > $kapasite) break;
                                    
                                    $durum = isset($koltukDurumlari[$koltuk_no]) ? $koltukDurumlari[$koltuk_no] : 'Bos';
                                    $disabled = $durum == 'Dolu' ? 'disabled' : '';
                                    $status_class = $durum == 'Dolu' ? 'taken' : 'available';
                                    echo '<input type="checkbox" id="koltuk_'.$koltuk_no.'" name="koltuklar[]" 
                                        value="'.$koltuk_no.'" '.$disabled.' class="seat-checkbox visually-hidden">
                                        <label for="koltuk_'.$koltuk_no.'" class="seat '.$status_class.'">'.$koltuk_no.'</label>';
                                endfor;
                                echo '</div>';
                            endfor;
                            ?>
                        </div>
                        <div class="seat-legend">
                            <div><span class="seat available"></span> Boş</div>
                            <div><span class="seat taken"></span> Dolu</div>
                            <div><span class="seat selected"></span> Seçili</div>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 16px;">
                            <i class="fas fa-ticket-alt"></i> Biletleri Satın Al
                        </button>
                        <a href="film_detay.php?id=<?= $film_id ?>" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<style>
/* Ortak buton stili */
.btn {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
    margin: 8px;
    text-decoration: none;
    border: 2px solid #FFD700;
    text-align: center;
}

/* Biletleri Satın Al butonu */
.btn-primary {
    background-color: #000000 !important;
    color: #FFFFFF !important;
}

.btn-primary:hover {
    background-color: #FFD700 !important;
    color: #000000 !important;
    border-color: #000000 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Geri Dön butonu */
.btn-outline {
    background-color: #000000 !important;
    color: #FFFFFF !important;
    border-color: #FFD700 !important;
}

.btn-outline:hover {
    background-color: #FFD700 !important;
    color: #000000 !important;
    border-color: #000000 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Active durumu (tıklanınca) */
.btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Seans kartı stilleri */
.session-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    margin: 10px;
    min-width: 150px;
}

.session-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.session-card.active {
    border-color: #FFD700;
    background-color: #fff8e1;
}

.session-card a {
    text-decoration: none;
    color: inherit;
}

.session-time {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.session-hall {
    color: #666;
    margin-bottom: 5px;
}

.session-price {
    color: #2e7d32;
    font-weight: bold;
    margin-bottom: 5px;
}

.session-occupancy {
    font-size: 0.9em;
    color: #fff;
    margin-top: 5px;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
    font-weight: bold;
}
.session-occupancy.low { background-color: #43a047; }      /* Yeşil */
.session-occupancy.midlow { background-color: #ffd600; color: #333; } /* Sarı */
.session-occupancy.midhigh { background-color: #ff9800; }  /* Turuncu */
.session-occupancy.high { background-color: #e53935; }     /* Kırmızı */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const koltukSayisiSelect = document.getElementById('koltuk_sayisi');
    const toplamFiyatInput = document.getElementById('toplam_fiyat');
    const biletFiyati = <?= $seans['bilet_fiyati'] ?>;
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox:not(:disabled)');

    // Toplam fiyatı güncelle
    function updateTotalPrice() {
        const selectedCount = parseInt(koltukSayisiSelect.value);
        toplamFiyatInput.value = (biletFiyati * selectedCount) + ' TL';
    }

    // Koltuk seçimini yönet
    function handleSeatSelection() {
        const selectedSeats = document.querySelectorAll('.seat-checkbox:checked').length;
        const allowedSeats = parseInt(koltukSayisiSelect.value);
        
        if(selectedSeats > allowedSeats) {
            this.checked = false;
            alert(`En fazla ${allowedSeats} koltuk seçebilirsiniz!`);
            return;
        }
        
        const label = document.querySelector(`label[for="${this.id}"]`);
        if(this.checked) {
            label.classList.add('selected');
            label.classList.remove('available');
        } else {
            label.classList.remove('selected');
            label.classList.add('available');
        }
    }

    // Form gönderimini kontrol et
    function validateForm(e) {
        const selectedSeats = document.querySelectorAll('.seat-checkbox:checked').length;
        const requestedSeats = parseInt(koltukSayisiSelect.value);
        
        if(selectedSeats !== requestedSeats) {
            e.preventDefault();
            alert(`Lütfen tam olarak ${requestedSeats} koltuk seçiniz!`);
            return false;
        }
        return true;
    }

    // Event listener'ları ekle
    koltukSayisiSelect.addEventListener('change', updateTotalPrice);
    seatCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleSeatSelection);
    });
    document.getElementById('biletForm').addEventListener('submit', validateForm);
    
    // Başlangıçta toplam fiyatı güncelle
    updateTotalPrice();
});
</script>

<?php 
include('footer.php'); 
?>