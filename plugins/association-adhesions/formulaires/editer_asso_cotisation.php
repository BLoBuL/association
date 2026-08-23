<?php

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/filtres');
include_spip('inc/autoriser');
include_spip('formulaires/inc/destinations');
include_spip('inc/fonctions/association_validite_calculator');

include_spip('inc/api_cotisations');
/**
 * Prépare les saisies pour le formulaire d'édition de cotisation.
 *
 * Cette fonction génère les champs nécessaires pour le formulaire d'édition de cotisation,
 * en utilisant les informations de l'auteur et les paramètres par défaut.
 *
 * @param int $id_auteur L'ID de l'auteur.
 * @param string $id_compte L'ID du compte (par défaut 'new').
 * @return array Un tableau contenant les saisies du formulaire.
 */
function formulaires_editer_asso_cotisation_saisies($id_compte = 'new') {
    include_spip('inc/api_cotisations');

    // Récupérer le contexte d'édition si on modifie une cotisation existante
    $id_auteur = null;
    $nom_prenom = '';
    $reinscription_defaut = 'reinscription';

    if (intval($id_compte)) {
		include_spip('inc/cotisations_stockage');
		$cot = association_cotisation_lire_par_compte($id_compte);
        if ($cot) {
            $id_auteur = intval($cot['id_auteur']);
            // préfèrer la valeur de reinscription existante
            if (isset($cot['reinscription']) && $cot['reinscription']) {
                $reinscription_defaut = $cot['reinscription'];
            }
        }
    }

    // Si pas trouvé via id_compte, utiliser la requête
    if (!$id_auteur) {
        $id_auteur = intval(_request('id_auteur'));
    }

    if ($id_auteur) {
        $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
        if ($query_auteur) {
            $nom_prenom = ($query_auteur['nom_famille']) ? $query_auteur['nom_famille']. ' ' . $query_auteur['prenom'] : $query_auteur['nom'];
        }
    }

    // Justification par défaut via la chaîne de langue (toujours sûre)
    $justification = _T('association_adhesions:justification_encaissement_cotisation', ['nom_prenom' => $nom_prenom, 'id_auteur' => $id_auteur]);

    // Préparation des paramètres pour l'API commune
    $params = [
        'id_auteur' => $id_auteur,
        'id_compte' => $id_compte,
        'reinscription' => $reinscription_defaut,
        'contexte' => 'prive',
        'nom_prenom' => $nom_prenom
    ];

    // Obtention des saisies communes via l'API
    $saisies = api_cotisations_saisies_communes($params);

    // Ajouter champs cachés pour conserver id_auteur / id_compte
    array_unshift($saisies, array(
        'saisie' => 'hidden',
        'options' => array(
            'nom' => 'id_compte',
            'defaut' => $id_compte,
        ),
    ));
    array_unshift($saisies, array(
        'saisie' => 'hidden',
        'options' => array(
            'nom' => 'id_auteur',
            'defaut' => $id_auteur,
        ),
    ));

    // Ajout des champs spécifiques au backoffice
    // Construire dynamiquement les saisies du fieldset admin
    $admin_saisies = array();

    // Ne proposer le statut QUE si on édite une cotisation existante
    if (intval($id_compte)) {
        // Déterminer la catégorie sélectionnée (préférer la valeur POST si présente)
        $selected_id_categorie = intval(_request('id_categorie'));
        if (!$selected_id_categorie && isset($cot) && !empty($cot['id_categorie'])) {
            $selected_id_categorie = intval($cot['id_categorie']);
        }

        // Par défaut proposer les statuts standards
        $status_data = array(
            'ok' => '<:association_adhesions:choix_cotisation_statut_ok:>',
            'attente' => '<:association_adhesions:choix_cotisation_statut_attente:>',
            'demande' => '<:association_adhesions:choix_cotisation_statut_demande:>',
        );

        // Si une catégorie est sélectionnée, restreindre les statuts selon sa validation
        if ($selected_id_categorie) {
            $cat = sql_fetsel('validation', 'spip_asso_categories_adherents', 'id_categorie=' . $selected_id_categorie);
            if ($cat && isset($cat['validation'])) {
                $validation = strtolower(trim($cat['validation']));
                // Limiter les options :
                // - 'pre-paiement' : proposer 'demande' et 'ok' (validation manuelle possible)
                // - 'post-paiement' ou 'auto' : proposer 'attente' et 'ok'
                if ($validation === 'pre-paiement') {
                    // Pour pre-paiement, ne PAS permettre le statut 'ok' (validation ne se fait qu'après paiement)
                    $status_data = array(
                        'demande' => '<:association_adhesions:choix_cotisation_statut_demande:>',
                        'attente' => '<:association_adhesions:choix_cotisation_statut_attente:>',
                    );
                } elseif ($validation === 'post-paiement' || $validation === 'auto') {
                    $status_data = array(
                        'attente' => '<:association_adhesions:choix_cotisation_statut_attente:>',
                        'ok' => '<:association_adhesions:choix_cotisation_statut_ok:>',
                    );
                }
            }
        }

        $admin_saisies[] = array(
            'saisie' => 'selection',
            'options' => array(
                'label' => '<:association_adhesions:form_cotisation_statut_label:>',
                'nom' => 'statut_cotisation',
                'data' => $status_data,
                'cacher_option_intro' => true,
                'explication' => '<:association_adhesions:form_cotisation_statut_explication:>',
                'defaut' => array_key_exists('attente', $status_data) ? 'attente' : (array_key_exists('demande', $status_data) ? 'demande' : 'ok'),
            ),
        );
    }

    // Reinscription (toujours affichée)
    $admin_saisies[] = array(
        'saisie' => 'selection',
        'options' => array(
            'label' => '<:association_adhesions:form_cotisation_reinscription_label:>',
            'nom' => 'reinscription',
            'explication' => '<:association_adhesions:form_cotisation_reinscription_explication:>',
            'data' => array(
                'inscription' => '<:association_adhesions:choix_cotisation_inscription:>',
                'reinscription' => '<:association_adhesions:choix_cotisation_reinscription:>',
            ),
            'cacher_option_intro' => true,
            'defaut' => $reinscription_defaut,
        ),
    );

    $admin_saisies[] = array(
        'saisie' => 'textarea',
        'options' => array(
            'label' => '<:association_adhesions:form_cotisation_justification_label:>',
            'nom' => 'justification',
            'rows' => 3,
            'explication' => '<:association_adhesions:form_cotisation_justification_explication:>',
            'defaut' => $justification,
            'traitements' => 'propre',
            'disable_avec_post' => 1,
        ),
    );

    $admin_saisies[] = array(
        'saisie' => 'case',
        'options' => array(
            'label' => '<:association_adhesions:form_cotisation_notification_label:>',
            'nom' => 'notifier',
            'defaut' => 'on',
            'label_case' => '<:association_adhesions:form_cotisation_notification_label_case:>',
            'explication' => '<:association_adhesions:form_cotisation_notification_explication:>',
        )
    );

    // Si on édite une cotisation existante, proposer le document justificatif
    // uniquement si la catégorie sélectionnée nécessite un justificatif.
    if (intval($id_compte)) {
        // Récupérer la liste des catégories qui exigent un justificatif (forme SQL IN)
        if (function_exists('identifier_categories_necessite_justificatif')) {
            $liste_categorie_cotisation_justificatif = identifier_categories_necessite_justificatif();
        } else {
            // Si la fonction n'existe pas pour une raison quelconque, laisser la condition large
            $liste_categorie_cotisation_justificatif = "()";
        }
        $afficher_si_document_justificatif = '@id_categorie@ IN ' . $liste_categorie_cotisation_justificatif;

        $admin_saisies[] = array(
            'saisie' => 'fichiers',
            'options' => array(
                'nom' => 'document_justificatif',
                'label' => '<:association_adhesions:form_cotisation_justificatif_label:>',
                'explication' => '<:association_adhesions:form_cotisation_justificatif_explication:>',
                'nb_fichiers' => 3,
                'obligatoire' => 'non',
                'afficher_si' => $afficher_si_document_justificatif,
            ),
            'verifier' => [
                'type' => 'fichiers',
                'options' => [
                    'mime' => 'specifique',
                    'mime_specifique' => array('application/pdf', 'image/jpeg', 'image/png'),
                    'taille_max' => 10240,
                ]
            ]
        );
    }

    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'form_cotisation_admin_fieldset',
            'label' => '<:association_adhesions:form_cotisation_admin_fieldset:>',
        ),
        'saisies' => $admin_saisies,
    );

    return $saisies;
}
/**
 * Charge le contexte pour le formulaire d'édition de cotisation.
 *
 * Cette fonction initialise le contexte du formulaire d'édition de cotisation
 * en récupérant les informations nécessaires depuis la base de données.
 *
 * @param int $id_auteur L'ID de l'auteur.
 * @param string $id_compte L'ID du compte (par défaut 'new').
 * @return array Le contexte du formulaire.
 */
