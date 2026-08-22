# Notifications du plugin `blobul-ASSO_BO`

## But

Documenter l'architecture commune des notifications du plugin, afin de comprendre:

- quels evenements declenchent un envoi;
- quels templates et quelles chaines de langue sont utilises;
- quelles metas de configuration influencent le contenu ou les destinataires;
- comment les mails sont prepares et mis en file.

## Architecture generale

Les notifications du plugin sont reparties entre:

- les classes de notifications de cotisation;
- les notifications d'echeance;
- les notifications liees aux inscriptions evenement;
- les notifications GIS;
- les notifications administratives de validation et de recapitulatif.

La logique d'envoi s'appuie sur:

- les squelettes dans [`notifications/`](../notifications);
- les chaines de langue dans [`lang/`](../lang);
- la preparation de contexte dans [`inc/cotisations.php`](../inc/cotisations.php) et les fichiers associes;
- la queue SPIP / Facteur via `job_queue_add('facteur_envoyer_app', ...)`.

## Familles de notifications

### Cotisations

Cette famille couvre les confirmations et recapitulatif de cotisation.

Elle est detaillee dans:

- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
- [`notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)

Les cas couverts incluent:

- creation d'une cotisation;
- validation d'une cotisation;
- encaissement;
- notification au membre;
- notification a la tresorerie.

### Echeances

Les notifications d'echeance sont decrites dans:

- [`notifications_echeances.md`](./notifications_echeances.md)

Elles s'appuient sur:

- `notification_adherent_echu`;
- `notification_echeance_cotisation`;
- les delais configures dans la page de configuration;
- le cron general du plugin.

### Inscriptions evenement

Les inscriptions evenement peuvent aussi generer des messages internes ou des notifications au responsable.

Cette famille est detaillee dans:

- [`notifications_evenements.md`](./notifications_evenements.md)

Points a surveiller:

- `meta_cfg_event_message_responsable`;
- `meta_cfg_event_email_defaut`;
- `meta_cfg_envoi_recu_paiement_participation`;
- `config_envoi_recu_participation_cc`;
- `meta_cfg_event_inscription`;
- `meta_cfg_event_validation`;
- `meta_cfg_event_file_attente`.

### GIS

Le bloc GIS de la configuration pilote des notifications geographiques et des alertes liees aux modifications d'adherent.

Champs de configuration:

- `notification_gis_config_email`;
- `notification_gis_config_action`.

### Validation de cotisation

Le bloc de validation / notification de cotisation repose sur:

- `meta_cfg_envoi_recu_paiement_adhesion`;
- `config_envoi_recu_adhesion_cc`;
- `meta_cfg_envoi_validation_paiement_adhesion`;
- `config_destinataires_creation_cotisation_tresorier`;
- et, selon les cas, `config_destinataires_creation_cotisation_adh`.

## Pipeline d'envoi

Le chemin classique est le suivant:

1. un evenement met a jour une cotisation, une inscription ou une echeance;
2. le contexte de notification est construit;
3. le bon template est selectionne;
4. le message est ajoute a la queue;
5. Facteur envoie le mail de facon asynchrone.

Cette architecture evite de bloquer l'action utilisateur sur l'envoi SMTP.

## Configuration a surveiller

La page `configurer_association` pilote plusieurs options qui impactent directement les notifications:

- `meta_cfg_envoi_recu_paiement_adhesion`;
- `meta_cfg_envoi_validation_paiement_adhesion`;
- `notification_adherent_echu`;
- `notification_echeance_cotisation`;
- `config_envoi_recu_adhesion_cc`;
- `config_envoi_recu_participation_cc`;
- `config_envoi_email_notif_defaut`;
- `notification_gis_config_email`;
- `notification_gis_config_action`;
- `meta_cfg_event_message_responsable`;
- `meta_cfg_event_email_defaut`;
- `meta_cfg_event_inscription`;
- `meta_cfg_event_validation`;
- `meta_cfg_event_file_attente`.

## Fichiers a relire

- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/api_cotisations.php`](../inc/api_cotisations.php)
- [`notifications/`](../notifications)
- [`lang/notifications_fr.php`](../lang/notifications_fr.php)
- [`docs/notifications_i18n_report.md`](./notifications_i18n_report.md)

## Points de vigilance

- Les templates et les chaines de langue doivent rester synchronises.
- Les notifications d'echeance ne doivent pas diverger du reste du flux cotisation sans raison fonctionnelle claire.
- Les adresses mail saisies dans la configuration sont validees, mais le contenu des listes doit rester propre pour eviter des departs en erreur.
- Les delais d'echeance et de reinscription sont une decision metier avant d'etre un reglage technique : ils doivent laisser assez de temps pour renouveler sans stress.
- Si ces delais sont trop courts, le cron peut faire basculer un membre en `echu` et retirer des privileges qui dependaient d'une adhesion active, y compris certains acces de redaction, d'administration ou de responsable.

## A lire en plus

- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
