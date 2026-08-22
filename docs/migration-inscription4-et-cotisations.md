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
du schéma `1.6.0` :

- auteur, catégorie et transaction ;
- contexte inscription/réinscription ;
- statut métier ;
- dates de création et de validité ;
- montant et devise ;
- lien optionnel vers l'écriture comptable.

La migration est idempotente et conserve temporairement les anciennes colonnes
de `spip_asso_comptes` pendant la transition. Les écritures passent par
`inc/cotisations_stockage.php`, qui alimente la table métier. La suppression
des colonnes historiques ne devra intervenir qu'après conversion et test de
tous les lecteurs, exports, notifications et intégrations de paiement.
