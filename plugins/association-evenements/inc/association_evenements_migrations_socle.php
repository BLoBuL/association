<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_evenements_association_migrations_historiques($flux) {
	include_spip('inc/association_evenements_migration_legacy');
	$versions = [
		'1.1.0', '1.1.1', '1.1.2', '1.1.5', '1.1.13', '1.1.15', '1.2.0', '1.2.1', '1.2.3', '1.2.5', '1.2.6', '1.2.8',
		'1.3.0', '1.3.1', '1.3.2', '1.3.3', '1.3.4', '1.3.5', '1.3.6', '1.3.7', '1.3.8', '1.3.9',
		'1.4.0', '1.4.1', '1.4.3', '1.4.4', '1.4.5', '1.4.8', '1.4.9', '1.5.2', '1.5.6', '1.5.8',
	];
	$etapes = [];
	foreach ($versions as $version) {
		if ($version === '1.1.0') {
			$priorite = 60;
		} elseif ($version === '1.3.3') {
			$priorite = 10;
		} else {
			$priorite = 20;
		}
		$etapes[] = [$version, ['association_evenements_migration_legacy', $version], $priorite];
	}
	return association_migrations_ajouter($flux, 'association_evenements', $etapes);
}
