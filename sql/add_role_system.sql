-- 1. Ajouter la colonne user_role à la table user
ALTER TABLE `user` 
ADD COLUMN `user_role` ENUM('user', 'admin') NOT NULL DEFAULT 'user' 