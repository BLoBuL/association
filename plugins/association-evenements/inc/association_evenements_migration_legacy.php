<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Adopte les anciennes colonnes d'inscription aux activités (migration 1.1.0).
 */
function association_maj_create() {
	sql_update('spip_asso_activites', [
		'id_auteur' => 'nom',
		'nombre_inscrits' => 'inscrits',
		'nom_participants' => 'non_membres',
		'responsable' => 'participant',
	]);
	association_import_champs_extras();
}

/**
 * Normalise les options booléennes historiques des événements (migration 1.1.2).
 */
function association_maj_112() {
	$evenements = sql_select('*', 'spip_evenements', "validation='' OR accompagnants='' OR file_attentes=''");
	while ($evenement = sql_fetch($evenements)) {
		$id_evenement = (int) $evenement['id_evenement'];
		sql_updateq('spip_evenements', [
			'validation' => empty($evenement['validation']) ? 'non' : $evenement['validation'],
			'accompagnants' => empty($evenement['accompagnants']) ? 'non' : $evenement['accompagnants'],
			'file_attentes' => empty($evenement['file_attentes']) ? 'non' : $evenement['file_attentes'],
		], 'id_evenement=' . $id_evenement);
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
		sql_updateq('spip_asso_activites', [
			'prenom_inscrit' => $auteur['prenom'],
			'nom_inscrit' => $auteur['nom_famille'],
			'email_inscrit' => $auteur['email'],
		], 'id_activite=' . $id_activite);
	}
}

/**
 * Rejoue une étape tardive du schéma historique du domaine Événements.
 */
