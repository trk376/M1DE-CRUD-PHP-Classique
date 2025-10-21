<?php
session_start();
include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table et l'ID
$table = $_GET['table'] ?? die("Table non spécifiée");
$id = $_GET['id'] ?? die("ID non spécifié");

// 2. Vérifier les permissions
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Vous devez être <a href='../auth/login.php'>connecté</a> pour modifier des éléments.");
}

// Bloquer l'accès à la table user
if ($table === 'user') {
    die("Accès refusé. Pour modifier votre compte, allez dans <a href='../auth/profile.php'>Mon profil</a>.");
}

// 3. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? die("Configuration non trouvée pour la table $table");

// 3. Récupérer l'élément
$stmt = $pdo->prepare("SELECT * FROM $table WHERE {$config['primary_key']} = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Élément non trouvé.");
}

// 4. Récupérer les colonnes à afficher
$stmt = $pdo->query("DESCRIBE $table");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
                // Supprimer l'ancienne image si elle n'est pas default.png
                if ($item['image_p'] !== 'default.png' && file_exists($uploadDir . $item['image_p'])) {
                    unlink($uploadDir . $item['image_p']);
                }
                $data['image_p'] = $fileName;
            }
        }
    }

    // Appliquer les valeurs par défaut pour les mots de passe
    if (isset($config['default_values']['user_password']) && isset($data['user_password']) && !empty($data['user_password'])) {
        $data['user_password'] = $config['default_values']['user_password']($data['user_password']);
    } else {
        unset($data['user_password']); // Ne pas modifier le mot de passe s'il n'est pas soumis
    }

    // Mettre à jour l'élément
    update($pdo, $table, $data, $config['primary_key'], $id);
    header("Location: read.php?table=$table");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un <?= ucfirst($table) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <h1>Modifier un <?= ucfirst($table) ?></h1>
    <form method="POST" enctype="multipart/form-data">
        <?php foreach ($formColumns as $column): ?>
            <?php if ($table === 'produit' && $column === 'image_p'): ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
                <div>
                    <?php if (!empty($item[$column])): ?>
                        <img src="../img_produit/<?= htmlspecialchars($item[$column]) ?>" alt="Image actuelle" style="max-width: 200px; max-height: 200px; display: block; margin-bottom: 10px;">
                        <p>Image actuelle: <?= htmlspecialchars($item[$column]) ?></p>
                    <?php endif; ?>
                    <input type="file" name="<?= $column ?>" accept="image/*">
                    <small>(Laissez vide pour conserver l'image actuelle)</small>
                </div>
                <br>
            <?php elseif ($table === 'produit' && $column === 'ppromo'): ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?> (%):</label>
                <input type="number" name="<?= $column ?>" min="0" max="100" step="0.01" 
                       value="<?= htmlspecialchars($item[$column] ?? '') ?>" 
                       placeholder="Ex: 15.50">
                <small>(Pourcentage de promotion entre 0 et 100, laissez vide si pas de promotion)</small>
                <br>
            <?php else: ?>
                <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
                <?php if (strpos($column, 'password') !== false): ?>
                    <input type="password" name="<?= $column ?>" placeholder="Laisser vide pour ne pas modifier">
                <?php else: ?>
                    <input type="text" name="<?= $column ?>" value="<?= htmlspecialchars($item[$column]) ?>" required>
                <?php endif; ?>
                <br>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">Mettre à jour</button>
    </form>
    <p><a href="read.php?table=<?= $table ?>">Retour à la liste</a></p>
</body>
</html>
