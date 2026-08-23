<?php
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if(!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/saisies');
include_spip('inc/filtres');
include_spip('formulaires/inc/inscription_evenement');
include_spip('formulaires/inc/inscription_evenement_saisies');
include_spip('formulaires/inc/inscription_evenement_backend');
include_spip('inc/fonctions/activite_enregistrement_calculator');

function ie_inscription_multi_public_ids($id_evenement = 0, $id_activite = null) {
    return ie_resoudre_ids_inscription($id_evenement, $id_activite);
}

function ie_multi_public_sanitiser_saisies_legacy($saisies) {
    if (!is_array($saisies)) {
        return $saisies;
    }

    foreach ($saisies as $cle => $valeur) {
        if ($cle === 'options' && is_array($valeur) && !empty($valeur['afficher_si']) && stripos($valeur['afficher_si'], 'select_type_inscrit') !== false) {
            unset($saisies[$cle]['afficher_si']);
            continue;
        }

        if (is_array($valeur)) {
            $saisies[$cle] = ie_multi_public_sanitiser_saisies_legacy($valeur);
        }
    }

    return $saisies;
}

function ie_multi_public_select_type_inscrit($id_evenement, $id_activite = null) {
    $eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);
    $gestions_places = gestions_places($id_evenement);

    if ($id_activite) {
        $data_activite = preparer_chargement_modification_inscription($id_activite);
        if (!empty($data_activite['select_type_inscrit'])) {
            return $data_activite['select_type_inscrit'];
        }
    }

    if (!empty($GLOBALS['visiteur_session']['id_auteur'])) {
        $info_auteur_connecte = preparer_info_auteur($GLOBALS['visiteur_session']['id_auteur'], $id_evenement);
        return (($info_auteur_connecte['statut_interne_auteur'] ?? '') == 'ok') ? 'membre' : 'non_membre';
    }

    if (!empty(_request('association')) && !empty($gestions_places['evenement_reseau_fiafe'])) {
        return 'membre_reseau_fiafe';
    }

    if (!empty($eligibilite_inscription_evenement['eligibilite_token_inscription']) && !empty(_request('association'))) {
        return 'membre_reseau_fiafe';
    }

    return 'public';
}

/**
 * Lire sans effet de bord le contexte signe des etapes precedentes.
 *
 * Le chargeur CVT construit les saisies avant que le coeur SPIP ne reinjecte
 * cvtm_prev_post dans _request(). Il doit pourtant connaitre nb_inscrits pour
 * declarer tous les champs dynamiques : sinon le jeton genere au
 * recapitulatif ne conserve que le premier participant.
 *
 * @return array
 */
function ie_multi_public_contexte_cvt_precedent() {
    $token = _request('cvtm_prev_post');
    if (empty($token)) {
        return array();
    }
    if (is_array($token)) {
        return $token;
    }

    include_spip('inc/filtres');
    if (function_exists('decoder_contexte_ajax')) {
        $contexte = decoder_contexte_ajax($token, 'inscription_evenement_multi_public');
        if (is_array($contexte)) {
            return $contexte;
        }
    }

    // Compatibilite avec les anciens payloads et les fixtures CLI.
    $normalise = ie_convertir_post(array('cvtm_prev_post' => $token));
    return is_array($normalise['cvtm_prev_post'] ?? null)
        ? $normalise['cvtm_prev_post']
        : array();
}

/**
 * Reprend la structure historique des étapes FO multi (pré-refactoring).
 * Ce helper n'est utilisé que pour le formulaire multi FO.
 */
