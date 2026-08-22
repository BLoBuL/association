# Normalisation du corpus documentaire

## But

Uniformiser la base de connaissance pour qu'elle soit:

- facile a parcourir;
- facile a charger pour une IA;
- facile a maintenir par theme;
- stable dans le temps.

## Principes

### 1. Une idee = une page

Chaque page doit couvrir un seul sujet principal.

Si un sujet devient trop large:

- garder une page de synthese;
- deplacer les details dans des pages secondaires;
- relier explicitement les pages entre elles.

### 2. Une famille documentaire = un perimetre clair

Les documents doivent appartenir a une seule famille metier:

- configuration;
- evenements;
- cotisations;
- notifications;
- comptabilite;
- maintenance;
- import / export;
- cartographie;
- sites et projets.

### 3. Les docs techniques doivent preceder la doc utilisateur

Les pages techniques servent de base:

- aux assistants;
- aux documentations utilisateurs;
- aux refactors;
- aux automatisations Codex.

## Structure recommandee

Chaque document doit idealement contenir:

1. un but;
2. des fichiers de reference;
3. une vue d'ensemble;
4. les regles metier;
5. les points de vigilance;
6. les liens vers les documents voisins.

Les pages de synthese doivent aussi terminer par:

1. un bloc `A lire en plus` avec les pages voisines;
2. si utile, un bloc `Fichiers a garder synchronises`;
3. une distinction nette entre synthese metier et fiches de dependances plugin.

## Convention de nommage

### Fichiers

Preferer des noms:

- en minuscules;
- sans accents;
- sans espaces;
- explicites;
- stables.

Exemples:

- `evenements_formulaires_inscription.md`;
- `categories_cotisation.md`;
- `maintenance_cron.md`;
- `guide_base_connaissance_ia.md`.

### Titres

Les titres doivent:

- annoncer clairement le sujet;
- eviter les formulations trop longues;
- rester coherents entre pages proches.

## Liens internes

Toujours lier:

- les pages de synthese;
- les pages techniques de reference;
- les pages de famille voisine;
- les indexes centraux.

Utiliser des chemins markdown cliquables avec chemins absolus quand la page est citée depuis une réponse.

## Normalisation du contenu

### A faire

- expliquer les champs techniques;
- distinguer configuration et comportement;
- lister les effets de bord;
- mentionner les fichiers source;
- préciser les dependances croisées.

### A éviter

- les doublons verbatim entre pages;
- les titres vagues;
- les listes de champs sans contexte;
- les descriptions sans lien avec le code;
- les pages qui mélangent plusieurs perimetres sans separation.

## Ordre de lecture

Le corpus doit pouvoir etre charge par couches:

1. `docs/README.md`;
2. le guide de base de connaissance;
3. la page de domaine;
4. les sous-pages techniques;
5. les fichiers source.

## Utilisation par les AGENTS

Les fichiers `AGENTS.md` doivent:

- pointer vers les pages de synthese;
- ne charger que la famille utile;
- renvoyer vers le bon projet ou le bon site;
- eviter le chargement global du corpus.

## Sites Blobul

Les projets et sites doivent etre identifies clairement:

- portail;
- support / espace client;
- demo FIAFE;
- demo APE;
- sites institutionnels;
- sites de developpement.

## Points de vigilance

- Une normalisation ne doit pas casser les liens historiques utiles.
- Les documents techniques doivent rester synchros avec le code.
- Les reformulations doivent conserver la signification metier.
- Les pages d'index doivent rester courtes et stables.

## A lire en plus

- [`guide_base_connaissance_ia.md`](./guide_base_connaissance_ia.md)
- [`README.md`](./README.md)
- [`parametrage_evenements.md`](./parametrage_evenements.md)
