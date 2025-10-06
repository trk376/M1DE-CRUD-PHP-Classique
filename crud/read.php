<?php
include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table
$table = $_GET['table'] ?? die("Table non spécifiée");

// 2. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? null;
if (!$config) {
    die("Configuration non trouvée pour la table '$table'. Vérifie que la table est définie dans crud_config.php.");
}

// 3. Récupérer les données
$stmt = $pdo->query("SELECT * FROM $table");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    echo "Aucune donnée dans la table '$table'.";
} else {
    // 4. Récupérer les colonnes
    $columns = array_keys($items[0]);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Liste des <?= ucfirst($table) ?>s</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
        <h1>Liste des <?= ucfirst($table) ?>s</h1>
        <a href="create.php?table=<?= $table ?>" class="button">Ajouter</a>
        <a href="../../index.php" class="button">Retour à l'accueil</a>
        <table border="1">
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <td><?= htmlspecialchars($item[$column]) ?></td>
                <?php endforeach; ?>
                <td>
                    <a href="update.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button">Modifier</a>
                    <a href="delete.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body>
    </html>
    <?php
}
?>