function ie_multi_public_saisies_legacy($id_evenement, $id_activite = null) {
    $saisies = array();

    $affichage_dans_activites = affichage_dans_activites($id_evenement);
    $gestions_places = gestions_places($id_evenement);
    $eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);
    $id_activite = $id_activite ?: ($eligibilite_inscription_evenement['id_activite'] ?? null);
    $select_type_inscrit = ie_multi_public_select_type_inscrit($id_evenement, $id_activite);
    $famille_selectionnee = !empty($GLOBALS['visiteur_session']['id_auteur'])
        ? ie_aplatir_liste_valeurs(_request('famille') ?: array('adherent'))
        : array('participant_principal');
    $limite_invites = ie_limite_invites_effective(
        $gestions_places,
        $affichage_dans_activites,
        $id_activite,
        max(1, count($famille_selectionnee))
    );

    $query_article_CGU = sql_fetsel('id_article,titre', 'spip_articles', "page = 'page_cgu' AND statut = 'publie'");
    $titre_CGU = supprimer_numero($query_article_CGU['titre'] ?? '');
    $url_CGU = !empty($query_article_CGU['id_article']) ? generer_url_entite($query_article_CGU['id_article'], 'spip_articles', 'var_zajax=content') : '';

    $info_auteur_connecte = array();
    $id_auteur_connecte = false;
    $radio_type_adherent = false;
    $saisie_famille_active = 'non';
    $data_famille = array();

    if (isset($GLOBALS['visiteur_session']['id_auteur'])) {
        $info_auteur_connecte = preparer_info_auteur($GLOBALS['visiteur_session']['id_auteur'], $id_evenement);
        $id_auteur_connecte = $info_auteur_connecte['id_auteur'] ?? false;
        $radio_type_adherent = $info_auteur_connecte['radio_type_adherent'] ?? false;
        $saisie_famille_active = $info_auteur_connecte['saisie_famille_active'] ?? 'non';
        $data_famille = $info_auteur_connecte['data_famille'] ?? array();

        if ($radio_type_adherent == 'individuel') {
            $affichage_dans_activites['accompagnants'] = false;
            $data_famille = array_slice((array) $data_famille, 0, 1, true);
        } elseif ($radio_type_adherent == 'couple') {
            $data_famille = array_slice((array) $data_famille, 0, 2, true);
        }
    }

    $saisies['options'] = array(
        'texte_submit' => 'Valider',
        'etapes_activer' => true,
        'etapes_suivant' => 'Suivant',
        'etapes_precedent' => 'Précédent',
        'etapes_navigation' => 'on',
        'etapes_precedent_suivant_titrer' => '',
    );

    $saisies[] = array(
        'saisie' => 'hidden',
        'options' => array(
            'nom' => 'select_type_inscrit',
            'defaut' => $select_type_inscrit,
        )
    );

    if ($id_auteur_connecte && ($saisie_famille_active == 'oui' || empty($affichage_dans_activites['accompagnants']))) {
        // champs_saisies_selection_membres_famille retourne soit une saisie unique,
        // soit un tableau [saisie_famille, saisie_nb_invite] si les invités sont actifs.
        $saisies_selection_famille = champs_saisies_selection_membres_famille(
            $data_famille,
            !empty($affichage_dans_activites['accompagnants']),
            array(
                'actif' => !empty($affichage_dans_activites['invites']) && $limite_invites > 0,
                'max'   => $limite_invites,
            )
        );
        // Normaliser : si tableau indexé de saisies, on le laisse tel quel ; sinon on enveloppe
        $saisies_selection_famille_array = (isset($saisies_selection_famille[0]) && is_array($saisies_selection_famille[0]))
            ? $saisies_selection_famille
            : array($saisies_selection_famille);
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'fieldset_selection_membre_famille',
                'label' => '<:association_evenements:fieldset_selection_membre_famille_label:>',
            ),
            'saisies' => $saisies_selection_famille_array,
        );

        $saisies_famille = champs_saisies_famille($id_auteur_connecte, $affichage_dans_activites);

        // Invités hors famille : on les ajoute à la suite des membres famille
        // On génère tous les fieldsets jusqu'à limite_invites avec afficher_si côté client
        if (!empty($affichage_dans_activites['invites']) && $limite_invites > 0) {
            $saisies_invites = champs_saisies_invites($limite_invites, $affichage_dans_activites);
            $saisies_famille = array_merge($saisies_famille, $saisies_invites);
        }

        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'fieldset_info_supplementaire_famille',
                'label' => '<:association_evenements:fieldset_info_supplementaire_famille_label:>',
            ),
            'saisies' => $saisies_famille,
        );
    } elseif (!empty($affichage_dans_activites['accompagnants'])) {
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'fieldset_nb_inscrit',
                'label' => '<:association_evenements:fieldset_nb_inscrit_label:>',
            ),
            'saisies' => array(
                champs_saisie_nb_inscrits($gestions_places, $affichage_dans_activites),
            ),
        );

        $contexte_cvt_precedent = ie_multi_public_contexte_cvt_precedent();
        $nb_inscrits = intval(_request('nb_inscrits'))
            ?: intval($contexte_cvt_precedent['nb_inscrits'] ?? 0)
            ?: 1;
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'fieldset_info_supplementaire',
                'label' => '<:association_evenements:fieldset_info_supplementaire_label:>',
            ),
            'saisies' => champs_saisies_inscrits($affichage_dans_activites, $nb_inscrits, $info_auteur_connecte),
        );
    } else {
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'fieldset_info_supplementaire',
                'label' => '<:association_evenements:fieldset_info_supplementaire_label:>',
            ),
            'saisies' => champs_saisies_inscrits($affichage_dans_activites, 1, $info_auteur_connecte),
        );
    }

    $saisies[] = array(
        'saisie' => 'hidden',
        'options' => array(
            'nom' => 'id_evenement',
            'defaut' => intval($id_evenement),
        )
    );
    $saisies[] = array(
        'saisie' => 'hidden',
        'options' => array(
            'nom' => 'id_activite',
            'defaut' => intval($id_activite),
        )
    );

    $saisies_modalites = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'fieldset_modalites',
            'label' => '<:association_evenements:fieldset_modalites:>',
        ),
        'saisies' => array(),
    );

    if (($GLOBALS['association_metas']['meta_cfg_event_message_responsable'] ?? 'non') != 'non') {
        $saisies_modalites['saisies'][] = array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'commentaire',
                'label' => _T('association_evenements:activite_form_public_commentaires'),
                'defaut' => _request('commentaire') ?: '',
                'obligatoire' => 'non',
                'rows' => '4'
            )
        );
    }

    if (!empty($query_article_CGU) && !$id_auteur_connecte) {
        $saisies_modalites['saisies'][] = array(
            'saisie' => 'case',
            'options' => array(
                'nom' => 'conditions_generales',
                'label_case' => _T('association_evenements:message_conditions_generales', array('url_CGU' => $url_CGU, 'titre_CGU' => $titre_CGU)),
                'obligatoire' => 'oui',
            )
        );
    }

    if (($affichage_dans_activites['condition_inscription'] ?? 'non') == 'oui' && !empty($affichage_dans_activites['message_condition_inscription'])) {
        $saisies_modalites['saisies'][] = array(
            'saisie' => 'case',
            'options' => array(
                'nom' => 'condition_inscription',
                'label_case' => typo($affichage_dans_activites['message_condition_inscription']),
                'obligatoire' => 'oui',
            )
        );
    }

    $saisies[] = $saisies_modalites;

    return ie_multi_public_sanitiser_saisies_legacy($saisies);
}
/**
 * Génère les champs du formulaire pour l'inscription à un événement.
 *
 * Cette fonction prépare les données et génère les champs du formulaire
 * d'inscription multi-étapes à un événement. Elle gère le chargement des
 * informations de l'événement, les informations de l'utilisateur, et la
 * configuration des étapes du formulaire en fonction du statut de l'utilisateur
 * et des paramètres de l'événement.
 *
 * @param int $id_evenement L'ID de l'événement.
 * @param int $id_activite L'ID de l'activité.
 * @return array Les champs du formulaire pour l'inscription à l'événement.
 */
