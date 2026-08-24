<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_commerce_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'commerce' => array(
			'ordre' => 50,
			'label' => _T('association_commerce:titre_menu'),
			'exec' => 'commerce',
			'icone' => 'commerce',
		),
	));
}
