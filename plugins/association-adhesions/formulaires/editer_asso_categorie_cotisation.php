<?php

/**
 * Formulaire d'édition de catégorie de cotisation
 *
 * @plugin     Associaspip
 * @copyright  2007-2024
 * @licence    GNU/GPL
 * @package    SPIP\Associaspip\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/saisies');
include_spip('inc/bank');
include_spip('inc/cotisations_devises');

/**
 * Saisies du formulaire d'édition de catégorie de cotisation
 *
 * @param int $id_categorie Identifiant de la catégorie à éditer
 * @return array Tableau des saisies
 */
function formulaires_editer_asso_categorie_cotisation_saisies_dist($id_categorie) {
	$devise_defaut = association_cotisation_devise_defaut();
	$devises = association_cotisation_devises_disponibles();
	$mode_paiement_liste = $mode_paiement_listes = [];

	if (function_exists('bank_lister_configs')) {
		$bank_lister_configs = bank_lister_configs();
		$mode_paiement_liste = !empty($GLOBALS['association_metas']['mode_paiement_adhesion']) ?
			unserialize($GLOBALS['association_metas']['mode_paiement_adhesion']) : [];

		foreach ($bank_lister_configs as $val) {
			$mode_paiement_liste = is_array($mode_paiement_liste) ? $mode_paiement_liste : [$mode_paiement_liste];
			if (in_array(bank_config_id($val), $mode_paiement_liste, true)) {
				$presta_id = bank_config_id($val);
				$presta_type = $val['presta'];
				$presta_label = empty($val['label']) ? '' : ' (' . $val['label'] . ')';
				$mode_paiement_listes += [$presta_id => _T('bank:label_presta_' . $presta_type . '') . $presta_label];
			}
		}
	}

	$saisies = [
		[
			'saisie' => 'input',
			'options' => [
				'nom' => 'valeur',
				'label' => _T('association_adhesions:nom_cotisation'),
				'type' => 'text',
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'statut',
				'label' => _T('association_adhesions:edit_cotisation_statut_label'),
				'explication' => _T('association_adhesions:edit_cotisation_statut_explication'),
				'data' => [
					'ok' => _T('association_adhesions:participation_active'),
					'desactive' => _T('association_adhesions:participation_desactive'),
				],
				'cacher_option_intro' => 'oui',
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'eligibilite',
				'label' => _T('association_adhesions:edit_cotisation_eligibilite_label'),
				'explication' => _T('association_adhesions:edit_cotisation_eligibilite_explication'),
				'data' => [
					'tout' => _T('association_adhesions:choix_eligibilite_tout') . ' ' . _T('association_adhesions:defaut'),
					'inscription' => _T('association_adhesions:choix_eligibilite_inscription'),
					'reinscription' => _T('association_adhesions:choix_eligibilite_reinscription'),
				],
				'defaut' => 'tout',
				'cacher_option_intro' => 'oui',
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'type_adherent',
				'label' => _T('association_adhesions:edit_cotisation_type_label'),
				'explication' => _T('association_adhesions:edit_cotisation_type_explication'),
				'data' => [
					'adherent' => _T('association_adhesions:cotisation_adherent') . ' ' . _T('association_adhesions:defaut'),
					'famille' => _T('association_adhesions:cotisation_famille'),
					'couple' => _T('association_adhesions:cotisation_couple'),
					'individuel' => _T('association_adhesions:cotisation_individuel'),
					'conjoint' => _T('association_adhesions:cotisation_conjoint'),
					'etudiant' => _T('association_adhesions:cotisation_etudiant'),
					'enfant' => _T('association_adhesions:cotisation_enfant'),
					'entreprise' => _T('association_adhesions:cotisation_entreprise'),
					'babysitting' => _T('association_adhesions:cotisation_babysitting'),
					'partenaire' => _T('association_adhesions:cotisation_partenaire'),
					'vip' => _T('association_adhesions:cotisation_vip'),
				],
				'defaut' => 'adherent',
				'cacher_option_intro' => 'oui',
			],
		],

		[
			'saisie' => 'input',
			'options' => [
				'nom' => 'nombre_enfants',
				'label' => _T('association_adhesions:edit_cotisation_nombre_enfants_label'),
				'explication' => _T('association_adhesions:edit_cotisation_nombre_enfants_explication'),
				'type' => 'number',
				'step' => '1',
				'afficher_si' => '@type_adherent@ IN "famille,enfant"',
				'defaut' => 0,
			],
		],

		[
			'saisie' => 'radio',
			'options' => [
				'nom' => 'document_justificatif',
				'label' => '<:association_adhesions:form_categorie_adherent_justificatif_label:>',
				'explication' => '<:association_adhesions:form_categorie_adherent_justificatif_explication:>',
				'data' => [
					'oui' => '<:association_adhesions:oui:>',
					'non' => '<:association_adhesions:non:>',
				],
				'defaut' => 'non',
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'validation',
				'label' => _T('association_adhesions:form_categorie_cotisation_validation_label'),
				'explication' => _T('association_adhesions:form_categorie_cotisation_validation_explication'),
				'data' => [
					'auto' => _T('association_adhesions:choix_validation_cotisation_auto') . ' ' . _T('association_adhesions:defaut'),
					'pre-paiement' => _T('association_adhesions:choix_validation_cotisation_pre_paiement'),
					'post-paiement' => _T('association_adhesions:choix_validation_cotisation_post_paiement'),
				],
				'defaut' => 'auto',
				'cacher_option_intro' => 'oui',
			],
		],

		// TODO : gérer les dates de validité d'une catégorie de cotisation
		// TODO : gérer l'activation de la cotisation pour les inscriptions
		// TODO : gérer la durée de validité de la cotisation

		/*      [
			 'saisie' => 'date_debut',
			 'options' => [
				 'nom' => 'mois_debut',
				 'label' => _T('association_adhesions:mois_debut_validite'),
				 'data' => array_combine(
					 range(1, 12),
					 array_map(fn($m) => _T("association_adhesions:mois_" . strtolower(date('F', mktime(0, 0, 0, $m)))), range(1, 12))
				 )
			 ]
		 ],
		 [
			 'saisie' => 'selection',
			 'options' => [
				 'nom' => 'jour_debut',
				 'label' => _T('association_adhesions:jour_debut_validite'),
				 'data' => array_combine(range(1, 31), range(1, 31))
			 ]
		 ],
		 [
			 'saisie' => 'selection',
			 'options' => [
				 'nom' => 'mois_fin',
				 'label' => _T('association_adhesions:mois_fin_validite'),
				 'data' => array_combine(
					 range(1, 12),
					 array_map(fn($m) => _T("association_adhesions:mois_" . strtolower(date('F', mktime(0, 0, 0, $m)))), range(1, 12))
				 )
			 ]
		 ],
		 [
			 'saisie' => 'selection',
			 'options' => [
				 'nom' => 'jour_fin',
				 'label' => _T('association_adhesions:jour_fin_validite'),
				 'data' => array_combine(range(1, 31), range(1, 31))
			 ]
		 ],*/

		// TODO : Rajouter une durée de validité de la cotisation

		[
			'saisie' => 'input',
			'options' => [
				'nom' => 'commentaires',
				'label' => _T('association_adhesions:explication_participation'),
				'rows' => 3,
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'devise',
				'label' => _T('association_adhesions:devise_cotisation_label'),
				'explication' => _T('association_adhesions:devise_cotisation_explication', ['devise' => $devise_defaut]),
				'data' => $devises,
				'defaut' => $devise_defaut,
				'cacher_option_intro' => 'oui',
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'nom' => 'cotisation',
				'label' => _T('association_adhesions:montant_cotisation'),
				'type' => 'number',
				'step' => '0.01',
			],
		],
		[
			'saisie' => 'checkbox',
			'options' => [
				'nom' => 'mode_paiement',
				'label' => _T('association_adhesions:mode_paiement_cotisation_label'),
				'datas' => $mode_paiement_listes,
				'defaut' => $mode_paiement_liste,
				'explication' => _T('association_adhesions:mode_paiement_cotisation_explication'),
			],
		],
	];

	if (!association_cotisations_multidevises_actives()) {
		foreach ($saisies as $index => $saisie) {
			if (($saisie['options']['nom'] ?? '') === 'devise') {
				unset($saisies[$index]);
			}
		}
		$saisies = array_values($saisies);
	}

	return $saisies;
}
/**
 * Charger les données du formulaire d'édition de catégorie de cotisation
 *
 * @param int $id_categorie Identifiant de la catégorie à éditer
 * @return array Contexte de chargement
 */
