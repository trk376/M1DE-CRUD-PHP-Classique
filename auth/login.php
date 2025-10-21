<?php
session_start();
include '../config/db.php';

$error = '';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['user_login'] ?? '';
    $password = $_POST['user_password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Rechercher l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM user WHERE user_login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['user_password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_login'] = $user['user_login'];
            $_SESSION['user_mail'] = $user['user_mail'];
            
            // Mettre à jour la date de dernière connexion
            $stmt = $pdo->prepare("UPDATE user SET user_date_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            header('Location: ../index.php');
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .auth-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #2980b9;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .auth-links a {
            color: #3498db;
            text-decoration: none;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>🔐 Connexion</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="user_login">Identifiant</label>
                <input type="text" id="user_login" name="user_login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="user_password">Mot de passe</label>
                <input type="password" id="user_password" name="user_password" required>
            </div>
            
            <button type="submit" class="submit-btn">Se connecter</button>
        </form>
        
        <div class="auth-links">
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
            <p><a href="../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
