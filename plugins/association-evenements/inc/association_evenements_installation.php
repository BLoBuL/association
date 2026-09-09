<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_evenements_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_evenements'],
		'tables' => ['spip_asso_categories_activites', 'spip_asso_activites', 'spip_asso_categories_activites_liens'],
		'objets' => ['spip_asso_categories_activites', 'spip_asso_activites'],
		'schemas' => ['association_evenements_base_version' => '1.2.0'],
	]);
}
