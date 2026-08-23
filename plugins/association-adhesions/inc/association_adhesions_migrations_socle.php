<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_adhesions_association_migrations_historiques($flux) {
	include_spip('inc/association_adhesions_migration');
	include_spip('inc/association_adhesions_migration_legacy');
	$versions = array('1.1.0','1.1.3','1.1.4','1.1.5','1.1.6','1.1.7','1.1.8','1.1.9','1.1.13','1.2.0','1.2.2','1.3.3','1.4.2','1.5.0','1.5.1','1.5.3','1.5.4','1.5.9');
	$etapes = array();
	foreach ($versions as $version) {
		$priorite = $version === '1.3.3' ? 20 : 10;
		$etapes[] = array($version, array('association_adhesions_migration_legacy', $version), $priorite);
	}
	$etapes[] = array('1.6.0', array('association_adhesions_migration_cotisations_creer'), 10);
	$etapes[] = array('1.6.1', array('association_completer_migration_cotisations'), 10);
	return association_migrations_ajouter($flux, 'association_adhesions', $etapes);
}
