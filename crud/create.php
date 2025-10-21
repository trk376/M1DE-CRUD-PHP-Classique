<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table
$table = $_GET['table'] ?? die("Table non spécifiée");

// 2. Vérifier les permissions
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Vous devez être <a href='../auth/login.php'>connecté</a> pour ajouter des éléments.");
}

// Bloquer l'accès à la table user
if ($table === 'user') {
    die("Accès refusé. Utilisez le formulaire d'<a href='../auth/register.php'>inscription</a> pour créer un compte.");
}

// 3. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? die("Configuration non trouvée pour la table $table");

// 3. Récupérer les colonnes de la table
$stmt = $pdo->query("DESCRIBE $table");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Filtrer les colonnes exclues
$formColumns = array_diff($columns, $config['excluded_columns']);

// 5. Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    // Valider et nettoyer le champ ppromo pour les produits
    if ($table === 'produit' && isset($data['ppromo'])) {
        if ($data['ppromo'] === '' || $data['ppromo'] === null) {
            $data['ppromo'] = null;
        } else {
            $data['ppromo'] = floatval($data['ppromo']);
            if ($data['ppromo'] < 0) $data['ppromo'] = 0;
            if ($data['ppromo'] > 100) $data['ppromo'] = 100;
        }
    }

    // Gérer l'upload d'image pour la table produit
    if ($table === 'produit' && isset($_FILES['image_p']) && $_FILES['image_p']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../img_produit/';
        $fileName = basename($_FILES['image_p']['name']);
        $targetFile = $uploadDir . $fileName;
        
        // Vérifier si c'est une image
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image_p']['tmp_name'], $targetFile)) {
                $data['image_p'] = $fileName;
            } else {
                $data['image_p'] = 'default.png';
            }
        } else {
            $data['image_p'] = 'default.png';
        }
    } elseif ($table === 'produit') {
        $data['image_p'] = 'default.png';
    }

    // Appliquer les valeurs par défaut
    foreach ($config['default_values'] as $column => $valueGenerator) {
        if (is_callable($valueGenerator)) {
            if ($column === 'user_password' && isset($data[$column])) {
                $data[$column] = $valueGenerator($data[$column]);
            } else {
                $data[$column] = $valueGenerator($pdo);
            }
        } else {
            $data[$column] = $valueGenerator;
        }
    }

    create($pdo, $table, $data, $config['primary_key']);
    header("Location: read.php?table=$table");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un <?= ucfirst($table) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <h1>Ajouter un <?= ucfirst($table) ?></h1>
    <form method="POST" enctype="multipart/form-data">
        <?php foreach ($formColumns as $column): ?>
            <?php if ($table === 'produit' && $column === 'image_p'): ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
                <input type="file" name="<?= $column ?>" accept="image/*">
                <small>(Laissez vide pour utiliser l'image par défaut)</small>
                <br>
            <?php elseif ($table === 'produit' && $column === 'ppromo'): ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?> (%):</label>
                <input type="number" name="<?= $column ?>" min="0" max="100" step="0.01" placeholder="Ex: 15.50">
                <small>(Pourcentage de promotion entre 0 et 100, laissez vide si pas de promotion)</small>
                <br>
            <?php else: ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
                <?php if (strpos($column, 'password') !== false): ?>
                    <input type="password" name="<?= $column ?>" required>
                <?php else: ?>
                    <input type="text" name="<?= $column ?>" required>
                <?php endif; ?>
                <br>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">Ajouter</button>
    </form>
    <p><a href="read.php?table=<?= $table ?>">Retour à la liste</a></p>
</body>
</html>
