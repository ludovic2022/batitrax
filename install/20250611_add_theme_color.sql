-- Migration: ajout de la colonne theme_color
ALTER TABLE accounts
  ADD COLUMN theme_color VARCHAR(7) NOT NULL DEFAULT '#ffffff';
