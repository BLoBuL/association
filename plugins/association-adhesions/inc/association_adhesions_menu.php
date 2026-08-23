<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_adhesions_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'adherents' => array('ordre' => 10, 'label' => _T('association_adhesions:titre_onglet_adherents'), 'exec' => 'adherents', 'icone' => 'adherents'),
		'cotisations' => array('ordre' => 20, 'label' => _T('association_adhesions:titre_onglet_cotisations'), 'exec' => 'cotisations', 'icone' => 'cotisations'),
	));
}
