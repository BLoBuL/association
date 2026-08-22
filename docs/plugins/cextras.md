# Dépendance - `cextras`

## Rôle dans Blobul

`cextras` permet de définir et exploiter des champs extra sur les auteurs et certains objets métier.

Blobul l'utilise pour:

- enrichir la fiche auteur;
- configurer des champs supplémentaires pour les cotisations et les adhérents;
- alimenter des filtres et des recherches;
- rendre certaines règles de saisie conditionnelles.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 3.0.5`

## Utilisations principales

- lecture et filtrage des champs extras;
- intégration dans les formulaires d'inscription auteur;
- aides à la recherche avancée des adhérents;
- certains comportements de notifications ou de segmentation.

## Points d'intégration

- `inc/cextras`;
- définition des champs dans les pipelines;
- lecture de la configuration des champs affichés ou obligatoires.

## Risques d'intégration

- un champ extra renommé peut casser les filtres, les exports ou les formulaires;
- les champs conditionnels doivent rester cohérents avec les validations;
- la structure de configuration de `cextras` doit être traitée comme une donnée technique stable.

## A lire en plus

- [`../recherche_adherents_segmentation.md`](../recherche_adherents_segmentation.md)
- [`../guide_base_connaissance_ia.md`](../guide_base_connaissance_ia.md)
