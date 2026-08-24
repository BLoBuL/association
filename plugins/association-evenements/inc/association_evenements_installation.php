<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_evenements_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array(
		'plugins' => array('association_evenements'),
		'tables' => array('spip_asso_categories_activites', 'spip_asso_activites', 'spip_asso_categories_activites_liens'),
		'objets' => array('spip_asso_categories_activites', 'spip_asso_activites'),
		'schemas' => array('association_evenements_base_version' => '1.2.0'),
	));
}
