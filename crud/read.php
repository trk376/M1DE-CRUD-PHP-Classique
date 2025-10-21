<?php
session_start();
include '../config/db.php';
$crudConfig = include '../config/crud_config.php';

// 1. Récupérer le nom de la table
$table = $_GET['table'] ?? die("Table non spécifiée");

// 2. Vérifier les permissions
$is_logged_in = isset($_SESSION['user_id']);

// Bloquer l'accès à la table user (sauf pour son propre profil)
if ($table === 'user') {
    die("Accès refusé. Pour gérer votre compte, allez dans <a href='../auth/profile.php'>Mon profil</a>.");
}

// 3. Charger la configuration pour cette table
$config = $crudConfig[$table] ?? null;
if (!$config) {
    die("Configuration non trouvée pour la table '$table'. Vérifie que la table est définie dans crud_config.php.");
}

// 4. Récupérer les données
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
        
        <?php if (!$is_logged_in): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ffeeba;">
                ⚠️ Mode lecture seule. <a href="../auth/login.php" style="color: #856404; text-decoration: underline;">Connectez-vous</a> pour ajouter, modifier ou supprimer des produits.
            </div>
        <?php endif; ?>
        
        <div class="actions-header">
            <?php if ($is_logged_in && $table !== 'historique_prix'): ?>
                <a href="create.php?table=<?= $table ?>" class="button">Ajouter</a>
            <?php endif; ?>
            <a href="../index.php" class="button">Retour à l'accueil</a>
        </div>
        
        <div class="cards-grid">
            <?php foreach ($items as $item): ?>
            <div class="card">
                <?php if ($table === 'produit'): ?>
                    <!-- Card pour produit -->
                    <?php 
                    $prix_ht = floatval($item['prix_ht']);
                    $ppromo = isset($item['ppromo']) ? floatval($item['ppromo']) : null;
                    $prix_promo = ($ppromo && $ppromo > 0) ? $prix_ht * (1 - $ppromo / 100) : $prix_ht;
                    ?>
                    <div class="card-image">
                        <img src="../img_produit/<?= htmlspecialchars($item['image_p']) ?>" alt="<?= htmlspecialchars($item['designation_p']) ?>">
                        <?php if ($ppromo && $ppromo > 0): ?>
                            <span class="promo-badge">-<?= number_format($ppromo, 0) ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($item['designation_p']) ?></h3>
                        <div class="card-details">
                            <p><strong>Type:</strong> <?= htmlspecialchars($item['type_p']) ?></p>
                            <p><strong>Stock:</strong> <?= htmlspecialchars($item['stock_p']) ?> unités</p>
                            <div class="card-price">
                                <?php if ($ppromo && $ppromo > 0): ?>
                                    <span class="old-price"><?= number_format($prix_ht, 2, ',', ' ') ?> €</span>
                                    <span class="current-price promo"><?= number_format($prix_promo, 2, ',', ' ') ?> €</span>
                                <?php else: ?>
                                    <span class="current-price"><?= number_format($prix_ht, 2, ',', ' ') ?> €</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="price_history.php?id=<?= $item[$config['primary_key']] ?>" class="button button-secondary">Historique</a>
                        <?php if ($is_logged_in): ?>
                            <a href="update.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button">Modifier</a>
                            <a href="delete.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        <?php endif; ?>
                    </div>
                    
                <?php elseif ($table === 'user'): ?>
                    <!-- Card pour user -->
                    <div class="card-content">
                        <h3>👤 <?= htmlspecialchars($item['nom_u'] ?? '') ?> <?= htmlspecialchars($item['prenom_u'] ?? '') ?></h3>
                        <div class="card-details">
                            <?php foreach ($columns as $column): ?>
                                <?php if (!in_array($column, ['id_u', 'nom_u', 'prenom_u'])): ?>
                                    <p><strong><?= ucfirst(str_replace('_', ' ', $column)) ?>:</strong> <?= htmlspecialchars($item[$column]) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-actions">
                        <?php if ($is_logged_in): ?>
                            <a href="update.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button">Modifier</a>
                            <a href="delete.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <!-- Card générique pour autres tables -->
                    <div class="card-content">
                        <div class="card-details">
                            <?php foreach ($columns as $column): ?>
                                <p><strong><?= ucfirst(str_replace('_', ' ', $column)) ?>:</strong> <?= htmlspecialchars($item[$column]) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-actions">
                        <?php if ($is_logged_in && $table !== 'historique_prix'): ?>
                            <a href="update.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button">Modifier</a>
                            <a href="delete.php?table=<?= $table ?>&id=<?= $item[$config['primary_key']] ?>" class="button delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        <?php elseif ($table === 'historique_prix'): ?>
                            <span style="color: #666; font-style: italic; font-size: 0.9em;">Lecture seule</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
}
?>
