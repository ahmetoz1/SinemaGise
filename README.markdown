# Marjinal Sinema - Bilet Rezervasyon Sistemi

## Proje Özeti

- **Amaç**: Sinema bilet rezervasyonlarını kolaylaştıran ve yönetim süreçlerini destekleyen bir web uygulaması.
- **Kapsam**: Müşteri bilet rezervasyonu, admin paneli ile film, seans, salon, bilet, personel, bayi ve müşteri yönetimi.
- **Hedef Kullanıcılar**: Sinema müşterileri, personel, bayiler ve yöneticiler.
- **Özellikler**:
  - **Müşteriler**:
    - Vizyondaki filmleri listeleme ve detay görüntüleme
    - Seans seçimi ve koltuk rezervasyonu
    - Bilet satın alma ve hesap yönetimi (profil, bilet geçmişi)
  - **Personel**:
    - Film, seans, salon ekleme/düzenleme/silme
    - Personel, bayi ve müşteri yönetimi
    - Bilet işlemleri ve iptal yönetimi
    - Kayıt anahtarı oluşturma ve yetkilendirme
  - **Bayiler**:
    - Bayi hesabı yönetimi
    - Bilet satış ve takibi
  - **Güvenlik**:
    - Prepared statements ile SQL Injection koruması
    - `password_hash` ile şifreleme
    - Yönetici yetki kontrolü
    - .htaccess ile güvenlik yapılandırması
  - **Veritabanı**:
    - MySQL ile veri yönetimi
    - Trigger'lar ile otomatik işlemler
- **Teknolojiler**: PHP 8.2.12, MySQL 8.0, HTML5, CSS3, JavaScript, Font Awesome 6

## Geliştirme Ortamı

- **Programlama Dili**: PHP 8.2.12
- **Veritabanı**: MySQL 8.0
- **Web Sunucusu**: Apache (XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript, Font Awesome 6 (CDN)
- **Bağımlılıklar**:
  - PHP `mysqli` uzantısı
  - Veritabanı bağlantısı için `config.php`
- **Geliştirme Araçları**: VS Code, XAMPP, phpMyAdmin, MySQL Workbench, Chrome, Git

## Kurulum ve Çalıştırma

1. **Repoyu Klonlayın**:
   ```bash
   git clone https://github.com/kullanici_adi/marjinal-sinema.git
   ```

2. **Sunucu Ortamını Kurun**:
   - XAMPP kurun ve projeyi `htdocs` klasörüne taşıyın
   - Apache ve MySQL servislerini başlatın

3. **Veritabanını Ayarlayın**:
   - MySQL'de veritabanı oluşturun:
     ```sql
     CREATE DATABASE sinema CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
     ```
   - `triggers` klasöründeki SQL dosyalarını içe aktarın
   - Veritabanı şemasını oluşturun

4. **Yapılandırmayı Güncelleyin**:
   - `config.php` dosyasındaki veritabanı bağlantı ayarlarını güncelleyin:
     ```php
     <?php
         $servername = "localhost"; 
         $username = "root";
         $password = "";
         $database = "sinema"; 
     ?>
     ```

5. **Siteyi Çalıştırın**:
   - Tarayıcıda `http://localhost/sinema/index.php` adresine gidin

6. **Admin Giriş Bilgileri**:
   - **Kullanıcı ID**: `1`
   - **Şifre**: `admin123`
   - Admin paneline giriş yapmak için `http://localhost/sinema/admin/login.php` adresini kullanın

## Kullanım

- **Müşteri**: 
  - Kayıt olun (`uye_ol.php`) veya giriş yapın (`giris_yap.php`)
  - `index.php` veya `film_liste.php` üzerinden vizyondaki filmleri görüntüleyin
  - `film_detay.php` ile film detaylarını inceleyin
  - `bilet_al.php` ile seans ve koltuk seçerek bilet satın alın
  - `hesabim.php` ile bilet geçmişini ve profil bilgilerini yönetin

- **Personel**:
  - Admin paneline giriş yapın (`personel_giris.php`)
  - `admin/dashboard.php` üzerinden son etkinlikleri izleyin
  - `admin/filmler.php` ile film yönetimi
  - `admin/seanslar.php` ile seans yönetimi
  - `admin/salonlar.php` ile salon yönetimi
  - `admin/biletler.php` ile bilet yönetimi
  - `admin/personeller.php` ile personel yönetimi
  - `admin/bayiler.php` ile bayi yönetimi
  - `admin/musteriler.php` ile müşteri yönetimi
  - `admin/personel_kayit.php` ile yeni personel kaydı

## Proje Yapısı

```
sinema/
├── admin/                 # Admin panel dosyaları
│   ├── includes/         # Admin panel yardımcı dosyaları
│   ├── dashboard.php     # Admin kontrol paneli
│   ├── filmler.php       # Film yönetimi
│   ├── seanslar.php      # Seans yönetimi
│   └── ...
├── uploads/              # Yüklenen dosyalar (film afişleri vb.)
├── triggers/             # Veritabanı trigger'ları
├── index.php            # Ana sayfa
├── film_liste.php       # Film listesi
├── film_detay.php       # Film detay sayfası
├── bilet_al.php         # Bilet satın alma
├── hesabim.php          # Kullanıcı hesap yönetimi
├── config.php           # Veritabanı yapılandırması
└── style_1.css          # Ana stil dosyası
```

## Notlar

- Veritabanı trigger'ları `triggers` klasöründe bulunur
- Responsive tasarım, mobil ve masaüstü cihazlarda uyumludur
- Güvenlik için .htaccess dosyası yapılandırılmıştır

## Lisans

- MIT Lisansı
