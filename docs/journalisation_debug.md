# Journalisation et debug

## But

Documenter la couche de journalisation du plugin et les options de debug disponibles.

## Fichiers sources

- [`inc/association_log.php`](../inc/association_log.php)
- bloc `debug` de [`formulaires/configurer_association.php`](../formulaires/configurer_association.php)

## Categories de logs

Les categories disponibles sont:

- autorisations;
- cotisations;
- notifications;
- inscriptions;
- comptabilite;
- adherents;
- cron;
- spam;
- email;
- gis;
- migration;
- sync.

## Niveaux

Le logger accepte:

- `erreur`;
- `critique`;
- `info`;
- `debug`.

Regle:

- les niveaux non-debug passent toujours;
- le niveau debug depent de la configuration.

## Configuration

La configuration enregistre les activations par categorie sous:

- `association/debug/categories/<categorie>`

La page de configuration propose des cases a cocher pour chaque categorie.

Les memes reglages sont disponibles en ligne de commande avec une liste blanche
stricte. Voir [`configuration_spip_cli.md`](./configuration_spip_cli.md). Le
commutateur virtuel `debug` permet d'activer ou de desactiver toutes les
categories en une seule commande, tandis que `debug.<categorie>` cible une seule
categorie.

### Onglet relie

Ces activations sont configurees dans l'onglet `debug` de `configurer_association`.

## Fonction principale

`association_log()`:

- ajoute le fichier et la ligne appelante;
- encode le contexte en JSON;
- envoie vers `spip_log()` avec le bon niveau;
- centralise les journaux du plugin.

## Usage metier

Cette couche sert a:

- tracer les decisions d'autorisation;
- suivre les envois d'emails;
- diagnostiquer le cron et la maintenance;
- auditer les migrations comptables;
- documenter les notifications GIS et les parcours complexes.

## Points de vigilance

- Le debug doit rester maitrise par le webmestre.
- Trop de logs activés peuvent rendre le diagnostic bruyant.
- Les messages doivent rester courts et structurés.
