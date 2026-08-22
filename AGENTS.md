# Association — consignes de travail

Ce dépôt est développé selon le profil DEV Blobul et les conventions SPIP 4.

Avant toute analyse, modification, recette ou déploiement :

1. lire `D:\Codex\repos\base_connaissance_blobul\kb\technique-design\15-regles-developpement-spip-blobul.md` ;
2. lire `D:\Codex\repos\base_connaissance_blobul\kb\technique-design\17-anti-patterns-developpement-blobul.md` ;
3. pour les squelettes, lire `D:\Codex\repos\base_connaissance_blobul\kb\technique-design\14-syntaxe-spip-squelettes.md` ;
4. pour les opérations distantes, lire la procédure BDC correspondant exactement à l'action et au site ;
5. vérifier l'état Git et préserver toute modification étrangère à la tâche.

## Cible technique

- SPIP 4 minimum, PHP 8 minimum ;
- privilégier les API, pipelines, autorisations, formulaires CVT et structures natives de SPIP ;
- conserver tous les plugins de la suite dans ce dépôt Git unique ;
- le cœur reste à la racine et les plugins métier autonomes sont rangés sous `plugins/` ;
- chaque plugin conserve son propre `paquet.xml`, ses dépendances, son schéma et ses tests ;
- `spip_asso_comptes` appartient à la comptabilité et les cotisations utilisent `spip_asso_cotisations` ;
- `inscription4` est la dépendance d'inscription de référence ;
- les plugins Blobul historiques restent hors dépendances du nouveau socle.

## Validation

- tester installation propre et migrations prévues ;
- exécuter lint, tests automatisés, compilation des squelettes et contrôle des journaux SPIP ;
- compléter par une recette navigateur authentifiée du privé et une recette publique responsive ;
- utiliser l'incarnation SPIP pour les profils métier sans manipuler un gestionnaire de mots de passe ;
- ne pas déclarer la recette complète sans preuve visuelle des parcours servis.

## Déploiement

- identifier le chemin SPIP actif avant toute copie ;
- suivre la BDC pour sauvegarde, déploiement, cache, versions et preuve serveur ;
- ne jamais afficher ni conserver un secret ; utiliser uniquement son nom logique via le coffre Blobul.
