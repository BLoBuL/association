<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/comptes');
include_spip('formulaires/inc/destinations');
function formulaires_editer_asso_comptes_saisies_dist($id_compte = 'new') {
    $id_compte = _request('id_compte') ?? 'new';



    $query_compte = sql_fetsel('*', 'spip_asso_comptes', "id_compte =" . intval($id_compte));

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
    if(_request('id_evenement') OR !empty($query_compte) AND ($query_compte['objet'] == 'evenement')) {
        $objet_defaut = 'evenement';
        $id_evenement = intval(_request('id_evenement'));
        $imputation_evenement = $GLOBALS['association_metas']['pc_activites_frais'] ?? '';
        $disable_evenement = 'oui';
    }else{
        $objet_defaut = _request('objet') ?: 'autre';
    }
    // Récupération des options de configuration
    $config = $GLOBALS['association_metas'];

    $saisies = [
        [
            'saisie' => 'selection',
            'options' => [
                'nom' => 'objet',
                'label' => _T('association_compta:form_operation_objet_label'),
                'explication' => _T('association_compta:form_operation_objet_explication'),
                'data' => [
                    'autre' => _T('association_compta:choix_autres'),
                    'cotisation' => _T('association_compta:choix_cotisation'),
                    'activite' => _T('association_compta:choix_activite'),
                    'evenement' => _T('association_compta:choix_evenement'),
                    'don' => _T('association_compta:choix_don')
                ],
                'defaut' => $objet_defaut,
                'obligatoire' => 'oui',
                'disable_avec_post' => $disable_evenement,
            ],
            'verifier' => [
                'type' => 'in_array',
                'options' => [
                    'array' => ['cotisation', 'activite', 'evenement', 'don', 'autre']
                ]
            ]
        ],
        // Si objet est "evenement", ajouter un sélecteur d'événements
        $saisies[] = [
            'saisie' => 'selection',
            'options' => [
                'nom' => 'id_evenement',
                'label' => _T('association_compta:form_operation_evenement_label'),
                'explication' => _T('association_compta:form_operation_evenement_explication'),
                'data' => preparer_liste_evenements(),
                'defaut' => $id_evenement ?? '',
                'obligatoire' => 'oui',
                'afficher_si' => '@objet@ == "evenement"',
                'disable_avec_post' => $disable_evenement,
            ],
        ],
        [
            'saisie' => 'selection',
            'options' => [
                'nom' => 'imputation',
                'label' => _T('association_compta:form_operation_imputation_label'),
                'explication' => _T('association_compta:form_operation_imputation_explication'),
                'data' => preparer_liste_asso_plan_compte('data_saisies'),
                'defaut' => $imputation_evenement ?? '',
                'obligatoire' => 'oui',
                'disable_avec_post' => $disable_complet,
                //'afficher_si' => '@objet@ == "autre"', // Afficher seulement si "autre" est sélectionné
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
                ]
            ]
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
        ]
    ];



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
    // Essayer de résoudre un id_evenement pertinent (depuis request / id_compte / id_activite)
    $id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());
    $opt_check = array();
    if ($id_evenement_ctx > 0) {
        $opt_check['id_evenement'] = $id_evenement_ctx;
    }

    if (!autoriser('modifier', 'asso_compte', $id_compte_int, null, $opt_check)) {
        // Interdire l'accès au formulaire
        return array('message_erreur' => _T('association_compta:erreur_autorisation'));
    }

    // Si c'est une modification, charger les données du compte
    if ($id_compte !== 'new') {
        $query_compte = sql_fetsel('*', 'spip_asso_comptes', "id_compte =" . intval($id_compte));

        if ($query_compte) {
            $valeurs = $query_compte;
        }

        if ($query_compte['objet'] == 'evenement') {
            $valeurs['id_evenement'] = intval($query_compte['id_objet']);

        }
        if ($query_compte['depense'] > 0) {
            $valeurs['type_operation'] = 'depense';
            $valeurs['montant'] = $query_compte['depense'];

        }elseif ($query_compte['recette'] > 0) {
            $valeurs['type_operation'] = 'recette';
            $valeurs['montant'] = $query_compte['recette'];
        } else {
            $valeurs['type_operation'] = '';
        }

    }

    return $valeurs;




}
/**
 * Prépare une liste des événements pour le sélecteur
 *
 * @return array Liste formatée des événements
 */
