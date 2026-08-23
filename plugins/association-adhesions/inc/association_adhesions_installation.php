<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_adhesions_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_adhesions'),
		'tables' => array('spip_asso_categories_adherents', 'spip_asso_cotisations'),
		'objets' => array('spip_asso_categories_adherents', 'spip_asso_cotisations'),
		'schemas' => array('association_adhesions_base_version' => '1.2.0'),
	));
}
