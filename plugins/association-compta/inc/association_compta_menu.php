<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_compta_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, [
		'comptes' => [
			'ordre' => 70,
			'label' => _T('association_compta:titre_onglet_comptes'),
			'exec' => 'comptes',
			'icone' => 'comptes',
		],
	]);
}
