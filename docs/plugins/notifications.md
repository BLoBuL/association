# Dépendance - `notifications`

## Rôle dans Blobul

Le plugin `notifications` fournit le socle de rendu et d'envoi des notifications SPIP.

Blobul l'utilise pour:

- rendre les squelettes de notifications;
- organiser les modèles de mails;
- coupler les sujets traduits et les contenus HTML;
- alimenter Facteur / la file de travaux.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 3.7.0`

## Utilisations principales

- squelettes `notifications/*.html`;
- inclusions de blocs communs;
- clés de langue `notifications:*`;
- envoi via les helpers métier Blobul.

## Zones du plugin qui en dépendent

- notifications de cotisation;
- notifications d'échéance;
- notifications de participation événement;
- notifications GIS;
- emails collectifs.

## Ce qu'il faut garder stable

- les clés de langue utilisées par les squelettes;
- les inclusions partagées;
- les conventions de contexte dans les templates.

## Risques d'intégration

- une clé de langue manquante casse le rendu ou produit du texte brut;
- une modification de squelette peut toucher plusieurs flux métier;
- les notifications doivent rester compatibles avec l'envoi asynchrone.

## A lire en plus

- [`../notifications.md`](../notifications.md)
- [`../notifications_cotisations.md`](../notifications_cotisations.md)
- [`../notifications_evenements.md`](../notifications_evenements.md)
