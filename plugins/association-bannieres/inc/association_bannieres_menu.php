<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bannieres_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'bannieres' => array('ordre' => 82, 'label' => _T('association_bannieres:bannieres'), 'exec' => 'bannieres', 'icone' => 'bannieres'),
	));
}
