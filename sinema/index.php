<?php 
include('header.php');
include('config.php');

// Vizyondaki filmleri çek
$filmlerQuery = "SELECT f.*, b.bayi_adi FROM film f 
                JOIN bayi b ON f.bayi_id = b.bayi_id
                WHERE gise_bitis > NOW() 
                ORDER BY gise_baslangıc DESC";
$filmlerResult = $conn->query($filmlerQuery);
?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h2>Sinemanın Büyülü Dünyasına Hoş Geldiniz</h2>
            <p>En yeni filmleri keşfedin ve kolayca biletinizi alın</p>
            <a href="film_liste.php" class="btn">Filmleri Görüntüle</a>
        </div>
    </section>

    <section class="featured-movies">
        <h2>Vizyondaki Filmler</h2>
        <div class="movie-grid">
            <?php if($filmlerResult->num_rows > 0): ?>
                <?php while($film = $filmlerResult->fetch_assoc()): ?>
                    <div class="movie-card">
                        <img src="uploads/film_<?= $film['film_id'] ?>.jpg" alt="<?= htmlspecialchars($film['ad']) ?>">
                        <div class="movie-info">
                            <h3><?= htmlspecialchars($film['ad']) ?></h3>
                            <div class="movie-meta">
                                <span><i class="fas fa-clock"></i> <?= $film['sure'] ?> dk</span>
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
                <p>Şu anda vizyonda film bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php 
$filmlerResult->close();
include('footer.php'); 
?>