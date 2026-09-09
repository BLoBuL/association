<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_ventes_comptabilite_disponible() {
	return association_plugin_actif('association_compta');
}

function association_ventes_compte_lire($id_vente, $imputation) {
	if (!association_ventes_comptabilite_disponible()) {
		return [];
	}
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_objet_lister('asso_vente', $id_vente, [
		'legacy_id_journal' => true,
		'imputations' => [$imputation],
		'champs' => 'id_compte,journal,id_auteur,id_objet,objet,imputation',
	]);
	return $ecritures[0] ?? [];
}

function association_ventes_comptes_supprimer($id_vente) {
	if (!association_ventes_comptabilite_disponible()) {
		return true;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecritures_objet_supprimer('asso_vente', $id_vente, [
		'legacy_id_journal' => true,
		'imputations' => array_filter([
			$GLOBALS['association_metas']['pc_ventes'] ?? '',
			$GLOBALS['association_metas']['pc_frais_envoi'] ?? '',
		], 'strlen'),
	]);
}

function association_ventes_compte_creer($date, $montant, $justification, $journal, $id_vente, $id_auteur, $imputation) {
	if (!association_ventes_comptabilite_disponible()) {
		return 0;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecriture_creer([
		'date' => $date, 'recette' => $montant, 'depense' => 0,
		'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
		'id_auteur' => (int) $id_auteur, 'id_objet' => (int) $id_vente, 'objet' => 'asso_vente',
	]);
}

function association_ventes_compte_modifier($id_compte, $date, $montant, $justification, $journal, $id_vente, $id_auteur, $imputation) {
	if (!association_ventes_comptabilite_disponible()) {
		return true;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecriture_modifier($id_compte, [
		'date' => $date, 'recette' => $montant, 'depense' => 0,
		'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
		'id_auteur' => (int) $id_auteur, 'id_objet' => (int) $id_vente, 'objet' => 'asso_vente',
	]);
}
