<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=2025_M1', 'user_2025', 'user');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Connexion réussie !</h2>";
    echo "<h3>Tables disponibles :</h3>";
    foreach ($pdo->query("SHOW TABLES") as $row) {
        echo "- " . $row[0] . "<br>";
    }
} catch (PDOException $e) {
    die("<h2>Erreur :</h2>" . $e->getMessage());
}
?>
