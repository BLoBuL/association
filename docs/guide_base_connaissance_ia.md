# Guide d'exploitation de la base de connaissance Blobul

## But

Donner aux agents une porte d'entree simple et stable pour exploiter la documentation Blobul.

L'objectif est d'avoir une base de connaissance:

- lisible par les humains;
- navigable par les IA;
- segmentee par familles metier;
- assez stable pour etre referencee depuis les fichiers `AGENTS.md`.

## Regle de lecture

Ne pas charger tout le corpus d'un coup.

Lire d'abord:

1. le `README.md` du dossier documentation;
2. la doc du domaine traite;
3. les documents de reference rattaches;
4. seulement ensuite les fichiers techniques source.

## Familles documentaires

### Configuration

- configuration generale du plugin;
- inventaire des metas;
- parametres evenement;
- parametres cotisation;
- parametres paiement;
- plan comptable.

### Metier

- cotisations;
- participations financieres;
- evenements;
- dons;
- ventes;
- ressources;
- commandes et factures.

### Technique

- formulaires;
- notifications;
- maintenance;
- cron;
- journalisation;
- import / export.

### Cartographie

- inventaire des fonctions;
- matrice des appels;
- plan de chargement;
- plan de nommage;
- priorisation des refactors.

## Ordre de lecture recommande

### Si tu travailles sur les evenements

1. [`parametrage_evenements.md`](./parametrage_evenements.md)
2. [`evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
3. [`categories_participation_financiere.md`](./categories_participation_financiere.md)
4. [`tarifs-logique.md`](./tarifs-logique.md)
5. [`comptabilite_evenements.md`](./comptabilite_evenements.md)

### Si tu travailles sur les cotisations

1. [`categories_cotisation.md`](./categories_cotisation.md)
2. [`modes_paiement.md`](./modes_paiement.md)
3. [`notifications_cotisations.md`](./notifications_cotisations.md)
4. [`notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
5. [`notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)
6. [`notifications_echeances.md`](./notifications_echeances.md)

### Si tu travailles sur la configuration globale

1. [`configurer_association.md`](./configurer_association.md)
2. [`configurer_association_inventaire.md`](./configurer_association_inventaire.md)
3. [`guide_base_connaissance_ia.md`](./guide_base_connaissance_ia.md)

## Sites et projets

La base de connaissance est organisee pour permettre un chargement par site ou par projet.

Sites prioritaires:

- `www.blobul.com` : portail;
- `assistance.blobul.com` : support et futur espace client;
- `fiafe.blobul.com` : demo FIAFE;
- `institutfrancais.blobul.com`;
- `alliancefrancaise.blobul.com`.

Sites de developpement:

- `dev.blobul.com` : PHP 8.4 / SPIP 4.4;
- `test-fiafe.blobul.com` : historique PHP 7.4 / SPIP 3.2;
- `test-ape.blobul.com` : PHP 8.4 / SPIP 4.4.

## Rappels d'utilisation pour les AGENTS

- charger seulement la famille documentaire utile;
- preferer les docs de synthese avant le code source;
- garder les docs synchronisees avec les formulaires et les metas;
- documenter les logiques metier avant de documenter l'interface utilisateur;
- noter les dependances inter-sites quand un comportement est mutualise.

## Points de vigilance

- Les docs sont des vues techniques, pas des spec figées.
- Les listes de champs et de metas doivent etre revues si le code change.
- Un changement de comportement dans un helper peut impacter plusieurs sites.
- Les docs d'un projet cible doivent rester compatibles avec son AGENTS local.

## Normalisation

Pour maintenir la qualité du corpus:

- preferer une page = un sujet;
- relier chaque page aux fichiers source utiles;
- garder les titres et les noms de fichiers stables;
- separer la synthese, le detail technique et les cas limites;
- eviter les doublons entre pages de meme famille.

La page [`normalisation_corpus.md`](./normalisation_corpus.md) precise ces regles de redaction et de chargement.
