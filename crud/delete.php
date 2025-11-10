<?php
session_start();
include '../config/db.php';
include '../config/image_handler.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table et l'ID
$table = $_GET['table'] ?? die("Table non spécifiée");
$id = $_GET['id'] ?? die("ID non spécifié");

// 2. Vérifier les permissions
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Vous devez être <a href='../auth/login.php'>connecté</a> pour supprimer des éléments.");
}

// Bloquer l'accès à la table user
if ($table === 'user') {
    die("Accès refusé. Pour supprimer votre compte, allez dans <a href='../auth/profile.php'>Mon profil</a>.");
}

// Bloquer l'accès à la table historique_prix (lecture seule)
if ($table === 'historique_prix') {
    die("Accès refusé. L'historique des prix ne peut pas être supprimé. Il s'agit d'un enregistrement immuable à des fins d'audit.");
}

// 3. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? die("Configuration non trouvée pour la table $table");

// 4. Récupérer l'élément avant suppression (pour nettoyer les images)
$stmt = $pdo->prepare("SELECT * FROM $table WHERE {$config['primary_key']} = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    // Nettoyer l'image si c'est un produit
    if ($table === 'produit' && !empty($item['image_p'])) {
        ImageHandler::delete($item['image_p']);
    }
}

// 5. Supprimer l'élément
$stmt = $pdo->prepare("DELETE FROM $table WHERE {$config['primary_key']} = ?");
$stmt->execute([$id]);

header("Location: read.php?table=$table");
exit();
?>
