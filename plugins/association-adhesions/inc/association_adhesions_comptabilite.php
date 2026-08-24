<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_adhesions_compte_cotisation_creer(
	$date,
	$montant,
	$justification,
	$imputation,
	$journal,
	$id_auteur,
	$inscription,
	$id_categorie,
	$statut,
	$id_transaction,
	$date_fin_validite = null
) {
	if (!association_adhesions_module_actif('association_compta')) {
		return (int) sql_insertq('spip_asso_cotisations', array(
			'id_compte' => null,
			'id_auteur' => (int) $id_auteur,
			'id_categorie' => (int) $id_categorie,
			'id_transaction' => (int) $id_transaction,
			'inscription' => (string) $inscription,
			'statut' => (string) $statut,
			'date_creation' => (string) $date,
			'date_fin_validite' => $date_fin_validite ?: null,
			'montant' => (float) $montant,
			'devise' => function_exists('association_cotisation_devise_defaut') ? association_cotisation_devise_defaut() : '',
		));
	}
	include_spip('inc/association_compta_ecritures');
	$id_compte = association_compta_ecriture_creer(array(
		'date' => $date,
		'date_fin_validite' => $date_fin_validite,
		'recette' => $montant,
		'depense' => 0,
		'justification' => $justification,
		'imputation' => $imputation,
		'journal' => $journal,
		'id_auteur' => (int) $id_auteur,
		'objet' => 'cotisation',
		'id_transaction' => (int) $id_transaction,
		'vu' => $statut === 'ok' ? 1 : 0,
	));
	association_adhesions_compte_cotisation_synchroniser($id_compte, array(
		'reinscription' => $inscription,
		'statut_cotisation' => $statut,
		'id_categorie' => (int) $id_categorie,
		'id_transaction' => (int) $id_transaction,
		'montant' => (float) $montant,
		'date' => $date,
	));
	return $id_compte;
}

function association_adhesions_compte_cotisation_modifier(
	$date,
	$montant,
	$justification,
	$imputation,
	$journal,
	$inscription,
	$id_categorie,
	$id_compte,
	$statut,
	$id_transaction,
	$date_fin_validite = null
) {
	if (!association_adhesions_module_actif('association_compta')) {
		$cotisation = association_cotisation_lire_par_compte((int) $id_compte);
		if (!$cotisation) {
			return false;
		}
		return sql_updateq('spip_asso_cotisations', array(
			'id_categorie' => (int) $id_categorie,
			'id_transaction' => (int) $id_transaction,
			'inscription' => (string) $inscription,
			'statut' => (string) $statut,
			'date_creation' => (string) $date,
			'date_fin_validite' => $date_fin_validite ?: null,
			'montant' => (float) $montant,
		), 'id_cotisation=' . (int) $cotisation['id_cotisation']) !== false;
	}
	include_spip('inc/association_compta_ecritures');
	$id_compte = association_compta_ecriture_modifier($id_compte, array(
		'date' => $date,
		'date_fin_validite' => $date_fin_validite,
		'recette' => $montant,
		'depense' => 0,
		'justification' => $justification,
		'imputation' => $imputation,
		'journal' => $journal,
		'objet' => 'cotisation',
		'id_transaction' => (int) $id_transaction,
		'vu' => $statut === 'ok' ? 1 : 0,
	));
	association_adhesions_compte_cotisation_synchroniser($id_compte, array(
		'inscription' => $inscription,
		'statut' => $statut,
		'id_categorie' => (int) $id_categorie,
		'id_transaction' => (int) $id_transaction,
		'montant' => (float) $montant,
		'date' => $date,
	));
	return $id_compte;
}

function association_adhesions_compte_cotisation_synchroniser($id_compte, array $donnees) {
	include_spip('inc/cotisations_stockage');
	return association_cotisation_synchroniser_depuis_compte((int) $id_compte, $donnees);
}