function preparer_liste_evenements() {
	return (array) pipeline('association_compta_objets_lister', array(
		'args' => array('objet' => 'evenement'),
		'data' => array(),
	));
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
    $id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());
    $opt_check = array();
    if ($id_evenement_ctx > 0) $opt_check['id_evenement'] = $id_evenement_ctx;
    if (!autoriser('modifier', 'asso_compte', $id_compte_int, null, $opt_check)) {
        return array('message_erreur' => _T('association_compta:erreur_autorisation'));
    }

    // Vérification que le montant est supérieur à 0
    if (_request('montant') <= 0) {
        $erreurs['montant'] = _T('association_compta:erreur_recette_depense');
    }

    // Récupération de l'objet de l'opération
    $objet = _request('objet');

    // Vérification spécifique selon l'objet de l'opération
    switch ($objet) {
        case 'autre':
            // Vérification de l'imputation pour les opérations "autre"
            $code = _request('imputation');
            if (empty($code)) {
                $erreurs['imputation'] = _T('association_compta:erreur_imputation_obligatoire');
            } else {
                $depense = _request('depense') ?: 0;
                $recette = _request('recette') ?: 0;

                // Vérification de la compatibilité entre le type d'opération et l'imputation
                if (!array_key_exists("montant", $erreurs)) {
                    $type_op = sql_getfetsel('type_op', 'spip_asso_plan', 'code=' . sql_quote($code));
                    if ((($type_op == 'credit') && ($depense > 0)) || (($type_op == 'debit') && ($recette > 0))) {
                        $erreurs['imputation'] = _T('association_compta:erreur_operation_non_permise_sur_ce_compte');
                    }
                }
            }
            break;

        case 'evenement':
            // Vérification qu'un événement est sélectionné
            if (empty(_request('id_evenement'))) {
                $erreurs['id_evenement'] = _T('association_compta:erreur_evenement_obligatoire');
            }
            // Définition de l'imputation pour les événements
/*            if ($depense = _request('depense') > 0) {
                set_request('imputation', $GLOBALS['association_metas']['pc_activites_paiement'] ?? '');
            } else {
                set_request('imputation', $GLOBALS['association_metas']['pc_activites_creance'] ?? '');
            }*/
            set_request('objet', 'evenement');
            set_request('id_objet', _request('id_evenement'));
            break;

        case 'cotisation':
            // Définition de l'imputation pour les cotisations
            set_request('imputation', $GLOBALS['association_metas']['pc_cotisations_creance'] ?? '');
            set_request('objet', 'cotisation');
            set_request('id_objet', _request('id_cotisation'));
            break;

        case 'activite':
            // Définition de l'imputation pour les activités
            set_request('imputation', $GLOBALS['association_metas']['pc_activites_creance'] ?? '');
            set_request('objet', 'activite');
            set_request('id_objet', _request('id_activite'));
            break;

        case 'don':
            // Définition de l'imputation pour les dons
            set_request('imputation', $GLOBALS['association_metas']['pc_dons'] ?? '');
            set_request('objet', 'don');
            set_request('id_objet', _request('id_don'));
            break;
    }

    // Vérification et définition des montants pour les recettes et dépenses
    if (_request('type_operation') == 'recette' AND _request('montant') > 0) {
        set_request('recette', _request('montant'));
        set_request('depense', 0);
    } elseif (_request('type_operation') == 'depense' AND _request('montant') > 0) {
        set_request('depense', _request('montant'));
        set_request('recette', 0);
    } else {
        $erreurs['montant'] = _T('association_compta:erreur_recette_depense');
    }

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
function formulaires_editer_asso_comptes_traiter_dist($id_compte='new', $id_rubrique=0, $retour='', $associer_objet='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
    $res = array();

    // Authorization re-check before performing DB changes
    include_spip('inc/autoriser');
    include_spip('association_autoriser');
    $id_compte_int = ($id_compte === 'new') ? 0 : intval($id_compte);
    $id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());
    $opt_check = array();
    if ($id_evenement_ctx > 0) $opt_check['id_evenement'] = $id_evenement_ctx;
    if (!autoriser('modifier', 'asso_compte', $id_compte_int, null, $opt_check)) {
        return array('message_erreur' => _T('association_compta:erreur_autorisation'));
    }

    // Récupérer les valeurs postées
    $date = affdate(_request('date'), 'Y-m-d');
    $objet = _request('objet');
    $id_objet = _request('id_evenement') ?? 0;
    $imputation = _request('imputation');
    $type_operation = _request('type_operation');
    $montant = _request('montant');
    $journal = _request('journal');
    $justification = _request('justification');
    $recette = $type_operation == 'recette' ? $montant : 0;
    $depense = $type_operation == 'depense' ? $montant : 0;
    if(_request('id_compte')){

        $id_compte = intval(_request('id_compte'));
        modifier_compte(
            $id_compte,
            $date,
            $recette,
            $depense,
            $justification,
            $imputation,
            $journal,
            0, // id_objet
            $objet,
        );
    } else {

        inserer_compte(
            $date,
            $recette,
            $depense,
            $justification,
            $imputation,
            $journal,
            0,
            $id_objet,
            $objet,
            NULL, // reinscription
            NULL,  //id_categorie
            NULL, // statut_cotisation
            NULL, // id_transaction
            1 // vu
        );
    }
    // Mettre à jour directement sans passer par objet_modifier


    // Invalider les caches
    include_spip('inc/invalideur');
    suivre_invalideur("id='asso_compte/$id_compte'");

    // Message de succès
    $res['message_ok'] = _T('asso_compte:message_ok');

    // Redirection si demandée
    if ($retour) {
        $res['redirect'] = $retour;
    }elseif($objet == 'evenement' AND $id_objet > 0) {
        $res['redirect'] = generer_url_ecrire('voir_activites','id=' . intval($id_objet) . '&affichage=comptabilite');
    } else {
        $res['redirect'] = generer_url_ecrire('comptes');
    }

    return $res;
}
