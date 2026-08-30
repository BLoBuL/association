<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bons_plans_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_bons_plans'),
		'tables' => array('spip_bons_plans', 'spip_bons_plans_liens'),
		'objets' => array('spip_bons_plans'),
		'schemas' => array('association_bons_plans_base_version' => '1.0.0'),
	));
}
