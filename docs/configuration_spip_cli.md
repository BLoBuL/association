# Configuration du plugin Association avec SPIP CLI

## Objet

Le plugin expose trois commandes non interactives :

```bash
spip association:config:lire [OPTION] [--format=human|json] [--snapshot] [--pretty]
spip association:config:ecrire OPTION VALEUR [--format=human|json] [--pretty]
spip association:config:restaurer FICHIER [--format=human|json] [--pretty]
```

Elles sont chargees automatiquement depuis `spip-cli/` lorsque le plugin
`association` est actif. La lecture, l'ecriture et la restauration sont des
actions distinctes. La liste blanche centrale est composee dans
`inc/association_config_cli.php` et son inventaire exhaustif se trouve dans
`inc/association_config_cli_registre.php`; une cle absente est refusee.

## Options livrees

### Debug

| Option CLI | Configuration persistante | Valeurs |
|---|---|---|
| `debug` | toutes les categories ci-dessous | booleen |
| `debug.<categorie>` | `association/debug/categories/<categorie>` | booleen |

Les categories sont `autorisations`, `cotisations`, `notifications`,
`inscriptions`, `comptabilite`, `adherents`, `cron`, `spam`, `email`, `gis`,
`migration` et `sync`.

Les booleens acceptent `on`, `off`, `oui`, `non`, `true`, `false`, `1` et `0`.
Ils sont persistes sous la forme `on` ou `off`. `debug` est virtuel : sa lecture
retourne `off`, `on` ou `partial`, et son ecriture pilote toutes les categories.

### Reglages globaux des recettes d'inscription evenement

| Option CLI | Meta `association_metas` | Valeurs autorisees |
|---|---|---|
| `evenement.inscription` | `meta_cfg_event_inscription` | `oui`, `non` |
| `evenement.selection_famille` | `meta_cfg_event_config_accompagnants` | `tout`, `membre_famille` |
| `evenement.informations_supplementaires` | `meta_cfg_event_form_info_supp` | `oui`, `non` |
| `evenement.accompagnants` | `meta_cfg_event_accompagnants` | `oui`, `non` |
| `evenement.invites` | `meta_cfg_event_invites` | `oui`, `non` |
| `evenement.limite_accompagnants` | `meta_cfg_event_limite_nb_accompagnants` | entier de `0` a `1000` |
| `evenement.type_inscrits` | `meta_cfg_event_type_inscrits_evenement` | `public`, `prive`, `strict`, `only_strict` |
| `evenement.validation` | `meta_cfg_event_validation` | `oui`, `non` |
| `evenement.quota` | `meta_cfg_event_type_quota` | `souple`, `strict` |
| `evenement.liste_attente` | `meta_cfg_event_file_attente` | `oui`, `non` |
| `evenement.validation_liste_attente` | `meta_cfg_event_validation_auto` | `oui`, `non` |
| `evenement.limite_liste_attente` | `meta_cfg_event_limite_places_file_attente` | entier de `0` a `100000` |

Ces options pilotent uniquement les valeurs globales reprises lors de la
creation ou du parametrage des evenements. Elles ne modifient aucun evenement
ni aucune inscription existante. `only_strict` produit des evenements `strict`
et verrouille ce choix dans leur formulaire ; `public`, `prive` et `strict`
restent les trois valeurs metier d'un evenement.

### Ensemble des configurations Association

Le registre expose 134 options : le commutateur debug agrege, ses 12 categories
et 121 configurations physiques uniques de `association_metas`. L'inventaire a
ete construit depuis toutes les saisies persistantes du formulaire central ;
les fieldsets, textes d'aide, doublons et le bouton d'execution de maintenance
ont ete exclus.

| Domaine CLI | Contenu |
|---|---|
| `info.*` | identite, adresse, contacts et informations de l'association |
| `adhesion.*` | validite, famille, privileges, dons, modalites et notifications |
| `entreprise.*` | validite, cotisation, evenements, listes et notifications entreprise |
| `evenement.*` | comportement global, quotas, contacts, recus et valeurs par defaut |
| `paiement.*` | modes de paiement, taxes et autorisation d'encaissement |
| `affichage.*` | segments, filtres et colonnes publiques ou privees |
| `modules.*` | configuration Association des modules optionnels, notamment GIS et FIAFE |
| `comptabilite.*` | activation, exercice, plans de comptes et destinations |
| `maintenance.*` | activation, dry-run, seuils, lot et actions autorisees |

La commande suivante constitue l'inventaire autoritatif :

```bash
spip association:config:lire --format=json --pretty
```

Les types acceptes sont les chaines bornees, listes d'emails validees, dates
`JJ/MM`, entiers bornes, decimaux bornes, enumerations, booleens et listes. Une
liste doit etre fournie sous forme de tableau JSON :

```bash
spip association:config:ecrire adhesion.echeances_notification '["60","15"]' --format=json
spip association:config:ecrire affichage.public_filtres_annuaire '["ville","quartier"]' --format=json
```

Les options `maintenance.*` modifient uniquement la configuration. Aucune
commande de cette livraison ne lance la maintenance ni son dry-run.