function formulaires_editer_asso_cotisation_charger_dist($id_compte = 'new') {
    $id_auteur = intval(_request('id_auteur'));

    // Si on édite une cotisation existante et que l'id_auteur n'est pas fourni,
    // on le récupère depuis la table des cotisations
    if ($id_compte && $id_compte != 'new' && !$id_auteur) {
		include_spip('inc/cotisations_stockage');
		$cot = association_cotisation_lire_par_compte($id_compte);
        if ($cot) {
            $id_auteur = intval($cot['id_auteur']);
        }
    }

    // Une création sans auteur ne peut pas produire les champs métier (catégorie,
    // justificatif, règles de validation). Ne jamais présenter dans ce cas un
    // formulaire partiel qui pourrait être pris pour le vrai formulaire.
    if ($id_compte == 'new' && !$id_auteur) {
        return array(
            'id_auteur' => 0,
            'id_compte' => $id_compte,
            'editable' => false,
            'message_erreur' => _T('association_adhesions:erreur_id_auteur_invalide'),
        );
    }

    // Droits d'édition : si l'utilisateur n'a pas le droit de modifier cet auteur, rendre non éditable
    $editable = true;
    if ($id_auteur && !autoriser('modifier', 'auteur', $id_auteur)) {
        $editable = false;
    }

    // Initialiser les valeurs par défaut et contexte
    $contexte = array(
        'id_auteur' => $id_auteur,
        'id_compte' => $id_compte,
        'editable' => $editable
    );

    // Si édition d'une cotisation existante, charger les données existantes
    if ($id_compte && $id_compte != 'new') {
		include_spip('inc/cotisations_stockage');
		$cotisation = association_cotisation_lire_par_compte($id_compte);
        if ($cotisation) {
            $contexte = array_merge($contexte, $cotisation);
        }
    }

    return $contexte;
}
/**
 * Vérifie les données saisies dans le formulaire d'édition de cotisation.
 *
 * Cette fonction contrôle la validité des données soumises dans le formulaire,
 * notamment les champs obligatoires, le format des dates et la présence d'un
 * justificatif si la catégorie sélectionnée l'exige.
 *
 * @param int $id_auteur L'ID de l'auteur concerné par la cotisation.
 * @param string $id_compte L'ID du compte (par défaut 'new' pour création).
 * @return array Un tableau contenant les éventuelles erreurs détectées.
 */
