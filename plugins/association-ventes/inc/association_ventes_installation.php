<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_ventes_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_ventes'), 'tables' => array('spip_asso_ventes'),
		'objets' => array('spip_asso_ventes'), 'schemas' => array('association_ventes_base_version' => '1.0.0'),
	));
}
