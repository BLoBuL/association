# Dépendance - `verifier`

## Rôle dans Blobul

`verifier` apporte des aides de validation et de contrôle utilisées dans la configuration et certains formulaires.

Blobul l'utilise comme brique de validation complémentaire au-dessus des formulaires SPIP.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 2.1.0`

## Utilisations principales

- validations de formulaires complexes;
- vérifications métier dans les parcours de saisie;
- compléments de contrôle dans la configuration.

## Risques d'intégration

- une règle de vérification plus stricte peut bloquer des flux historiques;
- les messages d'erreur doivent rester compréhensibles et localisés;
- les contrôles serveur doivent toujours rester synchronisés avec les champs visibles.

## A lire en plus

- [`../journalisation_debug.md`](../journalisation_debug.md)
- [`../normalisation_corpus.md`](../normalisation_corpus.md)
