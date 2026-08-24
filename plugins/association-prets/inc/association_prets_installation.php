<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_prets_association_installation_inventaire($flux) {
	$tables = array('spip_asso_ressources', 'spip_asso_prets');
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_prets'), 'tables' => $tables, 'objets' => $tables,
		'schemas' => array('association_prets_base_version' => '1.1.0'),
	));
}
