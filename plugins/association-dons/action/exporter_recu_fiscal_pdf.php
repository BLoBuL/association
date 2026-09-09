<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Aperçu réservé aux gestionnaires. L'émission fiscale définitive reste fermée :
 * il manque encore le registre des reçus, la signature et la qualification des versements.
 */
function action_exporter_recu_fiscal_pdf_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	if (!autoriser('associer', 'dons')) {
		return false;
	}
	if (!is_string($arg) || !preg_match('/^specimen-([1-9][0-9]*)-([2-9][0-9]{3})$/D', $arg, $matches)) {
		include_spip('inc/minipres');
		echo minipres(_T('association_dons:recu_fiscal_emission_indisponible'));
		return false;
	}
	$id_auteur = (int) $matches[1];
	$annee = (int) $matches[2];
	include_spip('inc/association_dons_recu_fiscal');
	$configuration = association_dons_recu_fiscal_emetteur();
	if (!$configuration['complet']) {
		include_spip('inc/minipres');
		echo minipres(_T('association_dons:recu_fiscal_emetteur_incomplet'));
		return false;
	}
	$auteur = sql_fetsel('id_auteur,nom', 'spip_auteurs', 'id_auteur=' . $id_auteur);
	if (!$auteur) {
		return false;
	}
	include_spip('inc/association_dons_comptabilite');
	$montant = association_dons_montant_fiscal($id_auteur, $annee);
	if (!is_numeric($montant) || !is_finite((float) $montant) || $montant <= 0) {
		include_spip('inc/minipres');
		echo minipres(_T('association_dons:recu_fiscal_aucun_don', ['annee' => $annee]));
		return false;
	}
	include_spip('inc/association_pdf');
	association_pdf_envoyer('prive/pdf/association_dons_specimen', [
		'emetteur' => $configuration['emetteur'],
		'nom_donateur' => $auteur['nom'],
		'annee' => $annee,
		'montant' => number_format((float) $montant, 2, ',', ' '),
	], 'specimen-dons-' . $annee . '-' . $id_auteur);
	return true;
}
