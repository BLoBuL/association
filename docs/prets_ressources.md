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

## Identifiants et cohérence du cycle

Depuis le schéma 1.1.1, `id_ressource` et `id_emprunteur` sont des identifiants
`BIGINT` indexés. La création et l'édition refusent une ressource inexistante.
La suppression d'une ressource est refusée tant qu'un prêt historique lui est
rattaché, afin de ne jamais créer de lien orphelin.

Le statut de la ressource est recalculé dans la même transaction que le prêt :

- au moins un prêt sans date de retour : `reserve` ;
- tous les prêts restitués ou supprimés : `ok`.

La suppression d'un prêt retrouve elle-même sa ressource depuis la base. Elle
ne fait plus confiance à un deuxième identifiant transmis dans l'URL signée.
L'édition directe d'une ressource conserve désormais son statut existant au
lieu de le réinitialiser silencieusement à `ok`.

## Points de vigilance

- Les interactions comptables restent moins structurantes que pour dons,
  ventes ou événements, mais l'écriture et le prêt sont modifiés dans une même
  transaction.
- La documentation utilisateur doit distinguer clairement ressource et prêt.

