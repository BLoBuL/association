# Dépendance - `newsletter`

## Rôle dans Blobul

La brique newsletter représente le versant "listes de diffusion" et abonnements email du projet.

Blobul l'utilise pour:

- gérer les abonnements liés aux auteurs;
- alimenter les relances collectives;
- synchroniser les listes de diffusion avec les données adhérents;
- nettoyer les inscriptions orphelines lors de la maintenance.

## Statut dans le projet

La "newsletter" n'est pas toujours un plugin unique dans le sens strict du terme.
Dans Blobul, elle désigne la couche fonctionnelle qui s'appuie notamment sur `mailsubscribers` et les objets de diffusion associés.

## Utilisations principales

- listes de diffusion;
- abonnements / désabonnements;
- segmentations d'audience;
- envois groupés préparés depuis le back-office.

## Zones du plugin qui en dépendent

- maintenance BDD;
- email collectif;
- notifications et relances ciblées;
- synchronisation des membres.

## Ce qu'il faut garder stable

- les identifiants des listes;
- le lien entre auteur et adresse email;
- la normalisation des destinataires;
- la logique de nettoyage des abonnements orphelins.

## Risques d'intégration

- une modification des listes peut impacter des campagnes en cours;
- un nettoyage trop agressif peut supprimer des destinataires utiles;
- les doublons d'email doivent être filtrés avant insertion.

## A lire en plus

- [`mailsubscribers`](./mailsubscribers.md)
- [`mailshot`](./mailshot.md)
- [`../maintenance_cron.md`](../maintenance_cron.md)
