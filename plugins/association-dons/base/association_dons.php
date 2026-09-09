<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_dons_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_dons'] = [
		'field' => [
			'id_don' => 'BIGINT NOT NULL',
			'date_don' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'bienfaiteur' => 'TEXT NOT NULL',
			'id_adherent' => 'INT NOT NULL',
			'argent' => 'TINYTEXT',
			'colis' => 'TEXT',
			'valeur' => 'TEXT NOT NULL',
			'contrepartie' => 'TINYTEXT',
			'commentaire' => 'TEXT',
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_don',
		],
		'principale' => 'oui',
		'titre' => 'bienfaiteur AS titre, "" AS lang',
	];
	return $tables;
}

function association_dons_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_dons'] = 'asso_dons';
	return $interfaces;
}
