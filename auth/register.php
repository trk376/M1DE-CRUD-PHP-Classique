<?php
session_start();
include '../config/db.php';

$error = '';
$success = '';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['user_login'] ?? '';
    $password = $_POST['user_password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $email = $_POST['user_mail'] ?? '';
    
    // Validation
    if (empty($login) || empty($password) || empty($email)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        // Vérifier si le login existe déjà
        $stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_mail = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                // Créer l'utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO user (user_login, user_password, user_mail) VALUES (?, ?, ?)");
                
                try {
                    $stmt->execute([$login, $hashed_password, $email]);
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } catch (PDOException $e) {
                    $error = "Erreur lors de l'inscription : " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
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
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #229954;
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
        .form-hint {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>📝 Inscription</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="user_login">Identifiant *</label>
                <input type="text" id="user_login" name="user_login" required autofocus value="<?= htmlspecialchars($login ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="user_mail">Email *</label>
                <input type="email" id="user_mail" name="user_mail" required value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="user_password">Mot de passe *</label>
                <input type="password" id="user_password" name="user_password" required>
                <div class="form-hint">Minimum 6 caractères</div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe *</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <button type="submit" class="submit-btn">S'inscrire</button>
        </form>
        
        <div class="auth-links">
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p><a href="../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
