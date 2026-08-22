<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
function association_ventes_declarer_tables_principales($tables) {
	$tables['spip_asso_ventes'] = array('field' => array(
		'id_vente' => 'BIGINT NOT NULL', 'article' => 'TINYTEXT NOT NULL', 'code' => 'TEXT NOT NULL',
		'acheteur' => 'TINYTEXT NOT NULL', 'id_acheteur' => 'BIGINT NOT NULL', 'quantite' => 'TINYTEXT NOT NULL',
		'date_vente' => "DATE NOT NULL DEFAULT '0000-00-00'", 'date_envoi' => "DATE DEFAULT '0000-00-00'",
		'prix_vente' => 'TINYTEXT', 'frais_envoi' => "FLOAT NOT NULL DEFAULT '0'", 'commentaire' => 'TEXT',
		'maj' => 'TIMESTAMP NOT NULL',
	), 'key' => array('PRIMARY KEY' => 'id_vente'));
	return $tables;
}

function association_ventes_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_ventes'] = 'asso_ventes';
	return $interfaces;
}
