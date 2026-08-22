<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_declarer_tables_principales($tables) {
	$tables['spip_asso_categories_activites'] = array(
		'field' => array(
			'id_categorie' => 'INT NOT NULL',
			'valeur' => 'TINYTEXT NOT NULL',
			'statut' => 'TINYTEXT NOT NULL',
			'quantite' => 'INT NOT NULL',
			'paiement_en_ligne' => 'BOOLEAN default 0',
			'commentaires' => 'TEXT NOT NULL',
			'deleted' => 'BOOLEAN default 0',
			'type_inscrit' => 'VARCHAR(255) DEFAULT NULL',
			'maj' => 'TIMESTAMP NOT NULL',
		),
		'key' => array('PRIMARY KEY' => 'id_categorie'),
	);
	$tables['spip_asso_activites'] = array(
		'field' => array(
			'id_activite' => 'BIGINT NOT NULL',
			'id_evenement' => 'BIGINT NOT NULL',
			'id_auteur' => 'BIGINT NOT NULL',
			'ip_inscrit' => 'VARCHAR(45) NULL',
			'email_inscrit' => 'TEXT',
			'nom_inscrit' => 'TEXT',
			'prenom_inscrit' => 'TEXT',
			'tel_inscrit' => 'TEXT',
			'log' => 'TEXT',
			'statut' => 'TEXT  NULL',
			'en_attente' => 'BOOLEAN NOT NULL',
			'valider' => 'BOOLEAN NOT NULL',
			'transaction' => 'TEXT NOT NULL',
			'nombre_inscrits' => "BIGINT NOT NULL DEFAULT '1'",
			'nb_invite' => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
			'nom_participants' => 'TEXT NOT NULL',
			'id_transaction' => 'BIGINT NOT NULL',
			'commentaire' => 'TEXT NOT NULL',
			'association' => 'VARCHAR(255) DEFAULT NULL',
			'participants_json' => 'TEXT DEFAULT NULL',
			'visible_in_list_members' => 'TINYINT(1) NOT NULL DEFAULT 1',
			'notify_the_members' => 'TINYINT(1) NOT NULL DEFAULT 1',
			'journal' => 'TEXT DEFAULT NULL',
			'annotation' => 'TEXT DEFAULT NULL',
			'condition_inscription' => 'VARCHAR(3) DEFAULT NULL',
			'date' => 'DATETIME NOT NULL',
			'maj' => 'DATETIME NOT NULL',
		),
		'key' => array('PRIMARY KEY' => 'id_activite'),
	);
	return $tables;
}

function association_evenements_declarer_tables_auxiliaires($tables) {
	$tables['spip_asso_categories_activites_liens'] = array(
		'field' => array(
			'id_evenement' => 'BIGINT NOT NULL',
			'id_categorie' => 'BIGINT NOT NULL',
			'montant' => 'TEXT NOT NULL',
		),
		'key' => array(
			'PRIMARY KEY' => 'id_evenement,id_categorie',
			'KEY id_evenement' => 'id_evenement',
		),
	);
	return $tables;
}

function association_evenements_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_categories_activites'] = 'asso_categories_activites';
	$interfaces['table_des_tables']['asso_activites'] = 'asso_activites';
	$interfaces['table_des_tables']['asso_categories_activites_liens'] = 'asso_categories_activites_liens';
	return $interfaces;
}
