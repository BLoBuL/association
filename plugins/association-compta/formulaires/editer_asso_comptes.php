<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/comptes');
include_spip('formulaires/inc/destinations');

function association_compta_objets_definitions() {
	return (array) pipeline('association_compta_objets_declarer', [
		'args' => [],
		'data' => [
			'autre' => ['label' => 'association_compta:choix_autres', 'objet' => 'autre'],
		],
	]);
}

function association_compta_objets_labels(array $definitions) {
	$labels = [];
	foreach ($definitions as $type => $definition) {
		$labels[$type] = _T($definition['label']);
	}
	return $labels;
}

function association_compta_objets_saisies(array $definitions, array $defauts = []) {
	$saisies = [];
	foreach ($definitions as $type => $definition) {
		if (empty($definition['champ'])) {
			continue;
		}
		$champ = $definition['champ'];
		$saisies[] = [
			'saisie' => 'selection',
			'options' => [
				'nom' => $champ,
				'label' => _T($definition['label_selection'] ?? $definition['label']),
				'explication' => !empty($definition['explication']) ? _T($definition['explication']) : '',
				'data' => (array) ($definition['data'] ?? []),
				'defaut' => $defauts[$champ] ?? '',
				'obligatoire' => 'oui',
				'afficher_si' => '@objet@ == "' . $type . '"',
				'disable_avec_post' => !empty($definition['verrouiller']) ? 'oui' : '',
			],
		];
	}
	return $saisies;
}

