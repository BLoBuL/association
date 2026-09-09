<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_dons_comptabilite_disponible() {
	return association_plugin_actif('association_compta');
}

/**
 * Construire la sélection canonique et historique de l'écriture d'un don.
 */
function association_dons_compte_lire($id_don) {
	if (!association_dons_comptabilite_disponible()) {
		return [];
	}
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_objet_lister('asso_don', $id_don, [
		'legacy_id_journal' => true,
		'imputations' => [$GLOBALS['association_metas']['pc_dons'] ?? ''],
		'champs' => 'id_compte,journal,id_auteur,id_objet,objet',
	]);
	return $ecritures[0] ?? [];
}

function association_dons_compte_supprimer($id_don) {
	if (!association_dons_comptabilite_disponible()) {
		return true;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecritures_objet_supprimer('asso_don', $id_don, [
		'legacy_id_journal' => true,
		'imputations' => [$GLOBALS['association_metas']['pc_dons'] ?? ''],
	]);
}

/**
 * Calcule les dons validés d'un adhérent pour une année fiscale.
 */
function association_dons_montant_fiscal($id_auteur, $annee) {
	$id_auteur = (int) $id_auteur;
	$annee = (int) $annee;
	if ($id_auteur <= 0 || $annee < 2000 || $annee > 9999) {
		return 0.0;
	}
	if (!association_dons_comptabilite_disponible()) {
		return (float) sql_getfetsel(
			'SUM(argent)',
			'spip_asso_dons',
			'id_adherent=' . $id_auteur
				. ' AND date_don>=' . sql_quote(sprintf('%04d-01-01', $annee))
				. ' AND date_don<' . sql_quote(sprintf('%04d-01-01', $annee + 1))
		);
	}
	$ids_dons = sql_allfetsel(
		'id_don',
		'spip_asso_dons',
		'id_adherent=' . $id_auteur
			. ' AND date_don>=' . sql_quote(sprintf('%04d-01-01', $annee))
			. ' AND date_don<' . sql_quote(sprintf('%04d-01-01', $annee + 1))
	);
	$ids_dons = array_map('intval', array_column($ids_dons ?: [], 'id_don'));
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecritures_objets_total('asso_don', $ids_dons, [
		'legacy_id_journal' => true,
		'imputations' => [$GLOBALS['association_metas']['pc_dons'] ?? ''],
		'validees' => true,
	]);
}

/**
 * Créer l'écriture comptable canonique d'un don.
 */
function association_dons_compte_creer($date, $montant, $journal, $bienfaiteur, $id_don, $id_auteur = 0) {
	if (!association_dons_comptabilite_disponible()) {
		return 0;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecriture_creer([
		'date' => $date, 'recette' => $montant, 'depense' => 0,
		'justification' => "[->asso_don{$id_don}] - {$bienfaiteur}",
		'imputation' => $GLOBALS['association_metas']['pc_dons'] ?? '', 'journal' => $journal,
		'id_auteur' => (int) $id_auteur, 'id_objet' => (int) $id_don, 'objet' => 'asso_don',
	]);
}

/**
 * Modifier et normaliser l'écriture comptable d'un don.
 */
function association_dons_compte_modifier($id_compte, $date, $montant, $journal, $bienfaiteur, $id_don, $id_auteur = 0) {
	if (!association_dons_comptabilite_disponible()) {
		return true;
	}
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecriture_modifier($id_compte, [
		'date' => $date, 'recette' => $montant, 'depense' => 0,
		'justification' => "[->asso_don{$id_don}] - {$bienfaiteur}",
		'imputation' => $GLOBALS['association_metas']['pc_dons'] ?? '', 'journal' => $journal,
		'id_auteur' => (int) $id_auteur, 'id_objet' => (int) $id_don, 'objet' => 'asso_don',
	]);
}
