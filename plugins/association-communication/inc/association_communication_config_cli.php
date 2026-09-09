<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Définitions CLI propres au module, extraites du registre historique.
 */
function association_communication_config_cli_definitions() {
	return [
		'affichage.segments' => [
			'type' => 'list',
			'writable' => true,
			'path' => '/association_metas/selection_segment',
			'default' => [],
			'description' => 'Configuration Association : affichage.segments.',
		],
		'maintenance.supprimer_urls_mailsubscriber' => [
			'type' => 'enum',
			'writable' => true,
			'path' => '/association_metas/meta_cfg_maintenance_supprimer_urls_mailsubscriber',
			'allowed' => [
				0 => 'oui',
				1 => 'non',
			],
			'default' => 'oui',
			'description' => 'Configuration Association : maintenance.supprimer_urls_mailsubscriber.',
		],
		'maintenance.supprimer_urls_obsoletes' => [
			'type' => 'enum',
			'writable' => true,
			'path' => '/association_metas/meta_cfg_maintenance_supprimer_urls_obsoletes',
			'allowed' => [
				0 => 'oui',
				1 => 'non',
			],
			'default' => 'oui',
			'description' => 'Configuration Association : maintenance.supprimer_urls_obsoletes.',
		],
		'maintenance.supprimer_mailsubscribers_orphelines' => [
			'type' => 'enum',
			'writable' => true,
			'path' => '/association_metas/meta_cfg_maintenance_supprimer_mailsubscribers_orphelines',
			'allowed' => [
				0 => 'oui',
				1 => 'non',
			],
			'default' => 'oui',
			'description' => 'Configuration Association : maintenance.supprimer_mailsubscribers_orphelines.',
		],
	];
}
