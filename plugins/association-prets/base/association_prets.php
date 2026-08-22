<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
function association_prets_declarer_tables_principales($tables) {
	$tables['spip_asso_ressources'] = array('field' => array(
		'id_ressource' => 'BIGINT NOT NULL', 'code' => 'TEXT NOT NULL', 'intitule' => 'TEXT NOT NULL',
		'date_acquisition' => "DATE NOT NULL DEFAULT '0000-00-00'", 'pu' => "FLOAT NOT NULL DEFAULT '0'",
		'statut' => 'TEXT NOT NULL', 'commentaire' => 'TEXT NOT NULL', 'maj' => 'TIMESTAMP NOT NULL',
	), 'key' => array('PRIMARY KEY' => 'id_ressource'));
	$tables['spip_asso_prets'] = array('field' => array(
		'id_pret' => 'BIGINT NOT NULL', 'id_ressource' => 'VARCHAR(20) NOT NULL',
		'date_sortie' => "DATE NOT NULL DEFAULT '0000-00-00'", 'duree' => "INT NOT NULL default '0'",
		'date_retour' => "DATE NOT NULL DEFAULT '0000-00-00'", 'id_emprunteur' => 'TEXT NOT NULL',
		'statut' => 'TEXT NOT NULL', 'commentaire_sortie' => 'TEXT NOT NULL',
		'commentaire_retour' => 'TEXT NOT NULL', 'maj' => 'TIMESTAMP NOT NULL',
	), 'key' => array('PRIMARY KEY' => 'id_pret'));
	return $tables;
}

function association_prets_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_prets'] = 'asso_prets';
	$interfaces['table_des_tables']['asso_ressources'] = 'asso_ressources';
	return $interfaces;
}
