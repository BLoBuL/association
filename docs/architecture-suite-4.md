# Architecture de la suite Association 4

## Statut

La branche `4.x` est distribuée depuis un monorepo sous la forme d’un socle et
de neuf plugins métier. Les tables, dépendances externes et pages publiques ont été transférées
à leur propriétaire. Le socle conserve encore les services de compatibilité du
BO 6.8 afin que la première livraison 4.0 n’impose aucune perte fonctionnelle ;
leur déplacement interne pourra ensuite se faire sans migration de données.

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

Le socle est à la racine du dépôt. Chaque module métier est un plugin autonome
dans `plugins/<nom-du-plugin>`. Le dossier déployé porte le nom du plugin, sans
préfixe `blobul-`. Les anciens dépôts séparés sont uniquement des sources
historiques : le développement, les commits et les versions partent désormais
de ce monorepo.

L'extraction des implémentations est suivie dans
[`extraction-logiques-socle.md`](./extraction-logiques-socle.md). Les lots Dons,
Ventes, Prêts, Comptabilité, Adhésions, Événements, Communication, Groupes et
Paiements possèdent désormais leurs actions, CVT, pages, autorisations, options
et pipelines métier. Le socle conserve les contrats transversaux, la
configuration commune et la compatibilité de migration.

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

Le contrat `association_rgpd_export_auteur` illustre l'intégration attendue :
le socle fournit l'enveloppe, chaque module écrit uniquement sa clé dans
`$flux['data']`, et aucun collecteur ne parcourt directement la table d'un autre
module.

## État de l’extraction 4.0

1. le Socle conserve `spip_association_metas`, la configuration et les
   migrations de compatibilité ;
2. Adhésions possède les catégories et `spip_asso_cotisations` ;
3. Événements possède les catégories, tarifs et `spip_asso_activites` ;
4. Comptabilité possède `spip_asso_comptes`, le plan et les destinations ;
5. Prêts, Dons et Ventes possèdent leurs tables respectives ;
6. Paiements porte la dépendance Bank ;
7. Communication porte Notifications et Mailsubscribers ;
8. Groupes porte Champs Extras ;
9. les pages publiques profil/adhésion/inscription sont dans Adhésions et la
   page événement dans Événements.

Chaque extraction exige une matrice de traçabilité 2.1 / 2.2 / 6.8 / 4.x,
des tests de caractérisation et une migration idempotente.

## Frontières confirmées

- les plugins Blobul externes restent hors périmètre : aucune modification de
  leur code et aucune dépendance implicite vers eux ;
- aucun plugin dont le préfixe ou le dossier commence par `blobul` ou `zblobul`
  n’est nécessaire au fonctionnement de la suite ;
- `spip_asso_comptes` est exclusivement le journal du module Comptabilité ;
- une cotisation existe dans `spip_asso_cotisations`, avec un lien optionnel
  `id_compte` vers son écriture comptable ;
- Association 4 nécessite Inscription 4. Le namespace de configuration
  `inscription3` et les pipelines `i3_*` restent utilisés uniquement parce
  qu'Inscription 4 les expose comme API de compatibilité documentée.
