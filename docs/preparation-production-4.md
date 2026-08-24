# Préparation de la mise en production — Association 4

## Composition obligatoire

La suite 4.0 comprend les préfixes suivants :

- `association` ;
- `association_adhesions` ;
- `association_evenements` ;
- `association_compta` ;
- `association_paiements` ;
- `association_groupes` ;
- `association_prets` ;
- `association_dons` ;
- `association_ventes` ;
- `association_communication`.

Aucun plugin Blobul (`blobul_*`, `zblobul_*` ou dossier `blobul-*`) n’est une
dépendance. Les dépendances tierces sont déclarées par le domaine qui les
utilise : Inscription 4, Agenda, Bank, Intl, Champs Extras, Saisies, Vérifier,
Notifications et Mailsubscribers.

## Migration d’un site historique

1. installer les neuf modules métier sans désactiver le socle historique ;
2. activer les modules puis exécuter `spip plugins:maj:bdd` ;
3. contrôler les volumes des tables avant toute suppression de dossier ;
4. remplacer le dossier historique par `plugins/association` ;
5. vider le cache, réactiver `association`, puis rejouer les migrations ;
6. vérifier que les dix préfixes sont actifs depuis des dossiers `association*` ;
7. vérifier les pages privées et publiques sans déclencher de paiement ni
   d’envoi réel pendant la recette.

Les installateurs des modules adoptent les tables existantes avec
`maj_tables()`. Les schémas `association_adhesions` et
`association_evenements` sont en version `1.2.0` afin d'inclure tous les champs
nécessaires à une première installation, et pas seulement les colonnes déjà
présentes sur un site historique. Leur désinstallation efface seulement la
méta de version et ne supprime aucune donnée métier.

Les colonnes historiques `reinscription`, `statut_cotisation` et
`id_categorie` peuvent encore exister physiquement dans `spip_asso_comptes`
après migration. Elles ne sont plus déclarées par le module Comptabilité et ne
doivent être supprimées qu'après avoir vérifié que chaque ancienne écriture de
cotisation possède sa ligne correspondante dans `spip_asso_cotisations`.

## Portes de sortie avant production

- syntaxe PHP 8 et tests métier verts ;
- compilation des squelettes privés et publics sous SPIP 4 ;
- installation à blanc et migration d’un jeu de données historique ;
- volumes de tables identiques avant/après extraction ;
- aucune dépendance ou copie active d’un plugin Blobul ;
- recette HTTP desktop/mobile des pages publiques ;
- recette visuelle authentifiée des pages BO et FO ;
- branches `4.x` propres et poussées pour les dix dépôts ;
- versions, états, tags et archives alignés au moment de la release.
