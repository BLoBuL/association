<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Normalise les catégories d'adhésion historiques (migration 1.4.2).
 */
function association_maj_142() {
	sql_updateq(
		'spip_asso_categories_adherents',
		array('type_adherent' => 'adherent'),
		'type_adherent IS NULL'
	);
	sql_delete('spip_asso_categories_adherents', "statut='supprime'");
}

/**
 * Importe l'ancien YAML de Champs Extras lorsqu'il existe encore.
 */
function association_import_champs_extras() {
	$res = array('editable' => true);
	$fichier = find_in_path('yaml/association.yaml');
	if (!$fichier || !is_file($fichier)) {
		$res['message_ok'] = 'Aucun ancien fichier YAML à importer.';
		return $res;
	}
	lire_fichier($fichier, $yaml);
	if (!$yaml) {
		$res['message_erreur'] = 'Lecture du fichier en erreur.';
		return $res;
	}
	include_spip('inc/yaml');
	$description = yaml_decode($yaml, true);
	if (!$description || !is_array($description)) {
		$res['message_erreur'] = 'Pas de champ trouvé dans le fichier.';
		return $res;
	}
	include_spip('formulaires/importer_champs_extras');
	if (iextras_importer_description($description, $message, false)) {
		$res['message_ok'] = $message;
	} else {
		$res['message_erreur'] = $message;
	}
	return $res;
}

/**
 * Rejoue une étape tardive du schéma historique du domaine Adhésions.
 */
function association_adhesions_migration_legacy($version) {
	$operations = array(
		'1.2.0' => array(
			'TABLE spip_asso_categories RENAME TO spip_asso_categories_adherents',
			'TABLE spip_asso_categories_adherents DROP libelle',
			'TABLE spip_asso_categories_adherents DROP duree',
		),
		'1.2.2' => array('TABLE spip_asso_categories_adherents DROP deleted'),
		'1.3.3' => array("TABLE spip_asso_categories_adherents ADD COLUMN type_adherent varchar(255) NOT NULL DEFAULT 'adherent' AFTER statut"),
		'1.4.2' => array("TABLE spip_asso_categories_adherents ALTER type_adherent SET DEFAULT 'adherent'"),
		'1.5.0' => array(
			"TABLE spip_asso_categories_adherents ADD COLUMN date_debut_validite VARCHAR(5) NULL DEFAULT NULL",
			"TABLE spip_asso_categories_adherents ADD COLUMN date_fin_validite VARCHAR(5) NULL DEFAULT NULL",
		),
		'1.5.1' => array(
			"TABLE spip_asso_categories_adherents ADD COLUMN validation VARCHAR(32) NULL DEFAULT 'auto'",
			"TABLE spip_asso_categories_adherents ADD COLUMN document_justificatif VARCHAR(3) NULL DEFAULT 'non'",
		),
		'1.5.3' => array("TABLE spip_asso_categories_adherents ADD COLUMN nombre_enfants VARCHAR(2) NULL DEFAULT ''"),
		'1.5.4' => array(
			"TABLE spip_asso_categories_adherents ADD COLUMN mode_paiement VARCHAR(32) NULL DEFAULT ''",
			"TABLE spip_asso_categories_adherents ADD COLUMN eligibilite VARCHAR(32) NULL DEFAULT ''",
		),
		'1.5.9' => array("TABLE spip_asso_categories_adherents ADD COLUMN devise VARCHAR(3) NOT NULL DEFAULT '' AFTER cotisation"),
	);
	foreach ($operations[(string) $version] ?? array() as $operation) {
		sql_alter($operation);
	}
	if ((string) $version === '1.1.0') {
		maj_tables(array('spip_asso_categories'));
	}
	if (in_array((string) $version, array('1.1.3', '1.1.4', '1.1.5', '1.1.6', '1.1.9', '1.1.13'), true)) {
		association_import_champs_extras();
	}
	if (in_array((string) $version, array('1.1.7', '1.1.8'), true)) {
		maj_tables(array('spip_asso_categories'));
	}
	if ((string) $version === '1.2.0') {
		maj_tables(array('spip_asso_categories_adherents'));
	}
	if ((string) $version === '1.4.2') {
		association_maj_142();
	}
	if ((string) $version === '1.2.2') {
		maj_tables(array('spip_asso_categories_adherents'));
	}
}
