<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('formulaires/inc/email_collectif');

function formulaires_email_collectif_evenement_fichiers($id_evenement = 0) {
	return association_formulaire_email_collectif_fichiers();
}

function formulaires_email_collectif_evenement_saisies($id_evenement = 0) {
	set_request('id_evenement', intval($id_evenement));
	return association_formulaire_email_collectif_saisies('evenement');
}

function formulaires_email_collectif_evenement_charger_dist($id_evenement = 0) {
	return association_formulaire_email_collectif_charger('evenement', $id_evenement);
}

function formulaires_email_collectif_evenement_verifier_dist($id_evenement = 0) {
	return association_formulaire_email_collectif_verifier('evenement', $id_evenement);
}

function formulaires_email_collectif_evenement_traiter_dist($id_evenement = 0) {
	return association_formulaire_email_collectif_traiter('evenement', $id_evenement);
}
