<?php 
include('header.php');
include('config.php');



if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = $conn->real_escape_string($_POST['ad']);
    $soyad = $conn->real_escape_string($_POST['soyad']);
    $dogum_tarihi = $conn->real_escape_string($_POST['dogum_tarihi']);
    $tel_no = $conn->real_escape_string($_POST['tel_no']);
    $e_posta = $conn->real_escape_string($_POST['e_posta']);
    $sifre = $_POST['sifre'];
    
    // Hata ayıklama için şifre hash'ini logla
    error_log("Orijinal şifre: $sifre");
    $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
    error_log("Hashlenmiş şifre: $hashed_password");
    
    // E-posta kontrolü
    $checkQuery = "SELECT musteri_id FROM musteri WHERE e_posta = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $e_posta);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if($checkResult->num_rows > 0) {
        $error = "Bu e-posta adresi zaten kayıtlı.";
    } else {
        // Yeni kullanıcı ekle
        $insertQuery = "INSERT INTO musteri (ad, soyad, dogum_tarihi, tel_no, e_posta, sifre, puan) 
                       VALUES (?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $ad, $soyad, $dogum_tarihi, $tel_no, $e_posta, $hashed_password);
        
        if($stmt->execute()) {
            $_SESSION['success_message'] = "Kayıt başarılı! Giriş yapabilirsiniz.";
            header("Location: giris_yap.php");
            exit();
        } else {
            $error = "Kayıt sırasında bir hata oluştu: " . $conn->error;
            error_log("Kayıt hatası: " . $conn->error);
        }
    }
    $stmt->close();
}
?>

<main>
    <section class="register-section">
        <div class="register-container">
            <h2>Üye Ol</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            
            <form action="uye_ol.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="ad">Ad:</label>
                        <input type="text" id="ad" name="ad" required>
                    </div>
                    <div class="form-group">
                        <label for="soyad">Soyad:</label>
                        <input type="text" id="soyad" name="soyad" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dogum_tarihi">Doğum Tarihi:</label>
                    <input type="date" id="dogum_tarihi" name="dogum_tarihi" required>
                </div>
                
                <div class="form-group">
                    <label for="tel_no">Telefon:</label>
                    <input type="tel" id="tel_no" name="tel_no" required>
                </div>
                
                <div class="form-group">
                    <label for="e_posta">E-posta:</label>
                    <input type="email" id="e_posta" name="e_posta" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sifre">Şifre:</label>
                        <input type="password" id="sifre" name="sifre" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="sifre_tekrar">Şifre Tekrar:</label>
                        <input type="password" id="sifre_tekrar" name="sifre_tekrar" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn">Kayıt Ol</button>
            </form>
            
            <p>Zaten hesabınız var mı? <a href="giris_yap.php">Giriş Yapın</a></p>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const sifre = document.getElementById('sifre').value;
        const sifreTekrar = document.getElementById('sifre_tekrar').value;
        
        if(sifre !== sifreTekrar) {
            e.preventDefault();
            alert('Şifreler uyuşmuyor!');
        }
    });
});
</script>

<?php include('footer.php'); ?>