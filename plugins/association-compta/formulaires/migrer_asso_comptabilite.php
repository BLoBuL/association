<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/comptes');
include_spip('inc/destinations');
include_spip('inc/config');

// Déclaration des saisies
function formulaires_migrer_asso_comptabilite_saisies_dist() {
	$saisies = [];

	$options_saisies = [
		'options' => [
			'texte_submit' => 'Valider',
			'etapes_suivant' => 'Suivant',
			'etapes_precedent' => 'Précédent',
			'etapes_navigation' => 'on',
			'etapes_precedent_suivant_titrer' => '',
		],
	];

	$liste_compte_imputation = preparer_liste_compte_imputation();
	$saisies = $options_saisies;

	$saisies[] = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'form_migrer_asso_comptabilite_fieldset',
			'label' => '<:association_compta:form_migrer_asso_comptabilite_label:>',
			'explication' => '<:association_compta:form_migrer_asso_comptabilite_explication:>',
		],
		'saisies' => [
			// Choix du mode de migration
			[
				'saisie' => 'selection',
				'options' => [
					'nom' => 'type_migration',
					'label' => '<:association_compta:form_migrer_asso_comptabilite_mode_label:>',
					'explication' => '<:association_compta:form_migrer_asso_comptabilite_mode_explication:>',
					'data' => [
						'auto' => _T('association_compta:form_migrer_asso_comptabilite_mode_auto'),
						'manuelle' => _T('association_compta:form_migrer_asso_comptabilite_mode_manuelle'),
					],
					'defaut' => 'auto',
					'obligatoire' => 'oui',
				],
			],
			// Bloc options manuelles (affiché seulement si mode = manuelle)
			[
				'saisie' => 'checkbox',
				'options' => [
					'nom' => 'imputations_existantes',
					'label' => '<:association_compta:form_migrer_asso_comptabilite_imputation_label:>',
					'data' => saisies_tableau2chaine($liste_compte_imputation),
					'obligatoire' => 'oui',
					'afficher_si' => '@type_migration@ == "manuelle"',
				],
			],
			[
				'saisie' => 'selection',
				'options' => [
					'nom' => 'pc_cotisations_creance',
					'label' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
					'explication' => _T('association_config:form_migrer_asso_comptabilite_compte_creance_label'),
					'data' => preparer_liste_asso_plan_compte('data_saisies', '1'),
					'defaut' => '',
					'afficher_si' => '@type_migration@ == "manuelle"',
				],
			],
			[
				'saisie' => 'selection',
				'options' => [
					'nom' => 'pc_cotisations_paiement',
					'label' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
					'explication' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
					'data' => preparer_liste_asso_plan_compte('data_saisies', '7'),
					'defaut' => '',
					'afficher_si' => '@type_migration@ == "manuelle"',
				],
			],
		],
	];

	return $saisies;
}

// Chargement
function formulaires_migrer_asso_comptabilite_charger_dist() {
	return [];
}

// Vérification
function formulaires_migrer_asso_comptabilite_verifier_dist() {
	$erreurs = [];

	$type_migration = _request('type_migration') ?: '';
	if (!$type_migration) {
		$erreurs['type_migration'] = _T('association_compta:erreur_obligatoire');
	}

	if ($type_migration === 'manuelle') {
		$imputations_existantes = _request('imputations_existantes');
		if (!$imputations_existantes || !is_array($imputations_existantes) || !count($imputations_existantes)) {
			$erreurs['imputations_existantes'] = _T('association_compta:erreur_obligatoire');
		}
		if (!_request('pc_cotisations_creance')) {
			$erreurs['pc_cotisations_creance'] = _T('association_compta:erreur_obligatoire');
		}
		if (!_request('pc_cotisations_paiement')) {
			$erreurs['pc_cotisations_paiement'] = _T('association_compta:erreur_obligatoire');
		}
	}

	return $erreurs;
}

// Traitement
function formulaires_migrer_asso_comptabilite_traiter_dist() {
	$retour = [];

	$type_migration = _request('type_migration');

	if ($type_migration === 'manuelle') {
		$imputations_existantes = _request('imputations_existantes');
		$pc_cotisations_creance = _request('pc_cotisations_creance');
		$pc_cotisations_paiement = _request('pc_cotisations_paiement');

		pipeline('association_compta_migration_metiers', [
			'args' => [
				'mode' => 'manuelle',
				'imputations_existantes' => (array) $imputations_existantes,
				'pc_cotisations_creance' => $pc_cotisations_creance,
				'pc_cotisations_paiement' => $pc_cotisations_paiement,
			],
			'data' => [],
		]);
	} else {
		// Nouveau: en mode auto, on nettoie d'abord la BDD puis on applique la migration et on synchronise les événements
		include_spip('genie/association_maintenance_bdd');

		$lot_max = 100000;

		// Laisser chaque plugin métier migrer puis synchroniser ses écritures.
		pipeline('association_compta_migration_metiers', [
			'args' => ['mode' => 'auto', 'lot' => $lot_max, 'maintenant' => time(), 'mois_non_encaisse' => 6],
			'data' => [],
		]);
	}

	$retour['message_ok'] = _T('association_compta:message_import_reussi');
	$retour['redirect'] = generer_url_ecrire('comptes');

	return $retour;
}

// Prépare la liste des comptes d'imputation existants
function preparer_liste_compte_imputation() {
	$res = [];

	$query_comptes = sql_select(
		'imputation, COUNT(*) as nb_occurrences',
		'spip_asso_comptes',
		'id_categorie > 0',
		'imputation'
	);
	while ($row = sql_fetch($query_comptes)) {
		$imputation = ($row['imputation']) ? $row['imputation'] : '0';
		$res[$imputation] = _T('association_compta:compte') . ' ' . $imputation . ' (' . $row['nb_occurrences'] . ' ' . _T('association_compta:nb_occurrences') . ')';
	}
	return $res;
}
