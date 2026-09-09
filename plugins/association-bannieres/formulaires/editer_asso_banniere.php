<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/editer');
function formulaires_editer_asso_banniere_charger_dist($id_banniere = 'new', $retour = '') {
	return formulaires_editer_objet_charger('banniere', $id_banniere, 0, 0, $retour, '');
}
function formulaires_editer_asso_banniere_verifier_dist($id_banniere = 'new', $retour = '') {
	return formulaires_editer_objet_verifier('banniere', $id_banniere, ['titre', 'emplacement']);
}
function formulaires_editer_asso_banniere_traiter_dist($id_banniere = 'new', $retour = '') {
	return formulaires_editer_objet_traiter('banniere', $id_banniere, 0, 0, $retour, '');
}
