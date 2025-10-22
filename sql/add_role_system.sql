-- Script pour ajouter le système de rôles au projet
-- Date: 2025-10-22

-- 1. Ajouter la colonne user_role à la table user
ALTER TABLE `user` 
ADD COLUMN `user_role` ENUM('user', 'admin') NOT NULL DEFAULT 'user' 
AFTER `user_mail`;