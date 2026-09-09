<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_ventes_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, [
		'ventes' => [
			'ordre' => 55,
			'label' => _T('association_ventes:titre_onglet_ventes'),
			'exec' => 'ventes',
			'icone' => 'ventes',
		],
	]);
}
