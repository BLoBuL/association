<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_partenaires_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_partenaires'), 'tables' => array('spip_asso_partenaires'),
		'objets' => array('spip_asso_partenaires'), 'schemas' => array('association_partenaires_base_version' => '1.0.0'),
	));
}
