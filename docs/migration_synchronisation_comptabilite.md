# Migration et synchronisation comptable

## But

Documenter la migration des comptes et la synchronisation comptable, en particulier pour:

- les cotisations;
- les inscriptions evenement;
- les transactions;
- le recalage des imputations.

## Fichiers sources

- [`formulaires/migrer_asso_comptabilite.php`](../formulaires/migrer_asso_comptabilite.php)
- [`action/synchroniser_comptabilite_evenement.php`](../action/synchroniser_comptabilite_evenement.php)
- [`inc/comptes.php`](../inc/comptes.php)
- [`inc/association_comptabilite.php`](../inc/association_comptabilite.php)
- [`genie/association_maintenance_bdd.php`](../genie/association_maintenance_bdd.php)

## Mode migration

Deux modes existent:

- `auto`;
- `manuelle`.

### Mode manuel

Le mode manuel:

- selectionne les imputations existantes a migrer;
- demande les comptes cibles pour les creances et les paiements;
- recalcule les imputations selon le statut cotisation.

### Mode auto

Le mode auto:

1. nettoie certaines donnees;
2. applique les nouvelles imputations selon la config;
3. synchronise les evenements ayant une transaction.

## Fonctions clefs

- `appliquer_migration_manuelle()`;
- `appliquer_migration_auto()`;
- `get_config_plan_comptable_migration()`;
- `synchroniser_comptabilite_evenement()`;
- `nettoyer_doublons_comptabilite()`.

## Regles de migration

### Cotisations

- si la cotisation est payee, l'imputation cible est le compte de paiement;
- sinon, l'imputation cible est le compte de creance;
- la justification est reconstruite pour produire un libelle plus lisible.

### Activites

- les lignes `objet='evenement'` avec transaction sont alignees sur le compte creance ou paiement selon le statut de la transaction;
- les remboursements sont conserves comme lignes distinctes.

## Synchronisation des evenements

La synchronisation evenement:

- recalcul le lien entre activite, transaction et ecriture comptable;
- gere les remboursements;
- nettoie les doublons de compta si necessaire.

## Configuration liee

- `pc_cotisations_creance`;
- `pc_cotisations_paiement`;
- `pc_activites_creance`;
- `pc_activites_paiement`;
- `pc_activites_frais`;
- `destinations`;
- `comptes`.

## Points de vigilance

- Le mode auto peut declencher des nettoyages importants.
- Le dry-run doit etre privilegie avant toute action destructive.
- La migration touche a la fois les comptes, les transactions et l'historique d'activites.

