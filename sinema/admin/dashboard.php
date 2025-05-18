<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

// İstatistikleri al
$filmSayisi = $conn->query("SELECT COUNT(*) FROM film")->fetch_row()[0];
$seansSayisi = $conn->query("SELECT COUNT(*) FROM seans WHERE tarih >= CURDATE()")->fetch_row()[0];
$musteriSayisi = $conn->query("SELECT COUNT(*) FROM musteri")->fetch_row()[0];
$biletSayisi = $conn->query("SELECT COUNT(*) FROM bilet")->fetch_row()[0];

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Hoş Geldiniz, <?= $_SESSION['personel_adi'] ?></h1>
        <div class="admin-user">
            <i class="fas fa-user-circle"></i>
            <span><?= $_SESSION['personel_pozisyon'] ?></span>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-film"></i></div>
            <div class="stat-value"><?= $filmSayisi ?></div>
            <h3>Toplam Film</h3>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value"><?= $seansSayisi ?></div>
            <h3>Aktif Seans</h3>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= $musteriSayisi ?></div>
            <h3>Kayıtlı Müşteri</h3>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-value"><?= $biletSayisi ?></div>
            <h3>Satılan Bilet</h3>
        </div>
    </div>
    
    <div class="recent-activity">
        <h2>Son Etkinlikler</h2>
        <?php
        // DÜZELTİLMİŞ SQL SORGU
        $etkinlikler = $conn->query("
            (SELECT 'film' AS tip, ad AS baslik, gise_baslangıc AS tarih FROM film ORDER BY gise_baslangıc DESC LIMIT 3)
            UNION
            (SELECT 'seans' AS tip, CONCAT('Salon ', salon_no) AS baslik, tarih FROM seans JOIN salon ON seans.salon_id = salon.salon_id ORDER BY tarih DESC LIMIT 3)
            UNION
            (SELECT 'bilet' AS tip, CONCAT(musteri.ad, ' ', musteri.soyad) AS baslik, satis_tarihi AS tarih FROM bilet JOIN musteri ON bilet.musteri_id = musteri.musteri_id ORDER BY satis_tarihi DESC LIMIT 3)
            ORDER BY tarih DESC LIMIT 5
        ");
        
        if($etkinlikler) {
            while($etkinlik = $etkinlikler->fetch_assoc()):
                $icon = '';
                $tip = '';
                if($etkinlik['tip'] == 'film') {
                    $icon = 'film';
                    $tip = 'Yeni Film';
                } elseif($etkinlik['tip'] == 'seans') {
                    $icon = 'calendar-alt';
                    $tip = 'Yeni Seans';
                } else {
                    $icon = 'ticket-alt';
                    $tip = 'Bilet Satışı';
                }
            ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-<?= $icon ?>"></i>
                </div>
                <div class="activity-content">
                    <strong><?= $tip ?></strong>: <?= htmlspecialchars($etkinlik['baslik']) ?>
                    <div class="activity-time"><?= date('d.m.Y H:i', strtotime($etkinlik['tarih'])) ?></div>
                </div>
            </div>
            <?php endwhile;
        } else {
            echo '<div class="alert error">Etkinlik bilgileri alınamadı: '.$conn->error.'</div>';
        }
        ?>
    </div>

    <div class="tickets-section">
        <h2>Satılan Biletler</h2>
        <?php
        $biletlerQuery = "SELECT b.*, f.ad as film_adi, m.ad as musteri_adi, m.soyad as musteri_soyad, 
                         s.tarih as seans_tarihi, sa.salon_no
                         FROM bilet b
                         JOIN seans s ON b.seans_id = s.seans_id
                         JOIN film f ON s.film_id = f.film_id
                         JOIN musteri m ON b.musteri_id = m.musteri_id
                         JOIN salon sa ON s.salon_id = sa.salon_id
                         ORDER BY b.satis_tarihi DESC";
        
        $biletler = $conn->query($biletlerQuery);
        
        if($biletler && $biletler->num_rows > 0): ?>
            <div class="tickets-table-container">
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Bilet ID</th>
                            <th>Film</th>
                            <th>Müşteri</th>
                            <th>Seans Tarihi</th>
                            <th>Salon</th>
                            <th>Koltuk</th>
                            <th>Fiyat</th>
                            <th>Satış Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($bilet = $biletler->fetch_assoc()): ?>
                            <tr>
                                <td><?= $bilet['bilet_id'] ?></td>
                                <td><?= htmlspecialchars($bilet['film_adi']) ?></td>
                                <td><?= htmlspecialchars($bilet['musteri_adi'] . ' ' . $bilet['musteri_soyad']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($bilet['seans_tarihi'])) ?></td>
                                <td>Salon <?= $bilet['salon_no'] ?></td>
                                <td><?= $bilet['koltuk_id'] ?></td>
                                <td><?= number_format($bilet['fiyat'], 2) ?> TL</td>
                                <td><?= date('d.m.Y H:i', strtotime($bilet['satis_tarihi'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-data">Henüz satılan bilet bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.tickets-section {
    margin-top: 2rem;
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tickets-table-container {
    overflow-x: auto;
    margin-top: 1rem;
}

.tickets-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

.tickets-table th,
.tickets-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.tickets-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.tickets-table tr:hover {
    background-color: #f8f9fa;
}

.no-data {
    text-align: center;
    padding: 2rem;
    color: #666;
}
</style>

<?php include(__DIR__.'/includes/footer.php'); ?>