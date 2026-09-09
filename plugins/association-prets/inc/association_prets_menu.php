<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_prets_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, [
		'prets' => [
			'ordre' => 80,
			'label' => _T('association_prets:titre_onglet_prets'),
			'exec' => 'prets',
			'icone' => 'prets',
		],
	]);
}
