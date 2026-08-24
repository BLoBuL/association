<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_paiements_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'transactions' => array(
			'ordre' => 75,
			'label' => _T('association_paiements:titre_transactions'),
			'exec' => 'transactions',
			'icone' => 'transactions',
		),
	));
}
