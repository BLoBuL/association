<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
function association_ventes_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_ventes'] = array('field' => array(
		'id_vente' => 'BIGINT NOT NULL', 'article' => 'TINYTEXT NOT NULL', 'code' => 'TEXT NOT NULL',
		'id_produit' => 'BIGINT DEFAULT NULL', 'id_commande' => 'BIGINT DEFAULT NULL',
		'id_commandes_detail' => 'BIGINT DEFAULT NULL', 'origine' => "VARCHAR(30) NOT NULL DEFAULT 'manuelle'",
		'acheteur' => 'TINYTEXT NOT NULL', 'id_acheteur' => 'BIGINT NOT NULL', 'quantite' => 'TINYTEXT NOT NULL',
		'date_vente' => "DATE NOT NULL DEFAULT '0000-00-00'", 'date_envoi' => "DATE DEFAULT '0000-00-00'",
		'prix_vente' => 'TINYTEXT', 'prix_unitaire_ht' => 'DECIMAL(20,6) DEFAULT NULL',
		'taxe' => 'DECIMAL(6,5) DEFAULT NULL', 'reduction' => 'DECIMAL(6,5) DEFAULT NULL',
		'frais_envoi' => "FLOAT NOT NULL DEFAULT '0'", 'commentaire' => 'TEXT',
		'maj' => 'TIMESTAMP NOT NULL',
	), 'key' => array('PRIMARY KEY' => 'id_vente', 'KEY id_produit' => 'id_produit', 'UNIQUE KEY commande_detail' => 'id_commande,id_commandes_detail'),
		'principale' => 'oui', 'titre' => 'article AS titre, "" AS lang');
	return $tables;
}

function association_ventes_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_ventes'] = 'asso_ventes';
	return $interfaces;
}
