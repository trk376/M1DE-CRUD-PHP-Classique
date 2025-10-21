<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $new_login = $_POST['user_login'] ?? '';
        $new_email = $_POST['user_mail'] ?? '';
        
        if (empty($new_login) || empty($new_email)) {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email n'est pas valide.";
        } else {
            // Vérifier si le login existe déjà (sauf pour l'utilisateur actuel)
            $stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_login = ? AND user_id != ?");
            $stmt->execute([$new_login, $user_id]);
            if ($stmt->fetch()) {
                $error = "Ce nom d'utilisateur est déjà pris.";
            } else {
                // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
                $stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_mail = ? AND user_id != ?");
                $stmt->execute([$new_email, $user_id]);
                if ($stmt->fetch()) {
                    $error = "Cette adresse email est déjà utilisée.";
                } else {
                    // Mettre à jour le profil
                    $stmt = $pdo->prepare("UPDATE user SET user_login = ?, user_mail = ? WHERE user_id = ?");
                    try {
                        $stmt->execute([$new_login, $new_email, $user_id]);
                        $_SESSION['user_login'] = $new_login;
                        $_SESSION['user_mail'] = $new_email;
                        $success = "Profil mis à jour avec succès !";
                        $user['user_login'] = $new_login;
                        $user['user_mail'] = $new_email;
                    } catch (PDOException $e) {
                        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    }
                }
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Veuillez remplir tous les champs du mot de passe.";
        } elseif (!password_verify($current_password, $user['user_password'])) {
            $error = "Le mot de passe actuel est incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (strlen($new_password) < 6) {
            $error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user SET user_password = ? WHERE user_id = ?");
            try {
                $stmt->execute([$hashed_password, $user_id]);
                $success = "Mot de passe modifié avec succès !";
                $user['user_password'] = $hashed_password;
            } catch (PDOException $e) {
                $error = "Erreur lors de la modification : " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_account') {
        $password_confirm = $_POST['password_confirm_delete'] ?? '';
        
        if (password_verify($password_confirm, $user['user_password'])) {
            $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
            try {
                $stmt->execute([$user_id]);
                session_destroy();
                header('Location: ../index.php?deleted=1');
                exit;
            } catch (PDOException $e) {
                $error = "Erreur lors de la suppression : " . $e->getMessage();
            }
        } else {
            $error = "Mot de passe incorrect. Suppression annulée.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-header h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .profile-info {
            color: #666;
            font-size: 0.95em;
        }
        .profile-sections {
            display: grid;
            gap: 20px;
        }
        .profile-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-section h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        .btn-profile {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .danger-zone {
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .danger-zone h3 {
            color: #e74c3c;
            margin-top: 0;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .info-box {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .info-box p {
            margin: 5px 0;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>👤 Mon Profil</h1>
            <div class="profile-info">
                <p>Membre depuis le <?= date('d/m/Y', strtotime($user['user_date_new'])) ?></p>
                <p>Dernière connexion : <?= date('d/m/Y à H:i', strtotime($user['user_date_login'])) ?></p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-sections">
            <!-- Informations du profil -->
            <div class="profile-section">
                <h2>Informations du profil</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="user_login">Identifiant</label>
                        <input type="text" id="user_login" name="user_login" value="<?= htmlspecialchars($user['user_login']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_mail">Email</label>
                        <input type="email" id="user_mail" name="user_mail" value="<?= htmlspecialchars($user['user_mail']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-profile btn-primary">Mettre à jour le profil</button>
                </form>
            </div>

            <!-- Changement de mot de passe -->
            <div class="profile-section">
                <h2>Modifier le mot de passe</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn-profile btn-primary">Changer le mot de passe</button>
                </form>
            </div>

            <!-- Zone de danger -->
            <div class="profile-section">
                <div class="danger-zone">
                    <h3>ATTENTIONNNNNNNNNNNN</h3>
                    <p>La suppression de votre compte est définitive et irréversible. Toutes vos données seront perdues.</p>
                    
                    <form method="POST" onsubmit="return confirm('Êtes-vous vraiment sûr de vouloir supprimer votre compte ? Cette action est IRRÉVERSIBLE !');">
                        <input type="hidden" name="action" value="delete_account">
                        
                        <div class="form-group">
                            <label for="password_confirm_delete">Confirmez votre mot de passe pour supprimer le compte</label>
                            <input type="password" id="password_confirm_delete" name="password_confirm_delete" required>
                        </div>
                        
                        <button type="submit" class="btn-profile btn-danger">Supprimer mon compte</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <a href="../index.php" class="btn-profile btn-secondary">Retour à l'accueil</a>
            <a href="logout.php" class="btn-profile btn-secondary">Se déconnecter</a>
        </div>
    </div>
</body>
</html>
