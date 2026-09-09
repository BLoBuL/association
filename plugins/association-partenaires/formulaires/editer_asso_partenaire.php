<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/editer');

function formulaires_editer_asso_partenaire_charger_dist($id_partenaire = 'new', $retour = '') {
	$valeurs = formulaires_editer_objet_charger('partenaire', $id_partenaire, 0, 0, $retour, '');
	$valeurs['_organisations'] = [];
	$organisations = sql_allfetsel('id_organisation, nom', 'spip_organisations', '', '', 'nom');
	foreach ($organisations as $organisation) {
		$valeurs['_organisations'][(int) $organisation['id_organisation']] = $organisation['nom'];
	}
	return $valeurs;
}

function formulaires_editer_asso_partenaire_verifier_dist($id_partenaire = 'new', $retour = '') {
	$erreurs = formulaires_editer_objet_verifier('partenaire', $id_partenaire, ['id_organisation']);
	if (intval(_request('id_organisation')) < 1) {
		$erreurs['id_organisation'] = _T('info_obligatoire');
	}
	return $erreurs;
}

function formulaires_editer_asso_partenaire_traiter_dist($id_partenaire = 'new', $retour = '') {
	return formulaires_editer_objet_traiter('partenaire', $id_partenaire, 0, 0, $retour, '');
}
