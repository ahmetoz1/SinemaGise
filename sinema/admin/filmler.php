<?php
define('IN_ADMIN', true);
require_once __DIR__.'/../config.php';
checkAdminSession();

if($_SESSION['personel_pozisyon'] != 'Yönetici') {
    $_SESSION['hata'] = "Bu işlem için yetkiniz yok!";
    header("Location: index.php");
    exit();
}

// Film silme işlemi
if(isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $film_id = (int)$_GET['sil'];

    // 1. Filme ait seansları bul
    $seanslar = $conn->query("SELECT seans_id FROM seans WHERE film_id = $film_id");
    if($seanslar && $seanslar->num_rows > 0) {
        while($seans = $seanslar->fetch_assoc()) {
            $seans_id = $seans['seans_id'];
            // 2. Seansa ait biletleri sil
            $stmt = $conn->prepare("DELETE FROM bilet WHERE seans_id = ?");
            $stmt->bind_param("i", $seans_id);
            $stmt->execute();
            // 3. Seansa ait koltukları sil
            $stmt = $conn->prepare("DELETE FROM koltuk WHERE seans_id = ?");
            $stmt->bind_param("i", $seans_id);
            $stmt->execute();
        }
        // 4. Seansları sil
        $conn->query("DELETE FROM seans WHERE film_id = $film_id");
    }
    // 5. Filmi sil
    $sil = $conn->query("DELETE FROM film WHERE film_id = $film_id");
    if($sil) {
        $_SESSION['basarili'] = "Film ve ilgili tüm seans, bilet ve koltuklar başarıyla silindi!";
    } else {
        $_SESSION['hata'] = "Film silinirken hata oluştu!";
    }
    header("Location: filmler.php");
    exit();
}

$query = "SELECT f.*, b.bayi_adi FROM film f LEFT JOIN bayi b ON f.bayi_id = b.bayi_id ORDER BY f.film_id DESC";
$result = $conn->query($query);

include(__DIR__.'/includes/header.php');
?>

<style>
.film-table-container {
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
    .film-table-container { padding: 10px 2px; }
    .admin-table th, .admin-table td { padding: 8px 4px; font-size: 0.95rem; }
}
</style>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: #2d3436; margin: 0;">Filmler</h1>
        <a href="film_duzenle.php?yeni=1" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Film Ekle
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

    <div class="film-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Film Adı</th>
                    <th>Bayi</th>
                    <th>Kategori</th>
                    <th>Yönetmen</th>
                    <th>Süre</th>
                    <th>Yaş Sınırı</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['film_id'] ?></td>
                            <td><?= htmlspecialchars($row['ad']) ?></td>
                            <td><?= htmlspecialchars($row['bayi_adi']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td><?= htmlspecialchars($row['yonetmen']) ?></td>
                            <td><?= htmlspecialchars($row['sure']) ?></td>
                            <td><?= htmlspecialchars($row['yas_siniri']) ?></td>
                            <td>
                                <a href="film_duzenle.php?id=<?= $row['film_id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Düzenle
                                </a>
                                <a href="filmler.php?sil=<?= $row['film_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu filmi silmek istediğinizden emin misiniz?')">
                                    <i class="fas fa-trash"></i> Sil
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color:#888;">Henüz film eklenmemiş.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(__DIR__.'/includes/footer.php'); ?>