# Dépendance - `mailsubscribers`

## Rôle dans Blobul

`mailsubscribers` sert de socle aux listes de diffusion et aux inscriptions email liées au compte adhérent.

Blobul l'utilise pour:

- rattacher des auteurs à des listes de diffusion;
- nettoyer les inscriptions orphelines;
- synchroniser certains emails de notification;
- alimenter les actions de maintenance.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 3.7.0`

## Utilisations principales

- lecture des listes de diffusion;
- création / suppression des inscriptions mails;
- nettoyage des abonnements orphelins;
- liaison entre auteur et segments d'emailing.

## Zones du plugin qui en dépendent

- notifications événement;
- maintenance BDD;
- notifications d'échéance;
- actions de segmentation.

## Risques d'intégration

- les inscriptions orphelines doivent être nettoyées avec prudence;
- les emails multiples doivent être normalisés;
- la suppression en maintenance ne doit pas casser des mailshots actifs.

## A lire en plus

- [`../maintenance_cron.md`](../maintenance_cron.md)
- [`../notifications.md`](../notifications.md)
