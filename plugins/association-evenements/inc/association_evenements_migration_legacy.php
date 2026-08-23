<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Adopte les anciennes colonnes d'inscription aux activités (migration 1.1.0).
 */
function association_maj_create() {
	sql_update('spip_asso_activites', array(
		'id_auteur' => 'nom',
		'nombre_inscrits' => 'inscrits',
		'nom_participants' => 'non_membres',
		'responsable' => 'participant',
	));
	association_import_champs_extras();
}

/**
 * Normalise les options booléennes historiques des événements (migration 1.1.2).
 */
function association_maj_112() {
	$evenements = sql_select('*', 'spip_evenements', "validation='' OR accompagnants='' OR file_attentes=''");
	while ($evenement = sql_fetch($evenements)) {
		$id_evenement = (int) $evenement['id_evenement'];
		sql_updateq('spip_evenements', array(
			'validation' => empty($evenement['validation']) ? 'non' : $evenement['validation'],
			'accompagnants' => empty($evenement['accompagnants']) ? 'non' : $evenement['accompagnants'],
			'file_attentes' => empty($evenement['file_attentes']) ? 'non' : $evenement['file_attentes'],
		), 'id_evenement=' . $id_evenement);
	}
}

/**
 * Recopie l'identité des auteurs dans les inscriptions historiques (1.3.3).
 */
function association_maj_spip_asso_activites() {
	$activites = sql_select('id_activite,id_auteur', 'spip_asso_activites', 'email_inscrit IS NULL');
	while ($activite = sql_fetch($activites)) {
		$id_auteur = (int) $activite['id_auteur'];
		$id_activite = (int) $activite['id_activite'];
		$auteur = sql_fetsel('prenom,nom_famille,email', 'spip_auteurs', 'id_auteur=' . $id_auteur);
		if (!$auteur) {
			continue;
		}
		sql_updateq('spip_asso_activites', array(
			'prenom_inscrit' => $auteur['prenom'],
			'nom_inscrit' => $auteur['nom_famille'],
			'email_inscrit' => $auteur['email'],
		), 'id_activite=' . $id_activite);
	}
}

/**
 * Rejoue une étape tardive du schéma historique du domaine Événements.
 */
function association_evenements_migration_legacy($version) {
	$operations = array(
		'1.4.0' => array("TABLE spip_asso_activites ADD COLUMN association varchar(255) NULL AFTER nom_inscrit"),
		'1.4.1' => array(
			"TABLE spip_evenements ADD COLUMN condition_inscription varchar(3) NULL DEFAULT 'non'",
			"TABLE spip_evenements ADD COLUMN message_condition_inscription TEXT",
		),
		'1.4.3' => array("TABLE spip_asso_activites ADD COLUMN tel_inscrit varchar(255) AFTER email_inscrit"),
		'1.4.4' => array("TABLE spip_asso_activites ADD COLUMN participants_json TEXT AFTER nom_participants"),
		'1.4.5' => array("TABLE spip_evenements CHANGE message_condition_inscription message_condition_inscription TEXT"),
		'1.4.8' => array(
			"TABLE spip_evenements CHANGE info_supplementaire info_supplementaire TEXT",
			"TABLE spip_asso_activites CHANGE journal journal TEXT",
		),
		'1.4.9' => array("TABLE spip_asso_activites CHANGE annotation annotation TEXT"),
		'1.5.2' => array("TABLE spip_asso_activites ADD COLUMN ip_inscrit VARCHAR(45) NULL AFTER tel_inscrit"),
		'1.5.6' => array(
			"TABLE spip_evenements ADD COLUMN invites VARCHAR(3) NOT NULL DEFAULT 'non'",
			"TABLE spip_evenements ADD COLUMN limite_invites INT(10) UNSIGNED NOT NULL DEFAULT '5'",
			"TABLE spip_asso_activites ADD COLUMN nb_invite INT(10) UNSIGNED NOT NULL DEFAULT '0'",
		),
		'1.5.8' => array("TABLE spip_evenements ADD COLUMN fermeture_inscription_date DATETIME NULL DEFAULT NULL AFTER fermeture_inscription"),
	);
	foreach ($operations[(string) $version] ?? array() as $operation) {
		sql_alter($operation);
	}
}
