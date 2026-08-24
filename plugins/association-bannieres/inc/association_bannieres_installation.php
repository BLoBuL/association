<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bannieres_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_bannieres'), 'tables' => array('spip_asso_bannieres'),
		'objets' => array('spip_asso_bannieres'), 'schemas' => array('association_bannieres_base_version' => '1.0.0'),
	));
}
