<?php
include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table et l'ID
$table = $_GET['table'] ?? die("Table non spécifiée");
$id = $_GET['id'] ?? die("ID non spécifié");

// 2. Charger la configuration pour cette table
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
    <form method="POST">
        <?php foreach ($formColumns as $column): ?>
            <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
            <?php if (strpos($column, 'password') !== false): ?>
                <input type="password" name="<?= $column ?>" placeholder="Laisser vide pour ne pas modifier">
            <?php else: ?>
                <input type="text" name="<?= $column ?>" value="<?= htmlspecialchars($item[$column]) ?>" required>
            <?php endif; ?>
            <br>
        <?php endforeach; ?>
        <button type="submit">Mettre à jour</button>
    </form>
    <p><a href="read.php?table=<?= $table ?>">Retour à la liste</a></p>
</body>
</html>
