# Projet CRUD - Gestion de bases de données

Projet réalisé dans le cadre du cours de développement web. Ce système permet de **créer, lire, mettre à jour et supprimer** des enregistrements dans une base de données MySQL via une interface web.

---

## **Prérequis**
- PHP 8+ (avec l'extension PDO activée).
- MySQL 8+.

##  **Installation et configuration**

### **1. Cloner le projet**

### **2. Configurer la base de données**
Modifie le fichier config/db.php avec tes identifiants MySQL :
```php
\$pdo = new PDO(
    'mysql\:host=localhost;dbname=2025_M1;port=3306',
    'root',          // Remplace par ton utilisateur MySQL
    ''               // Remplace par ton mot de passe (souvent vide sous XAMPP)
);
```
Il est nécessaire d'avoir une table **user** et une table **produit** , si vos tables se nomment autrements alors modifier **crud_config.php**

### 3. Lancer le projet
Avec le serveur intégré de PHP
Ouvre un terminal dans le dossier du projet et lance :
```bash
php -S localhost:8000
```
Accède au projet via http://localhost:8000.

