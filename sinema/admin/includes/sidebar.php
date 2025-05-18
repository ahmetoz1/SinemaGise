<?php defined('IN_ADMIN') or exit('No direct script access allowed'); 
// Aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="sidebar-header">
        <h3>Marjinal Sinema</h3>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="<?= $current_page == 'filmler.php' ? 'active' : '' ?>">
            <a href="filmler.php"><i class="fas fa-film"></i> Filmler</a>
        </li>
        <li class="<?= $current_page == 'seanslar.php' ? 'active' : '' ?>">
            <a href="seanslar.php"><i class="fas fa-clock"></i> Seanslar</a>
        </li>
        <li class="<?= $current_page == 'biletler.php' ? 'active' : '' ?>">
            <a href="biletler.php"><i class="fas fa-ticket-alt"></i> Biletler</a>
        </li>
        <li class="<?= $current_page == 'musteriler.php' ? 'active' : '' ?>">
            <a href="musteriler.php"><i class="fas fa-users"></i> Müşteriler</a>
        </li>
        <li class="<?= in_array($current_page, ['personeller.php', 'personel_ekle.php', 'personel_duzenle.php']) ? 'active' : '' ?>">
            <a href="personeller.php"><i class="fas fa-user-tie"></i> Personeller</a>
        </li>
        <li>
            <a href="bayiler.php">
                <i class="fas fa-building"></i>
                <span>Bayiler</span>
            </a>
        </li>
        <li>
            <a href="salonlar.php">
                <i class="fas fa-film"></i>
                <span>Salonlar</span>
            </a>
        </li>
        <li class="logout">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </li>
    </ul>
</div>

<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    // Mobile Menu Toggle
    document.getElementById('mobileMenuToggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
    });
</script>