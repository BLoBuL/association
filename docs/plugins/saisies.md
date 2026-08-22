# Dépendance - `saisies`

## Rôle dans Blobul

Le plugin `saisies` fournit le framework de construction des formulaires utilisé partout dans Blobul.

Blobul s'en sert pour:

- déclarer les champs des formulaires;
- conditionner l'affichage des champs;
- gérer les validations de base;
- construire les formulaires d'édition et d'inscription.

## Déclaration

`saisies` est utilisé de manière transversale par le code Blobul, même quand il n'est pas le coeur métier de la page.

## Utilisations principales

- formulaires de configuration;
- formulaires d'édition d'événements;
- formulaires de cotisation;
- formulaires d'inscription événement;
- formulaires d'import/export;
- formulaires de recherche et de segmentation.

## Ce qu'il faut garder stable

- les syntaxes `afficher_si`;
- les types de saisies supportés;
- les comportements de validation;
- les structures de tableaux de saisies.

## Risques d'intégration

- une évolution de schéma de saisies peut casser les formulaires Blobul;
- les affichages conditionnels doivent rester cohérents entre BO et FO;
- les validations HTML5 doivent rester compatibles avec les contrôles serveur.

## A lire en plus

- [`../evenements_formulaires_inscription.md`](../evenements_formulaires_inscription.md)
- [`../categories_cotisation.md`](../categories_cotisation.md)
- [`../guide_base_connaissance_ia.md`](../guide_base_connaissance_ia.md)
