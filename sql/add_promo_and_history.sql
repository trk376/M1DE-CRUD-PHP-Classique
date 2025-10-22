-- Script pour ajouter la gestion des promotions et l'historique des prix
-- Exécutez ce script si votre base de données est déjà créée

-- 1. Ajouter la colonne ppromo à la table produit
ALTER TABLE `produit` 
ADD COLUMN `ppromo` decimal(5,2) DEFAULT NULL COMMENT 'Pourcentage de promotion (ex: 15.00 pour 15%)' AFTER `image_p`;

-- 2. Créer la table historique_prix
CREATE TABLE IF NOT EXISTS `historique_prix` (
  `id_hist` int(11) NOT NULL AUTO_INCREMENT,
  `id_produit` int(11) NOT NULL,
  `ancien_prix` decimal(10,2) NOT NULL,
  `nouveau_prix` decimal(10,2) NOT NULL,
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp(),
  `motif` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_hist`),
  KEY `fk_historique_produit` (`id_produit`),
  CONSTRAINT `fk_historique_produit` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_p`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Créer un trigger pour enregistrer automatiquement les changements de prix
DELIMITER $$

CREATE TRIGGER `after_produit_price_update` 
AFTER UPDATE ON `produit`
FOR EACH ROW
BEGIN
    -- Vérifier si le prix a changé
    IF OLD.prix_ht != NEW.prix_ht THEN
        INSERT INTO `historique_prix` (`id_produit`, `ancien_prix`, `nouveau_prix`, `motif`)
        VALUES (NEW.id_p, OLD.prix_ht, NEW.prix_ht, 'Modification du prix');
    END IF;
END$$


DELIMITER ;

-- 5. Ajouter quelques promotions aux produits existants (exemples)
UPDATE `produit` SET `ppromo` = 14.00 WHERE `id_p` = 6;
UPDATE `produit` SET `ppromo` = 10.00 WHERE `id_p` = 9;
UPDATE `produit` SET `ppromo` = 20.00 WHERE `id_p` = 11;
UPDATE `produit` SET `ppromo` = 25.00 WHERE `id_p` = 14;
UPDATE `produit` SET `ppromo` = 5.00 WHERE `id_p` = 18;
UPDATE `produit` SET `ppromo` = 30.00 WHERE `id_p` = 22;
UPDATE `produit` SET `ppromo` = 12.50 WHERE `id_p` = 26;
UPDATE `produit` SET `ppromo` = 8.00 WHERE `id_p` = 30;
