<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_ventes_association_migrations_historiques($flux) {
	include_spip('inc/association_ventes_migration_legacy');
	return association_migrations_ajouter($flux, 'association_ventes', [
		['1.1.0', ['association_ventes_migration_legacy', '1.1.0'], 30],
	]);
}
