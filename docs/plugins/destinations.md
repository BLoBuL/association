# Dépendance - `destinations`

## Rôle dans Blobul

Le plugin / socle `destinations` est utilisé pour la ventilation comptable.

Blobul l'utilise pour:

- préparer les destinations comptables;
- associer des écritures aux bonnes classes et sous-classes;
- importer / migrer des plans comptables;
- alimenter les formulaires de configuration comptable.

## Déclaration

Le plugin est utilisé par le code Blobul et référencé dans les formulaires de configuration comptable.

## Utilisations principales

- `inc/destinations`;
- `preparer_liste_asso_destination_comptable()`;
- configuration des destinations dans `configurer_association`;
- import des destinations comptables.

## Zones du plugin qui en dépendent

- plan comptable;
- cotisations;
- activités / participations;
- dons;
- ventes;
- prêts.

## Risques d'intégration

- si la hiérarchie des destinations change, les écritures peuvent se retrouver mal classées;
- un import de destinations doit rester compatible avec les écritures historiques;
- les formulaires de configuration doivent rester alignés avec les codes comptables existants.

## A lire en plus

- [`../plan_comptable_destinations.md`](../plan_comptable_destinations.md)
- [`../migration_synchronisation_comptabilite.md`](../migration_synchronisation_comptabilite.md)
