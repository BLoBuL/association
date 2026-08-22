# Notifications d'echeances

## But

Documenter les notifications d'echeance liees aux cotisations, avec leur logique de calendrier, leurs delais et leur routage technique.

## Fichiers de reference

- [`notifications.md`](./notifications.md)
- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/fonctions/association_job_notifier_echeance.php`](../inc/fonctions/association_job_notifier_echeance.php)
- [`genie/association_taches_generales.php`](../genie/association_taches_generales.php)
- [`notifications/`](../notifications/)
- [`lang/notifications_fr.php`](../lang/notifications_fr.php)

## Flux actuel

- Le cron parcourt les auteurs actifs.
- Les delais sont calculés selon le type d'adhérent si le champ existe.
- Les notifications sont planifiees via la file de travaux.
- Le routage final passe par le helper de cotisation commun.

## Parametrage

Les delais s'appuient sur:

- `notification_adherent_echu`;
- `notification_echeance_cotisation`;
- `notification_echeance_cotisation_entreprise`;
- la configuration de validite et de type d'adhérent.

Point de vigilance fonctionnel :

- les delais choisis doivent laisser assez de temps pour un renouvellement serein ;
- ils ne doivent pas etre regles seulement du point de vue technique du cron ;
- un delai trop court augmente le risque de faire passer inutilement des comptes dans `echu`, avec perte de privileges associes.

## Contenu des messages

Les notifications d'echeance doivent transmettre:

- le statut de l'adhésion;
- la date de validité;
- le delai restant ou le fait que l'adhésion soit échue;
- un appel a action clair.

## Point technique important

La logique a été unifiée pour passer par `association_job_notifier_echeance()` puis `notifier_cotisation_adherent()`.
Cela permet d'utiliser les mêmes familles de gabarits que les autres notifications de cotisation.

## Points de vigilance

- Les entreprises et les adhérents individuels doivent pouvoir diverger dans le wording si nécessaire.
- Les dates doivent être lues avec prudence car elles pilotent l'envoi.
- La file de travaux doit rester surveillée pour les envois différés.

## A lire en plus

- [`notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
- [`notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)
- [`maintenance_cron.md`](./maintenance_cron.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
