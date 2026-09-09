<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_bons_plans_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_bons_plans'],
		'tables' => ['spip_bons_plans', 'spip_bons_plans_liens'],
		'objets' => ['spip_bons_plans'],
		'schemas' => ['association_bons_plans_base_version' => '1.0.0'],
	]);
}
