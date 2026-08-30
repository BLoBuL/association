<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bons_plans_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'bons_plans' => array('ordre' => 43, 'label' => _T('association_bons_plans:bons_plans'), 'exec' => 'bons_plans', 'icone' => 'bons_plans'),
	));
}
