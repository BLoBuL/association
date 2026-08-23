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
