# Batitrax

## Installation initiale
1. Uploadez les dossiers `Batitrax`, `api`, et `install` à la racine de votre sous-domaine (pointant vers `Batitrax`).
2. Exécutez `install/install.php` pour créer les tables `accounts` et `users` et l’utilisateur superadmin.


## Mise à jour de schéma
Après l’évolution, pour ajouter les tables `projects` et `messages`, exécutez :
```
http://batitrax.menuiserie-rieu.fr/install/update_schema.php
```

## Fonctionnalités
- **Superadmin** : création/suppression de comptes, création/suppression/utilisateur/changement de mot de passe.
- **Admin** : renommage de son compte, gestion des utilisateurs (CRUD + mot de passe), création de projets, messagerie interne.
- **Utilisateurs** : accès aux projets, envoi et lecture de messages (UI type WhatsApp).
