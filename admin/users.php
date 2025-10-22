<?php
session_start();
include '../config/db.php';
include 'check_admin.php';

$success = '';
$error = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_role') {
        $user_id = intval($_POST['user_id']);
        
        // Ne pas permettre de modifier son propre rôle
        if ($user_id === $_SESSION['user_id']) {
            $error = "Vous ne pouvez pas modifier votre propre rôle.";
        } else {
            try {
                // Récupérer le rôle actuel
                $stmt = $pdo->prepare("SELECT user_role, user_login FROM user WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $new_role = ($user['user_role'] === 'admin') ? 'user' : 'admin';
                    
                    $stmt = $pdo->prepare("UPDATE user SET user_role = ? WHERE user_id = ?");
                    $stmt->execute([$new_role, $user_id]);
                    
                    $success = "Rôle modifié avec succès pour " . htmlspecialchars($user['user_login']);
                }
            } catch (PDOException $e) {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_user') {
        $user_id = intval($_POST['user_id']);
        
        // Ne pas permettre de supprimer son propre compte
        if ($user_id === $_SESSION['user_id']) {
            $error = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT user_login FROM user WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    
                    $success = "Utilisateur supprimé avec succès.";
                }
            } catch (PDOException $e) {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    }
}

// Récupérer tous les utilisateurs
try {
    $stmt = $pdo->query("
        SELECT 
            user_id, 
            user_login, 
            user_mail, 
            user_role, 
            user_date_new, 
            user_date_login
        FROM user 
        ORDER BY user_role DESC, user_date_new DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur: " . $e->getMessage();
    $users = [];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs - Backoffice</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body { background: #f5f6fa; }
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
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
        .admin-header h1 { margin: 0; color: #2c3e50; }
        .admin-nav { display: flex; gap: 15px; }
        .admin-nav a {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .admin-nav a:hover { background: #2980b9; }
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        .users-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .users-table tr:hover {
            background: #f8f9fa;
        }
        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .role-badge.admin {
            background: #e74c3c;
            color: white;
        }
        .role-badge.user {
            background: #3498db;
            color: white;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            transition: all 0.3s;
        }
        .action-btn.toggle {
            background: #f39c12;
            color: white;
        }
        .action-btn.toggle:hover {
            background: #e67e22;
        }
        .action-btn.delete {
            background: #e74c3c;
            color: white;
        }
        .action-btn.delete:hover {
            background: #c0392b;
        }
        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .stats-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .stat-item {
            flex: 1;
            text-align: center;
        }
        .stat-item .value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-item .label {
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>👥 Gestion des utilisateurs</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="../index.php">Site public</a>
            </div>
        </div>
        
        <div class="content-section">
            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="stats-summary">
                <div class="stat-item">
                    <div class="value"><?= count($users) ?></div>
                    <div class="label">Total utilisateurs</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= count(array_filter($users, fn($u) => $u['user_role'] === 'admin')) ?></div>
                    <div class="label">Administrateurs</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= count(array_filter($users, fn($u) => $u['user_role'] === 'user')) ?></div>
                    <div class="label">Utilisateurs simples</div>
                </div>
            </div>
            
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($user['user_login']) ?></strong>
                            <?php if ($user['user_id'] === $_SESSION['user_id']): ?>
                                <span style="color: #3498db; font-size: 12px;">(vous)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['user_mail']) ?></td>
                        <td>
                            <span class="role-badge <?= $user['user_role'] ?>">
                                <?= $user['user_role'] === 'admin' ? 'Admin' : 'User' ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($user['user_date_new'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($user['user_date_login'])) ?></td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="action" value="toggle_role">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button 
                                    type="submit" 
                                    class="action-btn toggle"
                                    <?= ($user['user_id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>
                                    onclick="return confirm('Modifier le rôle de cet utilisateur ?')"
                                >
                                    <?= $user['user_role'] === 'admin' ? 'Rétrograder' : 'Promouvoir admin' ?>
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button 
                                    type="submit" 
                                    class="action-btn delete"
                                    <?= ($user['user_id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>
                                    onclick="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.')"
                                >
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
