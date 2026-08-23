<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_dons_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'dons' => array('ordre' => 60, 'label' => _T('association_dons:titre_onglet_dons'), 'exec' => 'dons', 'icone' => 'dons'),
	));
}
