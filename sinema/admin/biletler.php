<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

$biletler = $conn->query("
    SELECT b.*, m.ad AS musteri_ad, m.soyad AS musteri_soyad, 
           f.ad AS film_ad, s.tarih, sa.salon_no, k.koltuk_no
    FROM bilet b
    LEFT JOIN musteri m ON b.musteri_id = m.musteri_id
    JOIN seans s ON b.seans_id = s.seans_id
    JOIN film f ON s.film_id = f.film_id
    JOIN salon sa ON s.salon_id = sa.salon_id
    JOIN koltuk k ON b.koltuk_id = k.koltuk_id
    ORDER BY b.satis_tarihi DESC
");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Bilet Yönetimi</h1>
    <div class="admin-user">
        <i class="fas fa-user-circle"></i>
        <span><?= $_SESSION['personel_pozisyon'] ?></span>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Film</th>
            <th>Müşteri</th>
            <th>Tarih/Salon</th>
            <th>Koltuk</th>
            <th>Fiyat</th>
            <th>Satış Tarihi</th>
            <th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php while($bilet = $biletler->fetch_assoc()): ?>
        <tr>
            <td><?= $bilet['bilet_id'] ?></td>
            <td><?= htmlspecialchars($bilet['film_ad']) ?></td>
            <td>
                <?= $bilet['musteri_id'] ? 
                    htmlspecialchars($bilet['musteri_ad'].' '.$bilet['musteri_soyad']) : 
                    htmlspecialchars($bilet['ad'].' '.$bilet['soyad']) ?>
            </td>
            <td>
                <?= date('d.m.Y H:i', strtotime($bilet['tarih'])) ?><br>
                Salon: <?= $bilet['salon_no'] ?>
            </td>
            <td><?= $bilet['koltuk_no'] ?></td>
            <td><?= $bilet['fiyat'] ?> TL</td>
            <td><?= date('d.m.Y H:i', strtotime($bilet['satis_tarihi'])) ?></td>
            <td class="action-buttons">
                <a href="bilet_iptal.php?id=<?= $bilet['bilet_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?')">
                    <i class="fas fa-times"></i> İptal Et
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include(__DIR__.'/includes/footer.php'); ?>