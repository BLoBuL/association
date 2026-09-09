<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_compta_association_migrations_historiques($flux) {
	include_spip('inc/association_compta_migration_legacy');
	$versions = ['1.1.0', '1.1.7', '1.1.8', '1.1.9', '1.1.10', '1.2.4', '1.4.7', '1.5.5', '1.5.7'];
	$etapes = [];
	foreach ($versions as $version) {
		$etapes[] = [$version, ['association_compta_migration_legacy', $version], $version === '1.1.0' ? 40 : 20];
	}
	return association_migrations_ajouter($flux, 'association_compta', $etapes);
}
