# Import et export

## But

Documenter les principaux flux d'import, d'export et de migration du plugin.

## Fichiers de reference

- `export_activites.csv.html`
- `export_cotisations.csv.html`
- `export_evenements_compta.xml.html`
- `export_compta.xml.html`
- `export_comptes_evenement.csv.html`
- `inscriptions_evenement.csv.html`
- [`formulaires/importer_plan_comptable.php`](../formulaires/importer_plan_comptable.php)
- [`formulaires/importer_destination_comptable.php`](../formulaires/importer_destination_comptable.php)
- [`formulaires/migrer_asso_comptabilite.php`](../formulaires/migrer_asso_comptabilite.php)
- [`inc/fonctions/generer_export_csv.php`](../inc/fonctions/generer_export_csv.php)
- [`action/envoyer_relances.php`](../action/envoyer_relances.php)

## Exports

Les exports principaux couvrent:

- les activites;
- les cotisations;
- les inscriptions evenement;
- la comptabilite evenement;
- les comptes evenement;
- certains exports CSV techniques pour l'analyse et les reprises.

### Rôle

Les exports servent a:

- la lecture manuelle;
- les reprises externes;
- le controle comptable;
- l'archivage;
- le reporting.

### Point d'attention

Un export n'est pas toujours une extraction brute:

- il peut embarquer des calculs metier;
- il peut filtrer les lignes selon le contexte;
- il peut etre dépendant d'autres tables.

## Imports

Les imports principaux concernent:

- le plan comptable;
- les destinations comptables;
- la migration comptable.

### Formulaires

Les formulaires techniques exposent:

- la validation du fichier importe;
- des options de confirmation;
- des messages de reprise ou d'erreur;
- parfois des rapports intermediaires.

## Migration comptable

Le formulaire `formulaires/migrer_asso_comptabilite.php` sert a migrer ou re-synchroniser des donnees comptables historiques.

Il est a utiliser avec prudence car il peut:

- reconfigurer des comptes;
- relier des anciennes donnees a de nouveaux schemas;
- normaliser des objets historiques.

## Fonctionnement technique

La couche d'export et d'import s'appuie sur:

- des squelettes `.html` pour les sorties;
- des formulaires `.php` pour la saisie;
- des helpers communs de mise en forme CSV;
- les tables metier du plugin.

## Synchronisation des membres

Le plugin expose aussi une synchronisation des membres:

- [`formulaires/synchro_asso_membres.php`](../formulaires/synchro_asso_membres.php)
- [`action/synchroniser_asso_membres.php`](../action/synchroniser_asso_membres.php)

Cette brique est utile pour:

- aligner les donnees d'adhérents;
- mettre a jour les listes;
- preparer certains exports ou traitements.

## Regles communes

- les imports doivent etre compatibles avec l'historique du plugin;
- les donnees sont souvent structurees sous forme de JSON ou de structures proches;
- les saisies de confirmation doivent rester claires;
- les doublons et les incompatibilites doivent etre traites explicitement.

## Points de vigilance

- Verifier l'encodage et les separateurs lors des reprises CSV.
- Toujours tester un import sur un echantillon avant la masse.
- Un export comptable peut avoir des effets sur l'analyse si les champs sources changent.

## A lire en plus

- [`maintenance_cron.md`](./maintenance_cron.md)
- [`journalisation_debug.md`](./journalisation_debug.md)
- [`guide_base_connaissance_ia.md`](./guide_base_connaissance_ia.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/destinations.md`](./plugins/destinations.md)
- [`plugins/bank.md`](./plugins/bank.md)
- [`plugins/mailshot.md`](./plugins/mailshot.md)
