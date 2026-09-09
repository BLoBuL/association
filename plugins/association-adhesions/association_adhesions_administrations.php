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
	$maj = [];
	$maj['create'] = [
		['maj_tables', [
			'spip_asso_categories_adherents',
			'spip_asso_cotisations',
		]],
	];
	$maj['1.1.0'] = $maj['create'];
	$maj['1.2.0'] = [
		['maj_tables', ['spip_asso_cotisations']],
		['association_completer_migration_cotisations'],
	];
	$maj['1.3.0'] = [
		['association_adhesions_migrer_justificatifs_cotisations'],
	];
	$maj['1.4.0'] = [
		['association_adhesions_autonomiser_id_compte'],
	];
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Rend le lien comptable réellement facultatif.
 */
function association_adhesions_autonomiser_id_compte() {
	$table = 'spip_asso_cotisations';
	if (!sql_showtable($table, true)) {
		return;
	}

	$type_serveur = (string) ($GLOBALS['connexions'][0]['type'] ?? '');
	if (str_starts_with($type_serveur, 'sqlite')) {
		association_adhesions_reconstruire_cotisations_sqlite();
		return;
	}

	sql_updateq($table, ['id_compte' => null], 'id_compte=0');
	// MySQL/MariaDB : l'index historique est unique et porte le même nom.
	@sql_alter("TABLE $table DROP INDEX id_compte");
	sql_alter("TABLE $table MODIFY id_compte BIGINT NULL DEFAULT NULL");
	sql_alter("TABLE $table ADD INDEX id_compte (id_compte)");
}

function association_adhesions_reconstruire_cotisations_sqlite() {
	$table = 'spip_asso_cotisations';
	$historique = 'spip_asso_cotisations_migration_140';
	sql_query('BEGIN IMMEDIATE');
	try {
		if (sql_showtable($historique, true)) {
			throw new RuntimeException('La table temporaire de migration des cotisations existe déjà.');
		}
		if (sql_alter("TABLE $table RENAME TO $historique") === false) {
			throw new RuntimeException('Impossible de préserver les cotisations historiques.');
		}
		maj_tables([$table]);
		$description = sql_showtable($table, true);
		if (!$description || empty($description['field']['id_cotisation'])) {
			throw new RuntimeException('La nouvelle table des cotisations est invalide.');
		}
		$champs = array_flip(array_keys($description['field']));
		$lignes = sql_allfetsel('*', $historique);
		foreach ($lignes as $ligne) {
			if (empty($ligne['id_compte'])) {
				$ligne['id_compte'] = null;
			}
			if (sql_insertq($table, array_intersect_key($ligne, $champs)) === false) {
				throw new RuntimeException('Impossible de restaurer une cotisation historique.');
			}
		}
		if (sql_countsel($table) !== count($lignes)) {
			throw new RuntimeException('Le nombre de cotisations restaurées est incohérent.');
		}
		if (sql_drop_table($historique) === false) {
			throw new RuntimeException('Impossible de supprimer la table temporaire de migration.');
		}
		sql_query('COMMIT');
	} catch (Throwable $e) {
		sql_query('ROLLBACK');
		throw $e;
	}
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
