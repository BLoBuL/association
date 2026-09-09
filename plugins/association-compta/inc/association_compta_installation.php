<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_compta_association_installation_inventaire($flux) {
	$tables = ['spip_asso_comptes', 'spip_asso_plan', 'spip_asso_destination', 'spip_asso_destination_op'];
	return association_installation_ajouter($flux, [
		'plugins' => ['association_compta'], 'tables' => $tables, 'objets' => $tables,
		'schemas' => ['association_compta_base_version' => '1.0.0'],
	]);
}
