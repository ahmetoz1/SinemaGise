<?php 
include('header.php');
include('config.php');

// Başarı mesajı kontrolü
if(isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Form gönderildiyse işle
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $e_posta = $conn->real_escape_string($_POST['e_posta']);
    $sifre = $_POST['sifre'];
    
    // Hata ayıklama için giriş verilerini loglama (üretimde kaldırılmalı)
    error_log("Giriş denemesi - E-posta: $e_posta");
    
    $query = "SELECT musteri_id, ad, soyad, sifre FROM musteri WHERE e_posta = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if(!$stmt) {
        $error = "Veritabanı hatası: " . $conn->error;
    } else {
        $stmt->bind_param("s", $e_posta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Bu satırları kaldırın veya yorum satırına alın
            // error_log("Veritabanı hash: " . $user['sifre']);
            // error_log("Girilen şifre hash: " . password_hash($sifre, PASSWORD_DEFAULT));
            
            // Doğrulama yapalım
            if(password_verify($sifre, $user['sifre'])) {
                // Şifre doğru
                $_SESSION['musteri_id'] = $user['musteri_id'];
                $_SESSION['ad'] = $user['ad'];
                $_SESSION['soyad'] = $user['soyad'];
                
                // Hata ayıklama için oturum bilgisi
                error_log("Oturum açıldı - Kullanıcı ID: " . $_SESSION['musteri_id']);
                
                // Yönlendirme URL'si varsa oraya git
                if(isset($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: $redirect");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Hatalı şifre!";
                error_log("Hatalı şifre - E-posta: $e_posta");
            }
        } else {
            $error = "Bu e-posta adresiyle kayıtlı kullanıcı bulunamadı.";
            error_log("Kullanıcı bulunamadı - E-posta: $e_posta");
        }
        $stmt->close();
    }
}
?>

<main>
    <section class="login-section">
        <div class="login-container">
            <h2>Giriş Yap</h2>
            
            <?php if(isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            
            <form action="giris_yap.php" method="POST">
                <div class="form-group">
                    <label for="e_posta">E-posta:</label>
                    <input type="email" id="e_posta" name="e_posta" required value="<?= isset($_POST['e_posta']) ? htmlspecialchars($_POST['e_posta']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre:</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <button type="submit" class="btn">Giriş Yap</button>
            </form>
            <p>Hesabınız yok mu? <a href="uye_ol.php">Üye Olun</a></p>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>