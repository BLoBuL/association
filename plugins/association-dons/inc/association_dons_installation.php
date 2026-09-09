<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_dons_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_dons'],
		'tables' => ['spip_asso_dons'],
		'objets' => ['spip_asso_dons'],
		'schemas' => ['association_dons_base_version' => '1.0.0'],
	]);
}
