<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_bon_plan_identifier_dist($id_bon_plan = 'new', $id_rubrique = 0, $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return serialize(array((int) $id_bon_plan, $associer_objet));
}
function formulaires_editer_bon_plan_charger_dist($id_bon_plan = 'new', $id_rubrique = 0, $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return formulaires_editer_objet_charger('bon_plan', $id_bon_plan, $id_rubrique, $lier_trad, $retour, $config_fonc, $row, $hidden);
}
function formulaires_editer_bon_plan_verifier_dist($id_bon_plan = 'new', $id_rubrique = 0, $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$erreurs = formulaires_editer_objet_verifier('bon_plan', $id_bon_plan, array('titre'));
	if (($email = trim((string) _request('email_contact'))) && !email_valide($email)) { $erreurs['email_contact'] = _T('form_prop_indiquer_email'); }
	return $erreurs;
}
function formulaires_editer_bon_plan_traiter_dist($id_bon_plan = 'new', $id_rubrique = 0, $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$res = formulaires_editer_objet_traiter('bon_plan', $id_bon_plan, $id_rubrique, $lier_trad, $retour, $config_fonc, $row, $hidden);
	$id = (int) ($res['id_bon_plan'] ?? 0);
	if ($id && $associer_objet && str_contains($associer_objet, '|')) {
		list($objet, $id_objet) = explode('|', $associer_objet, 2);
		if ($objet && (int) $id_objet && autoriser('modifier', $objet, (int) $id_objet)) {
			include_spip('action/editer_liens');
			objet_associer(array('bon_plan' => $id), array($objet => (int) $id_objet));
		}
	}
	return $res;
}
