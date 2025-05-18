<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();
include('functions.php');

// Seans listesini çek
$seanslar = $conn->query("
    SELECT s.*, f.ad AS film_adi, sa.salon_no, f.sure
    FROM seans s
    JOIN film f ON s.film_id = f.film_id
    JOIN salon sa ON s.salon_id = sa.salon_id
    ORDER BY s.tarih ASC
");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Seans Yönetimi</h1>
    <div class="admin-actions">
        <a href="seans_ekle.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Seans Ekle
        </a>
    </div>
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
            <th>Süre</th>
            <th>Tarih/Saat</th>
            <th>Salon</th>
            <th>Fiyat</th>
            <th>Format</th>
            <th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php while($seans = $seanslar->fetch_assoc()): ?>
        <tr>
            <td><?= $seans['seans_id'] ?></td>
            <td><?= htmlspecialchars($seans['film_adi']) ?></td>
            <td><?= $seans['sure'] ?> dk</td>
            <td><?= date('d.m.Y H:i', strtotime($seans['tarih'])) ?></td>
            <td>Salon <?= $seans['salon_no'] ?></td>
            <td><?= number_format($seans['bilet_fiyati'], 2) ?> ₺</td>
            <td><?= $seans['goruntu'] ?> - <?= $seans['dil'] ?></td>
            <td class="action-buttons">
                <a href="seans_duzenle.php?id=<?= $seans['seans_id'] ?>" class="edit-btn"><i class="fas fa-edit"></i> Düzenle</a>
                <a href="seans_sil.php?id=<?= $seans['seans_id'] ?>" class="delete-btn" 
                   onclick="return confirm('Bu seansı silmek istediğinize emin misiniz?')">
                   <i class="fas fa-trash"></i> Sil
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include(__DIR__.'/includes/footer.php'); ?>