function formulaires_editer_asso_categorie_cotisation_charger_dist($id_categorie = 0) {
	$contexte = [];

	// Si on édite une catégorie existante
	if ($id_categorie) {
		$data = sql_fetsel('*', 'spip_asso_categories_adherents', "id_categorie=$id_categorie");
		if ($data) {
			$contexte = $data;
			if (empty($contexte['devise'])) {
				$contexte['devise'] = association_cotisation_devise_defaut();
			}

			// Décomposer les dates MM-JJ en mois et jour
			if (!empty($data['date_debut_validite'])) {
				[$mois_debut, $jour_debut] = explode('-', $data['date_debut_validite']);
				$contexte['mois_debut'] = intval($mois_debut);
				$contexte['jour_debut'] = intval($jour_debut);
			}

			if (!empty($data['date_fin_validite'])) {
				[$mois_fin, $jour_fin] = explode('-', $data['date_fin_validite']);
				$contexte['mois_fin'] = intval($mois_fin);
				$contexte['jour_fin'] = intval($jour_fin);
			}
		}
	} else {
		// Valeurs par défaut pour une nouvelle catégorie
		$contexte = [
			'statut' => 'ok',
			'paiement_en_ligne' => 1,
			'devise' => association_cotisation_devise_defaut(),
		];
	}

	$contexte['id_categorie'] = $id_categorie;
	$contexte['_id_categorie'] = $id_categorie; // Pour la construction du formulaire

	// Ajouter les saisies au contexte
	$contexte['_saisies'] = formulaires_editer_asso_categorie_cotisation_saisies_dist($id_categorie);

	return $contexte;
}