## Lecture et verification

Inventorier toutes les valeurs effectives :

```bash
spip association:config:lire
spip association:config:lire --format=json --pretty
spip association:config:lire debug.inscriptions --format=json
spip association:config:lire evenement.selection_famille --format=json
spip association:config:lire comptabilite.debut_exercice --format=json
```

Une ecriture reussie relit la meta et retourne notamment :

```json
{
  "status": "ok",
  "command": "association:config:ecrire",
  "option": "debug.inscriptions",
  "previous": "off",
  "value": "on",
  "verified": true,
  "changed": true,
  "affected_options": ["debug.inscriptions"]
}
```

Si la relecture ne confirme pas la valeur demandee, toutes les metas touchees
sont remises dans leur etat exact avant la tentative. La sortie d'erreur indique
`rollback_restored`.

## Instantane et restauration exacte

Avant une recette, produire un instantane complet dans un fichier :

```bash
spip association:config:lire --format=json --snapshot --pretty > association-config-initial.json
```

`--snapshot` exige `--format=json` et interdit de filtrer une seule option.
L'instantane versionne `association-config-snapshot-v2` conserve pour chacune
des 133 options physiques du registre :

- la valeur brute ;
- la valeur effective ;
- l'existence ou l'absence de la meta.

Cette derniere information permet de supprimer lors de la restauration une meta
qui n'existait pas initialement, au lieu de laisser une valeur par defaut
materialisee.

Restaurer puis verifier l'etat initial :

```bash
spip association:config:restaurer association-config-initial.json --format=json --pretty
```

La restauration refuse un instantane v2 incomplet, une option supplementaire,
une valeur hors liste blanche, un autre plugin ou une version de format inconnue.
Elle verifie l'etat brut final. Si une ecriture ou un effacement echoue, elle
restaure transactionnellement l'etat present avant sa propre tentative et
retourne un echec.

Les instantanes complets `association-config-snapshot-v1` produits avant
l'extension restent acceptes pour leurs 24 options historiques exactes. Ils ne
touchent jamais les nouvelles options. Un v1 partiel ou elargi est refuse ;
toute nouvelle recette doit utiliser un instantane v2 complet.

### Recette avec restauration garantie sous Bash

```bash
set -euo pipefail
snapshot="$(mktemp)"
spip association:config:lire --format=json --snapshot > "$snapshot"
restore() {
  spip association:config:restaurer "$snapshot" --format=json
  rm -f "$snapshot"
}
trap restore EXIT

spip association:config:ecrire debug on --format=json
spip association:config:ecrire evenement.selection_famille membre_famille --format=json
spip association:config:ecrire evenement.informations_supplementaires oui --format=json
spip association:config:ecrire evenement.accompagnants oui --format=json
spip association:config:ecrire evenement.type_inscrits strict --format=json
# Executer ici le diagnostic ou la recette locale autorisee.
```

Le `trap` appelle la restauration aussi si une ecriture ou la recette echoue.
L'automatisation doit conserver le code de sortie de la restauration comme une
preuve obligatoire ; un echec de restauration ne doit jamais etre masque.

## Codes de sortie

| Code | Signification |
|---|---|
| `0` | lecture, ecriture ou restauration reussie et verifiee |
| `1` | echec technique, fichier illisible, configuration indisponible, persistance ou restauration non verifiee |
| `2` | option, valeur, format ou instantane invalide |

## Securite

- aucune lecture arbitraire de `spip_meta` ou `spip_association_metas` ;
- aucun secret technique ni configuration de plugin tiers dans la liste blanche ;
- aucune mutation implicite lors d'une lecture ;
- validation complete avant la premiere ecriture ;
- sortie JSON bornee aux options declarees ;
- comportement non interactif et codes de sortie stables ;
- aucune commande ne modifie les enregistrements d'evenements ou d'inscriptions.

Certaines configurations autorisees contiennent des adresses de notification
ou des parametres comptables. Les sorties JSON et instantanes doivent donc etre
proteges comme des fichiers d'exploitation, ne pas etre publies et etre
supprimes apres restauration.

## Ajouter une option

Une option doit etre ajoutee explicitement a
`association_config_cli_completer_registre()`
avec un type, un chemin, une valeur par defaut, une description et, selon le
type, une enumeration ou des bornes. Son ajout exige des tests de lecture,
d'ecriture, de refus, de relecture effective, d'instantane et de restauration.
Une option sensible, un secret ou une donnee client reste hors perimetre.

Le code conserve la compatibilite syntaxique avec PHP 7.4 et les versions SPIP
declarees dans `paquet.xml`. Les commandes reposent sur la decouverte du dossier
`spip-cli/` par SPIP CLI.

## Limites de la livraison

Sont livres : toutes les configurations persistantes du formulaire Association,
les categories de debug, la lecture JSON, l'ecriture unitaire typee, la
verification effective et la restauration exacte transactionnelle.

Restent hors perimetre : les configurations des plugins tiers, les actions de
maintenance, les reglages propres a un objet ou evenement existant, l'ecriture
en lot, les donnees d'inscription, les secrets et tout deploiement non autorise.
