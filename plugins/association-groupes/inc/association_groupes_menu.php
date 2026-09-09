<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_groupes_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, [
		'benevoles' => [
			'ordre' => 40,
			'label' => _T('association_groupes:titre_onglet_benevoles'),
			'exec' => 'benevoles',
			'icone' => 'benevoles',
		],
	]);
}
