<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Recopie l'ancien identifiant de journal dans l'auteur du compte (1.2.4).
 */
function association_maj_124() {
	sql_update('spip_asso_comptes', array('id_auteur' => 'id_journal'));
}

/**
 * Rejoue une étape tardive du schéma historique du domaine Comptabilité.
 */
function association_compta_migration_legacy($version) {
	$operations = array(
		'1.1.9' => array(
			'TABLE spip_asso_comptes DROP type_paiement',
			'TABLE spip_asso_comptes DROP type_cotisation',
		),
		'1.2.4' => array('TABLE spip_asso_comptes DROP id_journal'),
		'1.4.7' => array(
			"TABLE spip_asso_comptes ADD COLUMN objet varchar(30) DEFAULT 'cotisation'",
			"TABLE spip_asso_comptes ADD COLUMN id_objet varchar(21) DEFAULT '0'",
		),
		'1.5.5' => array("TABLE spip_asso_comptes CHANGE date date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"),
		'1.5.7' => array("TABLE spip_asso_comptes MODIFY id_transaction BIGINT NOT NULL DEFAULT '0'"),
	);
	if ((string) $version === '1.2.4') {
		maj_tables(array('spip_asso_comptes'));
		association_maj_124();
		sql_alter($operations['1.2.4'][0]);
		return;
	}
	if ((string) $version === '1.1.0') {
		maj_tables(array('spip_asso_comptes', 'spip_asso_plan', 'spip_asso_destination', 'spip_asso_destination_op'));
		return;
	}
	foreach ($operations[(string) $version] ?? array() as $operation) {
		sql_alter($operation);
	}
	if (in_array((string) $version, array('1.1.7', '1.1.8', '1.1.10'), true)) {
		maj_tables(array('spip_asso_comptes'));
	}
	if ((string) $version === '1.1.9') {
		maj_tables(array('spip_asso_comptes'));
	}
}
