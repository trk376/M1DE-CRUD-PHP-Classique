<?php
include '../config/db.php';

// Récupérer l'ID du produit
$id = $_GET['id'] ?? die("ID non spécifié");

// Récupérer les informations du produit
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_p = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit non trouvé.");
}

// Récupérer l'historique des prix
$stmt = $pdo->prepare("
    SELECT * FROM historique_prix 
    WHERE id_produit = ? 
    ORDER BY date_modification DESC
");
$stmt->execute([$id]);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le prix actuel avec promotion
$prix_actuel = floatval($produit['prix_ht']);
$ppromo = isset($produit['ppromo']) ? floatval($produit['ppromo']) : null;
$prix_avec_promo = ($ppromo && $ppromo > 0) ? $prix_actuel * (1 - $ppromo / 100) : $prix_actuel;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Historique des prix - <?= htmlspecialchars($produit['designation_p']) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .product-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .product-info img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
        }
        .product-details {
            flex: 1;
        }
        .price-info {
            font-size: 1.3em;
            margin-top: 10px;
        }
        .old-price {
            text-decoration: line-through;
            color: #999;
        }
        .promo-price {
            color: #d9534f;
            font-weight: bold;
            font-size: 1.2em;
        }
        .promo-badge {
            background-color: #d9534f;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
        }
        .history-table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .history-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .price-increase {
            color: #d9534f;
        }
        .price-decrease {
            color: #5cb85c;
        }
        .price-arrow {
            font-size: 1.2em;
            margin-right: 5px;
        }
        .no-history {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .chart-container {
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>Historique des prix</h1>
    
    <div class="product-info">
        <img src="../img_produit/<?= htmlspecialchars($produit['image_p']) ?>" alt="<?= htmlspecialchars($produit['designation_p']) ?>">
        <div class="product-details">
            <h2><?= htmlspecialchars($produit['designation_p']) ?></h2>
            <p><strong>Type:</strong> <?= htmlspecialchars($produit['type_p']) ?></p>
            <p><strong>Stock:</strong> <?= htmlspecialchars($produit['stock_p']) ?> unités</p>
            <div class="price-info">
                <strong>Prix actuel:</strong>
                <?php if ($ppromo && $ppromo > 0): ?>
                    <span class="old-price"><?= number_format($prix_actuel, 2, ',', ' ') ?> €</span>
                    <span class="promo-price"><?= number_format($prix_avec_promo, 2, ',', ' ') ?> €</span>
                    <span class="promo-badge">-<?= number_format($ppromo, 0) ?>%</span>
                <?php else: ?>
                    <span style="font-weight: bold;"><?= number_format($prix_actuel, 2, ',', ' ') ?> €</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h2>Évolution des prix</h2>
    
    <?php if (empty($historique)): ?>
        <div class="no-history">
            <p>Aucun historique de prix disponible pour ce produit.</p>
        </div>
    <?php else: ?>
        <div class="history-cards">
            <?php foreach ($historique as $hist): ?>
            <?php 
                $ancien = floatval($hist['ancien_prix']);
                $nouveau = floatval($hist['nouveau_prix']);
                $variation = $nouveau - $ancien;
                $variation_percent = $ancien > 0 ? ($variation / $ancien) * 100 : 0;
            ?>
            <div class="history-card">
                <div class="history-date">
                    <?= date('d/m/Y H:i', strtotime($hist['date_modification'])) ?>
                </div>
                <div class="history-content">
                    <div class="price-change">
                        <div class="price-old">
                            <span class="label">Ancien prix</span>
                            <span class="value"><?= number_format($ancien, 2, ',', ' ') ?> €</span>
                        </div>
                        <div class="price-arrow-container">
                            <?php if ($variation > 0): ?>
                                <span class="arrow increase">↑</span>
                            <?php elseif ($variation < 0): ?>
                                <span class="arrow decrease">↓</span>
                            <?php else: ?>
                                <span class="arrow neutral">→</span>
                            <?php endif; ?>
                        </div>
                        <div class="price-new">
                            <span class="label">Nouveau prix</span>
                            <span class="value"><?= number_format($nouveau, 2, ',', ' ') ?> €</span>
                        </div>
                    </div>
                    <div class="variation-info">
                        <?php if ($variation > 0): ?>
                            <span class="variation increase">
                                +<?= number_format($variation, 2, ',', ' ') ?> € 
                                (+<?= number_format($variation_percent, 1) ?>%)
                            </span>
                        <?php elseif ($variation < 0): ?>
                            <span class="variation decrease">
                                <?= number_format($variation, 2, ',', ' ') ?> € 
                                (<?= number_format($variation_percent, 1) ?>%)
                            </span>
                        <?php else: ?>
                            <span class="variation neutral">Aucune variation</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($hist['motif'])): ?>
                        <div class="motif">
                            <strong>Motif:</strong> <?= htmlspecialchars($hist['motif']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="chart-container">
            <h3>Statistiques</h3>
            <?php
                $prix_min = min(array_column($historique, 'nouveau_prix'));
                $prix_max = max(array_column($historique, 'nouveau_prix'));
                $nb_modifications = count($historique) - 1; // -1 pour exclure le prix initial
            ?>
            <p><strong>Prix minimum:</strong> <?= number_format($prix_min, 2, ',', ' ') ?> €</p>
            <p><strong>Prix maximum:</strong> <?= number_format($prix_max, 2, ',', ' ') ?> €</p>
            <p><strong>Nombre de modifications:</strong> <?= $nb_modifications ?></p>
            <?php if ($prix_min > 0): ?>
                <p><strong>Variation totale:</strong> <?= number_format((($prix_actuel - $prix_min) / $prix_min) * 100, 1) ?>%</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p>
        <a href="read.php?table=produit" class="button">Retour à la liste des produits</a>
        <a href="update.php?table=produit&id=<?= $id ?>" class="button">Modifier le produit</a>
    </p>
</body>
</html>
