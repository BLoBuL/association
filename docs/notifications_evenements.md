# Notifications des evenements

## But

Documenter les notifications liees aux inscriptions evenement, aux changements de statut et aux reçus de paiement.

Cette page complete:

- la vue d'ensemble des notifications;
- la doc des formulaires d'inscription evenement;
- la doc de comptabilite evenementielle.

## Fichiers de reference

- [`inc/fonctions/facteur_envoyer_mail_activites.php`](../inc/fonctions/facteur_envoyer_mail_activites.php)
- [`inc/fonctions/validation_attente_automatique.php`](../inc/fonctions/validation_attente_automatique.php)
- [`inc/fonctions/facteur_envoyer_recu_participation.php`](../inc/fonctions/facteur_envoyer_recu_participation.php)
- [`formulaires/inscription_evenement.php`](../formulaires/inscription_evenement.php)
- [`formulaires/inscription_evenement_public.php`](../formulaires/inscription_evenement_public.php)
- [`formulaires/inscription_evenement_multi_public.php`](../formulaires/inscription_evenement_multi_public.php)
- [`notifications/`](../notifications/)
- [`lang/notifications_fr.php`](../lang/notifications_fr.php)

## Architecture generale

Le flux d'une notification evenement suit en general ce schema:

1. une inscription est creee, modifiee, desinscrite ou basculee en attente;
2. le code determine le type de message a produire;
3. un squelette SPIP dans `notifications/` est rendu;
4. le mail est envoye via `facteur_envoyer_app()` ou via la file de travaux;
5. les journaux techniques conservent la trace de l'operation.

## Types de notifications

### 1. Notifications adhérent

Les notifications envoyees a l'inscrit couvrent:

- inscription;
- preinscription;
- modification;
- desinscription;
- attente;
- inscription automatique;
- preinscription automatique;
- expiration automatique.

### 2. Notifications responsable

Les responsables peuvent recevoir:

- une alerte d'inscription;
- une alerte de preinscription;
- une alerte de modification;
- une alerte de desinscription;
- une alerte d'attente;
- un recapitulatif de plusieurs inscrits.

### 3. Reçus de paiement

Deux familles de reçus sont générées:

- reçu d'adhésion;
- reçu de participation événement.

## Routage technique

La fonction `facteur_envoyer_mail_activites()`:

- charge l'événement;
- recupere les responsables;
- envoie un mail par activité a l'adhérent;
- envoie ensuite un mail aux responsables si le type le permet.

### Détection du type

Le type de notification détermine:

- le sujet;
- le squelette SPIP;
- l'adressage;
- la presence ou non d'un BCC.

Les valeurs de type visibles dans le code couvrent notamment:

- `inscription_frontend`;
- `inscription_backend`;
- `modification_frontend`;
- `modification_backend`;
- `preinscription_frontend`;
- `preinscription_backend`;
- `attente_frontend`;
- `attente_backend`;
- `desinscription_frontend`;
- `desinscription_backend`;
- `inscription_automatique`;
- `preinscription_automatique`;
- `expiration_automatique`.

## Notifications d'inscription

### Déclenchement

Les notifications d'inscription reposent sur:

- le statut de l'activité;
- le nombre d'inscrits;
- la configuration d'accompagnants;
- le contexte public ou privé;
- le statut d'ouverture de l'événement.

### Contenu

Les squelettes utilisent les inclusions communes:

- `notifications/inc/inc-infos_inscription.html`;
- `notifications/inc/inc-infos_inscription_responsable.html`;
- `notifications/inc/inc-infos_inscrit.html`;
- `notifications/inc/inc-infos_evenement.html`.

### Regle de langue

Les sujets et textes sont construits avec les clés de langue `asso:*`.

## Notifications d'attente

La fonction `validation_attente_automatique()`:

- selectionne les inscriptions en liste d'attente;
- compare le nombre de places disponibles;
- bascule certaines activités vers `preinscrit` ou `ok`;
- journalise le changement;
- ajoute un travail de notification dans la queue.

## Modalites de destinataires

Les responsables sont resolus a partir de:

- la liste des responsables de l'événement;
- l'email par defaut de configuration si besoin;
- les adresses propres à chaque responsable.

Le BCC peut être piloté par:

- `config_envoi_email_notif_bcc`;
- ou les champs de configuration des reçus.

## Reçus de participation

La fonction `facteur_envoyer_recu_participation()`:

- récupère la transaction;
- récupère l'événement lié;
- récupère le nom de l'inscrit si possible;
- construit le numéro de reçu;
- envoie soit un reçu d'encaissement, soit un reçu de remboursement.

### Cas de remboursement

Si le type de reçu vaut `remboursement`:

- le modèle utilisé est `notifications/recu_remboursement_participation`;
- l'objet du message indique bien qu'il s'agit d'un remboursement;
- le BCC reste piloté par `config_envoi_recu_participation_cc`.

## Reçus d'adhésion

La fonction `facteur_envoyer_recu_adhesion()`:

- lit la transaction encaissée;
- lit la catégorie de cotisation;
- construit un numéro de reçu `ADH...TRA...`;
- rend le squelette `notifications/recu_encaissement_adhesion`;
- envoie le BCC défini par `config_envoi_recu_adhesion_cc`.

## Configuration influente

Les metas importantes sont:

- `meta_cfg_event_message_responsable`;
- `meta_cfg_event_email_defaut`;
- `meta_cfg_event_delai_expiration`;
- `meta_cfg_event_validation`;
- `meta_cfg_event_file_attente`;
- `config_envoi_email_notif_defaut`;
- `config_envoi_email_notif_bcc`;
- `config_envoi_recu_adhesion_cc`;
- `config_envoi_recu_participation_cc`;
- `meta_cfg_envoi_recu_paiement_adhesion`;
- `meta_cfg_envoi_recu_paiement_participation`.

### Onglets relies

Ces reglages proviennent principalement de trois onglets :

- `evenement` pour les contacts, notifications par defaut, recus de participation et modalites ;
- `evenement_defaut` pour la validation, la file d'attente et les regles d'inscription ;
- `mode_paiement` pour la taxe evenement et les autorisations d'encaissement qui influencent le cycle de paiement.

## Points de vigilance

- Les templates SPIP doivent rester synchrones avec les clés de langue.
- Les mails responsables peuvent partir en queue, donc leur présence n'est pas toujours immédiate.
- Les recepteurs d'email peuvent etre une liste composee; il faut toujours normaliser les adresses.
- Un changement dans la structure des activités peut casser les inclusions des notifications.

## A lire en plus

- [`notifications.md`](./notifications.md)
- [`evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
- [`plugins/agenda.md`](./plugins/agenda.md)