/**
 * Vérifier les données du formulaire d'édition de catégorie de cotisation
 *
 * @param int $id_categorie Identifiant de la catégorie à éditer
 * @return array Erreurs éventuelles
 */
// Fonction de vérification
function formulaires_editer_asso_categorie_cotisation_verifier_dist($id_categorie = 0) {
	$erreurs = [];

	// Vérification des champs obligatoires (hors montant qui peut valoir 0)
	foreach (['valeur', 'statut', 'type_adherent'] as $obligatoire) {
		if (!_request($obligatoire)) {
			$erreurs[$obligatoire] = _T('info_obligatoire');
		}
	}

	// Vérification du montant : obligatoire et numérique (0 autorisé)
	$cotisation = _request('cotisation');
	if ($cotisation === null || $cotisation === '') {
		$erreurs['cotisation'] = _T('info_obligatoire');
	} elseif (!is_numeric($cotisation) || floatval($cotisation) < 0) {
		$erreurs['cotisation'] = _T('association_adhesions:erreur_montant_invalide');
	}

	if (association_cotisations_multidevises_actives()) {
		$devise = strtoupper((string) _request('devise'));
		if (!isset(association_cotisation_devises_disponibles()[$devise])) {
			$erreurs['devise'] = _T('association_adhesions:erreur_devise_cotisation_invalide');
		}
	}

	// Vérification de la validité des dates
	if (_request('mois_debut') && _request('jour_debut')) {
		$jours_par_mois = [
			1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
			7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
		];

		$mois_debut = intval(_request('mois_debut'));
		$jour_debut = intval(_request('jour_debut'));

		if ($jour_debut > $jours_par_mois[$mois_debut]) {
			$erreurs['jour_debut'] = _T('association_adhesions:erreur_jour_invalide_pour_mois');
		}

		if (_request('mois_fin') && _request('jour_fin')) {
			$mois_fin = intval(_request('mois_fin'));
			$jour_fin = intval(_request('jour_fin'));

			if ($jour_fin > $jours_par_mois[$mois_fin]) {
				$erreurs['jour_fin'] = _T('association_adhesions:erreur_jour_invalide_pour_mois');
			}
		}
	}

	if (count($erreurs)) {
		$erreurs['message_erreur'] = _T('association_adhesions:erreur_titre');
	}

	return $erreurs;
}

// Fonction de traitement
function formulaires_editer_asso_categorie_cotisation_traiter_dist($id_categorie = 0) {
	// Formatage des dates
	$date_debut_validite = null;
	$date_fin_validite = null;

	/*
	if (_request('mois_debut') && _request('jour_debut')) {
			$date_debut_validite = sprintf('%02d-%02d',
				intval(_request('mois_debut')),
				intval(_request('jour_debut'))
			);
		}

		if (_request('mois_fin') && _request('jour_fin')) {
			$date_fin_validite = sprintf('%02d-%02d',
				intval(_request('mois_fin')),
				intval(_request('jour_fin'))
			);
		}
	*/

	$data = [
		'valeur' => _request('valeur'),
		'statut' => _request('statut') ?: 'ok',
		'validation' => _request('validation') ?: 'auto',
		'commentaires' => _request('commentaires') ?: '',
		'cotisation' => _request('cotisation') ?: 0,
		'devise' => association_cotisations_multidevises_actives()
			? strtoupper((string) _request('devise'))
			: association_cotisation_devise_defaut(),
		'paiement_en_ligne' => (_request('paiement_en_ligne') == 'on' || _request('paiement_en_ligne') == '1') ? 1 : 0,
		'type_adherent' => _request('type_adherent') ?: '',
		'document_justificatif' => _request('document_justificatif'),
		'nombre_enfants' => _request('nombre_enfants') ?? 0,
		'eligibilite' => _request('eligibilite') ?: 'tout',
		'mode_paiement' => is_array(_request('mode_paiement')) ? implode(',', _request('mode_paiement')) : '',
		// 'date_debut_validite' => $date_debut_validite,
		// 'date_fin_validite' => $date_fin_validite
	];

	// Insertion ou mise à jour
	if ($id_categorie) {
		sql_updateq('spip_asso_categories_adherents', $data, "id_categorie=$id_categorie");
		$message = _T('association_adhesions:categorie_mise_a_jour');
	} else {
		$id_categorie = sql_insertq('spip_asso_categories_adherents', $data);
		$message = _T('association_adhesions:categorie_ajoutee');
	}

	return [
		'message_ok' => $message,
		'redirect' => generer_url_ecrire('categories_cotisation'),
		'id_categorie' => $id_categorie,
	];
}
