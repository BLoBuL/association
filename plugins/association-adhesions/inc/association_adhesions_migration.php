<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Copie idempotente des données métier de cotisation hors du journal comptable.
 *
 * Les anciennes colonnes restent lisibles pendant la transition 4.0 afin que
 * les installations historiques puissent être adoptées sans perte de données.
 */
function association_migrer_cotisations_depuis_comptes() {
	$champ_objet = sql_showtable('spip_asso_comptes', true)['field']['objet'] ?? false;
	$where = $champ_objet
		? array("objet='cotisation' OR reinscription<>'' OR statut_cotisation<>''")
		: array("reinscription<>'' OR statut_cotisation<>''");
	foreach (sql_allfetsel('*', 'spip_asso_comptes', $where, '', 'id_compte') as $compte) {
		$id_compte = (int) $compte['id_compte'];
		if (!$id_compte) {
			continue;
		}

		$id_cotisation = (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_compte=' . $id_compte);
		if (!$id_cotisation) {
			$id_cotisation = (int) sql_insertq('spip_asso_cotisations', array(
				'id_compte' => $id_compte,
				'id_auteur' => (int) ($compte['id_auteur'] ?? 0),
				'id_categorie' => (int) ($compte['id_categorie'] ?? 0),
				'id_transaction' => (int) ($compte['id_transaction'] ?? 0),
				'inscription' => (string) ($compte['reinscription'] ?? ''),
				'statut' => (string) ($compte['statut_cotisation'] ?? 'attente'),
				'date_creation' => (string) ($compte['date'] ?? date('Y-m-d H:i:s')),
				// Les dates de validité n'existaient pas par cotisation dans le
				// schéma historique. Elles restent NULL plutôt que d'être déduites.
				'montant' => (float) ($compte['recette'] ?? 0),
				'devise' => association_cotisation_devise_historique($compte),
			));
		}

		association_cotisation_lier_compte($compte, $id_cotisation);
	}
}

/**
 * Retrouve la devise historique la plus fiable : transaction, catégorie, puis
 * devise par défaut du site.
 */
function association_cotisation_devise_historique($compte) {
	$devise = '';
	$id_transaction = (int) ($compte['id_transaction'] ?? 0);
	$transaction = sql_showtable('spip_transactions', true);
	if ($id_transaction && isset($transaction['field']['devise'])) {
		$devise = (string) sql_getfetsel('devise', 'spip_transactions', 'id_transaction=' . $id_transaction);
	}

	$id_categorie = (int) ($compte['id_categorie'] ?? 0);
	$categorie = sql_showtable('spip_asso_categories_adherents', true);
	if ($devise === '' && $id_categorie && isset($categorie['field']['devise'])) {
		$devise = (string) sql_getfetsel('devise', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
	}

	$devise = strtoupper(trim($devise));
	if (preg_match('/^[A-Z]{3}$/', $devise)) {
		return $devise;
	}

	include_spip('inc/cotisations_devises');
	return function_exists('association_cotisation_devise_defaut')
		? association_cotisation_devise_defaut()
		: '';
}

/**
 * Rattache l'écriture comptable à l'identifiant de la nouvelle cotisation.
 */
function association_cotisation_lier_compte($compte, $id_cotisation) {
	$id_compte = (int) ($compte['id_compte'] ?? 0);
	if (!$id_compte || !$id_cotisation || !isset($compte['objet']) || $compte['objet'] !== 'cotisation') {
		return;
	}
	sql_updateq('spip_asso_comptes', array('id_objet' => (int) $id_cotisation), 'id_compte=' . $id_compte);
}

/**
 * Complète sans écraser les données métier déjà modifiées après la séparation.
 */
function association_completer_migration_cotisations() {
	association_migrer_cotisations_depuis_comptes();
	foreach (sql_allfetsel('*', 'spip_asso_cotisations', "devise='' OR devise IS NULL") as $cotisation) {
		$id_compte = (int) ($cotisation['id_compte'] ?? 0);
		$compte = $id_compte ? sql_fetsel('*', 'spip_asso_comptes', 'id_compte=' . $id_compte) : array();
		if (!$compte) {
			continue;
		}
		sql_updateq(
			'spip_asso_cotisations',
			array('devise' => association_cotisation_devise_historique($compte)),
			'id_cotisation=' . (int) $cotisation['id_cotisation']
		);
		association_cotisation_lier_compte($compte, (int) $cotisation['id_cotisation']);
	}
}
