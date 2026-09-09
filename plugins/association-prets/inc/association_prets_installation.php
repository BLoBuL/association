<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_prets_association_installation_inventaire($flux) {
	$tables = ['spip_asso_ressources', 'spip_asso_prets'];
	return association_installation_ajouter($flux, [
		'plugins' => ['association_prets'], 'tables' => $tables, 'objets' => $tables,
		'schemas' => ['association_prets_base_version' => '1.1.1'],
	]);
}