function association_evenements_migration_legacy($version) {
	$operations = [
		'1.1.0' => [
			'TABLE spip_asso_activites DROP telephone',
			'TABLE spip_asso_activites DROP adresse',
			'TABLE spip_asso_activites DROP email',
			'TABLE spip_asso_activites DROP DATE_paiement',
			'TABLE spip_asso_activites DROP id_adherent',
			'TABLE spip_asso_activites DROP membres',
			'TABLE spip_asso_activites DROP nom',
			'TABLE spip_asso_activites DROP participant',
			'TABLE spip_asso_activites DROP non_membres',
			'TABLE spip_asso_activites DROP inscrits',
			'TABLE spip_asso_activites MODIFY id_auteur BIGINT NOT NULL AFTER id_evenement',
			'TABLE spip_asso_activites MODIFY statut TEXT NOT NULL AFTER id_auteur',
			'TABLE spip_asso_activites MODIFY ORGANISATEUR BOOLEAN NOT NULL AFTER statut',
			'TABLE spip_asso_activites MODIFY valider BOOLEAN NOT NULL AFTER ORGANISATEUR',
			"TABLE spip_asso_activites MODIFY nombre_inscrits BIGINT NOT NULL DEFAULT '1' AFTER valider",
			'TABLE spip_asso_activites MODIFY nom_participants TEXT NOT NULL AFTER nombre_inscrits',
			"TABLE spip_asso_activites MODIFY montant BIGINT NOT NULL DEFAULT '0' AFTER nom_participants",
			'TABLE spip_asso_activites MODIFY montant_payer BOOLEAN NOT NULL AFTER montant',
			'TABLE spip_asso_activites MODIFY commentaire TEXT NOT NULL AFTER montant_payer',
			'TABLE spip_asso_activites MODIFY date TIMESTAMP NOT NULL AFTER commentaire',
		],
		'1.1.5' => ['TABLE spip_asso_activites DROP ORGANISATEUR'],
		'1.1.13' => ["TABLE spip_evenements ADD COLUMN date_ouverture datetime DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER descriptif_securise"],
		'1.1.15' => [
			'TABLE spip_evenements DROP date_ouverture',
			"TABLE spip_evenements ADD COLUMN ouverture_differe tinytext DEFAULT '0' NOT NULL AFTER descriptif_securise",
		],
		'1.2.0' => [
			'TABLE spip_asso_categories_activites DROP cotisation',
			'TABLE spip_evenements ADD COLUMN gratuit BOOLEAN NOT NULL AFTER validation',
			'TABLE spip_evenements DROP montant',
			'TABLE spip_asso_activites DROP montant',
			'TABLE spip_asso_activites DROP montant_payer',
		],
		'1.2.1' => [
			'TABLE spip_asso_categories_activites DROP gratuit',
			'TABLE spip_evenements ADD COLUMN payant BOOLEAN NOT NULL AFTER validation',
		],
		'1.2.5' => ["TABLE spip_evenements ADD COLUMN mute BOOLEAN DEFAULT '0' NOT NULL"],
		'1.2.6' => [
			'TABLE spip_asso_categories_activites DROP gratuit',
			'TABLE spip_asso_activites DROP notify_the_members',
			"TABLE spip_evenements ADD COLUMN show_list_members BOOLEAN DEFAULT '0' NOT NULL AFTER maj",
			"TABLE spip_asso_activites ADD COLUMN visible_in_list_members BOOLEAN DEFAULT '1' NOT NULL AFTER maj",
		],
		'1.2.8' => ["TABLE spip_asso_activites ADD COLUMN notify_the_members BOOLEAN DEFAULT '1' NOT NULL"],
		'1.3.0' => [
			'TABLE spip_evenements ADD COLUMN mode_paiement varchar(124) NOT NULL',
			"TABLE spip_evenements ADD COLUMN type_inscrits_evenement varchar(30) NOT NULL DEFAULT 'strict'",
			"TABLE spip_evenements ADD COLUMN fermeture_inscription varchar(30) NOT NULL DEFAULT 'last_minute'",
		],
		'1.3.1' => [
			"TABLE spip_evenements ADD COLUMN validation_sur_paiement char(3) NOT NULL DEFAULT 'non'",
			'TABLE spip_evenements CHANGE show_list_members afficher_liste_inscrits BOOLEAN',
		],
		'1.3.2' => [
			'TABLE spip_asso_activites DROP en_attente',
			'TABLE spip_asso_activites DROP valider',
			'TABLE spip_asso_activites ADD COLUMN nom_inscrit varchar(255) AFTER id_auteur',
			'TABLE spip_asso_activites ADD COLUMN prenom_inscrit varchar(255) AFTER id_auteur',
			'TABLE spip_asso_activites ADD COLUMN email_inscrit varchar(255) AFTER id_auteur',
		],
		'1.3.3' => ["TABLE spip_asso_categories_activites ADD COLUMN type_inscrit varchar(255) NOT NULL DEFAULT 'indifferent' AFTER statut"],
		'1.3.4' => [
			'TABLE spip_asso_activites MODIFY date DATETIME NULL AFTER commentaire',
			'TABLE spip_asso_activites ADD COLUMN log TEXT AFTER commentaire',
			'TABLE spip_asso_activites ADD COLUMN tel_inscrit varchar(255) AFTER email_inscrit',
		],
		'1.3.5' => ["TABLE spip_evenements ADD COLUMN validation_attente_automatique char(3) NOT NULL DEFAULT 'non'"],
		'1.3.6' => [
			'TABLE spip_evenements ADD COLUMN info_supplementaire varchar(255) NULL',
			'TABLE spip_evenements ADD COLUMN responsables varchar(255) NULL',
		],
		'1.3.7' => ['TABLE spip_evenements ADD COLUMN ouverture_differe_date datetime NULL AFTER ouverture_differe'],
		'1.3.8' => [
			'TABLE spip_asso_activites ADD COLUMN journal varchar(255) NULL',
			'TABLE spip_asso_activites ADD COLUMN annotation varchar(255) NULL',
		],
		'1.3.9' => [
			"TABLE spip_evenements ADD COLUMN presentiel varchar(12) NULL DEFAULT 'oui'",
			'TABLE spip_evenements ADD COLUMN lien varchar(255) AFTER lieu',
			"TABLE spip_evenements ADD COLUMN reseau_fiafe varchar(3) NULL DEFAULT 'non'",
		],
		'1.4.0' => ['TABLE spip_asso_activites ADD COLUMN association varchar(255) NULL AFTER nom_inscrit'],
		'1.4.1' => [
			"TABLE spip_evenements ADD COLUMN condition_inscription varchar(3) NULL DEFAULT 'non'",
			'TABLE spip_evenements ADD COLUMN message_condition_inscription TEXT',
		],
		'1.4.3' => ['TABLE spip_asso_activites ADD COLUMN tel_inscrit varchar(255) AFTER email_inscrit'],
		'1.4.4' => ['TABLE spip_asso_activites ADD COLUMN participants_json TEXT AFTER nom_participants'],
		'1.4.5' => ['TABLE spip_evenements CHANGE message_condition_inscription message_condition_inscription TEXT'],
		'1.4.8' => [
			'TABLE spip_evenements CHANGE info_supplementaire info_supplementaire TEXT',
			'TABLE spip_asso_activites CHANGE journal journal TEXT',
		],
		'1.4.9' => ['TABLE spip_asso_activites CHANGE annotation annotation TEXT'],
		'1.5.2' => ['TABLE spip_asso_activites ADD COLUMN ip_inscrit VARCHAR(45) NULL AFTER tel_inscrit'],
		'1.5.6' => [
			"TABLE spip_evenements ADD COLUMN invites VARCHAR(3) NOT NULL DEFAULT 'non'",
			"TABLE spip_evenements ADD COLUMN limite_invites INT(10) UNSIGNED NOT NULL DEFAULT '5'",
			"TABLE spip_asso_activites ADD COLUMN nb_invite INT(10) UNSIGNED NOT NULL DEFAULT '0'",
		],
		'1.5.8' => ['TABLE spip_evenements ADD COLUMN fermeture_inscription_date DATETIME NULL DEFAULT NULL AFTER fermeture_inscription'],
	];
	if ((string) $version === '1.1.0') {
		maj_tables(['spip_asso_activites', 'spip_evenements']);
		association_maj_create();
		foreach ($operations['1.1.0'] as $operation) {
			sql_alter($operation);
		}
		return;
	}
	foreach ($operations[(string) $version] ?? [] as $operation) {
		sql_alter($operation);
	}
	if ((string) $version === '1.1.1') {
		maj_tables(['spip_evenements']);
	}
	if ((string) $version === '1.1.2') {
		association_maj_112();
	}
	if ((string) $version === '1.2.0') {
		maj_tables(['spip_asso_categories_activites', 'spip_asso_activites', 'spip_asso_categories_activites_liens']);
	}
	if ((string) $version === '1.2.3') {
		maj_tables(['spip_asso_categories_activites']);
	}
	if ((string) $version === '1.3.3') {
		association_maj_spip_asso_activites();
	}
}
