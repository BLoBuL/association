# Dépendance - `mailshot`

## Rôle dans Blobul

La brique mailshot couvre les envois collectifs préparés et suivis dans le temps.

Blobul l'utilise pour:

- créer un message groupé;
- alimenter une file de destinataires;
- relancer la surveillance de la file;
- envoyer des communications collectives depuis le back-office.

## Statut dans le projet

Comme pour la newsletter, le "mailshot" est surtout une brique fonctionnelle de SPIP / plugin mail associé.
Dans Blobul, il est documenté comme un mécanisme transversal d'envoi collectif.

## Utilisations principales

- création de `spip_mailshots`;
- ajout de `spip_mailshots_destinataires`;
- surveillance de la file de traitement;
- génération du HTML depuis un squelette de notification.

## Exemple d'usage Blobul

L'action de relance collective:

- collecte un sujet;
- prépare le contenu HTML;
- insère le mailshot;
- ajoute les destinataires;
- redémarre la surveillance de la file.

## Zones du plugin qui en dépendent

- `action/envoyer_relances.php`;
- email collectif adhérent;
- campagnes ponctuelles du back-office;
- maintenance et suivi des listes.

## Ce qu'il faut garder stable

- le schéma des tables de mailshot;
- le statut de la file;
- la normalisation des emails;
- la génération du contenu HTML via les squelettes SPIP.

## Risques d'intégration

- une table ou un statut de mailshot changé peut casser les campagnes en cours;
- les doublons de destinataires doivent être évités;
- les relances doivent rester compatibles avec la file SPIP.

## A lire en plus

- [`newsletter`](./newsletter.md)
- [`Facteur`](./facteur.md)
- [`../import_export.md`](../import_export.md)
