<?php
session_start();

// Vérifier si l'utilisateur vient de supprimer son compte
$account_deleted = isset($_GET['deleted']) && $_GET['deleted'] == 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Gestion CRUD</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .menu-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .menu-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .menu-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .menu-list {
            list-style: none;
            padding: 0;
        }
        .menu-list li {
            margin: 10px 0;
        }
        .menu-list a {
            display: inline-block;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .menu-list a:hover {
            background-color: #0056b3;
        }
        .user-info {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info .welcome {
            color: #2c3e50;
            font-weight: bold;
        }
        .user-info .auth-links {
            display: flex;
            gap: 10px;
        }
        .user-info a {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background 0.3s;
        }
        .user-info a:hover {
            background: #2980b9;
        }
        .user-info a.logout {
            background: #95a5a6;
        }
        .user-info a.logout:hover {
            background: #7f8c8d;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <h1>Système de Gestion CRUD</h1>
        
        <?php if ($account_deleted): ?>
            <div class="success-message">
                Votre compte a été supprimé avec succès. o7
            </div>
        <?php endif; ?>
        
        <div class="user-info">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="welcome">
                    Bienvenue, <?= htmlspecialchars($_SESSION['user_login']) ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 5px;">🛡️ Admin</span>
                    <?php endif; ?>
                </div>
                <div class="auth-links">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/index.php" style="background: #e74c3c;">Backoffice</a>
                    <?php endif; ?>
                    <a href="auth/profile.php">Mon profil</a>
                    <a href="auth/logout.php" class="logout">Déconnexion</a>
                </div>
            <?php else: ?>
                <div class="welcome">
                    Visiteur - Vous devez être connecté pour gérer les produits
                </div>
                <div class="auth-links">
                    <a href="auth/login.php">Connexion</a>
                    <a href="auth/register.php">Inscription</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="menu-section">
            <h2>Gestion des Données</h2>
            <ul class="menu-list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="crud/read.php?table=produit">Gérer les produits</a>
                    </li>
                    <li>
                        <a href="crud/read.php?table=historique_prix">Consulter l'historique des prix</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="crud/read.php?table=produit">Voir les produits (lecture seule)</a>
                    </li>
                    <li style="color: #999; padding: 12px 20px;">
                        Connectez-vous pour gérer les produits
                    </li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</body>
</html>