function formulaires_editer_asso_cotisation_verifier_dist($id_compte = 'new') {
    $erreurs = array();

    // Vérifier id_auteur
    $id_auteur = intval(_request('id_auteur'));
    if (!$id_auteur && $id_compte == 'new') {
        $erreurs['id_auteur'] = _T('association_adhesions:erreur_id_auteur_invalide');
        return $erreurs;
    }

    // Champs obligatoires minimaux
    $champs_obligatoires = array('id_categorie');
    foreach ($champs_obligatoires as $obligatoire) {
        if (!_request($obligatoire)) {
            $erreurs[$obligatoire] = _T('info_obligatoire');
        }
    }

    if (!isset($erreurs['id_categorie'])) {
        include_spip('inc/api_cotisations');
        $fichiers = _request('_fichiers') ?: ($_FILES ?? array());
        $controle_documents = cotisation_verifier_documents_justificatifs(intval(_request('id_categorie')), $fichiers, $id_compte);
        if (!$controle_documents['valide']) {
            $erreurs['document_justificatif'] = $controle_documents['message'];
        }
    }

    // Si le statut demande une justification, vérifier sa présence
    $statut = _request('statut_cotisation');
    $justification = trim(strval(_request('justification')));
    if ($statut == 'demande' && $justification == '') {
        $erreurs['justification'] = _T('info_obligatoire');
    }

    // Si création, la sélection du statut n'est pas proposée au formulaire.
    // On doit vérifier la règle liée à la catégorie : si la catégorie exige une validation
    // de type pré-paiement, la justification est obligatoire même en création.
    if ($id_compte == 'new') {
        $id_categorie = intval(_request('id_categorie'));
        if ($id_categorie) {
            $cat = sql_fetsel('validation', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
            if ($cat && isset($cat['validation'])) {
                $validation = strtolower(trim($cat['validation']));
                $validation = str_replace('_', '-', $validation);
                // si validation pré-paiement => justification requise
            if ($validation === 'pre-paiement') {
                     if ($justification == '') {
                        $erreurs['justification'] = _T('info_obligatoire');
                    }
                }
            }
        }
    }

    // Vérifier montant si présent
    $montant_raw = _request('montant');
    if ($montant_raw !== null && $montant_raw !== '') {
        // Accepter les formats avec virgule ou point
        $montant_sanitized = str_replace(',', '.', $montant_raw);
        if (!is_numeric($montant_sanitized)) {
            $erreurs['montant'] = _T('association_adhesions:erreur_montant_invalide');
        }
    }

    // Validation serveur : vérifier que le statut choisi est cohérent avec la catégorie
    // (utile si l'admin a contourné l'UI). S'applique en modification (id_compte != 'new')
    $statut_envoye = _request('statut_cotisation');
    if ($id_compte && $id_compte != 'new' && $statut_envoye) {
        $id_categorie = intval(_request('id_categorie')) ?: 0;
        if ($id_categorie) {
            $cat = sql_fetsel('validation', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
            if ($cat && isset($cat['validation'])) {
                $validation = strtolower(trim($cat['validation']));
                $allowed = array();
                if ($validation === 'pre-paiement') {
                    // En pre-paiement, la validation finale ne peut pas être 'ok' ici
                    // On autorise 'demande' (validation manuelle) et 'attente' (en attente de paiement)
                    $allowed = array('demande', 'attente');
                } elseif ($validation === 'post-paiement' || $validation === 'auto') {
                    $allowed = array('attente', 'ok');
                } else {
                    // fallback, autoriser les statuts standards
                    $allowed = array('demande', 'attente', 'ok');
                }
                if (!in_array($statut_envoye, $allowed, true)) {
                    $erreurs['statut_cotisation'] = 'Statut incohérent avec la catégorie sélectionnée.';
                }
            }
        }
    }

    if (count($erreurs)) {
        $erreurs['message_erreur'] = _T('association_adhesions:erreur_formulaire_cotisation');
    }

    return $erreurs;
}


/**
 * Traite les données du formulaire d'édition de cotisation.
 *
 * Cette fonction enregistre les données de cotisation dans la base de données
 * et gère les fichiers justificatifs éventuellement fournis.
 *
 * @param int $id_auteur L'ID de l'auteur concerné par la cotisation.
 * @param string $id_compte L'ID du compte (par défaut 'new' pour création).
 * @return array Un tableau contenant le résultat du traitement.
 */
function formulaires_editer_asso_cotisation_traiter($id_compte = 'new') {
    include_spip('inc/api_cotisations');

    if(!$id_auteur = intval(_request('id_auteur'))) {
        return ['message_erreur' => _T('association_adhesions:erreur_id_auteur_invalide')];
    }

    // Rassembler les paramètres en s'assurant de la sécurité
    $params = [
        'id_auteur' => $id_auteur,
        'id_compte' => $id_compte,
        'id_categorie' => intval(_request('id_categorie')),
        'origine' => 'prive',
        'justification' => _request('justification'),
        'reinscription' => _request('reinscription'),
        'statut_cotisation' => _request('statut_cotisation'),
        'notifier' => _request('notifier') ? _request('notifier') : null,
    ];

    // Ajouter le montant si fourni (normaliser la valeur numérique)
    $montant_raw = _request('montant');
    if ($montant_raw !== null && $montant_raw !== '') {
        $montant_sanitized = str_replace(',', '.', $montant_raw);
        $params['montant'] = floatval($montant_sanitized);
    }

    // Gestion des fichiers justificatifs : l'API attend la clé 'documents' ou utilisera $_FILES
    // SPIP place les uploads dans _request('_fichiers') ; récupérer cela en priorité.
    $fichiers = _request('_fichiers');
    if (!empty($fichiers)) {
        $params['documents'] = $fichiers;
    } else {
        // Compat : si le champ unique 'document_justificatif' est présent, envoyer sous forme associative
        $doc = _request('document_justificatif');
        if ($doc) {
            $params['documents'] = array('document_justificatif' => $doc);
        }
    }

    // Transmettre aussi le type d'adhérent si fourni (utile pour variantes entreprise)
    if ($type_adherent = _request('type_adherent')) {
        $params['type_adherent'] = $type_adherent;
    }

    // NOTE: l'assignation automatique du statut lors d'une création est désormais gérée
    // par api_traiter_cotisation() - ne pas dupliquer ici. Si un statut explicite est fourni
    // dans le formulaire (édition), il sera transmis via 'statut_cotisation' dans $params.

    $resultat = api_traiter_cotisation($params);

    if ($resultat['statut'] == 'erreur') {
        return ['message_erreur' => $resultat['message']];
    }

    return [
        'message_ok' => _T('association_adhesions:cotisation_enregistree'),
        'redirect' => generer_url_ecrire('voir_adherent', 'id_auteur=' . $id_auteur)
    ];
}
/**
 * Déclare les fichiers acceptés par le formulaire
 *
 * @return array Liste des noms des champs de type fichier
 */
function formulaires_editer_asso_cotisation_fichiers() {
    return array('document_justificatif');
}