function formulaires_inscription_evenement_multi_public_saisies($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_saisies_legacy($id_evenement, $id_activite);
}

function ie_multi_public_charger_legacy($id_evenement, $id_activite) {
    $contexte = ie_charger_commons('multi_public', $id_evenement, $id_activite);
    $saisies = ie_multi_public_saisies_legacy($id_evenement, $id_activite);
    $contexte['_saisies'] = $saisies;
    $contexte['_saisies_par_etapes'] = ie_generer_saisies_par_etapes($saisies);
    return $contexte;
}

function ie_multi_public_verifier_legacy($id_evenement, $id_activite = null, $etape_validation = null) {
     include_spip('formulaires/inc/inscription_evenement_backend');
     return ie_verifier_commons('multi_public', $id_evenement, $id_activite, array(), $etape_validation);
}
/**
 * Charge les données du formulaire pour l'inscription à un événement.
 *
 * Cette fonction charge les informations nécessaires pour le formulaire d'inscription à un événement,
 * y compris les détails de l'événement, l'éligibilité à l'inscription ou à la modification, et les informations de l'utilisateur.
 * Elle prépare les données à utiliser dans les champs du formulaire.
 *
 * @param int $id_evenement L'ID de l'événement.
 * @param int $id_activite L'ID de l'activité.
 * @return array Les données du formulaire pour l'inscription à l'événement.
 */
function formulaires_inscription_evenement_multi_public_charger_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_charger_legacy($id_evenement, $id_activite);
}
/**
 * Vérifie les données du formulaire d'inscription à un événement.
 *
 * Cette fonction vérifie les données soumises dans le formulaire d'inscription
 * à un événement, en s'assurant que les inscriptions sont autorisées, qu'il n'y a
 * pas de doublons, et que les quotas et les règles d'accompagnement sont respectés.
 * Elle gère également les vérifications anti-spam.
 *
 * @param int $id_evenement L'ID de l'événement.
 * @param int $id_activite L'ID de l'activité.
 * @return array Un tableau contenant les erreurs éventuelles.
 */
function formulaires_inscription_evenement_multi_public_verifier_dist($id_evenement,$id_activite){
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_verifier_legacy($id_evenement, $id_activite);
}

function formulaires_inscription_evenement_multi_public_verifier_1_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_verifier_legacy($id_evenement, $id_activite, 1);
}

function formulaires_inscription_evenement_multi_public_verifier_2_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_verifier_legacy($id_evenement, $id_activite, 2);
}

function formulaires_inscription_evenement_multi_public_verifier_3_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_verifier_legacy($id_evenement, $id_activite, 3);
}

function formulaires_inscription_evenement_multi_public_verifier_4_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_multi_public_verifier_legacy($id_evenement, $id_activite, 4);
}
/**
 * Traite la soumission du formulaire d'inscription à un événement.
 *
 * Cette fonction traite la soumission du formulaire d'inscription à un événement, y compris
 * la création de nouvelles inscriptions, la modification des inscriptions existantes, la gestion des transactions,
 * et l'envoi de notifications. Elle gère également les cookies pour les utilisateurs non connectés.
 *
 * @param int $id_evenement L'ID de l'événement.
 * @param int $id_activite L'ID de l'activité.
 * @return array Le résultat du traitement du formulaire, y compris l'URL de redirection.
 */
function formulaires_inscription_evenement_multi_public_traiter_dist($id_evenement, $id_activite) {
    list($id_evenement, $id_activite) = ie_inscription_multi_public_ids($id_evenement, $id_activite);
    return ie_traiter_commons('multi_public', $id_evenement, $id_activite, null);
}


