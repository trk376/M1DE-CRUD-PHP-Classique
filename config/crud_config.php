<?php
return [
    'user' => [
        'excluded_columns' => ['user_id', 'user_date_new', 'user_date_login', 'user_compte_id'],
        'default_values' => [
            'user_compte_id' => function($pdo) {
                $stmt = $pdo->query("SELECT MAX(user_compte_id) FROM user");
                $maxId = $stmt->fetchColumn();
                return $maxId ? $maxId + 1 : 1;
            },
            'user_password' => function($password) {
                return password_hash($password, PASSWORD_DEFAULT);
            }
        ],
        'primary_key' => 'user_id'
    ],
    'produit' => [  // Note : Utilise le même nom que dans ton URL (ex: produit, pas product)
        'excluded_columns' => ['produit_id', 'created_at'],
        'default_values' => [],
        'primary_key' => 'produit_id'
    ],
    // Ajoute d'autres tables ici
];
?>
