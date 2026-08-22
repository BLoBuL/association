# Architecture de la suite Association 4

## Statut

Ce document décrit la cible de refactorisation de la branche `4.x`. Le code
reste temporairement monolithique pendant l'extraction. Les règles métier du BO
6.8 continuent de faire référence tant qu'un domaine n'a pas été caractérisé,
migré et testé dans son plugin propriétaire.

## Modules cibles

| Plugin | Préfixe | Objets ou données propriétaires | Pipelines principaux |
|---|---|---|---|
| Association | `association` | configuration commune et journal de migration | configuration, migration, anonymisation |
| Adhésions | `association_adhesions` | catégories d'adhésion, `spip_asso_cotisations` et validité | formulaires auteurs, notifications, segmentation |
| Événements | `association_evenements` | inscriptions, participants et tarifs | Agenda, CVT d'inscription, quotas et listes d'attente |
| Comptabilité | `association_compta` | `spip_asso_comptes`, plan, destinations et exercices | écritures produites par les modules métier |
| Paiements | `association_paiements` | correspondances métier avec commandes et transactions | BANK et retours serveur |
| Groupes | `association_groupes` | groupes, fonctions, rôles et liens | autorisations et accès restreints |
| Prêts | `association_prets` | ressources, réservations, cautions et prêts | écritures comptables optionnelles |
| Dons | `association_dons` | dons et contreparties | reçus, comptabilité et paiement optionnel |
| Ventes | `association_ventes` | ventes et expéditions | comptabilité et paiement optionnel |
| Communication | `association_communication` | gabarits et préférences propres à la suite | Notifications, Mailshot et Mailsubscribers |

## Règles SPIP

Chaque plugin publié suit la structure d'un plugin-dist SPIP :

```text
action/
base/<prefixe>.php
formulaires/
inc/
lang/
prive/
tests/
<prefixe>_administrations.php
<prefixe>_autoriser.php
<prefixe>_fonctions.php
<prefixe>_options.php
<prefixe>_pipelines.php
paquet.xml
composer.json
README.md
```

Seuls les dossiers utiles au plugin sont créés. Un fichier `*_options.php` ne
doit contenir que le chargement indispensable à chaque hit. Les déclarations
SQL restent dans `base/`, les autorisations dans `*_autoriser.php` et les hooks
dans `*_pipelines.php`.

Les objets éditoriaux utilisent `declarer_tables_objets_sql`, l'API d'édition
des objets et les tables de liens SPIP. Une table purement métier peut utiliser
la couche SQL SPIP lorsque le modèle d'objet ne convient pas ; l'accès est alors
centralisé dans une API publique du module propriétaire.

## Contrats

- aucune inclusion d'un fichier interne appartenant à un autre module ;
- aucune écriture directe dans la table d'un autre module ;
- dépendance obligatoire déclarée dans `paquet.xml` ;
- intégration optionnelle par pipeline public documenté ;
- fonctions publiques préfixées par le plugin propriétaire ;
- wrappers historiques minces et temporaires ;
- statuts transactionnel, commande et métier toujours distincts.

## Ordre d'extraction

1. stabiliser le Socle et son registre de configuration ;
2. extraire Adhésions, déjà couvert par des tests métier étendus ;
3. extraire Événements et conserver Agenda comme propriétaire des événements ;
4. extraire Paiements autour des contrats BANK ;
5. extraire Comptabilité et introduire les exercices ;
6. extraire Groupes, Prêts, Dons, Ventes et Communication ;
7. réduire le Socle aux migrations, contrats partagés et configuration commune.

Chaque extraction exige une matrice de traçabilité 2.1 / 2.2 / 6.8 / 4.x,
des tests de caractérisation et une migration idempotente.

## Frontières confirmées

- les plugins Blobul externes restent hors périmètre : aucune modification de
  leur code et aucune dépendance implicite vers eux ;
- `spip_asso_comptes` est exclusivement le journal du module Comptabilité ;
- une cotisation existe dans `spip_asso_cotisations`, avec un lien optionnel
  `id_compte` vers son écriture comptable ;
- Association 4 nécessite Inscription 4. Le namespace de configuration
  `inscription3` et les pipelines `i3_*` restent utilisés uniquement parce
  qu'Inscription 4 les expose comme API de compatibilité documentée.
