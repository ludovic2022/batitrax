-- Ajout de la colonne logo dans la table accounts
ALTER TABLE `accounts`
  ADD COLUMN `logo` VARCHAR(255) DEFAULT NULL;
