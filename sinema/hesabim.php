<?php 
include('header.php');
include('config.php');

// Giriş kontrolü
if(!isset($_SESSION['musteri_id'])) {
    header("Location: giris_yap.php");
    exit();
}

// Müşteri bilgilerini çek
$query = "SELECT * FROM musteri WHERE musteri_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['musteri_id']);
$stmt->execute();
$result = $stmt->get_result();
$musteri = $result->fetch_assoc();

// Bilet geçmişini çek
$biletlerQuery = "SELECT b.*, f.ad as film_adi, s.tarih, s.bilet_fiyati, sa.salon_no
                 FROM bilet b
                 JOIN seans s ON b.seans_id = s.seans_id
                 JOIN film f ON s.film_id = f.film_id
                 JOIN salon sa ON s.salon_id = sa.salon_id
                 WHERE b.musteri_id = ?
                 ORDER BY b.satis_tarihi DESC, s.tarih DESC";
$stmt = $conn->prepare($biletlerQuery);
$stmt->bind_param("i", $_SESSION['musteri_id']);
$stmt->execute();
$biletlerResult = $stmt->get_result();

// Toplam harcama hesapla
$toplamHarcamaQuery = "SELECT SUM(fiyat) as toplam FROM bilet WHERE musteri_id = ?";
$stmt = $conn->prepare($toplamHarcamaQuery);
$stmt->bind_param("i", $_SESSION['musteri_id']);
$stmt->execute();
$toplamHarcamaResult = $stmt->get_result();
$toplamHarcama = $toplamHarcamaResult->fetch_assoc()['toplam'] ?? 0;
?>

<main>
    <section class="account-section">
        <div class="account-container">
            <div class="account-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?= htmlspecialchars($musteri['ad'] . ' ' . $musteri['soyad']) ?></h3>
                    <p class="member-since">Üyelik Tarihi: <?= date('d.m.Y', strtotime($musteri['kayit_tarihi'] ?? 'now')) ?></p>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?= $musteri['puan'] ?></span>
                            <span class="stat-label">Puan</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?= $biletlerResult->num_rows ?></span>
                            <span class="stat-label">Bilet</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?= number_format($toplamHarcama, 2) ?> TL</span>
                            <span class="stat-label">Harcama</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="account-content">
                <h2>Bilet Geçmişim</h2>
                
                <?php if(isset($_GET['bilet']) && $_GET['bilet'] == 'basarili'): ?>
                    <div class="alert success">Biletiniz başarıyla alındı!</div>
                <?php endif; ?>
                
                <?php if($biletlerResult->num_rows > 0): ?>
                    <div class="tickets-list">
                        <?php while($bilet = $biletlerResult->fetch_assoc()): ?>
                            <div class="ticket-card">
                                <div class="ticket-header">
                                    <h3><?= htmlspecialchars($bilet['film_adi']) ?></h3>
                                    <span class="ticket-price"><?= number_format($bilet['fiyat'], 2) ?> TL</span>
                                </div>
                                <div class="ticket-body">
                                    <p><i class="fas fa-calendar-alt"></i> Film Tarihi: <?= date('d F Y H:i', strtotime($bilet['tarih'])) ?></p>
                                    <p><i class="fas fa-chair"></i> Koltuk No: <?= $bilet['koltuk_id'] ?> - Salon <?= $bilet['salon_no'] ?></p>
                                    <p><i class="fas fa-receipt"></i> Satın Alma Tarihi: <?= date('d.m.Y H:i', strtotime($bilet['satis_tarihi'])) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Henüz bilet almadınız.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php 
$biletlerResult->close();
include('footer.php'); 
?>