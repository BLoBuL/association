# Dépendance - `Facteur`

## Rôle dans Blobul

`Facteur` est la brique d'envoi email utilisée par Blobul pour:

- envoyer les notifications métier;
- envoyer les reçus;
- gérer l'envoi asynchrone via la file de travaux;
- harmoniser les mails HTML / texte selon les modèles SPIP.

## Statut dans le projet

`Facteur` est une dépendance d'exécution, fortement utilisée par le code, même si elle n'est pas forcément déclarée comme `necessite` directe dans `paquet.xml`.

## Utilisations principales

- `facteur_envoyer_app()`;
- `job_queue_add()` pour l'envoi différé;
- rendu des squelettes `notifications/*.html`;
- gestion des BCC et de la normalisation des destinataires.

## Zones du plugin qui en dépendent

- notifications de cotisation;
- notifications d'échéance;
- notifications événement;
- reçus d'adhésion;
- reçus de participation;
- emails collectifs;
- notifications GIS.

## Ce qu'il faut garder stable

- les modes d'envoi synchrone / asynchrone;
- le format du contexte passé aux squelettes;
- les paramètres BCC et destinataires multiples;
- la compatibilité HTML / texte brut des templates.

## Risques d'intégration

- une évolution de l'API de file de travaux peut casser l'envoi différé;
- un changement de signature d'envoi peut impacter plusieurs familles de notifications;
- les erreurs de rendu des squelettes peuvent bloquer des flux métier entiers.

## A lire en plus

- [`../notifications.md`](../notifications.md)
- [`../notifications_evenements.md`](../notifications_evenements.md)
- [`../notifications_cotisations.md`](../notifications_cotisations.md)
