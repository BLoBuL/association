<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/** Définitions CLI propres au module, extraites du registre historique. */
function association_adhesions_config_cli_definitions() {
	return array (
  'adhesion.cotisations_multidevises' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_cotisations_multidevises',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.cotisations_multidevises.',
  ),
  'adhesion.validite' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/validite',
    'allowed' => 
    array (
      0 => 'scolaire',
      1 => 'annee',
    ),
    'default' => 'scolaire',
    'description' => 'Configuration Association : adhesion.validite.',
  ),
  'adhesion.date_scolaire_suivante' => 
  array (
    'type' => 'day_month',
    'writable' => true,
    'path' => '/association_metas/date_scolaire_suivante',
    'default' => '01/06',
    'description' => 'Configuration Association : adhesion.date_scolaire_suivante.',
  ),
  'adhesion.date_scolaire_nouvelle' => 
  array (
    'type' => 'day_month',
    'writable' => true,
    'path' => '/association_metas/date_scolaire_nouvelle',
    'default' => '30/09',
    'description' => 'Configuration Association : adhesion.date_scolaire_nouvelle.',
  ),
  'adhesion.compte_secondaire' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/config_compte_secondaire',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.compte_secondaire.',
  ),
  'adhesion.compte_secondaire_activation' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/config_compte_secondaire_activation',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.compte_secondaire_activation.',
  ),
  'adhesion.age_limite_enfants' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_age_limit_enfants',
    'default' => '',
    'min' => 0,
    'max' => 150,
    'description' => 'Configuration Association : adhesion.age_limite_enfants.',
    'allow_empty' => true,
  ),
  'adhesion.carte_adherent' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_carte_adherent',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.carte_adherent.',
  ),
  'adhesion.zones' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/zone_adherent',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : adhesion.zones.',
  ),
  'adhesion.listes_diffusion' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/liste_diffusion',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : adhesion.listes_diffusion.',
  ),
  'adhesion.donation' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_donation',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.donation.',
  ),
  'adhesion.donation_defaut' => 
  array (
    'type' => 'decimal',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_donation_defaut',
    'default' => '',
    'description' => 'Configuration Association : adhesion.donation_defaut.',
    'min' => 0,
    'max' => 100000000,
    'allow_empty' => true,
  ),
  'adhesion.pages_modalites_inscription' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/pages_modalite_inscription',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : adhesion.pages_modalites_inscription.',
  ),
  'adhesion.pages_modalites_evenement' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/pages_modalite_evenement',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : adhesion.pages_modalites_evenement.',
  ),
  'adhesion.recu_paiement' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_envoi_recu_paiement_adhesion',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : adhesion.recu_paiement.',
  ),
  'adhesion.recu_paiement_cc' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/config_envoi_recu_adhesion_cc',
    'default' => '',
    'description' => 'Configuration Association : adhesion.recu_paiement_cc.',
    'max_length' => 2000,
  ),
  'adhesion.notification_validation_paiement' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_envoi_validation_paiement_adhesion',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : adhesion.notification_validation_paiement.',
  ),
  'adhesion.notification_echeance' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/notification_adherent_echu',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : adhesion.notification_echeance.',
  ),
  'adhesion.echeances_notification' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/notification_echeance_cotisation',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : adhesion.echeances_notification.',
    'allowed' => 
    array (
      0 => '60',
      1 => '30',
      2 => '15',
      3 => '7',
    ),
  ),
  'adhesion.destinataires_creation_cotisation' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/config_destinataires_creation_cotisation_tresorier',
    'default' => '',
    'description' => 'Configuration Association : adhesion.destinataires_creation_cotisation.',
    'max_length' => 2000,
  ),
  'entreprise.validite' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/validite_entreprise',
    'allowed' => 
    array (
      0 => 'scolaire',
      1 => 'annee',
    ),
    'default' => 'scolaire',
    'description' => 'Configuration Association : entreprise.validite.',
  ),
  'entreprise.date_scolaire_suivante' => 
  array (
    'type' => 'day_month',
    'writable' => true,
    'path' => '/association_metas/date_scolaire_suivante_entreprise',
    'default' => '01/06',
    'description' => 'Configuration Association : entreprise.date_scolaire_suivante.',
  ),
  'entreprise.date_scolaire_nouvelle' => 
  array (
    'type' => 'day_month',
    'writable' => true,
    'path' => '/association_metas/date_scolaire_nouvelle_entreprise',
    'default' => '30/09',
    'description' => 'Configuration Association : entreprise.date_scolaire_nouvelle.',
  ),
  'entreprise.cotisation' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_cotisation_compte_entreprise',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : entreprise.cotisation.',
  ),
  'entreprise.inscription_evenement' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_inscription_compte_entreprise',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : entreprise.inscription_evenement.',
  ),
  'entreprise.listes_diffusion' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_liste_diffusion_compte_entreprise',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : entreprise.listes_diffusion.',
  ),
  'entreprise.notification_echeance' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/notification_echeance_notifier_echu_entreprise',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : entreprise.notification_echeance.',
  ),
  'entreprise.echeances_notification' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/notification_echeance_cotisation_entreprise',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : entreprise.echeances_notification.',
    'allowed' => 
    array (
      0 => '60',
      1 => '30',
      2 => '15',
      3 => '7',
    ),
  ),
  'entreprise.destinataires_creation_cotisation' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/config_destinataires_creation_cotisation_tresorier_entreprise',
    'default' => '',
    'description' => 'Configuration Association : entreprise.destinataires_creation_cotisation.',
    'max_length' => 2000,
  ),
  'affichage.public_filtres_annuaire' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/config_filtres_annuaire',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : affichage.public_filtres_annuaire.',
    'allowed' => 
    array (
      0 => 'code_postal',
      1 => 'quartier',
      2 => 'ville',
    ),
  ),
  'affichage.prive_filtres_adherents' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/config_champs_filtres_adherents',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : affichage.prive_filtres_adherents.',
  ),
  'affichage.prive_colonnes_adherents' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/config_champs_colonnes_adherents',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : affichage.prive_colonnes_adherents.',
  ),
  'modules.gis_email' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/notification_gis_config_email',
    'default' => '',
    'description' => 'Configuration Association : modules.gis_email.',
    'max_length' => 1000,
  ),
  'modules.gis_actions' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/notification_gis_config_action',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : modules.gis_actions.',
    'allowed' => 
    array (
      0 => 'modification_adherent',
      1 => 'echec_adherent',
    ),
  ),
  'maintenance.jours_inactivite' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_jours_inactivite',
    'default' => '365',
    'min' => 0,
    'max' => 36500,
    'description' => 'Configuration Association : maintenance.jours_inactivite.',
  ),
  'maintenance.supprimer_auteurs_sans_paiements' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_auteurs_sans_paiements',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_auteurs_sans_paiements.',
  ),
  'maintenance.anonymiser_auteurs_avec_paiements' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_anonymiser_auteurs_avec_paiements',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.anonymiser_auteurs_avec_paiements.',
  ),
);
}
