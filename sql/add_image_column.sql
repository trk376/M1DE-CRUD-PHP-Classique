-- Script pour ajouter la colonne image_p à la table produit existante
-- Exécutez ce script si votre base de données est déjà créée

ALTER TABLE `produit` 
ADD COLUMN `image_p` varchar(255) DEFAULT 'default.png' AFTER `stock_p`;

-- Mettre à jour tous les produits existants avec l'image par défaut
UPDATE `produit` SET `image_p` = 'default.png' WHERE `image_p` IS NULL;
