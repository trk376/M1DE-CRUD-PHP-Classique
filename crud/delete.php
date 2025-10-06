<?php
include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table et l'ID
$table = $_GET['table'] ?? die("Table non spécifiée");
$id = $_GET['id'] ?? die("ID non spécifié");

// 2. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? die("Configuration non trouvée pour la table $table");

// 3. Supprimer l'élément
$stmt = $pdo->prepare("DELETE FROM $table WHERE {$config['primary_key']} = ?");
$stmt->execute([$id]);

header("Location: read.php?table=$table");
exit();
?>
