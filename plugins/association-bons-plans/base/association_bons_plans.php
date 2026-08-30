<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_bons_plans_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['bons_plans'] = 'bons_plans';
	return $interfaces;
}

function association_bons_plans_declarer_tables_objets_sql($tables) {
	$tables['spip_bons_plans'] = array(
		'type' => 'bon_plan',
		'page' => 'bon_plan',
		'principale' => 'oui',
		'table_objet_surnoms' => array('bonsplan'),
		'texte_objets' => 'association_bons_plans:bons_plans',
		'texte_objet' => 'association_bons_plans:bon_plan',
		'texte_modifier' => 'association_bons_plans:bon_plan_modifier',
		'texte_creer' => 'association_bons_plans:bon_plan_creer',
		'info_aucun_objet' => 'association_bons_plans:bon_plan_aucun',
		'info_1_objet' => 'association_bons_plans:bon_plan_un',
		'info_nb_objets' => 'association_bons_plans:bon_plan_nb',
		'titre' => 'titre AS titre, lang AS lang',
		'date' => 'date',
		'field' => array(
			'id_bon_plan' => 'BIGINT NOT NULL',
			'id_rubrique' => 'BIGINT NOT NULL DEFAULT 0',
			'id_secteur' => 'BIGINT NOT NULL DEFAULT 0',
			'titre' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'texte' => "TEXT NOT NULL DEFAULT ''",
			'url_site_internet' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'adresse' => "TEXT NOT NULL DEFAULT ''",
			'telephone' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'email_contact' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'date' => 'DATETIME NULL',
			'date_depublication' => 'DATE NULL',
			'statut' => "VARCHAR(20) NOT NULL DEFAULT 'prepa'",
			'lang' => "VARCHAR(10) NOT NULL DEFAULT ''",
			'langue_choisie' => "VARCHAR(3) NOT NULL DEFAULT 'non'",
			'id_trad' => 'BIGINT NOT NULL DEFAULT 0',
			'maj' => 'TIMESTAMP NOT NULL',
		),
		'key' => array(
			'PRIMARY KEY' => 'id_bon_plan', 'KEY id_rubrique' => 'id_rubrique',
			'KEY id_secteur' => 'id_secteur', 'KEY statut' => 'statut', 'KEY lang' => 'lang',
		),
		'champs_editables' => array('titre', 'texte', 'url_site_internet', 'adresse', 'telephone', 'email_contact', 'date_depublication'),
		'champs_versionnes' => array('titre', 'texte', 'url_site_internet', 'adresse', 'telephone', 'email_contact', 'date_depublication'),
		'rechercher_champs' => array('titre' => 8, 'texte' => 5, 'adresse' => 2),
		'tables_jointures' => array('spip_bons_plans_liens'),
		'statut_textes_instituer' => array(
			'prepa' => 'texte_statut_en_cours_redaction', 'prop' => 'texte_statut_propose_evaluation',
			'publie' => 'texte_statut_publie', 'refuse' => 'texte_statut_refuse', 'poubelle' => 'texte_statut_poubelle',
		),
		'statut' => array(array('champ' => 'statut', 'publie' => 'publie', 'previsu' => 'publie,prop,prepa', 'post_date' => 'date', 'exception' => array('statut', 'tout'))),
		'texte_changer_statut' => 'association_bons_plans:bon_plan_changer_statut',
	);
	return $tables;
}

function association_bons_plans_declarer_tables_auxiliaires($tables) {
	$tables['spip_bons_plans_liens'] = array(
		'field' => array('id_bon_plan' => 'BIGINT NOT NULL DEFAULT 0', 'id_objet' => 'BIGINT NOT NULL DEFAULT 0', 'objet' => "VARCHAR(25) NOT NULL DEFAULT ''", 'vu' => "VARCHAR(6) NOT NULL DEFAULT 'non'"),
		'key' => array('PRIMARY KEY' => 'id_bon_plan,id_objet,objet', 'KEY id_bon_plan' => 'id_bon_plan', 'KEY objet' => 'objet,id_objet'),
	);
	return $tables;
}
