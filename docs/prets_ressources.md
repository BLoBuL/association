# Prets et ressources

## But

Documenter la gestion des ressources pretable et des prets:

- creation et modification d'une ressource;
- creation, modification et suppression d'un pret;
- lien eventuel avec la comptabilite;
- affichage prive et operationnel.

## Fichiers sources

- [`formulaires/editer_asso_ressources.php`](../formulaires/editer_asso_ressources.php)
- [`formulaires/editer_asso_pret.php`](../formulaires/editer_asso_pret.php)
- [`formulaires/editer_asso_pret.html`](../formulaires/editer_asso_pret.html)
- [`action/supprimer_prets.php`](../action/supprimer_prets.php)
- [`inc/comptes.php`](../inc/comptes.php)
- [`inc/association_comptabilite.php`](../inc/association_comptabilite.php)

## Donnees principales

### Ressources

Table:

- `spip_asso_ressources`

Champs clefs:

- `code`;
- `intitule`;
- `date_acquisition`;
- `pu`;
- `statut`;
- `commentaire`.

### Prets

Table:

- `spip_asso_prets`

Champs clefs:

- `id_ressource`;
- `date_sortie`;
- `duree`;
- `date_retour`;
- `id_emprunteur`;
- `statut`;
- `commentaire_sortie`;
- `commentaire_retour`.

## Formulaire ressource

Le formulaire de ressource:

- charge ou initialise la ressource;
- force un statut `ok` par defaut;
- pre-remplit la date d'acquisition;
- formate le prix unitaire;
- adapte le titre du formulaire selon creation ou edition.

## Validation ressource

La validation controle:

- prix unitaire positif;
- date d'acquisition valide.

## Traitement ressource

Le traitement convertit les dates saisies puis delegate a `formulaires_editer_objet_traiter`.

## Cycle pret

Le pret suit une logique simple:

1. creation du pret;
2. modification de la duree, des dates ou de l'emprunteur;
3. suppression si besoin.

## Points de vigilance

- Le code contient encore des commentaires `TODO` sur le traitement des identifiants.
- Les interactions comptables semblent presentes mais moins structurantes que pour dons, ventes ou evenements.
- La doc utilisateur devra distinguer clairement ressource et pret.

