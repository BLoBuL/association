AGENTS.md

Base commune
- Lire `¤base_connaissance_blobul\README.md` puis `¤base_connaissance_blobul\AGENTS.md`.
- `BDC` signifie `base de connaissance Blobul`.
- La BDC commune est dans son propre dépôt Git, souvent situé dans le même dossier `GIT` que le dépôt courant.
- Si le dossier `¤base_connaissance_blobul` n'est pas dans ce dépôt, le chercher comme dépôt voisin, par exemple `..\¤base_connaissance_blobul\`.
- Dans les dépôts Blobul, les fichiers `AGENTS.md` attendus pour le travail sur le code renvoient vers cette BDC puis vers le profil `DEV`.

Spécificités du dépôt
- Back-office association : adhérents, événements, cotisations, participations, exports et tâches cron.
- Logique métier dans `association_pipelines.php`; stockage dans `base/association.php`.
- Intégrations importantes : `bank`, `notifications`, `cextras`, `inscription3`, `mailsubscribers`.
- API stables à préserver : `association_taches_generales_cron`, `association_trig_bank_notifier_reglement`, `association_sync_repetitions_tarifs`.

Fichiers à lire en priorité
- `docs/README.md`
- `paquet.xml`
- `association_pipelines.php`
- `base/association.php`
- `inc/notifications_emails.php`
- `notifications/`
- `lang/`
- `saisies/`
- `docs/`
