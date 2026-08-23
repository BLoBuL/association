<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/** Définitions CLI propres au module, extraites du registre historique. */
function association_evenements_config_cli_definitions() {
	return array (
  'evenement.inscription' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_inscription',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Activation globale des inscriptions aux evenements.',
  ),
  'evenement.selection_famille' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_config_accompagnants',
    'allowed' => 
    array (
      0 => 'tout',
      1 => 'membre_famille',
    ),
    'default' => 'tout',
    'description' => 'Selection libre ou limitee aux membres de la famille.',
  ),
  'evenement.informations_supplementaires' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_form_info_supp',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Activation des informations supplementaires du parcours multi-etapes.',
  ),
  'evenement.accompagnants' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_accompagnants',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Autorisation globale des accompagnants.',
  ),
  'evenement.invites' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_invites',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Autorisation des invites hors famille.',
  ),
  'evenement.limite_accompagnants' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_limite_nb_accompagnants',
    'default' => '5',
    'min' => 0,
    'max' => 1000,
    'description' => 'Limite globale du nombre d accompagnants.',
  ),
  'evenement.type_inscrits' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_type_inscrits_evenement',
    'allowed' => 
    array (
      0 => 'public',
      1 => 'prive',
      2 => 'strict',
      3 => 'only_strict',
    ),
    'default' => 'prive',
    'description' => 'Type d inscrits par defaut ; only_strict verrouille le mode strict.',
  ),
  'evenement.validation' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_validation',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Validation manuelle requise pour les inscriptions.',
  ),
  'evenement.quota' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_type_quota',
    'allowed' => 
    array (
      0 => 'souple',
      1 => 'strict',
    ),
    'default' => 'souple',
    'description' => 'Comportement global des quotas.',
  ),
  'evenement.liste_attente' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_file_attente',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Activation de la liste d attente.',
  ),
  'evenement.validation_liste_attente' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_validation_auto',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Validation automatique des inscriptions en attente.',
  ),
  'evenement.limite_liste_attente' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_limite_places_file_attente',
    'default' => '5',
    'min' => 0,
    'max' => 100000,
    'description' => 'Limite globale de la liste d attente.',
  ),
  'evenement.inscription_repetition' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_inscription_sur_repetition',
    'allowed' => 
    array (
      0 => 'source_et_repetition',
      1 => 'source',
    ),
    'default' => 'source',
    'description' => 'Configuration Association : evenement.inscription_repetition.',
  ),
  'evenement.modification_inscription' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_modification_inscription',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : evenement.modification_inscription.',
  ),
  'evenement.desinscription' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_desinscription_inscription',
    'allowed' => 
    array (
      0 => 'souple',
      1 => 'strict',
    ),
    'default' => 'souple',
    'description' => 'Configuration Association : evenement.desinscription.',
  ),
  'evenement.message_responsable' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_message_responsable',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : evenement.message_responsable.',
  ),
  'evenement.delai_expiration' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_delai_expiration',
    'allowed' => 
    array (
      0 => '',
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
      8 => '8',
      9 => '9',
      10 => '10',
    ),
    'default' => '',
    'description' => 'Configuration Association : evenement.delai_expiration.',
  ),
  'evenement.quota_adherent' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_quota_inscription_adherent',
    'allowed' => 
    array (
      0 => 'desactive',
      1 => 'global',
      2 => 'activites',
    ),
    'default' => 'desactive',
    'description' => 'Configuration Association : evenement.quota_adherent.',
  ),
  'evenement.nombre_quota_adherent' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/nb_inscription_quota_adherent',
    'default' => '',
    'min' => 0,
    'max' => 100000,
    'description' => 'Configuration Association : evenement.nombre_quota_adherent.',
    'allow_empty' => true,
  ),
  'evenement.jours_quota_adherent' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/nb_jour_quota_adherent',
    'default' => '',
    'min' => 0,
    'max' => 3650,
    'description' => 'Configuration Association : evenement.jours_quota_adherent.',
    'allow_empty' => true,
  ),
  'evenement.destinataires_notification' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/config_envoi_email_notif_defaut',
    'default' => '',
    'description' => 'Configuration Association : evenement.destinataires_notification.',
    'max_length' => 2000,
  ),
  'evenement.recu_paiement' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_envoi_recu_paiement_participation',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'non',
    'description' => 'Configuration Association : evenement.recu_paiement.',
  ),
  'evenement.recu_paiement_cc' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/config_envoi_recu_participation_cc',
    'default' => '',
    'description' => 'Configuration Association : evenement.recu_paiement_cc.',
    'max_length' => 2000,
  ),
  'evenement.telephone_responsable' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_telephone_responsable',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : evenement.telephone_responsable.',
  ),
  'evenement.formulaire_contact' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_evenement_formulaire_contact',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : evenement.formulaire_contact.',
  ),
  'evenement.email_defaut' => 
  array (
    'type' => 'email_list',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_email_defaut',
    'default' => '',
    'description' => 'Configuration Association : evenement.email_defaut.',
    'max_length' => 1000,
  ),
  'evenement.afficher_liste_inscrits' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_afficher_liste_inscrits',
    'allowed' => 
    array (
      0 => 'toujours',
      1 => '1',
      2 => '0',
      3 => 'jamais',
    ),
    'default' => '1',
    'description' => 'Configuration Association : evenement.afficher_liste_inscrits.',
  ),
  'evenement.ouverture_differee' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_ouverture_differe',
    'allowed' => 
    array (
      0 => '0',
      1 => 'dt',
      2 => '7',
      3 => '14',
      4 => '21',
      5 => '28',
      6 => '42',
      7 => '56',
    ),
    'default' => '0',
    'description' => 'Configuration Association : evenement.ouverture_differee.',
  ),
  'evenement.deadline' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_inscription_deadline',
    'allowed' => 
    array (
      0 => 'last_minute',
      1 => 'midnight',
      2 => 'midi',
      3 => '24h',
      4 => '48h',
      5 => '72h',
      6 => '96h',
      7 => '7j',
      8 => '14j',
      9 => '30j',
    ),
    'default' => 'last_minute',
    'description' => 'Configuration Association : evenement.deadline.',
  ),
  'evenement.condition_inscription' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_condition_inscription',
    'allowed' => 
    array (
      0 => 'toujours',
      1 => 'oui',
      2 => 'non',
      3 => 'jamais',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : evenement.condition_inscription.',
  ),
  'evenement.message_condition' => 
  array (
    'type' => 'string',
    'writable' => true,
    'path' => '/association_metas/message_condition_inscription_defaut',
    'default' => '',
    'description' => 'Configuration Association : evenement.message_condition.',
    'max_length' => 10000,
  ),
  'affichage.public_statuts_inscrits' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/config_statuts_liste_publique_inscrits',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : affichage.public_statuts_inscrits.',
    'allowed' => 
    array (
      0 => 'preinscrit',
      1 => 'liste_attente',
    ),
  ),
  'modules.reseau_fiafe' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_reseau_fiafe',
    'allowed' => 
    array (
      0 => 'active',
      1 => 'desactive',
    ),
    'default' => 'desactive',
    'description' => 'Configuration Association : modules.reseau_fiafe.',
  ),
  'modules.profil_reseau_fiafe' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_event_profil_reseau_fiafe',
    'allowed' => 
    array (
      0 => 'active',
      1 => 'desactive',
    ),
    'default' => 'desactive',
    'description' => 'Configuration Association : modules.profil_reseau_fiafe.',
  ),
  'maintenance.jours_inscriptions_attente' => 
  array (
    'type' => 'integer',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_jours_inscriptions_attente',
    'default' => '90',
    'min' => 0,
    'max' => 36500,
    'description' => 'Configuration Association : maintenance.jours_inscriptions_attente.',
  ),
  'maintenance.supprimer_inscriptions_non_validees' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_inscriptions_non_validees',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_inscriptions_non_validees.',
  ),
  'maintenance.anonymiser_inscriptions_inactifs' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_anonymiser_inscriptions_inactifs',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.anonymiser_inscriptions_inactifs.',
  ),
  'maintenance.supprimer_participations_orphelines' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_participations_orphelines',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_participations_orphelines.',
  ),
  'maintenance.supprimer_participations_obsoletes' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_participations_obsoletes',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_participations_obsoletes.',
  ),
);
}
