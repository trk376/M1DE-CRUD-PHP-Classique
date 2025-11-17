<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=127.0.0.1;dbname=2025_m1;port=3306;charset=utf8', 
    'root', 
    '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fonction pour lister les entrées d'une table
function getAll($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function create($pdo, $table, $data, $primaryKey) {
    unset($data[$primaryKey]); // Ne pas inclure la clé primaire (auto-incrémentée)

    $columns = implode(', ', array_keys($data)); // implode sert à joindre les clés avec une virgule
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}

function update($pdo, $table, $data, $primaryKey, $id) {
    $setParts = [];
    foreach ($data as $key => $value) {
        if ($key !== $primaryKey) {
            $setParts[] = "$key = :$key";
        }
    }
    $setClause = implode(', ', $setParts);
    $sql = "UPDATE $table SET $setClause WHERE $primaryKey = :$primaryKey";
    $stmt = $pdo->prepare($sql);
    $data[$primaryKey] = $id; 
    return $stmt->execute($data);
}


// Fonction pour supprimer une entrée (supporte différents noms de clé primaire)
function delete($pdo, $table, $id) {
    // Déterminer le nom de la clé primaire
    $primaryKey = getPrimaryKey($pdo, $table);
    
    $stmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = ?");
    return $stmt->execute([$id]);
}

// Fonction pour récupérer une entrée par ID (supporte différents noms de clé primaire)
function getById($pdo, $table, $id) {
    // Déterminer le nom de la clé primaire
    $primaryKey = getPrimaryKey($pdo, $table);
    
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les colonnes d'une table (pour les formulaires)
function getTableColumns($pdo, $table) {
    $stmt = $pdo->query("DESCRIBE $table");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Nouvelle fonction pour obtenir le nom de la clé primaire d'une table
function getPrimaryKey($pdo, $table) {
    $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['Column_name'] ?? 'id';
}

// Fonction de tri générique
function sortItems(&$items, $sortBy, $sortOrder, $table = 'produit') {
    usort($items, function($a, $b) use ($sortBy, $sortOrder, $table) {
        $valA = $a[$sortBy] ?? '';
        $valB = $b[$sortBy] ?? '';
        
        // Gestion spéciale pour les prix avec promotions
        if ($table === 'produit' && $sortBy === 'prix_ht') {
            $prixA = floatval($a['prix_ht']);
            $prixB = floatval($b['prix_ht']);
            
            // Si promotion, utiliser le prix promo pour le tri
            if (isset($a['ppromo']) && floatval($a['ppromo']) > 0) {
                $prixA = $prixA * (1 - floatval($a['ppromo']) / 100);
            }
            if (isset($b['ppromo']) && floatval($b['ppromo']) > 0) {
                $prixB = $prixB * (1 - floatval($b['ppromo']) / 100);
            }
            
            $valA = $prixA;
            $valB = $prixB;
        }
        
        // Comparaison numérique ou string
        if (is_numeric($valA) && is_numeric($valB)) {
            $result = $valA - $valB;
        } else {
            $result = strcasecmp(strval($valA), strval($valB));
        }
        
        return $sortOrder === 'desc' ? -$result : $result;
    });
}

?>