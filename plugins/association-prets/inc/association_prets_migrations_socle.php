<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_prets_association_migrations_historiques($flux) {
	include_spip('inc/association_prets_migration_legacy');
	return association_migrations_ajouter($flux, 'association_prets', [
		['1.1.0', ['association_prets_migration_legacy', '1.1.0'], 50],
	]);
}
