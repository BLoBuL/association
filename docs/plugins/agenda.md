# Dépendance - `agenda`

## Rôle dans Blobul

`agenda` fournit le socle des événements calendaires.

Blobul s'en sert pour:

- éditer et publier des événements;
- gérer les dates de début et de fin;
- lire les répétitions;
- normaliser le fuseau horaire des saisies.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 4.5.0`

## Utilisations principales

- formulaire d'édition d'événement;
- lecture des événements liés aux articles;
- logique de répétition;
- affichage et conversions de dates.

## Points d'intégration

- `formulaires/editer_evenement.php`;
- `association_fonctions.php`;
- helpers liés au fuseau horaire;
- notifications événement.

## Risques d'intégration

- les changements de format de date peuvent impacter les formulaires;
- les répétitions doivent rester synchronisées avec les occurrences réelles;
- les conversions de timezone doivent rester cohérentes entre BO et FO.

## A lire en plus

- [`../evenements_formulaires_inscription.md`](../evenements_formulaires_inscription.md)
- [`../parametrage_evenements.md`](../parametrage_evenements.md)
