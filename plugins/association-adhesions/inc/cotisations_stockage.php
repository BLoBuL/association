<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourne une cotisation métier avec son écriture comptable optionnelle.
 */
function association_cotisation_lire_par_compte($id_compte) {
	$id_compte = (int) $id_compte;
	$cotisation = sql_fetsel('*', 'spip_asso_cotisations', 'id_compte=' . $id_compte);
	if (!$cotisation) return array();
	$compte = $id_compte ? sql_fetsel('*', 'spip_asso_comptes', 'id_compte=' . $id_compte) : array();
	return array_merge((array) $compte, $cotisation, array(
		'reinscription' => $cotisation['inscription'],
		'statut_cotisation' => $cotisation['statut'],
	));
}

/**
 * Crée ou actualise la ligne métier associée à une écriture de cotisation.
 */
function association_cotisation_synchroniser_depuis_compte($id_compte, $donnees = array()) {
	$id_compte = (int) $id_compte;
	$compte = $id_compte ? sql_fetsel('*', 'spip_asso_comptes', 'id_compte=' . $id_compte) : array();
	if (!$compte) {
		return 0;
	}
	$devise = strtoupper(trim((string) ($donnees['devise'] ?? '')));
	$id_categorie = (int) ($donnees['id_categorie'] ?? 0);
	if ($devise === '' && $id_categorie) {
		$devise = strtoupper(trim((string) sql_getfetsel(
			'devise',
			'spip_asso_categories_adherents',
			'id_categorie=' . $id_categorie
		)));
	}
	include_spip('inc/cotisations_devises');
	if (!preg_match('/^[A-Z]{3}$/', $devise) && function_exists('association_cotisation_devise_defaut')) {
		$devise = association_cotisation_devise_defaut();
	}

	$valeurs = array(
		'id_compte' => $id_compte,
		'id_auteur' => (int) ($donnees['id_auteur'] ?? $compte['id_auteur'] ?? 0),
		'id_categorie' => $id_categorie,
		'id_transaction' => (int) ($donnees['id_transaction'] ?? $compte['id_transaction'] ?? 0),
		'inscription' => (string) ($donnees['reinscription'] ?? $donnees['inscription'] ?? ''),
		'statut' => (string) ($donnees['statut_cotisation'] ?? $donnees['statut'] ?? 'attente'),
		'date_creation' => (string) ($donnees['date'] ?? $compte['date'] ?? date('Y-m-d H:i:s')),
		'montant' => (float) ($donnees['recette'] ?? $donnees['montant'] ?? $compte['recette'] ?? 0),
		'devise' => $devise,
	);
	if (array_key_exists('date_debut_validite', $donnees)) {
		$valeurs['date_debut_validite'] = $donnees['date_debut_validite'] ?: null;
	}
	if (array_key_exists('date_fin_validite', $donnees)) {
		$valeurs['date_fin_validite'] = $donnees['date_fin_validite'] ?: null;
	}
	$id_cotisation = (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_compte=' . $id_compte);
	if ($id_cotisation) {
		sql_updateq('spip_asso_cotisations', $valeurs, 'id_cotisation=' . $id_cotisation);
		association_cotisation_rattacher_compte($id_compte, $id_cotisation);
		return $id_cotisation;
	}
	$id_cotisation = (int) sql_insertq('spip_asso_cotisations', $valeurs);
	association_cotisation_rattacher_compte($id_compte, $id_cotisation);
	return $id_cotisation;
}

/**
 * Maintient le lien comptable vers l'objet métier cotisation.
 */
function association_cotisation_rattacher_compte($id_compte, $id_cotisation) {
	$id_compte = (int) $id_compte;
	$id_cotisation = (int) $id_cotisation;
	if ($id_compte && $id_cotisation) {
		sql_updateq(
			'spip_asso_comptes',
			array('objet' => 'cotisation', 'id_objet' => $id_cotisation),
			'id_compte=' . $id_compte
		);
	}
}