function association_compta_objet_contexte(array $definitions, array $compte = []) {
	$contexte = ['type' => _request('objet') ?: 'autre', 'defauts' => [], 'verrouiller' => false];
	foreach ($definitions as $type => $definition) {
		$champ = $definition['champ'] ?? '';
		$id_demande = $champ ? (int) _request($champ) : 0;
		if ($id_demande > 0 || ($compte && ($compte['objet'] ?? '') === ($definition['objet'] ?? ''))) {
			$contexte['type'] = $type;
			$contexte['verrouiller'] = $id_demande > 0;
			if ($champ) {
				$contexte['defauts'][$champ] = $id_demande ?: (int) ($compte['id_objet'] ?? 0);
			}
			break;
		}
	}
	return $contexte;
}
function formulaires_editer_asso_comptes_saisies_dist($id_compte = 'new') {
	$id_compte = _request('id_compte') ?? 'new';

	include_spip('inc/association_compta_ecritures');
	$query_compte = association_compta_ecriture_lire((int) $id_compte);

	// Defaults
	$disable_complet = false;
	$disable_partiel = false;
	$disable_montant = false;

	if (!empty($query_compte)) {
		// Detect if this compte is linked to a transaction
		$has_transaction = !empty($query_compte['id_transaction']) && intval($query_compte['id_transaction']) > 0;

		// If there is no transaction, allow editing whatever the 'vu' flag is
		if (!$has_transaction) {
			$disable_complet = false;
			$disable_partiel = false;
			$disable_montant = false;
		} else {
			// With a linked transaction, keep conservative locking behavior
			if (intval($query_compte['vu']) === 0) {
				// not validated/viewed -> keep some partial disables
				$disable_partiel = 'oui';
				$disable_complet = false;
			}

			if (intval($query_compte['vu']) === 1) {
				// validated/viewed and linked to a transaction -> lock most fields
				$disable_complet = 'oui';
				$disable_partiel = 'oui';
				$disable_montant = 'oui';
			}

			// Always lock montant when a transaction exists as precaution
			$disable_montant = 'oui';
		}
	}
	$definitions_objets = association_compta_objets_definitions();
	$contexte_objet = association_compta_objet_contexte($definitions_objets, (array) $query_compte);
	$objet_defaut = $contexte_objet['type'];
	$definition_defaut = $definitions_objets[$objet_defaut] ?? [];
	$type_operation_defaut = _request('type_operation') ?: (!empty($query_compte['depense']) ? 'depense' : 'recette');
	$imputation_defaut = $query_compte['imputation'] ?? ($definition_defaut['imputation_' . $type_operation_defaut] ?? '');
	$saisies = [
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'objet',
				'label' => _T('association_compta:form_operation_objet_label'),
				'explication' => _T('association_compta:form_operation_objet_explication'),
				'data' => association_compta_objets_labels($definitions_objets),
				'defaut' => $objet_defaut,
				'obligatoire' => 'oui',
				'disable_avec_post' => $contexte_objet['verrouiller'] ? 'oui' : '',
			],
			'verifier' => [
				'type' => 'in_array',
				'options' => [
					'array' => array_keys($definitions_objets),
				],
			],
		],
		[
			'saisie' => 'selection',
			'options' => [
				'nom' => 'imputation',
				'label' => _T('association_compta:form_operation_imputation_label'),
				'explication' => _T('association_compta:form_operation_imputation_explication'),
				'data' => preparer_liste_asso_plan_compte('data_saisies'),
				'defaut' => $imputation_defaut,
				'obligatoire' => 'oui',
				'disable_avec_post' => $disable_complet,
				// 'afficher_si' => '@objet@ == "autre"', // Afficher seulement si "autre" est sélectionné
			],
		],
		[
			'saisie' => 'date',
			'options' => [
				'label' => '<:association_compta:form_operation_date_label:>',
				'nom' => 'date',
				'explication' => '<:association_compta:form_operation_date_explication:>',
				'obligatoire' => 'oui',
				'defaut' => date('Y-m-d'),
				'sqltype' => 'date',
			],
		],
		[
			'saisie' => 'radio',
			'options' => [
				'label' => '<:association_compta:form_operation_type_label:>',
				'nom' => 'type_operation',
				'rows' => 3,
				'explication' => '<:association_compta:form_operation_type_explication:>',
				'defaut' => 'recette',
				'data' => ['recette' => '<:association_compta:choix_recette:>', 'depense' => '<:association_compta:choix_depense:>'],
				'obligatoire' => 'oui',
				'disable_avec_post' => $disable_partiel,
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'label' => '<:association_compta:form_operation_montant_label:>',
				'nom' => 'montant',
				'rows' => 3,
				'explication' => '<:association_compta:form_operation_montant_explication:>',
				'obligatoire' => 'oui',
				'placeholder' => 10,
				'disable_avec_post' => $disable_montant,
			],
			'verifier' => [
				'type' => 'decimal',
				'options' => [
					'min' => 0.01,
					'nb_decimales' => 2,
				],
			],
		],
		[
			'saisie' => 'textarea',
			'options' => [
				'label' => '<:association_compta:form_operation_justification_label:>',
				'nom' => 'justification',
				'rows' => 3,
				'explication' => '<:association_compta:form_operation_justification_explication:>',
				'traitements' => 'propre',
				'obligatoire' => 'oui',
				'disable_avec_post' => $disable_partiel,
			],
		],
		[
			'saisie' => 'hidden',
			'options' => [
				'nom' => 'id_compte',
				'defaut' => ($id_compte === 'new') ? 'new' : intval($id_compte),
			],
		],
	];
	$defauts_objets = $contexte_objet['defauts'];
	foreach ($definitions_objets as $definition) {
		if (!empty($definition['champ'])) {
			$defauts_objets[$definition['champ']] ??= _request($definition['champ']);
		}
	}
	$saisies = array_merge($saisies, association_compta_objets_saisies($definitions_objets, $defauts_objets));

	return $saisies;
}

