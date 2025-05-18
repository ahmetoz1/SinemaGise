-- BAYİ TABLOSU
CREATE TABLE `bayi` (
  `bayi_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `bayi_adi` varchar(50) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `adres` varchar(50) DEFAULT NULL,
  `calisan_sayisi` tinyint(4) DEFAULT NULL,
  `musteri_kapasitesi` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`bayi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- SALON TABLOSU
CREATE TABLE `salon` (
  `salon_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `bayi_id` smallint(6) DEFAULT NULL,
  `salon_no` varchar(30) DEFAULT NULL,
  `kapasite` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`salon_id`),
  KEY `bayi_id` (`bayi_id`),
  CONSTRAINT `salon_ibfk_1` FOREIGN KEY (`bayi_id`) REFERENCES `bayi` (`bayi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FİLM TABLOSU
CREATE TABLE `film` (
  `film_id` int(11) NOT NULL AUTO_INCREMENT,
  `bayi_id` smallint(6) DEFAULT NULL,
  `ad` varchar(30) DEFAULT NULL,
  `gise_baslangıc` datetime DEFAULT NULL,
  `gise_bitis` datetime DEFAULT NULL,
  `sure` time DEFAULT NULL,
  `yas_siniri` tinyint(4) DEFAULT NULL,
  `kategori` varchar(10) DEFAULT NULL,
  `yonetmen` varchar(30) DEFAULT NULL,
  `ozet` text DEFAULT NULL,
  `oyuncular` text NOT NULL,
  `dil` varchar(80) NOT NULL,
  `format` enum('2D','3D','','') NOT NULL,
  PRIMARY KEY (`film_id`),
  KEY `bayi_id` (`bayi_id`),
  KEY `idx_film_ad` (`ad`),
  KEY `idx_film_kategori` (`kategori`),
  KEY `idx_film_yas_siniri` (`yas_siniri`),
  KEY `idx_film_bayi` (`bayi_id`),
  CONSTRAINT `film_ibfk_1` FOREIGN KEY (`bayi_id`) REFERENCES `bayi` (`bayi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- PERSONEL TABLOSU
CREATE TABLE `personel` (
  `personel_id` int(11) NOT NULL AUTO_INCREMENT,
  `bayi_id` smallint(6) DEFAULT NULL,
  `adres` varchar(50) DEFAULT NULL,
  `ad` varchar(20) DEFAULT NULL,
  `soyad` varchar(20) DEFAULT NULL,
  `pozisyon` varchar(20) DEFAULT NULL,
  `dogum_tarihi` date DEFAULT NULL,
  `tel_no` varchar(20) DEFAULT NULL,
  `sifre` varchar(100) DEFAULT NULL,
  `e_posta` varchar(20) NOT NULL,
  PRIMARY KEY (`personel_id`),
  KEY `bayi_id` (`bayi_id`),
  CONSTRAINT `personel_ibfk_1` FOREIGN KEY (`bayi_id`) REFERENCES `bayi` (`bayi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- MÜŞTERİ TABLOSU
CREATE TABLE `musteri` (
  `musteri_id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(20) DEFAULT NULL,
  `soyad` varchar(20) DEFAULT NULL,
  `dogum_tarihi` date DEFAULT NULL,
  `tel_no` varchar(20) DEFAULT NULL,
  `e_posta` varchar(30) DEFAULT NULL,
  `sifre` varchar(100) DEFAULT NULL,
  `puan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`musteri_id`),
  UNIQUE KEY `e_posta` (`e_posta`),
  KEY `idx_musteri_eposta` (`e_posta`),
  KEY `idx_musteri_soyad` (`soyad`),
  KEY `idx_musteri_dogum_tarihi` (`dogum_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- SEANS TABLOSU
CREATE TABLE `seans` (
  `seans_id` int(11) NOT NULL AUTO_INCREMENT,
  `salon_id` smallint(6) DEFAULT NULL,
  `film_id` int(11) DEFAULT NULL,
  `tarih` datetime DEFAULT NULL,
  `bilet_fiyati` int(11) DEFAULT NULL,
  `goruntu` enum('2D','3D') DEFAULT NULL,
  `dil` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`seans_id`),
  KEY `salon_id` (`salon_id`),
  KEY `film_id` (`film_id`),
  CONSTRAINT `seans_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salon` (`salon_id`),
  CONSTRAINT `seans_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- KOLTUK TABLOSU
CREATE TABLE `koltuk` (
  `koltuk_id` int(11) NOT NULL AUTO_INCREMENT,
  `salon_id` smallint(6) DEFAULT NULL,
  `seans_id` int(11) DEFAULT NULL,
  `koltuk_no` smallint(6) DEFAULT NULL,
  `durum` enum('Bos','Dolu') NOT NULL DEFAULT 'Bos',
  PRIMARY KEY (`koltuk_id`),
  KEY `salon_id` (`salon_id`),
  KEY `seans_id` (`seans_id`),
  CONSTRAINT `koltuk_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salon` (`salon_id`),
  CONSTRAINT `koltuk_ibfk_2` FOREIGN KEY (`seans_id`) REFERENCES `seans` (`seans_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- BİLET TABLOSU
CREATE TABLE `bilet` (
  `bilet_id` int(11) NOT NULL AUTO_INCREMENT,
  `seans_id` int(11) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `ad` varchar(20) DEFAULT NULL,
  `soyad` varchar(20) DEFAULT NULL,
  `koltuk_id` int(11) DEFAULT NULL,
  `satis_tarihi` datetime DEFAULT NULL,
  `fiyat` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`bilet_id`),
  KEY `seans_id` (`seans_id`),
  KEY `musteri_id` (`musteri_id`),
  KEY `koltuk_id` (`koltuk_id`),
  CONSTRAINT `bilet_ibfk_1` FOREIGN KEY (`seans_id`) REFERENCES `seans` (`seans_id`),
  CONSTRAINT `bilet_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `musteri` (`musteri_id`),
  CONSTRAINT `bilet_ibfk_3` FOREIGN KEY (`koltuk_id`) REFERENCES `koltuk` (`koltuk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- VIEW: salon_doluluk_orani
CREATE VIEW `salon_doluluk_orani` AS
SELECT
  `se`.`seans_id` AS `seans_id`,
  `s`.`salon_id` AS `salon_id`,
  `s`.`salon_no` AS `salon_no`,
  `s`.`kapasite` AS `kapasite`,
  count(`k`.`koltuk_id`) AS `dolu_koltuk_sayisi`,
  count(`k`.`koltuk_id`) / `s`.`kapasite` * 100 AS `doluluk_orani`,
  `se`.`tarih` AS `tarih`,
  `f`.`ad` AS `film_adi`
FROM (((`salon` `s`
  JOIN `seans` `se` ON(`s`.`salon_id` = `se`.`salon_id`))
  JOIN `film` `f` ON(`se`.`film_id` = `f`.`film_id`))
  LEFT JOIN `koltuk` `k` ON(`se`.`seans_id` = `k`.`seans_id` AND `k`.`durum` = 'Dolu'))
GROUP BY `se`.`seans_id`;

-- TRIGGERLAR
DELIMITER $$
CREATE TRIGGER `bilet_satis_sonrasi_koltuk_guncelle` AFTER INSERT ON `bilet` FOR EACH ROW BEGIN
    UPDATE koltuk SET durum = 'Dolu' WHERE koltuk_id = NEW.koltuk_id;
END $$
CREATE TRIGGER `bilet_silme_sonrasi_koltuk_guncelle` AFTER DELETE ON `bilet` FOR EACH ROW BEGIN
    UPDATE koltuk SET durum = 'Bos' WHERE koltuk_id = OLD.koltuk_id;
END $$
DELIMITER ;

-- VERİ EKLEME
INSERT INTO `bayi` (`bayi_id`, `bayi_adi`, `telefon`, `adres`, `calisan_sayisi`, `musteri_kapasitesi`) VALUES
(1, 'Marjinal Sinema Merkez', '02121234561', 'İstanbul, Merkez', 15, 127),
(2, 'Marjinal Sinema Şube', '02121234568', 'İstanbul, Şube', 10, 127),
(4, 'Marjinal Sinema Başiskele', '2123123121', 'izmit', 4, 127);

INSERT INTO `salon` (`salon_id`, `bayi_id`, `salon_no`, `kapasite`) VALUES
(1, 1, 'merkez 1', 100),
(2, 1, '12', 80),
(3, 2, 'şube 2', 120),
(4, 4, '321', 15),
(5, 4, '322', 45),
(6, 4, 'başiskele 3', 50);

INSERT INTO `film` (`film_id`, `bayi_id`, `ad`, `gise_baslangıc`, `gise_bitis`, `sure`, `yas_siniri`, `kategori`, `yonetmen`, `ozet`, `oyuncular`, `dil`, `format`) VALUES
(3, 2, 'Yeşil Yol', '2023-01-01 00:00:00', '2023-12-31 23:59:59', '03:09:00', 16, 'Drama', 'Frank Darabont', '1935 yılında, iki adamın hikayesi anlatılır. Biri, ölüm cezasına çarptırılmış bir mahkum, diğeri ise bir hapishane gardiyanıdır. Bu iki adam arasında gelişen dostluk, her ikisinin de hayatını değiştirecektir.', 'Tom Hanks, Michael Clarke Duncan, David Morse', 'Türkçe Dublaj', '2D'),
(4, 1, 'Yıldızlararası', '2025-05-15 23:56:00', '2025-05-29 23:56:00', '02:49:00', 0, 'Aksiyon', 'Christopher Nolan', 'Yıldızlararası, insanlığın geleceğini kurtarmak için uzayda yeni yaşanabilir bir gezegen arayan bir grup astronotun hikayesini anlatıyor. Dünya artık yaşanmaz hale gelmiştir ve insanlık yok olma tehlikesiyle karşı karşıyadır. Cooper ve ekibi, solucan deliğinden geçerek yeni bir yaşanabilir gezegen bulmak için tehlikeli bir yolculuğa çıkar.', '', '', '2D'),
(9, 1, 'Babacan', '2025-05-15 22:30:00', '2025-05-29 22:30:00', '02:19:00', 13, 'Komedi', 'AHMET KÖZ', 'Özür dilerim...', '', '', '2D'),
(10, 2, 'Yol', '2025-05-16 00:01:00', '2025-05-30 00:01:00', '10:19:00', 18, 'Korku', 'Ahmet KÖZ', 'Bir genç girmemesi gereken O eve girer...', '', '', '2D'),
(11, 4, 'Keleoğlanın Maceraları', '2025-05-18 02:43:00', '2025-06-01 02:43:00', '02:00:00', 0, 'Aksiyon', 'Ahmet Köz', 'adından belli', '', '', '2D');

INSERT INTO `personel` (`personel_id`, `bayi_id`, `adres`, `ad`, `soyad`, `pozisyon`, `dogum_tarihi`, `tel_no`, `sifre`, `e_posta`) VALUES
(1, 1, 'İstanbul, Merkez', 'Admin', 'Yönetici', 'Yönetici', '1990-05-15', '05551112233', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', ''),
(2, 1, 'İstanbul, Merkez', 'Ayşecik', 'Kaya', 'Temizlik Görevlisi', '1985-08-20', '05551112234', NULL, 'ayse@gmail.com'),
(4, 1, 'İstanbul, Merkez', 'Ahmet', 'Yılmaz', 'Gişe Görevlisi', '1990-05-15', '05551112233', '123456', ''),
(6, 2, 'İstanbul, Şube', 'Mehmet', 'Demir', 'Gişe Görevlisi', '1980-03-10', '05551112235', '123456', 'ahjgajshfg@gmail.com'),
(7, 1, 'başiskele/kocaeli', 'Banu', 'Özsoy', 'Gişe Görevlisi', '1986-05-15', '05112365985', '$2y$10$6dCj3HqFNsq7QGc58NY1mepfjyGVzReA3afmz4evEUQv/YtWhIpFq', 'banus@gmail.com');

INSERT INTO `musteri` (`musteri_id`, `ad`, `soyad`, `dogum_tarihi`, `tel_no`, `e_posta`, `sifre`, `puan`) VALUES
(3, 'Duha Yusuf', 'Bindere', '2005-11-07', '05550291141', 'duhayusuf2005@gmail.com', '$2y$10$zd1/Va3C1YG2T', 0),
(5, 'Duha Yusuf', 'Bindere', '2025-05-15', '05550291141', 'B@gmail.com', '$2y$10$/LI/N7nBLqJYj86ntpvI0uZounVJtT2RF7oPRJdr5huJyHni7P7k6', 100),
(11, 'ahmet', 'öz', '2003-11-16', '5331361808', 'ahmetoz@gmail.com', '$2y$10$17Pi8j5d1q.DSW8odHcmw.K5YHQqQU9MPZoG85TG/iWVyakFoJDSK', 127);

INSERT INTO `seans` (`seans_id`, `salon_id`, `film_id`, `tarih`, `bilet_fiyati`, `goruntu`, `dil`) VALUES
(8, 3, 3, '2028-06-15 20:00:00', 40, '2D', 'Türkçe Altyazı'),
(12, 3, 3, '2025-06-15 20:00:00', 40, '2D', 'Türkçe Altyazı'),
(14, 1, 4, '2025-06-15 18:00:00', 60, '3D', 'Türkçe Dublaj'),
(20, 4, 11, '2025-05-18 07:41:00', 852, '2D', 'Türkçe Dublaj'),
(23, 1, 4, '2025-05-19 10:00:00', 50, '3D', 'Türkçe Dublaj');

INSERT INTO `koltuk` (`koltuk_id`, `salon_id`, `seans_id`, `koltuk_no`, `durum`) VALUES
(133, NULL, 14, 4, 'Dolu'),
(134, NULL, 14, 13, 'Dolu'),
(135, NULL, 14, 15, 'Dolu'),
(136, NULL, 14, 24, 'Dolu'),
(137, NULL, 14, 33, 'Dolu'),
(138, NULL, 14, 35, 'Dolu'),
(139, NULL, 14, 16, 'Dolu'),
(140, NULL, 14, 17, 'Dolu'),
(141, NULL, 14, 28, 'Dolu'),
(142, NULL, 14, 29, 'Dolu'),
(143, NULL, 14, 36, 'Dolu'),
(144, NULL, 14, 37, 'Bos'),
(145, NULL, 14, 12, 'Dolu'),
(146, NULL, 14, 21, 'Dolu'),
(147, NULL, 14, 22, 'Dolu'),
(148, NULL, 14, 23, 'Bos'),
(149, NULL, 14, 32, 'Bos'),
(150, NULL, 14, 14, 'Dolu'),
(151, NULL, 14, 25, 'Dolu'),
(152, NULL, 14, 26, 'Dolu'),
(153, NULL, 14, 34, 'Dolu'),
(164, NULL, 14, 5, 'Dolu'),
(165, NULL, 14, 6, 'Dolu'),
(166, NULL, 14, 7, 'Dolu'),
(167, NULL, 14, 8, 'Dolu'),
(168, NULL, 14, 48, 'Dolu'),
(169, NULL, 14, 49, 'Dolu'),
(170, NULL, 14, 1, 'Dolu'),
(171, NULL, 14, 2, 'Dolu'),
(172, NULL, 14, 11, 'Dolu'),
(173, NULL, 14, 3, 'Bos'),
(174, NULL, 14, 27, 'Bos'),
(175, NULL, 14, 31, 'Bos'),
(176, NULL, 14, 9, 'Bos'),
(177, NULL, 14, 10, 'Bos'),
(178, NULL, 14, 18, 'Bos'),
(179, NULL, 14, 19, 'Bos'),
(180, NULL, 14, 20, 'Bos'),
(181, NULL, 14, 30, 'Bos'),
(182, NULL, 14, 38, 'Bos'),
(183, NULL, 14, 39, 'Bos'),
(184, NULL, 14, 40, 'Bos'),
(185, NULL, 14, 50, 'Bos'),
(186, NULL, 14, 59, 'Bos'),
(187, NULL, 14, 60, 'Bos'),
(188, NULL, 14, 42, 'Bos'),
(189, NULL, 14, 43, 'Bos'),
(190, NULL, 14, 44, 'Bos'),
(191, NULL, 14, 45, 'Bos'),
(192, NULL, 14, 46, 'Bos'),
(193, NULL, 14, 47, 'Bos'),
(206, NULL, 23, 1, 'Bos'),
(207, NULL, 23, 2, 'Dolu'),
(208, NULL, 23, 3, 'Dolu'),
(209, NULL, 23, 6, 'Dolu'),
(216, NULL, 23, 9, 'Dolu'),
(217, NULL, 23, 10, 'Dolu'),
(218, NULL, 23, 19, 'Dolu'),
(219, NULL, 23, 20, 'Dolu'),
(220, NULL, 23, 29, 'Dolu'),
(221, NULL, 23, 30, 'Dolu'),
(222, NULL, 14, 83, 'Dolu'),
(223, NULL, 14, 84, 'Dolu'),
(224, NULL, 14, 85, 'Dolu'),
(225, NULL, 14, 93, 'Dolu'),
(226, NULL, 14, 94, 'Dolu'),
(227, NULL, 14, 95, 'Dolu');

INSERT INTO `bilet` (`bilet_id`, `seans_id`, `musteri_id`, `ad`, `soyad`, `koltuk_id`, `satis_tarihi`, `fiyat`) VALUES
(18, 14, 5, NULL, NULL, 133, '2025-05-15 22:33:52', 60),
(19, 14, 5, NULL, NULL, 134, '2025-05-15 22:33:52', 60),
(20, 14, 5, NULL, NULL, 135, '2025-05-15 22:33:52', 60),
(21, 14, 5, NULL, NULL, 136, '2025-05-15 22:33:52', 60),
(22, 14, 5, NULL, NULL, 137, '2025-05-15 22:33:52', 60),
(23, 14, 5, NULL, NULL, 138, '2025-05-15 22:33:52', 60),
(24, 14, 5, NULL, NULL, 139, '2025-05-15 22:35:13', 60),
(25, 14, 5, NULL, NULL, 140, '2025-05-15 22:35:13', 60),
(26, 14, 5, NULL, NULL, 141, '2025-05-15 22:35:13', 60),
(27, 14, 5, NULL, NULL, 142, '2025-05-15 22:35:13', 60),
(28, 14, 5, NULL, NULL, 143, '2025-05-15 22:35:13', 60),
(51, 14, 5, NULL, NULL, 164, '2025-05-15 23:40:22', 60),
(52, 14, 5, NULL, NULL, 165, '2025-05-15 23:40:22', 60),
(53, 14, 5, NULL, NULL, 166, '2025-05-15 23:40:22', 60),
(54, 14, 5, NULL, NULL, 167, '2025-05-15 23:40:22', 60),
(55, 14, 5, NULL, NULL, 168, '2025-05-15 23:40:22', 60),
(56, 14, 5, NULL, NULL, 169, '2025-05-15 23:40:22', 60),
(57, 14, 5, NULL, NULL, 170, '2025-05-15 23:40:32', 60),
(58, 14, 5, NULL, NULL, 171, '2025-05-15 23:40:32', 60),
(59, 14, 5, NULL, NULL, 172, '2025-05-15 23:40:32', 60),
(60, 14, 5, NULL, NULL, 145, '2025-05-15 23:40:32', 60),
(61, 14, 5, NULL, NULL, 146, '2025-05-15 23:40:32', 60),
(62, 14, 5, NULL, NULL, 147, '2025-05-15 23:40:32', 60),
(101, 23, 11, NULL, NULL, 207, '2025-05-18 13:25:32', 50),
(102, 23, 11, NULL, NULL, 208, '2025-05-18 13:25:32', 50),
(103, 23, 11, NULL, NULL, 209, '2025-05-18 14:57:33', 50),
(110, 23, 11, NULL, NULL, 216, '2025-05-18 15:02:32', 50),
(111, 23, 11, NULL, NULL, 217, '2025-05-18 15:02:32', 50),
(112, 23, 11, NULL, NULL, 218, '2025-05-18 15:02:32', 50),
(113, 23, 11, NULL, NULL, 219, '2025-05-18 15:02:32', 50),
(114, 23, 11, NULL, NULL, 220, '2025-05-18 15:02:32', 50),
(115, 23, 11, NULL, NULL, 221, '2025-05-18 15:02:32', 50),
(116, 14, 11, NULL, NULL, 222, '2025-05-18 15:09:42', 60),
(117, 14, 11, NULL, NULL, 223, '2025-05-18 15:09:42', 60),
(118, 14, 11, NULL, NULL, 224, '2025-05-18 15:09:42', 60),
(119, 14, 11, NULL, NULL, 225, '2025-05-18 15:09:42', 60),
(120, 14, 11, NULL, NULL, 226, '2025-05-18 15:09:42', 60),
(121, 14, 11, NULL, NULL, 227, '2025-05-18 15:09:42', 60);

COMMIT;