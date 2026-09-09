<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_bannieres_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_bannieres'] = [
		'page' => 'banniere',
		'texte_objets' => 'association_bannieres:bannieres',
		'texte_objet' => 'association_bannieres:banniere',
		'texte_modifier' => 'association_bannieres:banniere_modifier',
		'texte_creer' => 'association_bannieres:banniere_creer',
		'info_aucun_objet' => 'association_bannieres:banniere_aucune',
		'info_1_objet' => 'association_bannieres:banniere_une',
		'info_nb_objets' => 'association_bannieres:banniere_nb',
		'titre' => 'titre AS titre, "" AS lang',
		'date' => 'date_debut',
		'principale' => 'oui',
		'champs_editables' => ['titre', 'descriptif', 'url', 'emplacement', 'ordre', 'date_debut', 'date_fin', 'statut'],
		'field' => [
			'id_banniere' => 'BIGINT NOT NULL',
			'titre' => "TEXT NOT NULL DEFAULT ''",
			'descriptif' => "TEXT NOT NULL DEFAULT ''",
			'url' => "TEXT NOT NULL DEFAULT ''",
			'emplacement' => "VARCHAR(80) NOT NULL DEFAULT 'principal'",
			'ordre' => 'INT NOT NULL DEFAULT 0',
			'date_debut' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'date_fin' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'statut' => "VARCHAR(20) NOT NULL DEFAULT 'publie'",
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => ['PRIMARY KEY' => 'id_banniere', 'KEY emplacement' => 'emplacement', 'KEY statut' => 'statut'],
		'rechercher_champs' => ['titre' => 8, 'descriptif' => 4, 'emplacement' => 2],
		'statut_textes_instituer' => ['publie' => 'texte_statut_publie', 'prepa' => 'texte_statut_en_cours_redaction', 'poubelle' => 'texte_statut_poubelle'],
		'statut' => [['champ' => 'statut', 'publie' => 'publie', 'previsu' => 'publie,prepa', 'exception' => ['statut', 'tout']]],
		'logo' => 'oui',
	];
	return $tables;
}

function association_bannieres_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_bannieres'] = 'asso_bannieres';
	return $interfaces;
}