function formulaires_editer_asso_comptes_charger_dist($id_compte = 'new') {
	$id_compte = _request('id_compte') ?? 'new';
	$valeurs = [];

	// Vérifier les autorisations : simplification -> admins et responsables peuvent
	// accéder au formulaire (création ou édition). On utilise une autorisation
	// unique 'modifier' sur l'objet 'asso_compte' en passant un contexte d'événement
	// si disponible.
	include_spip('inc/autoriser');
	// inclure les autorisations consolidées du plugin
	include_spip('association_autoriser');

	// Normaliser l'id de compte pour l'autorisation (0 = création)
	$id_compte_int = ($id_compte === 'new') ? 0 : intval($id_compte);
	if (!autoriser('modifier', 'asso_compte', $id_compte_int)) {
		// Interdire l'accès au formulaire
		return ['message_erreur' => _T('association_compta:erreur_autorisation')];
	}

	// Si c'est une modification, charger les données du compte
	if ($id_compte !== 'new') {
		include_spip('inc/association_compta_ecritures');
		$query_compte = association_compta_ecriture_lire((int) $id_compte);

		if ($query_compte) {
			$valeurs = $query_compte;
		}

		foreach (association_compta_objets_definitions() as $type => $definition) {
			if (($definition['objet'] ?? '') === ($query_compte['objet'] ?? '') && !empty($definition['champ'])) {
				$valeurs['objet'] = $type;
				$valeurs[$definition['champ']] = (int) $query_compte['id_objet'];
				break;
			}
		}
		if ($query_compte['depense'] > 0) {
			$valeurs['type_operation'] = 'depense';
			$valeurs['montant'] = $query_compte['depense'];

		} elseif ($query_compte['recette'] > 0) {
			$valeurs['type_operation'] = 'recette';
			$valeurs['montant'] = $query_compte['recette'];
		} else {
			$valeurs['type_operation'] = '';
		}

	}
	$definitions = association_compta_objets_definitions();
	$type_objet = $valeurs['objet'] ?? 'autre';
	$destination_defaut = $definitions[$type_objet]['destination_defaut'] ?? '';
	update_destination_contexte_from_compte($valeurs, $id_compte_int, $destination_defaut);

	return $valeurs;

}
/**
 * Vérifie les données soumises dans le formulaire d'édition des comptes associatifs.
 *
 * Cette fonction effectue plusieurs vérifications sur les données soumises :
 * - Vérifie que le montant est valide et supérieur à 0.
 * - Vérifie les contraintes spécifiques selon l'objet de l'opération (autre, événement, cotisation, activité, don).
 * - Définit les imputations et objets associés en fonction des règles de configuration.
 * - Vérifie la validité de la date.
 *
 * @return array Tableau des erreurs détectées, vide si aucune erreur.
 */
function formulaires_editer_asso_comptes_verifier_dist($id_compte = 'new') {
	$erreurs = [];

	// Authorization re-check: ensure the submitter still has right to modify/create
	include_spip('inc/autoriser');
	include_spip('association_autoriser');
	$id_compte_int = ($id_compte === 'new') ? 0 : intval($id_compte);
	if (!autoriser('modifier', 'asso_compte', $id_compte_int)) {
		return ['message_erreur' => _T('association_compta:erreur_autorisation')];
	}

	// Vérification que le montant est supérieur à 0
	if (_request('montant') <= 0) {
		$erreurs['montant'] = _T('association_compta:erreur_recette_depense');
	}

	$type_objet = _request('objet');
	$definitions = association_compta_objets_definitions();
	if (!isset($definitions[$type_objet])) {
		$erreurs['objet'] = _T('association_compta:erreur_obligatoire');
	} else {
		$definition = $definitions[$type_objet];
		set_request('objet', $definition['objet']);
		if (!empty($definition['champ'])) {
			$id_objet = (int) _request($definition['champ']);
			if ($id_objet <= 0) {
				$erreurs[$definition['champ']] = _T('association_compta:erreur_obligatoire');
			} else {
				set_request('id_objet', $id_objet);
			}
		}
		$type_operation = _request('type_operation');
		$imputation_metier = $definition['imputation_' . $type_operation] ?? '';
		if ($imputation_metier !== '') {
			set_request('imputation', $imputation_metier);
		}
		if ($type_objet === 'autre') {
			// Vérification de l'imputation pour les opérations "autre"
			$code = _request('imputation');
			if (empty($code)) {
				$erreurs['imputation'] = _T('association_compta:erreur_imputation_obligatoire');
			} else {
				$depense = _request('depense') ?: 0;
				$recette = _request('recette') ?: 0;

				// Vérification de la compatibilité entre le type d'opération et l'imputation
				if (!array_key_exists('montant', $erreurs)) {
					$type_op = sql_getfetsel('type_op', 'spip_asso_plan', 'code=' . sql_quote($code));
					if ((($type_op == 'credit') && ($depense > 0)) || (($type_op == 'debit') && ($recette > 0))) {
						$erreurs['imputation'] = _T('association_compta:erreur_operation_non_permise_sur_ce_compte');
					}
				}
			}
		}
	}

	// Vérification et définition des montants pour les recettes et dépenses
	if (_request('type_operation') == 'recette' and _request('montant') > 0) {
		set_request('recette', _request('montant'));
		set_request('depense', 0);
	} elseif (_request('type_operation') == 'depense' and _request('montant') > 0) {
		set_request('depense', _request('montant'));
		set_request('recette', 0);
	} else {
		$erreurs['montant'] = _T('association_compta:erreur_recette_depense');
	}

	verifier_destination_comptable((float) _request('montant'), 'montant', $erreurs);

	/*    // Vérification de la date
		if ($erreur_date = association_verifier_date(_request('date'))) {
			$erreurs['date'] = _request('date') . "&nbsp;:&nbsp;" . $erreur_date;
		}*/

	// Ajout d'un message d'erreur global si des erreurs sont détectées
	if (count($erreurs)) {
		$erreurs['message_erreur'] = _T('association_compta:erreur_titre');
	}

	return $erreurs;
}/**
 * Traite le formulaire d'édition des comptes associatifs
 *
 * Cette fonction gère le traitement des données soumises par le formulaire d'édition des comptes.
 * Elle réalise les opérations suivantes:
 * - Récupère l'identifiant du compte (nouveau ou existant)
 * - Enrichit la justification avec les informations de l'événement si applicable
 * - Détermine l'URL de redirection après traitement
 * - Enregistre les données en base via formulaires_editer_objet_traiter()
 *
 * @note Attention: lors de l'édition d'une opération existante, cette fonction
 *       peut créer des doublons si elle est appelée plusieurs fois
 *
 * @return array|int Identifiant du compte créé ou modifié, ou tableau de résultats
 */
