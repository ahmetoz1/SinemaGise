<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

$personeller = fetchAll('personel', 'ad ASC');

include(__DIR__.'/includes/header.php');
?>

<div class="admin-content-container">
    <div class="admin-header">
        <h1><i class="fas fa-user-tie"></i> Personel Yönetimi</h1>
        <a href="personel_ekle.php" class="add-btn">
            <i class="fas fa-plus"></i> Yeni Personel Ekle
        </a>
    </div>

    <?php if(isset($_SESSION['basarili'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['basarili'] ?>
        </div>
        <?php unset($_SESSION['basarili']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['hata'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['hata'] ?>
        </div>
        <?php unset($_SESSION['hata']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Ad Soyad</th>
                        <th width="200">Pozisyon</th>
                        <th width="220">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personeller as $personel): ?>
                    <tr>
                        <td>#<?= $personel['personel_id'] ?></td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <strong><?= htmlspecialchars($personel['ad'].' '.$personel['soyad']) ?></strong>
                                    <small><?= htmlspecialchars($personel['e_posta']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $personel['pozisyon'] == 'Yönetici' ? 'badge-primary' : 'badge-secondary' ?>">
                                <?= htmlspecialchars($personel['pozisyon']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="personel_duzenle.php?id=<?= $personel['personel_id'] ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <a href="personel_sil.php?id=<?= $personel['personel_id'] ?>" class="btn btn-danger" 
                               onclick="return confirm('Bu personeli silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash-alt"></i> Sil
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>