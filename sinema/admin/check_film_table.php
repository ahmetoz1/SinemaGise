<?php
require_once __DIR__.'/../config.php';

// Tablo yapısını göster
$result = $conn->query("DESCRIBE film");
echo "<h2>Film Tablosu Yapısı:</h2>";
echo "<table border='1'>";
echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// İlk 5 filmi göster
$result = $conn->query("SELECT * FROM film LIMIT 5");
echo "<h2>İlk 5 Film:</h2>";
echo "<table border='1'>";
if($result->num_rows > 0) {
    // Başlıkları göster
    $first_row = $result->fetch_assoc();
    echo "<tr>";
    foreach($first_row as $key => $value) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    
    // İlk satırı tekrar göster
    echo "<tr>";
    foreach($first_row as $value) {
        echo "<td>" . $value . "</td>";
    }
    echo "</tr>";
    
    // Diğer satırları göster
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach($row as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }
}
echo "</table>"; 