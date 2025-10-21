<?php
return [
    'user' => [
        'excluded_columns' => ['user_id', 'user_date_new', 'user_date_login', 'user_compte_id'],
        'default_values' => [
            'user_compte_id' => function($pdo) {
                return null;
            },
            'user_password' => function($password) {
                return password_hash($password, PASSWORD_DEFAULT);
            }
        ],
        'primary_key' => 'user_id'
    ],
    'produit' => [ 
        'excluded_columns' => ['id_p', 'date_in', 'timeS_in'],
        'default_values' => [
            'id_p' => function($pdo) {
                $stmt = $pdo->query("SELECT MAX(user_compte_id) FROM user");
                $maxId = $stmt->fetchColumn();
                return $maxId ? $maxId + 1 : 1;
            },
            'date_in' => function() {
            return date('Y-m-d'); 
            }
        ],
        'primary_key' => 'id_p'
    ],
    'historique_prix' => [ 
        'excluded_columns' => ['id_hist', 'date_modification'],
        'default_values' => [],
        'primary_key' => 'id_hist'
    ],
];
?>
