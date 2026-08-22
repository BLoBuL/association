# Commandes et factures

## But

Documenter le volet commandes/factures tel qu'il apparait dans le depot.

## Etat du code

Le dépôt contient quelques points d'intégration facultatifs :

- une action de suppression de commande;
- une synchronisation comptable des commandes envoyees vers `spip_asso_comptes`.

La logique metier reste plus legere que pour les cotisations, les ventes ou les evenements, mais le flux comptable minimal est maintenant explicite.

## Fichiers sources

- [`inc/comptes.php`](../inc/comptes.php)
- [`association_pipelines.php`](../association_pipelines.php)
- [`action/supprimer_commande.php`](../action/supprimer_commande.php)

## Observation technique

### Suppression

L'action de suppression:

- securise l'appel;
- lit un `id_transaction`;
- supprime la transaction associee dans `spip_transactions`.

Cela ressemble davantage a une gestion de transaction bancaire qu'a un vrai sous-systeme commande autonome.

L'ancienne miniature isolée a été retirée : elle n'avait aucun appelant et
imposait la table `COMMANDES` à la compilation même lorsque le plugin Commandes
n'était pas installé. L'intégration restante vérifie l'existence des tables ou
du plugin avant toute lecture.

### Comptabilite

La synchronisation comptable des commandes est portee par `association_commande_comptable_synchroniser()`.

Regles actuelles:

- une commande sans `date_envoi` et sans paiement ne cree pas d'ecriture;
- a l'envoi de la commande, une ecriture `spip_asso_comptes` est creee ou mise a jour avec `objet=commande` et `id_objet=id_commande`;
- cette ecriture est non validee (`vu=0`) tant que le paiement n'est pas constate;
- au retour Bank reussi, la meme ecriture est validee (`vu=1`), rattachee a `id_transaction` et datee sur la date de paiement;
- la synchronisation est idempotente pour eviter les doublons lors d'une reprise technique ou d'un webhook rejoue.

Imputations:

- creance: `pc_commandes_creance` si configure, sinon `pc_activites_creance`, sinon `101`;
- paiement: `pc_commandes_paiement` si configure, sinon `pc_ventes`, sinon `pc_activites_paiement`, sinon `701`.

Le montant vient en priorite de la transaction rattachee a la commande. A defaut, le code tente les champs de montant de la commande puis les lignes `spip_commandes_details`.

## Hypothese fonctionnelle

Cette partie semble etre:

- une brique connectee au circuit Assistance/commandes/factures;
- un point d'accroche vers le socle bancaire/transaction;
- une source d'ecritures de creance puis de paiement pour la comptabilite interne Blobul.

## Points de vigilance

- Cette zone doit etre revue avant toute documentation utilisateur.
- La suppression de transaction est une action sensible.
- Une commande preparee sans envoi ne doit pas creer d'ecriture comptable.
- Le hook Bank ne doit pas creer de doublon si l'ecriture a deja ete creee a l'envoi.

