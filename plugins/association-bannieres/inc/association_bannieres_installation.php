<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_bannieres_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_bannieres'],
		'tables' => ['spip_asso_bannieres'],
		'objets' => ['spip_asso_bannieres'],
		'schemas' => ['association_bannieres_base_version' => '1.0.0'],
	]);
}
