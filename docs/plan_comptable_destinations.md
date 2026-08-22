# Plan comptable et destinations

## But

Documenter la gestion du plan comptable, des destinations comptables et de leur utilisation dans le plugin.

## Fichiers sources

- [`formulaires/editer_asso_plan.php`](../formulaires/editer_asso_plan.php)
- [`formulaires/editer_asso_destinations.php`](../formulaires/editer_asso_destinations.php)
- [`formulaires/importer_plan_comptable.php`](../formulaires/importer_plan_comptable.php)
- [`formulaires/importer_destination_comptable.php`](../formulaires/importer_destination_comptable.php)
- [`inc/association_comptabilite.php`](../inc/association_comptabilite.php)
- [`inc/destinations.php`](../inc/destinations.php)
- [`inc/comptes.php`](../inc/comptes.php)

## Structure de donnees

### Plan comptable

Table:

- `spip_asso_plan`

Champs clefs:

- `code`;
- `intitule`;
- `classe`;
- `type_op`;
- `solde_anterieur`;
- `date_anterieure`;
- `commentaire`;
- `active`.

### Destinations

Table:

- `spip_asso_destination`

Table de ventilation:

- `spip_asso_destination_op`

Champs clefs:

- `id_destination`;
- `recette`;
- `depense`;
- `id_compte`.

## Formulaire du plan comptable

Le formulaire:

- charge les champs d'une ligne du plan;
- fixe des valeurs par defaut pour une creation;
- valide la structure du code;
- interdit les doublons de code;
- convertit les dates au format attendu.

### Regles de validation

- le code doit commencer par le numero de classe;
- le code doit etre unique;
- la date ancienne doit etre valide.

## Formulaire des destinations

Le formulaire destination:

- charge une destination simple;
- permet la modification ou la creation;
- delegue ensuite au traitement standard des objets SPIP.

## Import JSON

Les formulaires d'import:

- lisent un fichier JSON;
- decomposent recursivement les arbres de comptes ou de destinations;
- preparant des saisies de selection pour valider ce qui doit etre importe.

Fonctions importantes:

- `lister_comptes_json()`;
- `lister_compte_recurive()`;
- `lister_destinations_json()`;
- `lister_destination_recurive()`;
- `generer_saisies_comptes()`;
- `generer_saisies_destinations()`.

## Utilisation metier

Le plan comptable et les destinations sont consommes par:

- les cotisations;
- les dons;
- les ventes;
- les activites/evenements;
- la migration comptable;
- l'affichage prive des operations.

## Configuration liee

- `comptes`;
- `classe_banques`;
- `destinations`;
- `pc_*`;
- `dc_*`.

### Onglet relie

Ces cles sont configurees dans l'onglet `comptabilite` de `configurer_association`.

## Points de vigilance

- Les destinations ne doivent etre actives que si la brique est configuree.
- Un mauvais mapping de code peut casser la migration ou les saisies.
- Les imports JSON sont recursifs et doivent etre controles avant fusion en base.
