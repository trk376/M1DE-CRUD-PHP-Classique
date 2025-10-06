<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table
$table = $_GET['table'] ?? die("Table non spécifiée");

// 2. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? die("Configuration non trouvée pour la table $table");

// 3. Récupérer les colonnes de la table
$stmt = $pdo->query("DESCRIBE $table");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Filtrer les colonnes exclues
$formColumns = array_diff($columns, $config['excluded_columns']);

// 5. Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

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
    <form method="POST">
        <?php foreach ($formColumns as $column): ?>
            <label><?= ucfirst(str_replace('_', ' ', $column)) ?>:</label>
            <?php if (strpos($column, 'password') !== false): ?>
                <input type="password" name="<?= $column ?>" required>
            <?php else: ?>
                <input type="text" name="<?= $column ?>" required>
            <?php endif; ?>
            <br>
        <?php endforeach; ?>
        <button type="submit">Ajouter</button>
    </form>
    <p><a href="read.php?table=<?= $table ?>">Retour à la liste</a></p>
</body>
</html>