function formulaires_editer_asso_comptes_traiter_dist($id_compte = 'new', $id_rubrique = 0, $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = [], $hidden = '') {
	$res = [];

	// Authorization re-check before performing DB changes
	include_spip('inc/autoriser');
	include_spip('association_autoriser');
	$id_compte_int = ($id_compte === 'new') ? 0 : intval($id_compte);
	if (!autoriser('modifier', 'asso_compte', $id_compte_int)) {
		return ['message_erreur' => _T('association_compta:erreur_autorisation')];
	}

	// Récupérer les valeurs postées
	$date = affdate(_request('date'), 'Y-m-d');
	$objet = _request('objet');
	$id_objet = (int) _request('id_objet');
	$imputation = _request('imputation');
	$type_operation = _request('type_operation');
	$montant = _request('montant');
	$journal = _request('journal');
	$justification = _request('justification');
	$recette = $type_operation == 'recette' ? $montant : 0;
	$depense = $type_operation == 'depense' ? $montant : 0;
	$id_compte_demande = (int) _request('id_compte');
	if ($id_compte_demande > 0) {

		$id_compte = $id_compte_demande;
		include_spip('inc/association_compta_ecritures');
		association_compta_ecriture_modifier($id_compte, [
			'date' => $date, 'recette' => $recette, 'depense' => $depense,
			'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
			'id_objet' => $id_objet, 'objet' => $objet,
		]);
	} else {

		include_spip('inc/association_compta_ecritures');
		$id_compte = association_compta_ecriture_creer([
			'date' => $date, 'recette' => $recette, 'depense' => $depense,
			'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
			'id_objet' => $id_objet, 'objet' => $objet, 'vu' => 1,
		]);
	}
	ajouter_destinations((int) $id_compte, (float) $recette, (float) $depense);
	// Mettre à jour directement sans passer par objet_modifier

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='asso_compte/$id_compte'");

	// Message de succès
	$res['message_ok'] = _T('association_compta:operation_enregistree');

	// Redirection si demandée
	if ($retour) {
		$res['redirect'] = $retour;
	} else {
		$res['redirect'] = pipeline('association_compta_redirection_ecriture', [
			'args' => ['objet' => $objet, 'id_objet' => $id_objet, 'id_compte' => $id_compte],
			'data' => generer_url_ecrire('comptes'),
		]);
	}

	return $res;
}
