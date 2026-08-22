# Maintenance et cron

## But

Documenter les traitements automatisés du plugin, en particulier:

- les expirations d'adhesions;
- les notifications d'echeance;
- la maintenance BDD;
- le nettoyage des donnees obsoletes ou orphelines;
- la relance des travaux asynchrones.

## Fichiers de reference

- [`genie/association_taches_generales.php`](../genie/association_taches_generales.php)
- [`genie/association_maintenance_bdd.php`](../genie/association_maintenance_bdd.php)
- [`genie/association_expiration_auto_evenement.php`](../genie/association_expiration_auto_evenement.php)
- [`inc/fonctions/association_job_notifier_echeance.php`](../inc/fonctions/association_job_notifier_echeance.php)
- [`action/envoyer_relances.php`](../action/envoyer_relances.php)
- [`formulaires/configurer_association.php`](../formulaires/configurer_association.php)
- [`inc/association_log.php`](../inc/association_log.php)

## Vue d'ensemble

Le plugin s'appuie sur trois mecanismes:

1. les taches CRON de SPIP (`genie_*`);
2. les actions manuelles ou semi-manuales lancees depuis le back-office;
3. la file de travaux pour les notifications et les traitements asynchrones.

## Cron general des adhesions

La tache `genie_association_taches_generales()`:

- parcourt les auteurs non webmestres;
- verifie leur validite;
- retire les privileges si l'adhesion a expire;
- programme les notifications d'echeance;
- remet a jour les statuts internes;
- reapplique la verification des privileges.

### Regles metier

- un adherent echu passe en `statut_interne = echu`;
- un compte admin peut etre recule vers `6forum` si son adhesion est echue;
- les notifications utilisent les delais configures par type d'adhérent.

Implication importante :

- le cron n'attend pas un arbitrage humain si la validite est depassee ;
- si la configuration laisse une fenetre de reinscription trop courte, un adherent peut basculer plus vite que prevu dans `echu` ;
- ce basculement peut aussi retirer des droits de back-office quand ceux-ci dependent de privileges adherent encore actifs.

## Notification d'echeance

La fonction `association_job_notifier_echeance()` sert de passerelle entre le cron et les notifications email.

Elle s'appuie sur:

- la liste des delais configuree;
- le type d'adhérent;
- les templates de cotisation correspondants.

## Maintenance BDD

La tache `genie_association_maintenance_bdd()` orchestre la maintenance globale.

### Principes

- le mode `dry_run` existe et doit rester le mode d'usage prudent;
- la configuration pilote tous les seuils et les actions;
- le traitement fonctionne par lots pour limiter l'impact.

### Grandes familles d'actions

#### 1. Auteurs inactifs

Le cron peut:

- supprimer les auteurs sans historique de paiement;
- anonymiser les auteurs avec historique de paiement;
- supprimer les cotisations et transactions associees;
- nettoyer les mailsubscriptions associees.

#### 2. Inscriptions evenement

Le cron peut:

- supprimer les inscriptions anciennes non validees;
- anonymiser les inscriptions des auteurs inactifs;
- supprimer les transactions non reglees associees.

#### 3. Nettoyage des orphelins

Le cron peut:

- supprimer les cotisations orphelines;
- supprimer les transactions orphelines;
- supprimer les participations evenement orphelines;
- supprimer les URLs obsoletes;
- supprimer les mailsubscribers orphelins.

### Parametres principaux

- `meta_cfg_maintenance_bdd_enable`;
- `meta_cfg_maintenance_dry_run`;
- `meta_cfg_maintenance_jours_inactivite`;
- `meta_cfg_maintenance_jours_inscriptions_attente`;
- `meta_cfg_maintenance_mois_non_encaisse`;
- `meta_cfg_maintenance_lot`;
- l'ensemble des `meta_cfg_maintenance_*` d'actions.

### Onglet relie

Ces reglages sont portes par l'onglet `maintenance_bdd` de `configurer_association`.

## Expiration automatique des evenements

La tache `genie_association_expiration_auto_evenement()` traite les evenements arrivant a expiration et peut declencher les notifications et mises a jour associees.

Cette brique est liee:

- aux statuts d'inscription;
- a la validation automatique;
- a la file de travaux de notifications.

## Relances

L'action `action_envoyer_relances()` fabrique un mailshot a partir:

- d'un sujet;
- d'un titre;
- d'un chapeau;
- d'un texte;
- d'une option d'information paiement.

Puis elle:

- alimente `spip_mailshots`;
- ajoute les destinataires dedoublonnés;
- reprogramme la surveillance de la file.

## Logging

La maintenance journalise via `association_log()`:

- le lancement;
- les options retenues;
- les comptages;
- le mode dry-run;
- les suppressions ou anonymisations effectives.

## Points de vigilance

- Les traitements peuvent toucher a des donnees sensibles.
- Le `dry_run` doit rester la voie de verification avant toute activation large.
- Les suppressions par lot sont importantes pour la stabilite.
- Une action de maintenance peut impacter des donnees presentes dans plusieurs sites Blobul.

## A lire en plus

- [`journalisation_debug.md`](./journalisation_debug.md)
- [`import_export.md`](./import_export.md)
- [`guide_base_connaissance_ia.md`](./guide_base_connaissance_ia.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/mailsubscribers.md`](./plugins/mailsubscribers.md)
- [`plugins/newsletter.md`](./plugins/newsletter.md)
- [`plugins/mailshot.md`](./plugins/mailshot.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
