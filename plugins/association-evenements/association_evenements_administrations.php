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
	if (in_array('tarifs_selectionnes', $champs, true)) {
		return;
	}
	if (!in_array('transaction', $champs, true)) {
		maj_tables(array('spip_asso_activites'));
		return;
	}

	$type_serveur = $GLOBALS['connexions'][0]['type'] ?? '';
	if (str_starts_with((string) $type_serveur, 'sqlite')) {
		sql_alter('TABLE spip_asso_activites RENAME COLUMN "transaction" TO tarifs_selectionnes');
	} else {
		sql_alter('TABLE spip_asso_activites CHANGE `transaction` tarifs_selectionnes TEXT NOT NULL');
	}
}

function association_evenements_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}
