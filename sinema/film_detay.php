<?php 
include('header.php');
include('config.php');

if(!isset($_GET['id'])) {
    header("Location: film_liste.php");
    exit();
}

$film_id = intval($_GET['id']);

// Film bilgilerini çek
$filmQuery = "SELECT f.*, b.bayi_adi FROM film f 
             JOIN bayi b ON f.bayi_id = b.bayi_id
             WHERE f.film_id = ?";
$stmt = $conn->prepare($filmQuery);
$stmt->bind_param("i", $film_id);
$stmt->execute();
$filmResult = $stmt->get_result();

if($filmResult->num_rows == 0) {
    header("Location: film_liste.php");
    exit();
}

$film = $filmResult->fetch_assoc();
$stmt->close();

// Debug için film verilerini yazdır
echo "<!-- Debug Bilgileri:\n";
print_r($film);
echo "\n-->";

// Film seanslarını çek
$seanslarQuery = "SELECT s.*, sa.salon_no FROM seans s
                 JOIN salon sa ON s.salon_id = sa.salon_id
                 WHERE s.film_id = ? AND s.tarih > NOW()
                 ORDER BY s.tarih ASC";
$stmt = $conn->prepare($seanslarQuery);
$stmt->bind_param("i", $film_id);
$stmt->execute();
$seanslarResult = $stmt->get_result();
?>

<main>
    <section class="movie-detail-section">
        <div class="movie-detail-container">
            <div class="movie-poster">
                <img src="uploads/film_<?= $film['film_id'] ?>.jpg" alt="<?= htmlspecialchars($film['ad']) ?>">
                <a href="bilet_al.php?film=<?= $film['film_id'] ?>" class="btn">Bilet Al</a>
            </div>
            
            <div class="movie-content">
                <h1><?= htmlspecialchars($film['ad']) ?></h1>
                
                <div class="movie-meta">
                    <span><i class="fas fa-user-lock"></i> <?= $film['yas_siniri'] == 0 ? 'Genel İzleyici' : $film['yas_siniri'] . '+' ?></span>
                    <span><i class="fas fa-clock"></i> <?= substr($film['sure'], 0, 5) ?></span>
                    <span><i class="fas fa-calendar-alt"></i> <?= date('Y', strtotime($film['gise_baslangıc'])) ?></span>
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($film['kategori']) ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($film['yonetmen']) ?></span>
                </div>
                
                <h3>Özet</h3>
                <p><?= nl2br(htmlspecialchars($film['ozet'] ?? 'Film açıklaması bulunmamaktadır.')) ?></p>
                
                <h3>Seanslar</h3>
                <?php if($seanslarResult->num_rows > 0): ?>
                    <div class="sessions-grid">
                        <?php 
                        $current_date = '';
                        while($seans = $seanslarResult->fetch_assoc()): 
                            $seans_date = date('Y-m-d', strtotime($seans['tarih']));
                            if($seans_date != $current_date):
                                if($current_date != '') echo '</div></div>';
                                $current_date = $seans_date;
                        ?>
                        <div class="session-card">
                            <h4><?= date('d F Y', strtotime($seans_date)) ?></h4>
                            <div class="session-times">
                        <?php endif; ?>
                                <a href="bilet_al.php?seans=<?= $seans['seans_id'] ?>" class="session-time">
                                    <?= date('H:i', strtotime($seans['tarih'])) ?> - Salon <?= $seans['salon_no'] ?> (<?= $seans['goruntu'] ?>)
                                </a>
                        <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Bu film için henüz seans planlanmamıştır.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php 
$seanslarResult->close();
include('footer.php'); 
?>