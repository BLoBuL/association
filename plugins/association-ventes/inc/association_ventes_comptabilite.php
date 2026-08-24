<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_ventes_compte_where($id_vente, $imputation) {
	$id_vente = (int) $id_vente;
	$imputation = sql_quote((string) $imputation);

	return "imputation={$imputation} AND ((objet='asso_vente' AND id_objet={$id_vente})"
		. " OR id_journal={$id_vente})";
}

function association_ventes_compte_lire($id_vente, $imputation) {
	return sql_fetsel(
		'id_compte,journal,id_auteur,id_objet,objet,imputation',
		'spip_asso_comptes',
		association_ventes_compte_where($id_vente, $imputation),
		'',
		"(objet='asso_vente') DESC, id_compte DESC"
	) ?: array();
}

function association_ventes_compte_creer($date, $montant, $justification, $journal, $id_vente, $id_auteur, $imputation) {
	include_spip('inc/comptes');

	return inserer_compte(
		$date,
		$montant,
		0,
		$justification,
		$imputation,
		$journal,
		(int) $id_auteur,
		(int) $id_vente,
		'asso_vente'
	);
}

function association_ventes_compte_modifier($id_compte, $date, $montant, $justification, $journal, $id_vente, $id_auteur, $imputation) {
	include_spip('inc/comptes');
	$id_compte = modifier_compte($id_compte, $date, $montant, 0, $justification, $imputation, $journal);
	if ($id_compte) {
		sql_updateq('spip_asso_comptes', array(
			'justification' => $justification,
			'journal' => $journal,
			'id_auteur' => (int) $id_auteur,
			'id_objet' => (int) $id_vente,
			'objet' => 'asso_vente',
		), 'id_compte=' . (int) $id_compte);
	}

	return $id_compte;
}
