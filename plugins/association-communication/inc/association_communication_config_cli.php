<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/** Définitions CLI propres au module, extraites du registre historique. */
function association_communication_config_cli_definitions() {
	return array (
  'affichage.segments' => 
  array (
    'type' => 'list',
    'writable' => true,
    'path' => '/association_metas/selection_segment',
    'default' => 
    array (
    ),
    'description' => 'Configuration Association : affichage.segments.',
  ),
  'maintenance.supprimer_urls_mailsubscriber' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_urls_mailsubscriber',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_urls_mailsubscriber.',
  ),
  'maintenance.supprimer_urls_obsoletes' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_urls_obsoletes',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_urls_obsoletes.',
  ),
  'maintenance.supprimer_mailsubscribers_orphelines' => 
  array (
    'type' => 'enum',
    'writable' => true,
    'path' => '/association_metas/meta_cfg_maintenance_supprimer_mailsubscribers_orphelines',
    'allowed' => 
    array (
      0 => 'oui',
      1 => 'non',
    ),
    'default' => 'oui',
    'description' => 'Configuration Association : maintenance.supprimer_mailsubscribers_orphelines.',
  ),
);
}
