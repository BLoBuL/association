<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_adhesions_rgpd_export_categorie($id_categorie) {
	include_spip('inc/cotisations_devises');
	$id_categorie = intval($id_categorie);
	if ($id_categorie <= 0) {
		return array();
	}

	$row = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
	if (!$row) {
		return array();
	}

	return array(
		'id_categorie' => intval($row['id_categorie'] ?? 0),
		'valeur' => (string)($row['valeur'] ?? ''),
		'statut' => (string)($row['statut'] ?? ''),
		'cotisation' => (float)($row['cotisation'] ?? 0),
		'devise' => association_cotisation_resoudre_devise($row['devise'] ?? ''),
		'paiement_en_ligne' => intval($row['paiement_en_ligne'] ?? 0),
		'commentaires' => (string)($row['commentaires'] ?? ''),
		'maj' => association_rgpd_export_date($row['maj'] ?? ''),
	);
}

function association_adhesions_rgpd_export_cotisations($id_auteur) {
	$rows = sql_allfetsel(
		'*',
		'spip_asso_cotisations',
		'id_auteur=' . intval($id_auteur),
		'',
		'date_creation DESC, id_cotisation DESC'
	);
	$export = array();
	foreach ($rows as $row) {
		$id_compte = intval($row['id_compte'] ?? 0);
		$ligne = array(
			'id_cotisation' => intval($row['id_cotisation'] ?? 0),
			'id_compte' => $id_compte,
			'id_auteur' => intval($row['id_auteur'] ?? 0),
			'id_categorie' => intval($row['id_categorie'] ?? 0),
			'id_transaction' => intval($row['id_transaction'] ?? 0),
			'inscription' => (string)($row['inscription'] ?? ''),
			'statut' => (string)($row['statut'] ?? ''),
			'date_creation' => association_rgpd_export_date($row['date_creation'] ?? ''),
			'date_debut_validite' => association_rgpd_export_date($row['date_debut_validite'] ?? ''),
			'date_fin_validite' => association_rgpd_export_date($row['date_fin_validite'] ?? ''),
			'montant' => (float)($row['montant'] ?? 0),
			'devise' => (string)($row['devise'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
			'categorie' => association_adhesions_rgpd_export_categorie(intval($row['id_categorie'] ?? 0)),
		);
		$export[] = $ligne;
	}
	return $export;
}
