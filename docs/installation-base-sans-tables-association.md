# Installer la suite sur une base SPIP sans tables Association

Cette procédure couvre une première installation sur une base SPIP 4 déjà
créée, mais ne contenant aucune table `spip_asso_*` ni
`spip_association_metas`. Elle ne doit pas être utilisée pour effacer les
données d'une installation existante.

## Préconditions

- SPIP 4 et PHP 8 minimum ;
- une base SPIP fonctionnelle et un compte webmaster ;
- SPIP-CLI disponible depuis la racine du site ;
- les dix dossiers produits par `tools/stage-suite.ps1` installés comme frères
  dans le répertoire de plugins actif ;
- les dépendances des `paquet.xml` installées, notamment `inscription4`,
  `agenda`, `bank`, `cextras`, `iextras`, `intl`, `mailsubscribers`,
  `notifications`, `pays`, `saisies`, `verifier` et `yaml`.

`inscription4` 4.1.14 déclare encore la bibliothèque
`lib/jquery-validation-1.10.0`. Son URL historique n'étant plus fiable, le
répertoire déjà validé doit être provisionné dans `lib/` avant l'activation.
Cette contrainte appartient à Inscription 4 et devra être modernisée dans ce
plugin.

## Prouver que la base est vierge pour Association

Adapter le préfixe SQL si le site n'utilise pas `spip_` :

```sql
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND (table_name LIKE 'spip_asso%' OR table_name = 'spip_association_metas');
```

Le résultat doit être vide. Ne jamais supprimer les tables trouvées dans le
cadre de cette procédure.

Contrôler séparément les métas de schéma : une base sans table peut encore
contenir les versions d'une ancienne activation interrompue. Dans ce cas SPIP
considérerait à tort les créations comme déjà exécutées.

```sql
SELECT nom, valeur
FROM spip_meta
WHERE nom IN (
  'association_base_version',
  'association_adhesions_base_version',
  'association_compta_base_version',
  'association_dons_base_version',
  'association_evenements_base_version',
  'association_prets_base_version',
  'association_ventes_base_version'
);
```

Pour une vraie première installation, le résultat doit également être vide.
Si les quatorze tables sont toutes absentes mais que certaines de ces sept
métas subsistent, désactiver la suite puis supprimer uniquement ces métas de
schéma avant l'activation. Ne jamais appliquer ce nettoyage lorsqu'une table
Association existe : il ferait rejouer des migrations sur des données métier.

Les réglages fonctionnels ne sont pas stockés dans ces sept lignes : ils
appartiennent à `spip_association_metas`, créée vide lors de l'installation.
Les valeurs absentes utilisent les défauts déclarés par le code et sont ensuite
enregistrées depuis les formulaires de configuration ou la CLI documentée.

## Activer et initialiser

Activer d'abord les dépendances, puis les modules et enfin le socle :

```bash
spip plugins:activer --from-list=association_dons,association_evenements,association_groupes,association_prets,association_ventes,association_communication,association_adhesions,association_compta,association_paiements,association --yes
spip plugins:maj:bdd
spip cache:vider
```

L'activation seule n'est pas une preuve d'installation : la création des
tables et l'écriture des versions de schéma sont réalisées par
`plugins:maj:bdd`.

La branche `create` du socle ne simule jamais une ancienne version : elle crée
directement la structure finale et laisse chaque module propriétaire créer ses
tables. Les migrations historiques ne sont donc pas rejouées sur une base
vierge.

## Contrôler le résultat

```bash
spip association:installation:verifier
```

Le contrôle exige :

- les dix plugins actifs ;
- quatorze tables présentes ;
- douze tables métier déclarées comme objets SQL principaux SPIP 4 ;
- les sept versions de schéma attendues.

Les quatorze tables sont :

```text
spip_association_metas
spip_asso_activites
spip_asso_categories_activites
spip_asso_categories_activites_liens
spip_asso_categories_adherents
spip_asso_comptes
spip_asso_cotisations
spip_asso_destination
spip_asso_destination_op
spip_asso_dons
spip_asso_plan
spip_asso_prets
spip_asso_ressources
spip_asso_ventes
```

`spip_asso_comptes` appartient exclusivement au module Comptabilité et les
cotisations sont conservées dans `spip_asso_cotisations`.

Les sept métas de schéma attendues après installation sont celles de la requête
de précontrôle, avec les valeurs suivantes :

```text
association_base_version=1.6.1
association_adhesions_base_version=1.3.0
association_compta_base_version=1.0.0
association_dons_base_version=1.0.0
association_evenements_base_version=1.2.0
association_prets_base_version=1.1.1
association_ventes_base_version=1.0.0
```

## Vérifier l'idempotence et les journaux

Exécuter une seconde fois :

```bash
spip plugins:maj:bdd
spip association:installation:verifier
```

La mise à jour doit annoncer qu'aucune mise à jour de plugin n'est nécessaire.
Contrôler ensuite les journaux SPIP produits pendant l'installation et refuser
la recette en présence d'une erreur SQL, d'une erreur fatale PHP ou d'une table
Association absente.

Des avertissements provenant exclusivement de l'installateur SPIP-CLI
(`flag_sqlite`, `langue_site`) ou du chargement initial de Champs Extras doivent
être distingués des erreurs de la suite ; ils ne dispensent jamais du contrôle
des quatorze tables et des parcours servis.
