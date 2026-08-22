<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('formulaires/inc/email_collectif');

function formulaires_email_collectif_adherent_fichiers() {
	return association_formulaire_email_collectif_fichiers();
}

function formulaires_email_collectif_adherent_saisies() {
	return association_formulaire_email_collectif_saisies('adherent');
}

function formulaires_email_collectif_adherent_charger_dist() {
	return association_formulaire_email_collectif_charger('adherent');
}

function formulaires_email_collectif_adherent_verifier_dist() {
	return association_formulaire_email_collectif_verifier('adherent');
}

function formulaires_email_collectif_adherent_traiter_dist() {
	return association_formulaire_email_collectif_traiter('adherent');
}
