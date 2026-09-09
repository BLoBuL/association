<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_compta_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_comptes'] = [
		'principale' => 'oui',
		'titre' => 'justification AS titre, "" AS lang',
		'field' => [
			'id_compte' => 'BIGINT NOT NULL',
			'id_auteur' => 'BIGINT NOT NULL',
			'date' => 'DATE DEFAULT NULL',
			'id_transaction' => "BIGINT NOT NULL default '0'",
			'objet' => "VARCHAR(30) NOT NULL DEFAULT ''",
			'id_objet' => "BIGINT NOT NULL DEFAULT '0'",
			'recette' => "FLOAT NOT NULL DEFAULT '0'",
			'depense' => "FLOAT NOT NULL DEFAULT '0'",
			'justification' => 'TEXT',
			'imputation' => 'TEXT',
			'journal' => 'TINYTEXT',
			'id_journal' => "INT NOT NULL default '0'",
			'vu' => 'BOOLEAN default 0',
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_compte',
			'KEY id_transaction' => 'id_transaction',
			'KEY objet' => 'objet,id_objet',
		],
	];
	$tables['spip_asso_plan'] = [
		'principale' => 'oui',
		'titre' => 'intitule AS titre, "" AS lang',
		'field' => [
			'id_plan' => 'INT NOT NULL',
			'code' => 'TEXT NOT NULL',
			'intitule' => 'TEXT NOT NULL',
			'classe' => 'TEXT NOT NULL',
			'type_op' => "ENUM('credit','debit', 'multi') NOT NULL DEFAULT 'multi'",
			'solde_anterieur' => "FLOAT NOT NULL DEFAULT '0'",
			'date_anterieure' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'commentaire' => 'TEXT NOT NULL',
			'active' => 'BOOLEAN DEFAULT 1',
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_plan',
		],
	];
	$tables['spip_asso_destination'] = [
		'principale' => 'oui',
		'titre' => 'intitule AS titre, "" AS lang',
		'field' => [
			'id_destination' => 'INT NOT NULL',
			'intitule' => 'TEXT NOT NULL',
			'commentaire' => 'TEXT NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_destination',
		],
	];
	$tables['spip_asso_destination_op'] = [
		'principale' => 'oui',
		'titre' => 'CONCAT("Affectation ", id_dest_op) AS titre, "" AS lang',
		'field' => [
			'id_dest_op' => 'INT NOT NULL',
			'id_compte' => 'INT NOT NULL',
			'id_destination' => 'INT NOT NULL',
			'recette' => "FLOAT NOT NULL DEFAULT '0'",
			'depense' => "FLOAT NOT NULL DEFAULT '0'",
		],
		'key' => [
			'PRIMARY KEY' => 'id_dest_op',
		],
	];
	return $tables;
}

function association_compta_declarer_tables_interfaces($interfaces) {
	foreach (['asso_comptes', 'asso_plan', 'asso_destination', 'asso_destination_op'] as $table) {
		$interfaces['table_des_tables'][$table] = $table;
	}
	return $interfaces;
}
