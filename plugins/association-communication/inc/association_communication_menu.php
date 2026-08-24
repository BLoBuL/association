<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_communication_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'notifications' => array(
			'ordre' => 85,
			'label' => _T('association_communication:titre_notifications'),
			'exec' => 'notifications',
			'icone' => 'notifications',
		),
	));
}
