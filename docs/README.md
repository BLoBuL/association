# Documentation technique `blobul-ASSO_BO`

## Objet

Cette documentation regroupe les informations techniques courantes du plugin `association`.
Elle privilegie les regles metier stables, l'architecture effective du code et les plans de test utiles en maintenance.

## Parcours recommande

1. Lire `inscriptions/README.md` avant toute modification des formulaires d'inscription.
2. Consulter `notifications/` pour les flux d'emails et les impacts metier associes.
3. Consulter `comptabilite_evenements.md` pour les effets comptables lies aux activites et transactions.
4. Utiliser `todo/` et `archive/` uniquement comme references secondaires.

## Sommaire

### Domaine adhésions et cotisations

- [`categories_cotisation.md`](./categories_cotisation.md) : paramétrage, montants à 0 €, justificatifs et contrôle en backoffice.
- [`notifications_cotisations.md`](./notifications_cotisations.md) : déclencheurs, destinataires, état des justificatifs et audit des notifications.
- [`tests_adhesions.md`](./tests_adhesions.md) : matrice automatique et commandes de validation locale/dev.

### Domaine inscriptions
- `inscriptions/README.md` : point d'entree du domaine.
- `inscriptions/architecture.md` : organisation du backend commun et wrappers.
- `inscriptions/data-flow.md` : cycle charge/verifie/traite.
- `inscriptions/fields.md` : champs saisis, champs calcules, conventions FO/BO.
- `inscriptions/validation.md` : quotas, doublons, anti-spam, calcul du nombre a verifier.
- `inscriptions/tests-plan.md` : recette manuelle et cibles de tests automatises.
- `inscriptions/plan_refactor_inscriptions.md` : roadmap et dette restante.
- `inscriptions/plan_status.json` : etat machine lisible du chantier.

### Domaine notifications
- `notifications/notifications_cotisations.md`
- `notifications/notifications_cotisation_adherent.md`
- `notifications/notifications_cotisation_admin.md`
- `notifications/notifications_echeances.md`
- `notifications/notifications_i18n_report.md`

### Domaine comptabilite
- `comptabilite_evenements.md`

### Configuration et exploitation
- [`configuration_spip_cli.md`](./configuration_spip_cli.md) : lecture et modification securisees de la configuration Association avec SPIP CLI.
- [`journalisation_debug.md`](./journalisation_debug.md) : categories, niveaux et comportement des journaux du plugin.

### Domaine RGPD et export de donnees
- `export-rgpd-mapping-bdd.md` : mapping complet entre la structure BDD et l'export RGPD
- `import_export.md` : exports CSV et migration comptable

### References secondaires
- `todo/` : notes de travaux a planifier.
- `archive/` : documentation historique et analyses ponctuelles archivees.

## Regles de maintenance documentaire

- Documenter l'etat courant du code, pas seulement un correctif ponctuel.
- Mettre a jour `inscriptions/fields.md` si un nom de champ, un payload ou une convention change.
- Mettre a jour `inscriptions/validation.md` si une regle metier ou anti-spam change.
- Mettre a jour `inscriptions/tests-plan.md` apres tout changement impactant les parcours FO/BO.
- Archiver sous `docs/archive/` les documents lies a une investigation ou a une correction ponctuelle.

