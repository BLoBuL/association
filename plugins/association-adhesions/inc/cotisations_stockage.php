<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

if (!function_exists('association_adhesions_module_actif')) {
	function association_adhesions_module_actif($prefixe) {
		include_spip('inc/association_capacites');
		return association_plugin_actif($prefixe);
	}
}

/**
 * Retourne une cotisation métier avec son écriture comptable optionnelle.
 */
function association_cotisation_lire_par_compte($id_compte) {
	$id_compte = (int) $id_compte;
	$cotisation = sql_fetsel('*', 'spip_asso_cotisations', 'id_compte=' . $id_compte);
	if (!$cotisation) {
		$cotisation = sql_fetsel('*', 'spip_asso_cotisations', 'id_cotisation=' . $id_compte);
	}
	if (!$cotisation) {
		return [];
	}
	$compte = [];
	if (!empty($cotisation['id_compte']) && association_adhesions_module_actif('association_compta')) {
		include_spip('inc/association_compta_ecritures');
		$compte = association_compta_ecriture_lire((int) $cotisation['id_compte']);
	}
	return array_merge((array) $compte, $cotisation, [
		'reinscription' => $cotisation['inscription'],
		'statut_cotisation' => $cotisation['statut'],
	]);
}

/**
 * Crée ou actualise la ligne métier associée à une écriture de cotisation.
 */
function association_cotisation_synchroniser_depuis_compte($id_compte, $donnees = []) {
	$id_compte = (int) $id_compte;
	include_spip('inc/association_compta_ecritures');
	$compte = association_compta_ecriture_lire($id_compte);
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

	$valeurs = [
		'id_compte' => $id_compte,
		'id_auteur' => (int) ($donnees['id_auteur'] ?? $compte['id_auteur'] ?? 0),
		'id_categorie' => $id_categorie,
		'id_transaction' => (int) ($donnees['id_transaction'] ?? $compte['id_transaction'] ?? 0),
		'inscription' => (string) ($donnees['reinscription'] ?? $donnees['inscription'] ?? ''),
		'statut' => (string) ($donnees['statut_cotisation'] ?? $donnees['statut'] ?? 'attente'),
		'date_creation' => (string) ($donnees['date'] ?? $compte['date'] ?? date('Y-m-d H:i:s')),
		'montant' => (float) ($donnees['recette'] ?? $donnees['montant'] ?? $compte['recette'] ?? 0),
		'devise' => $devise,
	];
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
		include_spip('inc/association_compta_ecritures');
		association_compta_ecriture_modifier($id_compte, ['objet' => 'cotisation', 'id_objet' => $id_cotisation]);
	}
}

/**
 * Modifie l'état métier et maintient temporairement la colonne historique.
 */
function association_cotisation_statut_modifier($id_compte, $statut) {
	$id_compte = (int) $id_compte;
	$statut = (string) $statut;
	$id_cotisation = $id_compte
		? (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_compte=' . $id_compte)
		: 0;
	if (!$id_cotisation && $id_compte) {
		$id_cotisation = (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_cotisation=' . $id_compte);
	}
	if (!$id_cotisation) {
		return false;
	}
	if (sql_updateq('spip_asso_cotisations', ['statut' => $statut], 'id_cotisation=' . $id_cotisation) === false) {
		return false;
	}

	// Compatibilité 4.0 : cette colonne sera retirée après validation de tous les
	// sites historiques. Aucun lecteur métier de la suite ne l'utilise plus.
	$table_compte = association_adhesions_module_actif('association_compta') ? sql_showtable('spip_asso_comptes', true) : [];
	if (!empty($table_compte['field']['statut_cotisation'])) {
		// Colonne transitoire hors du contrat normalisé de Comptabilité.
		sql_updateq('spip_asso_comptes', ['statut_cotisation' => $statut], 'id_compte=' . $id_compte);
	}
	return true;
}
