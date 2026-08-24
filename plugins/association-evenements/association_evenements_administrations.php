<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_upgrade($nom_meta_base_version, $version_cible) {
	include_spip('base/upgrade');
	$maj = array(
		'create' => array(
			array('maj_tables', array(
				'spip_asso_categories_activites',
				'spip_asso_activites',
				'spip_asso_categories_activites_liens',
			)),
			array('association_evenements_migrer_tarifs_selectionnes'),
		),
	);
	$maj['1.1.0'] = $maj['create'];
	$maj['1.2.0'] = array(
		array('association_evenements_migrer_tarifs_selectionnes'),
	);
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Renomme la sélection tarifaire historique sans perdre ses valeurs.
 *
 * Le nom `transaction` décrivait en réalité un tableau sérialisé indexé par
 * catégorie tarifaire. C'est aussi un mot réservé SQLite qui empêchait la
 * création et la sauvegarde de la table complète.
 */
function association_evenements_migrer_tarifs_selectionnes() {
	$description = sql_showtable('spip_asso_activites', true);
	if (!$description) {
		maj_tables(array('spip_asso_activites'));
		return;
	}

	$champs = array_keys($description['field'] ?? array());
	if (!in_array('transaction', $champs, true)) {
		if (!in_array('tarifs_selectionnes', $champs, true)) {
			maj_tables(array('spip_asso_activites'));
		}
		return;
	}

	$type_serveur = $GLOBALS['connexions'][0]['type'] ?? '';
	if (str_starts_with((string) $type_serveur, 'sqlite')) {
		association_evenements_migrer_tarifs_selectionnes_sqlite();
	} elseif (in_array('tarifs_selectionnes', $champs, true)) {
		sql_query('UPDATE spip_asso_activites SET tarifs_selectionnes = `transaction`');
		sql_alter('TABLE spip_asso_activites DROP COLUMN `transaction`');
	} else {
		sql_alter('TABLE spip_asso_activites CHANGE `transaction` tarifs_selectionnes TEXT NOT NULL');
	}
}

/**
 * Reconstruit la table sur les anciennes versions de SQLite.
 *
 * SQLite n'a ajouté ALTER TABLE DROP COLUMN qu'en 3.35. La reconstruction
 * reste donc nécessaire sur les installations SPIP 4 utilisant SQLite 3.34.
 * Elle est transactionnelle et conserve la table historique en cas d'échec.
 */
function association_evenements_migrer_tarifs_selectionnes_sqlite() {
	$table = 'spip_asso_activites';
	$table_historique = 'spip_asso_activites_migration_120';

	sql_query('BEGIN IMMEDIATE');
	try {
		if (sql_showtable($table_historique, true)) {
			throw new RuntimeException('La table temporaire de migration existe déjà.');
		}

		if (sql_alter("TABLE $table RENAME TO $table_historique") === false) {
			throw new RuntimeException('Impossible de préserver la table historique.');
		}

		maj_tables(array($table));
		$description_nouvelle = sql_showtable($table, true);
		if (!$description_nouvelle || !isset($description_nouvelle['field']['tarifs_selectionnes'])) {
			throw new RuntimeException('La nouvelle table des inscriptions est invalide.');
		}

		$champs_nouveaux = array_keys($description_nouvelle['field']);
		$lignes = sql_allfetsel('*', $table_historique);
		foreach ($lignes as $ligne) {
			$ligne['tarifs_selectionnes'] = $ligne['transaction'] ?? '';
			$ligne = array_intersect_key($ligne, array_flip($champs_nouveaux));
			if (sql_insertq($table, $ligne) === false) {
				throw new RuntimeException('Impossible de restaurer une inscription historique.');
			}
		}

		if (sql_countsel($table) !== count($lignes)) {
			throw new RuntimeException('Le nombre d’inscriptions restaurées est incohérent.');
		}
		if (sql_drop_table($table_historique) === false) {
			throw new RuntimeException('Impossible de supprimer la table temporaire de migration.');
		}
		sql_query('COMMIT');
	} catch (Throwable $e) {
		sql_query('ROLLBACK');
		throw $e;
	}
}

function association_evenements_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}
