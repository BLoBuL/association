<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_dons_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_dons'), 'tables' => array('spip_asso_dons'),
		'objets' => array('spip_asso_dons'), 'schemas' => array('association_dons_base_version' => '1.0.0'),
	));
}
