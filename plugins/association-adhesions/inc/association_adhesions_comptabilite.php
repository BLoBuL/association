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
	$id_transaction
) {
	include_spip('inc/comptes');

	return inserer_compte(
		$date,
		$montant,
		0,
		$justification,
		$imputation,
		$journal,
		(int) $id_auteur,
		null,
		'cotisation',
		$inscription,
		(int) $id_categorie,
		$statut,
		(int) $id_transaction,
		0
	);
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
	$id_transaction
) {
	include_spip('inc/comptes');

	return modifier_compte(
		$id_compte,
		$date,
		$montant,
		0,
		$justification,
		$imputation,
		$journal,
		null,
		'cotisation',
		$inscription,
		(int) $id_categorie,
		$statut,
		(int) $id_transaction,
		0,
		create_destination_map_for_montant('dc_cotisations', $montant)
	);
}
