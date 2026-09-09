<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclare les panneaux de configuration propres à la communication.
 */
function association_communication_configurer_saisies($config) {
	$saisies = [];
	if ($config === 'maintenance_bdd' || empty($config)) {
		$saisies[] = association_config_maintenance_fieldset(
			'config_maintenance_communication_fieldset',
			_T('association_communication:maintenance_titre'),
			[
				association_config_maintenance_radio('supprimer_urls_mailsubscriber'),
				association_config_maintenance_radio('supprimer_urls_obsoletes'),
				association_config_maintenance_radio('supprimer_mailsubscribers_orphelines'),
			]
		);
	}
	if ($config !== 'segments' && !empty($config)) {
		return $saisies;
	}

	$saisies[] = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'config_selection_segment_fieldset',
			'label' => _T('association_config:config_selection_segment_fieldset'),
			'explication' => _T('association_config:config_selection_segment_explication'),
		],
		'saisies' => [
			[
				'saisie' => 'selection_multiple',
				'options' => [
					'nom' => 'selection_segment',
					'label' => _T('association_config:config_selection_segment_label'),
					'size' => 10,
					'data' => preparer_liste_champs_filtres(),
				],
			],
		],
	];

	return $saisies;
}
