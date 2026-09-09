<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_adhesions_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_adhesions'],
		'tables' => ['spip_asso_categories_adherents', 'spip_asso_cotisations'],
		'objets' => ['spip_asso_categories_adherents', 'spip_asso_cotisations'],
		'schemas' => ['association_adhesions_base_version' => '1.4.0'],
	]);
}
