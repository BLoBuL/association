<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Construire la sélection canonique et historique de l'écriture d'un don.
 */
function association_dons_compte_where($id_don) {
	$id_don = (int) $id_don;
	$imputation = sql_quote((string) ($GLOBALS['association_metas']['pc_dons'] ?? ''));

	return "(objet='asso_don' AND id_objet={$id_don})"
		. " OR (id_journal={$id_don} AND imputation={$imputation})";
}

/**
 * Retrouver l'écriture comptable liée à un don.
 */
function association_dons_compte_lire($id_don) {
	return sql_fetsel(
		'id_compte,journal,id_auteur,id_objet,objet',
		'spip_asso_comptes',
		association_dons_compte_where($id_don),
		'',
		"(objet='asso_don') DESC, id_compte DESC"
	) ?: array();
}

/**
 * Créer l'écriture comptable canonique d'un don.
 */
function association_dons_compte_creer($date, $montant, $journal, $bienfaiteur, $id_don, $id_auteur = 0) {
	include_spip('inc/comptes');

	return inserer_compte(
		$date,
		$montant,
		0,
		"[->asso_don{$id_don}] - {$bienfaiteur}",
		$GLOBALS['association_metas']['pc_dons'] ?? '',
		$journal,
		(int) $id_auteur,
		(int) $id_don,
		'asso_don'
	);
}

/**
 * Modifier et normaliser l'écriture comptable d'un don.
 */
function association_dons_compte_modifier($id_compte, $date, $montant, $journal, $bienfaiteur, $id_don, $id_auteur = 0) {
	include_spip('inc/comptes');
	$id_compte = modifier_compte(
		$id_compte,
		$date,
		$montant,
		0,
		"[->asso_don{$id_don}] - {$bienfaiteur}",
		$GLOBALS['association_metas']['pc_dons'] ?? '',
		$journal
	);
	if ($id_compte) {
		sql_updateq('spip_asso_comptes', array(
			'justification' => "[->asso_don{$id_don}] - {$bienfaiteur}",
			'journal' => $journal,
			'id_auteur' => (int) $id_auteur,
			'id_objet' => (int) $id_don,
			'objet' => 'asso_don',
		), 'id_compte=' . (int) $id_compte);
	}

	return $id_compte;
}
