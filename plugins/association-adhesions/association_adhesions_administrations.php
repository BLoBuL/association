<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Installe ou adopte les tables historiques du domaine Adhésions.
 *
 * maj_tables() est idempotent : une migration depuis Association 4 conserve
 * les lignes existantes et ne fait qu'aligner la structure déclarée.
 */
function association_adhesions_upgrade($nom_meta_base_version, $version_cible) {
	include_spip('base/upgrade');
	include_spip('inc/association_adhesions_migration');
	include_spip('inc/association_adhesions_migration_justificatifs');
	$maj = array();
	$maj['create'] = array(
		array('maj_tables', array(
			'spip_asso_categories_adherents',
			'spip_asso_cotisations',
		)),
	);
	$maj['1.1.0'] = $maj['create'];
	$maj['1.2.0'] = array(
		array('maj_tables', array('spip_asso_cotisations')),
		array('association_completer_migration_cotisations'),
	);
	$maj['1.3.0'] = array(
		array('association_adhesions_migrer_justificatifs_cotisations'),
	);
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Désinstalle le paquet sans supprimer les données métier.
 *
 * La suppression explicite des tables relève d'une opération de maintenance
 * distincte afin d'éviter une perte accidentelle d'adhésions.
 */
function association_adhesions_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}
