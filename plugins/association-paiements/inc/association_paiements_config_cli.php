<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/** Définitions CLI propres au module, extraites du registre historique. */
function association_paiements_config_cli_definitions() {
	return array (
  'paiement.modes_adhesion' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/mode_paiement_adhesion',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : paiement.modes_adhesion.',
  ),
  'paiement.modes_participation' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/mode_paiement_participation',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : paiement.modes_participation.',
  ),
  'paiement.modes_formulaire' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/mode_paiement_formidable',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : paiement.modes_formulaire.',
  ),
  'paiement.taxe_adhesion' => 
  array (
    'type' => 'decimal',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_taxe',
    'default' => '',
    'description' => 'Configuration Association : paiement.taxe_adhesion.',
    'min' => 0,
    'max' => 100,
    'allow_empty' => true,
  ),
  'paiement.taxe_evenement' => 
  array (
    'type' => 'decimal',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_taxe_evenement',
    'default' => '',
    'description' => 'Configuration Association : paiement.taxe_evenement.',
    'min' => 0,
    'max' => 100,
    'allow_empty' => true,
  ),
  'paiement.autorisation_encaissement' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_autorisation_encaisser_transaction',
    'allowed' => 
    array (
      0 => 'tresoriere',
      1 => 'admin_only',
      2 => 'admin_et_responsable',
    ),
    'default' => 'admin_et_responsable',
    'description' => 'Configuration Association : paiement.autorisation_encaissement.',
  ),
  'maintenance.supprimer_transactions_orphelines' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_transactions_orphelines',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_transactions_orphelines.',
  ),
);
}
