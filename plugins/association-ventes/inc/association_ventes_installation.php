<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_ventes_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_ventes'],
		'tables' => ['spip_asso_ventes'],
		'objets' => ['spip_asso_ventes'],
		'schemas' => ['association_ventes_base_version' => '1.1.0'],
	]);
}
