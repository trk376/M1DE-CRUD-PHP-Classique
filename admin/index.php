<?php
session_start();
include '../config/db.php';
include 'check_admin.php';

// Récupérer les statistiques
try {
    // Nombre total de produits
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produit");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Produits en promotion
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produit WHERE ppromo IS NOT NULL AND ppromo > 0");
    $products_in_promo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Valeur totale du stock
    $stmt = $pdo->query("SELECT SUM(prix_ht * stock_p) as total FROM produit");
    $total_stock_value = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Stock faible (moins de 10 unités)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produit WHERE stock_p < 10");
    $low_stock_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Backoffice - Tableau de bord</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            background: #f5f6fa;
        }
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            margin: 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        .admin-nav a {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #2980b9;
        }
        .admin-nav a.logout {
            background: #95a5a6;
        }
        .admin-nav a.logout:hover {
            background: #7f8c8d;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .stat-card .label {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-card.blue { border-left: 4px solid #3498db; }
        .stat-card.green { border-left: 4px solid #27ae60; }
        .stat-card.orange { border-left: 4px solid #f39c12; }
        .stat-card.purple { border-left: 4px solid #9b59b6; }
        .stat-card.red { border-left: 4px solid #e74c3c; }
        
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .content-section h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .quick-action {
            padding: 20px;
            background: #ecf0f1;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .quick-action:hover {
            background: #3498db;
            color: white;
            border-color: #2980b9;
            transform: translateY(-2px);
        }
        .quick-action .icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .logs-table th, .logs-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        .logs-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .logs-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge.info { background: #d1ecf1; color: #0c5460; }
        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Backoffice Administration</h1>
            <div class="admin-nav">
                <span style="color: #7f8c8d; align-self: center;">
                    <?= htmlspecialchars($_SESSION['user_login']) ?>
                </span>
                <a href="../index.php">Site public</a>
                <a href="../auth/logout.php" class="logout">Déconnexion</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="label">Produits total</div>
                <div class="value"><?= $total_products ?></div>
            </div>
            
            <div class="stat-card green">
                <div class="label">Utilisateurs</div>
                <div class="value"><?= $total_users ?></div>
            </div>
            
            <div class="stat-card orange">
                <div class="label">Produits en promo</div>
                <div class="value"><?= $products_in_promo ?></div>
            </div>
            
            <div class="stat-card purple">
                <div class="label">Valeur du stock</div>
                <div class="value"><?= number_format($total_stock_value, 0, ',', ' ') ?> €</div>
            </div>
            
            <div class="stat-card red">
                <div class="label">Stock faible</div>
                <div class="value"><?= $low_stock_count ?></div>
            </div>
        </div>
        
        <div class="content-section">
            <h2>Actions rapides</h2>
            <div class="quick-actions">
                <a href="users.php" class="quick-action">
                    <div>Gérer les utilisateurs</div>
                </a>
                <a href="../crud/read.php?table=produit" class="quick-action">
                    <div>Gérer les produits</div>
                </a>
            </div>
        </div>
        
    </div>
</body>
</html>
