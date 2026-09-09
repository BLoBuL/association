<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_partenaires_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_partenaires'],
		'tables' => ['spip_asso_partenaires'],
		'objets' => ['spip_asso_partenaires'],
		'schemas' => ['association_partenaires_base_version' => '1.0.0'],
	]);
}
