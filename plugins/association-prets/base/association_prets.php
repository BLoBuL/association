<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_prets_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_ressources'] = [
		'field' => [
			'id_ressource' => 'BIGINT NOT NULL',
			'code' => 'TEXT NOT NULL',
			'intitule' => 'TEXT NOT NULL',
			'date_acquisition' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'pu' => "FLOAT NOT NULL DEFAULT '0'",
			'statut' => 'TEXT NOT NULL',
			'commentaire' => 'TEXT NOT NULL',
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_ressource',
		],
		'principale' => 'oui',
		'titre' => 'intitule AS titre, "" AS lang',
	];
	$tables['spip_asso_prets'] = [
		'field' => [
			'id_pret' => 'BIGINT NOT NULL',
			'id_ressource' => 'BIGINT NOT NULL',
			'date_sortie' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'duree' => "INT NOT NULL default '0'",
			'date_retour' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'id_emprunteur' => 'BIGINT NOT NULL',
			'statut' => 'TEXT NOT NULL',
			'commentaire_sortie' => 'TEXT NOT NULL',
			'commentaire_retour' => 'TEXT NOT NULL',
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => [
			'PRIMARY KEY' => 'id_pret',
			'KEY id_ressource' => 'id_ressource',
			'KEY id_emprunteur' => 'id_emprunteur',
		],
		'principale' => 'oui',
		'titre' => 'CONCAT("Pret ", id_pret) AS titre, "" AS lang',
	];
	return $tables;
}

function association_prets_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_prets'] = 'asso_prets';
	$interfaces['table_des_tables']['asso_ressources'] = 'asso_ressources';
	return $interfaces;
}
