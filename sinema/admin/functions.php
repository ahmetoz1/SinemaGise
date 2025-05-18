<?php
require_once __DIR__.'/../config.php';
checkAdminSession(); // Bu satırı her admin sayfasının EN BAŞINA ekleyin


function getBayiler() {
    global $conn;
    $result = $conn->query("SELECT bayi_id, bayi_adi FROM bayi");
    $bayiler = [];
    while($row = $result->fetch_assoc()) {
        $bayiler[$row['bayi_id']] = $row['bayi_adi'];
    }
    return $bayiler;
}

function getSalonlar($bayi_id = null) {
    global $conn;
    $query = "SELECT salon_id, salon_no FROM salon";
    if($bayi_id) {
        $query .= " WHERE bayi_id = " . (int)$bayi_id;
    }
    $result = $conn->query($query);
    $salonlar = [];
    while($row = $result->fetch_assoc()) {
        $salonlar[$row['salon_id']] = 'Salon ' . $row['salon_no'];
    }
    return $salonlar;
}

function getFilmler() {
    global $conn;
    $result = $conn->query("SELECT film_id, ad FROM film");
    $filmler = [];
    while($row = $result->fetch_assoc()) {
        $filmler[$row['film_id']] = $row['ad'];
    }
    return $filmler;
}

function getMusteriler() {
    global $conn;
    $result = $conn->query("SELECT musteri_id, CONCAT(ad, ' ', soyad) AS ad_soyad FROM musteri");
    $musteriler = [];
    while($row = $result->fetch_assoc()) {
        $musteriler[$row['musteri_id']] = $row['ad_soyad'];
    }
    return $musteriler;
}

function getSeanslar($bayi_id = null) {
    global $conn;
    $query = "SELECT s.seans_id, CONCAT(f.ad, ' - ', DATE_FORMAT(s.tarih, '%d.%m.%Y %H:%i')) AS seans_bilgisi 
              FROM seans s JOIN film f ON s.film_id = f.film_id";
    if($bayi_id) {
        $query .= " WHERE f.bayi_id = " . (int)$bayi_id;
    }
    $result = $conn->query($query);
    $seanslar = [];
    while($row = $result->fetch_assoc()) {
        $seanslar[$row['seans_id']] = $row['seans_bilgisi'];
    }
    return $seanslar;
}

function getFilmlerByBayi($bayi_id) {
    global $conn;
    $result = $conn->query("
        SELECT film_id, ad 
        FROM film 
        WHERE bayi_id = ".(int)$bayi_id."
        ORDER BY ad
    ");
    $filmler = [];
    while($row = $result->fetch_assoc()) {
        $filmler[$row['film_id']] = $row['ad'];
    }
    return $filmler;
}

function getMusteriBiletleri($musteri_id) {
    global $conn;
    return $conn->query("
        SELECT b.*, f.ad AS film_adi, s.tarih, sa.salon_no
        FROM bilet b
        JOIN seans s ON b.seans_id = s.seans_id
        JOIN film f ON s.film_id = f.film_id
        JOIN salon sa ON s.salon_id = sa.salon_id
        WHERE b.musteri_id = ".(int)$musteri_id."
        ORDER BY b.satis_tarihi DESC
    ");
}