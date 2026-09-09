<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_partenaires_declarer_tables_objets_sql($tables) {
	$tables['spip_asso_partenaires'] = [
		'page' => 'partenaire',
		'texte_objets' => 'association_partenaires:partenaires',
		'texte_objet' => 'association_partenaires:partenaire',
		'texte_modifier' => 'association_partenaires:partenaire_modifier',
		'texte_creer' => 'association_partenaires:partenaire_creer',
		'info_aucun_objet' => 'association_partenaires:partenaire_aucun',
		'info_1_objet' => 'association_partenaires:partenaire_un',
		'info_nb_objets' => 'association_partenaires:partenaire_nb',
		'titre' => 'titre AS titre, "" AS lang',
		'date' => 'date_debut',
		'principale' => 'oui',
		'champs_editables' => ['id_organisation', 'titre', 'niveau', 'descriptif', 'url', 'ordre', 'date_debut', 'date_fin', 'statut'],
		'field' => [
			'id_partenaire' => 'BIGINT NOT NULL',
			'id_organisation' => 'BIGINT NOT NULL DEFAULT 0',
			'titre' => "TEXT NOT NULL DEFAULT ''",
			'niveau' => "VARCHAR(50) NOT NULL DEFAULT ''",
			'descriptif' => "TEXT NOT NULL DEFAULT ''",
			'url' => "TEXT NOT NULL DEFAULT ''",
			'ordre' => 'INT NOT NULL DEFAULT 0',
			'date_debut' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'date_fin' => "DATE NOT NULL DEFAULT '0000-00-00'",
			'statut' => "VARCHAR(20) NOT NULL DEFAULT 'publie'",
			'maj' => 'TIMESTAMP NOT NULL',
		],
		'key' => ['PRIMARY KEY' => 'id_partenaire', 'KEY id_organisation' => 'id_organisation', 'KEY statut' => 'statut'],
		'rechercher_champs' => ['titre' => 8, 'descriptif' => 4, 'niveau' => 2],
		'statut_textes_instituer' => ['publie' => 'texte_statut_publie', 'prepa' => 'texte_statut_en_cours_redaction', 'poubelle' => 'texte_statut_poubelle'],
		'statut' => [['champ' => 'statut', 'publie' => 'publie', 'previsu' => 'publie,prepa', 'exception' => ['statut', 'tout']]],
	];
	return $tables;
}

function association_partenaires_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['asso_partenaires'] = 'asso_partenaires';
	return $interfaces;
}
