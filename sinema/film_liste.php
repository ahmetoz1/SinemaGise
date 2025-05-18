<?php 
include('header.php');
include('config.php');

// Filtreleme parametreleri
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$tarih = isset($_GET['tarih']) ? $_GET['tarih'] : '';

// Filmleri çek
$filmlerQuery = "SELECT f.*, b.bayi_adi FROM film f 
                JOIN bayi b ON f.bayi_id = b.bayi_id
                WHERE gise_bitis > NOW()";

// Filtreleme ekle
if(!empty($kategori) && $kategori != 'all') {
    $filmlerQuery .= " AND kategori = '$kategori'";
}

if(!empty($tarih)) {
    $today = date('Y-m-d');
    if($tarih == 'today') {
        $filmlerQuery .= " AND DATE(gise_baslangıc) = '$today'";
    } elseif($tarih == 'week') {
        $filmlerQuery .= " AND gise_baslangıc BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 7 DAY)";
    } elseif($tarih == 'month') {
        $filmlerQuery .= " AND gise_baslangıc BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 30 DAY)";
    }
}

$filmlerQuery .= " ORDER BY gise_baslangıc ASC";
$filmlerResult = $conn->query($filmlerQuery);
?>

<main>
    <section class="movies-section">
        <div class="section-header">
            <h2>Vizyondaki Filmler</h2>
            <div class="filter-options">
                <form method="get" action="film_liste.php">
                    <select name="kategori" id="genre-filter" onchange="this.form.submit()">
                        <option value="all" <?= $kategori == 'all' ? 'selected' : '' ?>>Tüm Türler</option>
                        <option value="Aksiyon" <?= $kategori == 'Aksiyon' ? 'selected' : '' ?>>Aksiyon</option>
                        <option value="Komedi" <?= $kategori == 'Komedi' ? 'selected' : '' ?>>Komedi</option>
                        <option value="Drama" <?= $kategori == 'Drama' ? 'selected' : '' ?>>Drama</option>
                        <option value="Korku" <?= $kategori == 'Korku' ? 'selected' : '' ?>>Korku</option>
                        <option value="Bilim Kurgu" <?= $kategori == 'Bilim Kurgu' ? 'selected' : '' ?>>Bilim Kurgu</option>
                    </select>
                    <select name="tarih" id="date-filter" onchange="this.form.submit()">
                        <option value="all" <?= $tarih == 'all' ? 'selected' : '' ?>>Tüm Tarihler</option>
                        <option value="today" <?= $tarih == 'today' ? 'selected' : '' ?>>Bugün</option>
                        <option value="week" <?= $tarih == 'week' ? 'selected' : '' ?>>Bu Hafta</option>
                        <option value="month" <?= $tarih == 'month' ? 'selected' : '' ?>>Bu Ay</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="movie-grid">
            <?php if($filmlerResult->num_rows > 0): ?>
                <?php while($film = $filmlerResult->fetch_assoc()): ?>
                    <div class="movie-card">
                        <img src="uploads/film_<?= $film['film_id'] ?>.jpg" alt="<?= htmlspecialchars($film['ad']) ?>">
                        <div class="movie-info">
                            <h3><?= htmlspecialchars($film['ad']) ?></h3>
                            <div class="movie-meta">
                                <span><i class="fas fa-clock"></i> <?= substr($film['sure'], 0, 5) ?></span>
                                <span><i class="fas fa-building"></i> <?= htmlspecialchars($film['bayi_adi']) ?></span>
                            </div>
                            <div class="movie-actions">
                            <a href="film_detay.php?id=<?= $film['film_id'] ?>" class="btn-outline">Detaylar</a>
                            <?php
                            // Film için ilk uygun seansı kontrol et
                            $seansKontrol = "SELECT seans_id FROM seans WHERE film_id = ? AND tarih > NOW() LIMIT 1";
                            $stmt = $conn->prepare($seansKontrol);
                            $stmt->bind_param("i", $film['film_id']);
                            $stmt->execute();
                            $seansVarMi = $stmt->get_result()->num_rows > 0;
                            ?>
                            
                            <?php if($seansVarMi): ?>
                                <a href="bilet_al.php?film=<?= $film['film_id'] ?>" class="btn">Bilet Al</a>
                            <?php else: ?>
                                <button class="btn" disabled>Seans Yok</button>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-results">Seçtiğiniz kriterlere uygun film bulunamadı.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php 
$filmlerResult->close();
include('footer.php'); 
?>