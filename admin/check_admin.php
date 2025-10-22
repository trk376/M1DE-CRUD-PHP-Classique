<?php
// Fichier de sécurité pour vérifier les droits admin
// À inclure en haut de chaque page admin

if (!isset($_SESSION['user_id'])) {
    // Non connecté
    header('Location: ../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Pas admin
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Accès refusé</title>
        <link rel="stylesheet" href="/assets/css/style.css">
        <style>
            .error-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 40px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .error-icon {
                font-size: 80px;
                color: #e74c3c;
                margin-bottom: 20px;
            }
            .error-container h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            .error-container p {
                color: #555;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            .error-container a {
                display: inline-block;
                padding: 12px 24px;
                background: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                transition: background 0.3s;
            }
            .error-container a:hover {
                background: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Accès refusé</h1>
            <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.<br>
            Seuls les administrateurs peuvent accéder au backoffice.</p>
            <p>HAHAHAAHAHAHAHAAHAHAHAHAHA 😂</p>
            <a href="../index.php">Retour à l'accueil</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>
