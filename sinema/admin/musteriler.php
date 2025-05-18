<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

$musteriler = $conn->query("SELECT * FROM musteri ORDER BY ad ASC");

include(__DIR__.'/includes/header.php');
?>

<div class="admin-header">
    <h1>Müşteri Yönetimi</h1>
    <div class="admin-user">
        <i class="fas fa-user-circle"></i>
        <span><?= $_SESSION['personel_pozisyon'] ?></span>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>Telefon</th>
            <th>E-posta</th>
            <th>Puan</th>
            <th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php while($musteri = $musteriler->fetch_assoc()): ?>
        <tr>
            <td><?= $musteri['musteri_id'] ?></td>
            <td><?= htmlspecialchars($musteri['ad'].' '.$musteri['soyad']) ?></td>
            <td><?= htmlspecialchars($musteri['tel_no']) ?></td>
            <td><?= htmlspecialchars($musteri['e_posta']) ?></td>
            <td><?= $musteri['puan'] ?></td>
            <td class="action-buttons">
                <a href="musteri_goruntule.php?id=<?= $musteri['musteri_id'] ?>" class="btn"><i class="fas fa-eye"></i> Görüntüle</a>
                <a href="musteri_sil.php?id=<?= $musteri['musteri_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include(__DIR__.'/includes/footer.php'); ?>