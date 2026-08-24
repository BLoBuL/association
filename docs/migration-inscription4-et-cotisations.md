# Migration vers Inscription 4 et séparation des cotisations

## Inscription 4

Le manifeste exige `inscription4` à partir de la version `4.1.14`. Cette version
procure encore `inscription3` et conserve les pipelines publics `i3_*`, la méta
`inscription3` et `inc/inscription3_champs_obligatoires`. Ces noms historiques
ne signifient donc pas que le plugin Inscription 3 reste une dépendance.

Les accès de compatibilité seront remplacés par de nouvelles API Inscription 4
lorsqu'elles seront proposées en amont. Association ne doit ni lire les fichiers
internes d'Inscription 4, ni dupliquer sa gestion des champs auteurs.

## Propriété des données

`spip_asso_comptes` devient le journal comptable. Les champs métier historiques
de cotisation sont copiés dans `spip_asso_cotisations` lors de la mise à jour
des schémas `1.6.0` et `1.6.1` :

- auteur, catégorie et transaction ;
- contexte inscription/réinscription ;
- statut métier ;
- date de création ;
- montant et devise ;
- lien optionnel vers l'écriture comptable.

La devise est résolue, dans l'ordre, depuis la transaction Bank, la catégorie
de cotisation, puis la devise par défaut du site. `spip_asso_comptes.id_objet`
est actualisé avec le nouvel `id_cotisation` lorsque l'écriture porte
`objet='cotisation'`.

Les anciennes écritures ne conservaient aucune période de validité propre à
chaque cotisation. `date_debut_validite` et `date_fin_validite` restent donc à
`NULL` pour l'historique : recopier la validité actuelle de l'auteur donnerait
une date fausse aux cotisations antérieures. Ces champs seront alimentés lors
des nouvelles adhésions dès que la règle de période sera enregistrée au moment
de leur validation.

La migration est idempotente et conserve temporairement les anciennes colonnes
de `spip_asso_comptes` pendant la transition. Les écritures passent par
`inc/cotisations_stockage.php`, qui alimente la table métier. La suppression
des colonnes historiques ne devra intervenir qu'après conversion et test de
tous les lecteurs, exports, notifications et intégrations de paiement.
## Contrôle sur une base migrée

Le contrôle fonctionnel sans données personnelles s'exécute depuis la racine
du site SPIP :

```bash
spip php:run --include=plugins/association-adhesions/tests/verifier_migration_cotisations_spip.php
```

Il vérifie la correspondance exacte entre les anciennes écritures et la table
`spip_asso_cotisations`, le rattachement comptable, l'absence de validités
inventées, la présence des devises et l'idempotence de la migration.
