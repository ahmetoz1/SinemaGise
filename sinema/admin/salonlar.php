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

// Salonları çek
$query = "SELECT s.*, b.bayi_adi 
          FROM salon s 
          LEFT JOIN bayi b ON s.bayi_id = b.bayi_id 
          ORDER BY s.salon_id DESC";
$result = $conn->query($query);

include(__DIR__.'/includes/header.php');
?>

<style>
.salon-table-container {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 16px 0 rgba(44,62,80,.07);
    padding: 32px 24px;
    margin-top: 24px;
    overflow-x: auto;
}
.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
}
.admin-table th, .admin-table td {
    padding: 16px 12px;
    text-align: left;
}
.admin-table th {
    background: #f5f7fa;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #ececec;
}
.admin-table tr {
    transition: background 0.2s;
}
.admin-table tbody tr:hover {
    background: #f0f4ff;
}
.admin-table td {
    border-bottom: 1px solid #f0f0f0;
    color: #444;
}
.btn {
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    font-size: 0.97rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    margin-right: 6px;
    margin-bottom: 2px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-primary {
    background: #6c5ce7;
    color: #fff;
}
.btn-primary:hover {
    background: #4834d4;
}
.btn-danger {
    background: #d63031;
    color: #fff;
}
.btn-danger:hover {
    background: #b71c1c;
}
@media (max-width: 700px) {
    .salon-table-container { padding: 10px 2px; }
    .admin-table th, .admin-table td { padding: 8px 4px; font-size: 0.95rem; }
}
</style>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: #2d3436; margin: 0;">Salonlar</h1>
        <a href="salon_ekle.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Salon Ekle
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

    <div class="salon-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Salon No</th>
                    <th>Bayi</th>
                    <th>Kapasite</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['salon_id'] ?></td>
                            <td><?= htmlspecialchars($row['salon_no']) ?></td>
                            <td><?= htmlspecialchars($row['bayi_adi']) ?></td>
                            <td><?= $row['kapasite'] ?></td>
                            <td>
                                <a href="salon_duzenle.php?id=<?= $row['salon_id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Düzenle
                                </a>
                                <a href="salon_sil.php?id=<?= $row['salon_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu salonu silmek istediğinizden emin misiniz?')">
                                    <i class="fas fa-trash"></i> Sil
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#888;">Henüz salon eklenmemiş.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?> 