<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

// Backend d'orchestration (stub) pour centraliser charger/verifier/traiter
// Ce fichier fournit des fonctions ie_* minimalistes. Il s'agit d'un stub
// pour l'étape 1 : implémentation progressive des fonctions réelles.

include_spip('formulaires/inc/inscription_evenement');
include_spip('formulaires/inc/inscription_evenement_saisies');
include_spip('inc/autoriser');
include_spip('inc/diagnostic_formulaire_inscription');
include_spip('association_fonctions');

/**
 * Resout les identifiants d'un formulaire d'inscription, y compris quand une
 * URL de modification BO ne transmet que l'activite existante.
 *
 * @return array{0:int,1:int|null}
 */
function ie_resoudre_ids_inscription($id_evenement = 0, $id_activite = null) {
    $id_evenement = intval($id_evenement) ?: intval(_request('id_evenement'));
    $id_activite = intval($id_activite) ?: intval(_request('id_activite'));

    if ($id_evenement <= 0 && $id_activite > 0) {
        $activite = sql_fetsel('id_evenement', 'spip_asso_activites', 'id_activite=' . $id_activite);
        $id_evenement = intval($activite['id_evenement'] ?? 0);
    }

    if ($id_evenement > 0 && _request('id_evenement') === null) {
        set_request('id_evenement', $id_evenement);
    }
    if ($id_activite > 0 && _request('id_activite') === null) {
        set_request('id_activite', $id_activite);
    }
    // Une URL BO de modification ne porte historiquement que id_activite.
    // Propager explicitement le mode modification afin que chaque requete CVT
    // reconstruise le meme nombre d'etapes et ne revienne pas silencieusement
    // sur la premiere etape.
    if ($id_activite > 0 && _request('modif') === null) {
        set_request('modif', 'oui');
    }

    return array($id_evenement, $id_activite > 0 ? $id_activite : null);
}

/**
 * Replace dans la requete CVT les valeurs persistantes d'une inscription en
 * modification. Les valeurs deja soumises par une etape restent prioritaires.
 */
function ie_rehydrater_requete_modification($data_activite) {
    if (!is_array($data_activite)) {
        return;
    }

    $champs_internes = array(
        'id_auteur_activite',
        'query_transaction_statut',
        'saisie_famille_active',
    );
    foreach ($data_activite as $champ => $valeur) {
        if (in_array($champ, $champs_internes, true)) {
            continue;
        }
        if (_request($champ) === null) {
            set_request($champ, $valeur);
        }
    }
}

/**
 * Journalise l'état structurel du formatage multi sans exposer les valeurs
 * personnelles saisies par les participants.
 */
function ie_log_formater_multi($trace_id, $mode, $post, $data_form, $phase) {
    if (strpos((string) $mode, 'multi') === false) {
        return;
    }

    $post = is_array($post) ? $post : array();
    $premier_inscrit = is_array($data_form['premier_inscrit'] ?? null)
        ? $data_form['premier_inscrit']
        : array();
    $champs_simples = array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit');
    $champs_reconstruits = array();
    foreach (array('prenom', 'nom', 'email', 'tel') as $champ) {
        if (trim((string) ($premier_inscrit[$champ] ?? '')) !== '') {
            $champs_reconstruits[] = $champ;
        }
    }

    $simple_identity_present = false;
    foreach ($champs_simples as $champ) {
        if (trim((string) ($post[$champ] ?? '')) !== '') {
            $simple_identity_present = true;
            break;
        }
    }

    $categories_selectionnees = 0;
    foreach ((array) ($data_form['categorie_result'] ?? array()) as $qte) {
        $categories_selectionnees += is_array($qte)
            ? count(array_filter($qte))
            : max(0, intval($qte));
    }

    spip_log('[IE_FORMATER_MULTI][' . $trace_id . '] ' . json_encode(array(
        'phase' => (string) $phase,
        'mode' => (string) $mode,
        'simple_identity_present' => $simple_identity_present ? 1 : 0,
        'normalized_identity_fields' => $champs_reconstruits,
        'nombre_participants' => intval($data_form['nombre_participants'] ?? 0),
        'categories_selectionnees' => $categories_selectionnees,
        'premier_inscrit_nom_present' => in_array('nom', $champs_reconstruits, true) ? 1 : 0,
        'premier_inscrit_complete' => count($champs_reconstruits) === 4 ? 1 : 0,
    )), 'association' . _LOG_DEBUG);
}

/**
 * Chargement commun pour les formulaires d'inscription.
 * Implémentation générique réutilisant les helpers existants.
 *
 * @param string $mode 'public'|'prive'|'multi_public'|'multi_prive'
 * @param int $id_evenement
 * @param int|null $id_activite
 * @param array $opts
 * @return array
 */
function ie_charger_commons($mode, $id_evenement = 0, $id_activite = null, $opts = array()) {
    list($id_evenement, $id_activite) = ie_resoudre_ids_inscription($id_evenement, $id_activite);
    // init
    $res = array();
    $saisies = array();
    $saisies_general = array();
    $saisies_tarifs = array();
    $saisies_modalites = array();
    $saisies_hidden = array();

    // sécurité sur metas
    if (!isset($GLOBALS['association_metas']) || !is_array($GLOBALS['association_metas'])) {
        $GLOBALS['association_metas'] = array();
    }

    // récupérer données évènement / places / eligibility (appel direct des helpers)
    $affichage_dans_activites = affichage_dans_activites($id_evenement);
    $gestions_places = gestions_places($id_evenement);
    $eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);
    $ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);

    // préparation création/modification
    // En FO uniquement : si aucun id_activite n'est fourni, détecter une inscription
    // existante de l'auteur connecté pour pré-charger une modification.
    // En BO, sans id_activite explicite on est toujours en création (l'admin inscrit
    // quelqu'un d'autre ; résoudre son propre id_activite n'a pas de sens).
    $est_mode_bo = in_array($mode, array('prive', 'multi_prive'));
    if (!$est_mode_bo) {
        $id_activite = $id_activite ?: ($eligibilite_inscription_evenement['id_activite'] ?? null);
    }
    $multi_etapes = (strpos($mode, 'multi') !== false);
    $modif_request = _request('modif');
    $eligibilite_modification_evenement = isset($id_activite) ? eligibilite_modification_evenement($id_activite) : false;

    if (!empty($ouverture_inscription_evenement['inscription_ouverte']) && $ouverture_inscription_evenement['inscription_ouverte'] == 'oui'
        && ($ouverture_inscription_evenement['statut_ouverture_inscription'] ?? '') == 'inscription_ouverte'
        && ($eligibilite_inscription_evenement['eligible_inscription'] ?? '') == 'oui' && !isset($id_activite)) {
        $creation_inscription = 'oui';
        $modification_inscription = 'non';
    } elseif ((($ouverture_inscription_evenement['statut_ouverture_inscription'] ?? '') == 'inscription_ouverte') && ($eligibilite_modification_evenement == 'possible') && ($modif_request == 'oui') && isset($id_activite)) {
        $modification_inscription = 'oui';
        $creation_inscription = 'non';
    } else {
        $creation_inscription = $modification_inscription = 'non';
    }

    // récupération éventuelle d'une association passée en paramètres
    $email_inscrit = _request('email') ?? '';
    $association = _request('association') ?? '';
    $info_adherent = array();

    // charger données en modification
    $data_activite = array();
    if ($id_activite) {
        $mode_multi = (strpos($mode, 'multi') !== false);
        if ($mode_multi) {
            $public_or_prive = (strpos($mode, 'public') !== false) ? 'public' : 'prive';
            $data_activite = preparer_chargement_modification_inscription_multi($id_activite, $public_or_prive);
        } else {
            $data_activite = preparer_chargement_modification_inscription($id_activite);
        }

        $id_auteur_activite = $data_activite['id_auteur_activite'] ?? null;
        $saisie_famille_active = $data_activite['saisie_famille_active'] ?? 'non';

        ie_rehydrater_requete_modification($data_activite);

        // En multi/famille, forcer la sélection préchargée si rien n'est encore saisi.
        if ($mode_multi && isset($data_activite['famille']) && _request('famille') === null) {
            set_request('famille', $data_activite['famille']);
        }
        // Pré-charger nb_invite depuis la base si pas encore dans la requête
        if ($mode_multi && isset($data_activite['nb_invite']) && _request('nb_invite') === null) {
            set_request('nb_invite', $data_activite['nb_invite']);
        }
        // Pré-charger les champs supplémentaires des invités externes (nomenclature inscrit_N)
        if ($mode_multi) {
            $nb_invite_stored = intval($data_activite['nb_invite'] ?? 0);
            for ($j = 1; $j <= $nb_invite_stored; $j++) {
                $cle = 'inscrit_' . $j;
                $champs_invite = array('prenom', 'nom', 'email', 'telephone', 'date_naissance',
                    'nationalite', 'fonction', 'entreprise',
                    'type_document_identite', 'numero_document_identite',
                    'date_expiration_document_identite', 'lieu_naissance', 'categorie');
                foreach ($champs_invite as $champ) {
                    $cle_full = $champ . '_' . $cle;
                    if (isset($data_activite[$cle_full]) && _request($cle_full) === null) {
                        set_request($cle_full, $data_activite[$cle_full]);
                    }
                }
            }
        }
    } else {
        $id_auteur_activite = null;
        $saisie_famille_active = 'non';
    }

    // configuration accompagnants
    $config_accompagnants = isset($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']) ? $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] : 'tout';

    // statut visiteur / en BO utiliser le select 'membre' / 'non_membre'
    // En front (FO) on conserve le comportement existant (visiteur connecté)
    if ($est_mode_bo) {
        // En BO, l'administrateur peut inscrire une autre personne :
        // on doit ignorer l'auteur connecté et récupérer l'id_auteur
        // à partir des selects fournis (request ou données d'activité).
        $select_type = _request('select_type_inscrit') ?: ($data_activite['select_type_inscrit'] ?? null);
        $id_auteur_selection = null;
        if ($select_type === 'membre') {
            $id_auteur_selection = _request('membre') ?: ($data_activite['membre'] ?? null);
        } elseif ($select_type === 'non_membre') {
            $id_auteur_selection = _request('non_membre') ?: ($data_activite['non_membre'] ?? null);
        } elseif ($select_type === 'membre_reseau_fiafe') {
            // pas de famille pour ce type en général
            $id_auteur_selection = null;
        }

        $id_auteur_connecte = $id_auteur_selection ?: false;

        // Calculer le statut interne de l'auteur sélectionné (utile pour affichages)
        if ($id_auteur_connecte) {
            $row_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur_connecte));
            $date_validite = affdate($row_auteur['validite'] ?? '', 'Y-m-d 23:59:59');
            if (!empty($date_validite) && isset($affichage_dans_activites['date_fermeture_inscription']) && $date_validite >= $affichage_dans_activites['date_fermeture_inscription']) {
                $statut_interne_auteur_connecte = 'ok';
            } else {
                $statut_interne_auteur_connecte = false;
            }
        } else {
            $statut_interne_auteur_connecte = false;
        }

        if ($config_accompagnants == 'membre_famille' && $id_auteur_connecte) {
            $data_famille = generer_famille_adherent($id_auteur_connecte);
            $saisie_famille_active = 'oui';
        } else {
            $saisie_famille_active = 'non';
            $data_famille = false;
        }
    } else {
        // Front : comportement historique basé sur le visiteur connecté
        if (isset($GLOBALS['visiteur_session']['id_auteur'])) {
            $id_auteur_connecte = $GLOBALS['visiteur_session']['id_auteur'];
            $statut_auteur = $GLOBALS['visiteur_session']['statut'] ?? null;
            $statut_interne_auteur = $GLOBALS['visiteur_session']['statut_interne'] ?? null;
            $radio_type_adherent = $GLOBALS['visiteur_session']['radio_type_adherent'] ?? false;
            if ($radio_type_adherent == 'individuel') {
                $affichage_dans_activites['accompagnants'] = false;
            }
            $date_validite_auteur_connecte = affdate($GLOBALS['visiteur_session']['validite'] ?? '', 'Y-m-d 23:59:59');
            if (!empty($date_validite_auteur_connecte) && isset($affichage_dans_activites['date_fermeture_inscription']) && $date_validite_auteur_connecte >= $affichage_dans_activites['date_fermeture_inscription']) {
                $statut_interne_auteur_connecte = 'ok';
            } else {
                $statut_interne_auteur_connecte = false;
            }
            if ($config_accompagnants == 'membre_famille') {
                // appel direct demandé : generer_famille_adherent
                $data_famille = generer_famille_adherent($id_auteur_connecte);
                $saisie_famille_active = 'oui';
            } else {
                $saisie_famille_active = 'non';
                $data_famille = false;
            }
        } else {
            $id_auteur_connecte = $statut_interne_auteur_connecte = false;
            $saisie_famille_active = 'non';
            $data_famille = false;
        }
    }

    // préparer les saisies publiques si nécessaire
    $public_mode = strpos($mode, 'public') !== false;

    // closure locale pour fusionner des saisies sans dupliquer les champs par 'nom'
    $saisies_merge_unique = function (&$dest, $to_add) {
        if (!is_array($to_add)) {
            return;
        }
        // construire index existant
        $exist = array();
        foreach ($dest as $d) {
            if (isset($d['options']['nom'])) {
                $exist[$d['options']['nom']] = true;
            }
        }
        foreach ($to_add as $s) {
            $nom = $s['options']['nom'] ?? null;
            if ($nom && isset($exist[$nom])) {
                // déjà présent -> ignorer
                continue;
            }
            $dest[] = $s;
            if ($nom) $exist[$nom] = true;
        }
    };

    // En BO (prive/multi_prive), conserver la selection d'inscrit historique.
    if (in_array($mode, array('prive', 'multi_prive'))) {
        $liste_membre = array();
        $liste_non_membre = array();
        $ids_auteurs_deja_inscrits = array();
        $ids_auteurs_desactives = '';

        // En création BO, afficher les personnes déjà inscrites/préinscrites
        // mais les rendre non sélectionnables.
        $where_deja_inscrits = array(
            'id_evenement=' . intval($id_evenement),
            "statut IN ('ok','preinscrit')",
            'id_auteur > 0',
        );
        $query_auteurs_deja_inscrits = sql_select('DISTINCT id_auteur', 'spip_asso_activites', $where_deja_inscrits);
        while ($row = sql_fetch($query_auteurs_deja_inscrits)) {
            $id_auteur_deja_inscrit = intval($row['id_auteur']);
            if ($id_auteur_deja_inscrit > 0) {
                $ids_auteurs_deja_inscrits[$id_auteur_deja_inscrit] = $id_auteur_deja_inscrit;
            }
        }
        sql_free($query_auteurs_deja_inscrits);

        // En modification, laisser sélectionnable l'auteur de l'inscription éditée.
        if (!empty($id_auteur_activite)) {
            unset($ids_auteurs_deja_inscrits[intval($id_auteur_activite)]);
        }
        if (!empty($ids_auteurs_deja_inscrits)) {
            $ids_auteurs_desactives = implode(',', array_values($ids_auteurs_deja_inscrits));
        }

        $query_liste_membres = sql_select('id_auteur,prenom,nom_famille,statut_interne', 'spip_auteurs', "statut_interne != 'desactive' AND statut <> '5poubelle'", '', 'nom_famille ASC');
        while ($membre = sql_fetch($query_liste_membres)) {
            $label = strtoupper($membre['nom_famille']) . ' ' . $membre['prenom'];
            if ($membre['statut_interne'] == 'ok') {
                $liste_membre[$membre['id_auteur']] = $label;
            } else {
                $liste_non_membre[$membre['id_auteur']] = $label;
            }
        }
        sql_free($query_liste_membres);

        $saisies_general[] = array(
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'select_type_inscrit',
                'label' => '<:association:fieldset_select_type_inscrit_label:>',
                'obligatoire' => 'oui',
                'datas' => array(
                    'membre' => '<:association:fieldset_select_type_inscrit_choix_membre:>',
                    'non_membre' => '<:association:fieldset_select_type_inscrit_choix_non_membre:>',
                    'membre_reseau_fiafe' => '<:association:fieldset_select_type_inscrit_choix_fiafe:>',
                    'public' => '<:association:fieldset_select_type_inscrit_choix_public:>'
                ),
                'defaut' => _request('select_type_inscrit') ?: ($data_activite['select_type_inscrit'] ?? 'membre'),
            )
        );

        $saisies_general[] = array(
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'membre',
                'id' => 'champ_membre',
                'label' => '<:association:fieldset_select_type_inscrit_choix_membre:>',
                'class' => 'select2',
                'obligatoire' => 'oui',
                'afficher_si' => "@select_type_inscrit@=='membre'",
                'datas' => $liste_membre,
                'disable_choix' => $ids_auteurs_desactives,
                'defaut' => _request('membre') ?: ($data_activite['membre'] ?? ''),
            )
        );

        $saisies_general[] = array(
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'non_membre',
                'id' => 'champ_non_membre',
                'label' => '<:association:fieldset_select_type_inscrit_choix_non_membre:>',
                'class' => 'select2',
                'obligatoire' => 'oui',
                'afficher_si' => "@select_type_inscrit@=='non_membre'",
                'datas' => $liste_non_membre,
                'disable_choix' => $ids_auteurs_desactives,
                'defaut' => _request('non_membre') ?: ($data_activite['non_membre'] ?? ''),
            )
        );

        // En BO, afficher les champs identite publique quand necessaire.
        $saisies_merge_unique($saisies_general, generer_saisies_info_public('prive'));
    } else {
        // En FO, select_type_inscrit doit rester un champ cache.
        // Valeur calculee selon le contexte (connecte/public/FIAFE).
        $select_type_fo = _request('select_type_inscrit') ?: ($data_activite['select_type_inscrit'] ?? '');

        if ($select_type_fo === '') {
            if (!empty($id_auteur_connecte)) {
                $select_type_fo = (!empty($statut_interne_auteur_connecte) && $statut_interne_auteur_connecte == 'ok') ? 'membre' : 'non_membre';
            } elseif (!empty($association) || !empty($eligibilite_inscription_evenement['eligibilite_token_inscription'])) {
                $select_type_fo = 'membre_reseau_fiafe';
            } else {
                $select_type_fo = 'public';
            }
        }

        $saisies_general[] = array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'select_type_inscrit',
                'defaut' => $select_type_fo,
            )
        );
    }

    // Champs identite (nom, prenom, email, tel).
    // En FO, pour un evenement ouvert au public, ces champs doivent etre visibles
    // meme sans select_type_inscrit explicite dans la requete.
    // En BO, on conserve l'affichage conditionnel sur le type selectionne.
    $select_type_request = _request('select_type_inscrit') ?: ($data_activite['select_type_inscrit'] ?? null);
    $evenement_type_inscrits = $affichage_dans_activites['type_inscrits_evenement'] ?? '';
    $doit_afficher_infos_public = (
        $select_type_request === 'public'
        || $select_type_request === 'membre_reseau_fiafe'
        || !empty($eligibilite_inscription_evenement['eligibilite_token_inscription'])
        || (
            $public_mode
            && $evenement_type_inscrits === 'public'
            && empty($id_auteur_connecte)
        )
    );
    if ($doit_afficher_infos_public) {
        $saisies_merge_unique($saisies_general, generer_saisies_info_public($public_mode ? 'public' : 'prive'));
    }

    // Préparer tableau nb_personne utilisé pour les sélections de tarifs / nb inscrits
    $nb_personne = array();
    $nb_personne[1] = _T('association:activite_form_public_nb_personne');
    $max_places_limit = isset($gestions_places['places_limites']) ? intval($gestions_places['places_limites']) : 1;
    for ($i = 2; $i <= max(2, $max_places_limit); $i++) {
        $nb_personne[$i] = _T('association:activite_form_public_nb_personnes', array('nb' => $i));
    }

    // EVENEMENT GRATUIT
    if (isset($affichage_dans_activites['payant']) && $affichage_dans_activites['payant'] == false) {
        // Sans accompagnants, le formulaire simple n'a pas besoin de sélection du nombre.
        // On force une seule inscription via un champ caché pour éviter d'appeler le helper.
        if (empty($affichage_dans_activites['accompagnants'])) {
            $saisies_general[] = array(
                'saisie' => 'hidden',
                'options' => array(
                    'nom' => 'nb_inscrits',
                    'defaut' => 1,
                ),
            );
        } else {
            $saisies_general[] = champs_saisie_nb_inscrits($gestions_places, $affichage_dans_activites);
        }

        // famille
        if ($saisie_famille_active == 'oui' && $id_auteur_connecte && !empty($affichage_dans_activites['accompagnants'])) {
            $saisies_general[] = champs_saisies_selection_membres_famille($data_famille, true);
        }
    }

    // EVENEMENT PAYANT
    if (isset($affichage_dans_activites['payant']) && $affichage_dans_activites['payant'] == true) {
        // Construction des catégories tarifaires brutes
        $tableau_categories = $affichage_dans_activites['montant'] ?? array();
        $saisie_categories = array();
        $datas_categories = array();
        if (is_array($tableau_categories) || is_object($tableau_categories)) {
            foreach ($tableau_categories as $categorie => $value) {
                $id_categorie = $value['id_categorie'];
                $choix_montant = ($value['titre'] ?? '') . ' ' . ($value['montant_symbole'] ?? '');
                $datas_categories[$id_categorie] = $choix_montant;
                $saisie_categories[] = $value;
            }
        }

        // Déterminer les types de tarifs accessibles selon le statut du compte connecté
        if (($statut_interne_auteur_connecte ?? '') == 'ok' && in_array($statut_auteur ?? '', array('1comite', '0minirezo'))) {
            $array_type_adherents = array('adherent', 'indifferent', 'benevole');
        } elseif (($statut_interne_auteur_connecte ?? '') == 'ok') {
            $array_type_adherents = array('adherent', 'indifferent');
        } else {
            $array_type_adherents = array('non_adherent', 'indifferent');
        }

        // --- CAS FAMILLE + PAYANT + SANS ACCOMPAGNANT ---
        // On propose : 1 radio pour sélectionner l'unique participant parmi la famille,
        //              1 radio pour sélectionner le tarif.
        if ($saisie_famille_active == 'oui' && !empty($id_auteur_connecte) && empty($affichage_dans_activites['accompagnants'])) {
            // Sélection du membre famille (radio unique)
            $saisies_general[] = champs_saisies_selection_membres_famille($data_famille, false);

            // Tarifs filtrés selon le statut du titulaire du compte
            $datas_categories_filtrees = generer_array_categories_participation($tableau_categories, 'datas', $array_type_adherents, 1);
            if (empty($datas_categories_filtrees)) {
                $datas_categories_filtrees = $datas_categories;
            }
            $type_saisie_tarif = (count($datas_categories_filtrees) <= 4) ? 'radio' : 'selection';
            $saisies_tarifs[] = array(
                'saisie' => $type_saisie_tarif,
                'options' => array(
                    'nom' => 'categorie',
                    'cacher_option_intro' => 0,
                    'label' => _T('association:activite_form_public_tarif'),
                    'defaut' => !empty(_request('categorie')) ? _request('categorie') : '',
                    'obligatoire' => 'oui',
                    'datas' => $datas_categories_filtrees,
                ),
            );

        // --- CAS FAMILLE + PAYANT + AVEC ACCOMPAGNANTS ---
        // Un radio par membre de la famille pour sélectionner son tarif.
        } elseif ($saisie_famille_active == 'oui' && !empty($id_auteur_connecte) && !empty($affichage_dans_activites['accompagnants'])) {
            spip_log('[IE_CHARGER][CAS_FAMILLE_PAYANT_ACCOMPAGNANTS] famille_active=oui id_auteur=' . intval($id_auteur_connecte) . ' nb_membres=' . count((array)$data_famille), 'association' . _LOG_DEBUG);
            $datas_categories_filtrees = generer_array_categories_participation($tableau_categories, 'datas', $array_type_adherents, 1);
            if (empty($datas_categories_filtrees)) {
                $datas_categories_filtrees = $datas_categories;
            }
            $type_saisie_tarif = (count($datas_categories_filtrees) <= 4) ? 'radio' : 'selection';
            $valeur_default = _request('categorie');
            foreach ($data_famille as $cle_membre => $prenom_membre) {
                if (empty($prenom_membre)) {
                    continue;
                }
                $opts = array(
                    'nom' => 'categorie[' . $cle_membre . ']',
                    'label' => $prenom_membre,
                    'cacher_option_intro' => 1,
                    'datas' => $datas_categories_filtrees,
                    'defaut' => !empty($valeur_default[$cle_membre]) ? $valeur_default[$cle_membre] : '',
                    'obligatoire' => 'non',
                );
                if (!$public_mode) {
                    $opts['afficher_si'] = "@select_type_inscrit@=='membre' || @select_type_inscrit@=='non_membre'";
                }
                $saisies_tarifs[] = array('saisie' => $type_saisie_tarif, 'options' => $opts);
            }

        // --- CAS SANS FAMILLE + AVEC ACCOMPAGNANTS ---
        // Un sélecteur nombre par tarif.
        } elseif (!empty($affichage_dans_activites['accompagnants'])) {
            foreach ($saisie_categories as $saisie_categorie) {
                $valeur_default = _request('categorie');
                $opts = array(
                    'nom' => 'categorie[' . $saisie_categorie['id_categorie'] . ']',
                    'label' => ($saisie_categorie['titre'] ?? '') . ' : ' . ($saisie_categorie['montant_symbole'] ?? ''),
                    'defaut' => !empty($valeur_default[$saisie_categorie['id_categorie']]) ? $valeur_default[$saisie_categorie['id_categorie']] : '',
                    'obligatoire' => 'non',
                    'explication' => $saisie_categorie['commentaires'] ?? '',
                    'datas' => $nb_personne,
                );
                $saisies_tarifs[] = array('saisie' => 'selection', 'options' => $opts);
            }

        // --- CAS SANS FAMILLE + SANS ACCOMPAGNANT ---
        // Un radio/select unique pour le tarif.
        } else {
            $type_saisie_tarif = (count($datas_categories) <= 4) ? 'radio' : 'selection';
            $opts = array(
                'nom' => 'categorie',
                'cacher_option_intro' => 0,
                'label' => _T('association:activite_form_public_tarif'),
                'defaut' => !empty(_request('categorie')) ? _request('categorie') : '',
                'obligatoire' => 'oui',
                'datas' => $datas_categories,
            );
            $saisies_tarifs[] = array('saisie' => $type_saisie_tarif, 'options' => $opts);
        }
    }

    // En formulaire simple (non-multi), hors mode famille, conserver une saisie libre
    // des noms des accompagnants pour FO/BO.
    // Cette saisie est placée dans les modalités pour l'afficher juste avant
    // le message au responsable.
    if (!$multi_etapes
        && $saisie_famille_active != 'oui'
        && !empty($affichage_dans_activites['accompagnants'])
    ) {
        $defaut_nom_participants = _request('nom_participants');
        if ($defaut_nom_participants === null || $defaut_nom_participants === '') {
            $defaut_nom_participants = $data_activite['nom_participants'] ?? '';
        }
        $saisies_modalites[] = array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'nom_participants',
                'label' => _T('association:activite_form_public_nom_participants'),
                'defaut' => $defaut_nom_participants,
                'obligatoire' => 'non',
                'rows' => '4',
            ),
        );
    }

    // Ajouter champs complémentaires si nécessaires
    if (!empty($GLOBALS['association_metas']['meta_cfg_event_message_responsable']) && $GLOBALS['association_metas']['meta_cfg_event_message_responsable'] != 'non') {
        $saisies_modalites[] = array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'commentaire',
                'label' => _T('association:activite_form_public_commentaires'),
                'defaut' => !empty(_request('commentaire')) ? _request('commentaire') : '',
                'obligatoire' => 'non',
                'rows' => '4'
            )
        );
    }

    // Annotation privée : visible uniquement en BO pour les profils autorisés
    // (responsables de l'événement et admins).
    // Placée en dernière position des modalités.
    $est_mode_bo = in_array($mode, array('prive', 'multi_prive'));
    if ($est_mode_bo && autoriser('voir_activites', 'evenement', $id_evenement)) {
        $defaut_annotation = _request('annotation');
        if ($defaut_annotation === null || $defaut_annotation === '') {
            $defaut_annotation = $data_activite['annotation'] ?? '';
        }
        $saisies_modalites[] = array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'annotation',
                'label' => _T('association:activite_form_prive_annotation'),
                'explication' => _T('association:activite_form_prive_annotation_explication'),
                'defaut' => $defaut_annotation,
                'obligatoire' => 'non',
                'rows' => '4',
            )
        );
    }

    // En back-office, laisser l'operateur choisir explicitement si la
    // notification automatique doit etre envoyee a l'inscrit. Le comportement
    // historique reste actif par defaut, mais une case decochee est respectee
    // par traiter().
    if ($est_mode_bo) {
        $label_notification = !empty($id_activite)
            ? _T('association:activite_bouton_notifier_modification_adherent')
            : _T('association:activite_bouton_notifier_adherent');
        $saisies_modalites[] = array(
            'saisie' => 'case',
            'options' => array(
                'nom' => 'notifier_adherent',
                'label' => _T('association:activite_bouton_notification_active'),
                'label_case' => $label_notification,
                'explication' => _T('association:activite_bouton_notification_explication'),
                'defaut' => 'on',
                'valeur_oui' => 'on',
                'obligatoire' => 'non',
            ),
        );
    }


    // CGU / modalités d'inscription (articles configurés dans le BO)
    // Uniquement pour les non-connectés (les adhérents n'ont pas à re-valider)
    if (!$est_mode_bo && empty($id_auteur_connecte)) {
        $saisies_cgu = champs_saisies_modalites_evenement();
        if (!empty($saisies_cgu)) {
            $saisies_modalites = array_merge($saisies_modalites, $saisies_cgu);
        }
    }

    // Condition d'inscription spécifique à l'événement
    if (!$est_mode_bo && !empty($affichage_dans_activites['condition_inscription'])
        && $affichage_dans_activites['condition_inscription'] == 'oui'
        && !empty($affichage_dans_activites['message_condition_inscription'])
    ) {
        $saisies_modalites[] = array(
            'saisie' => 'case',
            'options' => array(
                'nom' => 'condition_inscription',
                'label_case' => typo($affichage_dans_activites['message_condition_inscription']),
                'obligatoire' => 'oui',
            ),
        );
    }

    if ($id_evenement > 0) {
        $saisies_hidden[] = array(
            'saisie' => 'hidden',
            'options' => array('nom' => 'id_evenement', 'defaut' => $id_evenement)
        );
    }

    if ($id_activite) {
        $saisies_hidden[] = array(
            'saisie' => 'hidden',
            'options' => array('nom' => 'id_activite', 'defaut' => $id_activite)
        );
        $saisies_hidden[] = array(
            'saisie' => 'hidden',
            'options' => array('nom' => 'modif', 'defaut' => 'oui')
        );
        // Forcer id_activite dans le contexte de requête pour qu'il soit disponible
        // à chaque étape CVT via _request('id_activite'), même si le champ hidden
        // n'est pas soumis à l'étape courante.
        if (_request('id_activite') === null) {
            set_request('id_activite', $id_activite);
        }
    }

    // Point d'extension minimal: permet aux plugins metier (ex: FIAFE client)
    // d'injecter des saisies/valeurs sans dupliquer tout le formulaire.
    $trace_id = _request('ie_trace_id');
    if (!$trace_id) {
        $trace_id = 'ie_charger_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
        set_request('ie_trace_id', $trace_id);
    }
    $flux_charger = pipeline('association_inscription_evenement_charger', array(
        'args' => array(
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'id_activite' => intval($id_activite),
            'trace_id' => $trace_id,
            'association' => $association,
            'email_inscrit' => $email_inscrit,
            'post' => ie_requete_attendue($mode, $id_evenement, $id_activite, array()),
        ),
        'data' => array(
            'saisies_general' => $saisies_general,
            'saisies_tarifs' => $saisies_tarifs,
            'saisies_modalites' => $saisies_modalites,
            'saisies_hidden' => $saisies_hidden,
            'data_activite' => $data_activite,
            'info_adherent' => $info_adherent,
        ),
    ));
    if (is_array($flux_charger) && isset($flux_charger['data']) && is_array($flux_charger['data'])) {
        $saisies_general = isset($flux_charger['data']['saisies_general']) && is_array($flux_charger['data']['saisies_general']) ? $flux_charger['data']['saisies_general'] : $saisies_general;
        $saisies_tarifs = isset($flux_charger['data']['saisies_tarifs']) && is_array($flux_charger['data']['saisies_tarifs']) ? $flux_charger['data']['saisies_tarifs'] : $saisies_tarifs;
        $saisies_modalites = isset($flux_charger['data']['saisies_modalites']) && is_array($flux_charger['data']['saisies_modalites']) ? $flux_charger['data']['saisies_modalites'] : $saisies_modalites;
        $saisies_hidden = isset($flux_charger['data']['saisies_hidden']) && is_array($flux_charger['data']['saisies_hidden']) ? $flux_charger['data']['saisies_hidden'] : $saisies_hidden;
        $data_activite = isset($flux_charger['data']['data_activite']) && is_array($flux_charger['data']['data_activite']) ? $flux_charger['data']['data_activite'] : $data_activite;
        $info_adherent = isset($flux_charger['data']['info_adherent']) && is_array($flux_charger['data']['info_adherent']) ? $flux_charger['data']['info_adherent'] : $info_adherent;
    }

    $saisies_par_etapes = array();
    if ($multi_etapes) {
        // Build canonical fieldsets list and delegate assembly to the helper so
        // the resulting $saisies always has 'options' as associative key and
        // numeric fieldsets following.
        $opts = ie_options_etapes_saisies(true);
        $fieldsets = array();
        if (!empty($saisies_general)) {
            $fieldsets[] = array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_infos_generales',
                    'label' => _T('association:fieldset_selection_membre_famille_label'),
                ),
                'saisies' => $saisies_general,
            );
        }

        // Prepare participant fieldsets to be inserted between general infos and tarifs
        $participant_fieldsets = array();
        $nb_inscrits = ie_nombre_inscrits_contexte($data_activite);
        if ($nb_inscrits <= 0 && !empty($data_activite['famille']) && is_array($data_activite['famille'])) {
            $nb_inscrits = count($data_activite['famille']);
        }

        // If family-mode is active and we have a connected author, build famille fieldsets
        if ($saisie_famille_active == 'oui' && $id_auteur_connecte) {
            $saisies_famille = champs_saisies_famille($id_auteur_connecte, $affichage_dans_activites);
            if (!empty($saisies_famille) && is_array($saisies_famille)) {
                foreach (array_values($saisies_famille) as $fs) {
                    $participant_fieldsets[] = $fs;
                }
            }
        } elseif ($nb_inscrits > 0) {
            // For anonymous/public/non-membre flow, render generic 'inscrit_N' fieldsets
            $saisies_inscrits = champs_saisies_inscrits($affichage_dans_activites, $nb_inscrits, array('id_auteur' => $id_auteur_connecte));
            if (!empty($saisies_inscrits) && is_array($saisies_inscrits)) {
                foreach (array_values($saisies_inscrits) as $fs) {
                    $participant_fieldsets[] = $fs;
                }
            }
        }

        // Comme en FO, toutes les identites appartiennent a une seule etape.
        // Les fieldsets inscrit_N restent distincts visuellement, mais ne
        // deviennent plus chacun une etape CVT autonome.
        if (!empty($participant_fieldsets)) {
            $fieldsets[] = array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_info_supplementaire',
                    'label' => _T('association:fieldset_info_supplementaire_label'),
                ),
                'saisies' => $participant_fieldsets,
            );
        }

        if (!empty($saisies_tarifs)) {
            $fieldsets[] = array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_tarifs',
                    'label' => _T('association:activite_form_public_tarif'),
                ),
                'saisies' => $saisies_tarifs,
            );
        }
        if (!empty($saisies_modalites)) {
            $fieldsets[] = array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_modalites',
                    'label' => _T('association:fieldset_modalites'),
                ),
                'saisies' => $saisies_modalites,
            );
        }

        // Assemble the final $saisies with options first and numeric entries
        // afterwards (fieldsets then hidden fields).
        $saisies = ie_assembler_saisies_multi($opts, $fieldsets, $saisies_hidden);

        // compute steps from the fieldsets (will ignore the 'options' entry)
        $saisies_par_etapes = ie_generer_saisies_par_etapes($saisies);
    } else {
        // non-multi: flatten groups into a single sequence
        $saisies = array_merge($saisies_general, $saisies_tarifs, $saisies_modalites, $saisies_hidden);
    }

    // préparer valeurs de retour
    $res = array_merge($res, array(
        '_saisies' => $saisies,
        'editable' => (intval($id_evenement) > 0),
        'ie_mode' => $mode,
        'id_evenement' => intval($id_evenement),
        'id_activite' => intval($id_activite),
        'modif' => $id_activite ? 'oui' : '',
        'gestions_places' => $gestions_places,
        'creation_inscription' => $creation_inscription,
        'modification_inscription' => $modification_inscription,
        'alerte' => alerte_inscription_evenement($id_evenement, $id_activite),
    ));
    if (association_diagnostic_inscription_webmestre()) {
        $regles_diagnostic = association_inscription_regles_effectives(
            $mode,
            $affichage_dans_activites,
            $gestions_places,
            $GLOBALS['visiteur_session'] ?? array()
        );
        $contexte_diagnostic = association_diagnostic_inscription_contexte_formulaire(
            $mode,
            $id_activite,
            $saisies,
            $saisie_famille_active
        );
        $res['diagnostic_inscription'] = association_diagnostic_formulaire_inscription(
            $id_evenement,
            $contexte_diagnostic,
            array(
                'ouverture_inscription' => $ouverture_inscription_evenement,
                'eligibilite_inscription' => $eligibilite_inscription_evenement,
                'affichage_dans_activites' => $affichage_dans_activites,
                'gestions_places' => $gestions_places,
                'regles_effectives' => $regles_diagnostic['diagnostic'],
                'configuration' => array_intersect_key(
                    $GLOBALS['association_metas'],
                    array_flip(array(
                        'meta_cfg_event_config_accompagnants',
                        'meta_cfg_event_form_info_supp',
                        'meta_cfg_event_accompagnants',
                        'meta_cfg_event_limite_nb_accompagnants',
                        'meta_cfg_event_type_inscrits_evenement',
                        'meta_cfg_event_type_quota',
                        'meta_cfg_event_invites',
                        'meta_cfg_event_quota_inscription_adherent',
                        'nb_inscription_quota_adherent',
                        'nb_jour_quota_adherent',
                    ))
                ),
            )
        );
    }
    if ($multi_etapes) {
        $res = array_merge($res, array('_saisies_par_etapes' => $saisies_par_etapes));
        // Diagnostic log to validate shape of _saisies for CVT multi-step
        $trace_charger = 'ie_charger_' . date('YmdHis') . '_' . mt_rand(100, 999);
        $saisies_keys = is_array($saisies) ? array_keys($saisies) : array();
        $first_fieldsets = array();
        foreach ($saisies as $k => $v) {
            if (is_int($k) && is_array($v) && isset($v['options']['nom'])) {
                $first_fieldsets[] = $v['options']['nom'];
            }
            if (count($first_fieldsets) >= 5) {
                break;
            }
        }
        spip_log('[IE_CHARGER][' . $trace_charger . '] keys=' . implode(',', $saisies_keys) . ' fieldsets=' . implode(',', $first_fieldsets) . ' steps=' . json_encode($saisies_par_etapes), 'association' . _LOG_DEBUG);
    }

    if (!empty($info_adherent)) {
        $res = array_merge($info_adherent, $res);
    }
    if (!empty($data_activite)) {
        $res = array_merge($data_activite, $res, array('id_activite' => $id_activite));
    }
    if (!empty($GLOBALS['message_erreur'])) {
        $res = array_merge($_POST, $res);
    }

    return $res;
}

/**
 * Retourne les types de tarifs accessibles au titulaire de l'inscription.
 *
 * @param int $id_auteur
 * @return array
 */
function ie_types_tarifs_auteur($id_auteur) {
    $types = array('non_adherent', 'indifferent');
    $id_auteur = intval($id_auteur);
    if ($id_auteur < 1) {
        return $types;
    }

    $auteur = sql_fetsel('statut, statut_interne', 'spip_auteurs', 'id_auteur=' . $id_auteur);
    if (($auteur['statut_interne'] ?? '') !== 'ok') {
        return $types;
    }

    $types = array('adherent', 'indifferent');
    if (in_array($auteur['statut'] ?? '', array('1comite', '0minirezo'), true)) {
        $types[] = 'benevole';
    }

    return $types;
}

/**
 * Normalise les tarifs postes en couples participant/categorie.
 *
 * @param string $mode
 * @param array $post
 * @param array $data_form
 * @param array $ids_categories_evenement
 * @return array
 */
function ie_normaliser_tarifs_postes($mode, $post, $data_form, $ids_categories_evenement = array()) {
    $selections = array();
    $mode_multi = (strpos($mode, 'multi') !== false);

    if ($mode_multi) {
        foreach ((array)($data_form['categorie_result'] ?? array()) as $participant => $id_categorie) {
            // Le BO sans accompagnants poste categorie[id_categorie]=quantite,
            // tandis que le multi famille poste participant=id_categorie.
            if (is_numeric($participant)
                && in_array(intval($participant), array_map('intval', $ids_categories_evenement), true)
                && is_scalar($id_categorie)
                && intval($id_categorie) > 0
            ) {
                $selections[] = array(
                    'participant' => 'inscrit_1',
                    'id_categorie' => intval($participant),
                    'quantite' => intval($id_categorie),
                );
            } elseif ($id_categorie !== '' && $id_categorie !== null) {
                $selections[] = array(
                    'participant' => (string)$participant,
                    'id_categorie' => intval($id_categorie),
                    'quantite' => 1,
                );
            }
        }
        return $selections;
    }

    $categories = $post['categorie'] ?? null;
    if (!is_array($categories)) {
        if ($categories !== '' && $categories !== null) {
            $selections[] = array(
                'participant' => 'inscrit_1',
                'id_categorie' => intval($categories),
                'quantite' => 1,
            );
        }
        return $selections;
    }

    $roles = array('adherent', 'conjoint', 'enfant_1', 'enfant_2', 'enfant_3', 'enfant_4', 'enfant_5', 'invite_1', 'invite_2');
    foreach ($categories as $cle => $valeur) {
        if (in_array((string)$cle, $roles, true)) {
            if ($valeur !== '' && $valeur !== null) {
                $selections[] = array(
                    'participant' => (string)$cle,
                    'id_categorie' => intval($valeur),
                    'quantite' => 1,
                );
            }
            continue;
        }

        $id_categorie = intval($cle);
        if (is_array($valeur)) {
            foreach ($valeur as $participant) {
                $selections[] = array(
                    'participant' => (string)$participant,
                    'id_categorie' => $id_categorie,
                    'quantite' => 1,
                );
            }
        } elseif (intval($valeur) > 0) {
            $selections[] = array(
                'participant' => 'inscrit_1',
                'id_categorie' => $id_categorie,
                'quantite' => intval($valeur),
            );
        }
    }

    return $selections;
}

/**
 * Controle que chaque tarif poste est actif, lie a l'evenement et compatible.
 *
 * @param int $id_evenement
 * @param string $mode
 * @param array $post
 * @param array $data_form
 * @return array
 */
function ie_verifier_tarifs_compatibles($id_evenement, $mode, $post, $data_form) {
    $categories = array();
    $selection_sql = sql_select(
        '*',
        'spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a ON(a.id_categorie=b.id_categorie)',
        'a.id_evenement=' . intval($id_evenement) . " AND b.statut='ok'"
    );
    while ($categorie = sql_fetch($selection_sql)) {
        if (!array_key_exists('montant', $categorie)
            || $categorie['montant'] === ''
            || $categorie['montant'] === null
        ) {
            continue;
        }
        $categories[intval($categorie['id_categorie'])] = $categorie;
    }
    sql_free($selection_sql);

    // Dans le BO, l'auteur connecte reste present dans le contexte meme quand
    // l'operateur inscrit explicitement une personne sans compte. Son statut ne
    // doit alors pas accorder (ni retirer) des tarifs au participant saisi.
    $post_etapes = (isset($post['cvtm_prev_post']) && is_array($post['cvtm_prev_post']))
        ? array_merge($post['cvtm_prev_post'], $post)
        : $post;
    $select_type_inscrit = $post_etapes['select_type_inscrit']
        ?? ($data_form['select_type_inscrit'] ?? _request('select_type_inscrit'));
    $id_auteur_tarifs = $data_form['id_auteur'] ?? 0;
    if (in_array($select_type_inscrit, array('public', 'membre_reseau_fiafe'), true)) {
        $id_auteur_tarifs = 0;
    }
    $types_auteur = ie_types_tarifs_auteur($id_auteur_tarifs);
    $participants = array_keys((array)($data_form['array_post_inscrits'] ?? array()));
    if (empty($participants)) {
        $participants = ie_aplatir_liste_valeurs($post['famille'] ?? array());
    }

    $selections = ie_normaliser_tarifs_postes($mode, $post, $data_form, array_keys($categories));
    foreach ($selections as $selection) {
        $id_categorie = intval($selection['id_categorie']);
        $participant = $selection['participant'];
        $categorie = $categories[$id_categorie] ?? null;
        $type = $categorie['type_inscrit'] ?? '';
        $cle_erreur = (strpos($mode, 'multi') !== false && $participant !== '')
            ? 'categorie_' . $participant
            : 'categorie';

        if (!$categorie) {
            return array($cle_erreur => _T('association:erreur_tarif_incompatible'));
        }

        if ($type === 'couple') {
            $premier = in_array($participant, array('adherent', 'inscrit_1'), true);
            $conjoint_present = in_array('conjoint', $participants, true)
                || in_array('inscrit_2', $participants, true)
                || intval($selection['quantite']) >= 2;
            if (!$premier || !$conjoint_present) {
                return array($cle_erreur => _T('association:erreur_tarif_incompatible'));
            }
            continue;
        }

        $types_participant = association_types_tarifs_par_role($participant ?: 'inscrit_1', $types_auteur);
        if (!in_array($type, $types_participant, true)) {
            return array($cle_erreur => _T('association:erreur_tarif_incompatible'));
        }
    }

    return array();
}

/**
 * Vérification commune pour les formulaires d'inscription.
 * Centralise quotas, doublons, spam et validations basiques.
 *
 * @param string $mode
 * @param int $id_evenement
 * @param int|null $id_activite
 * @param array|null $post
 * @param int|null $etape_validation Etape CVT explicitement verifiee
 * @return array Erreurs (vide = ok)
 */
function ie_verifier_commons($mode, $id_evenement = 0, $id_activite = null, $post = null, $etape_validation = null) {
    list($id_evenement, $id_activite) = ie_resoudre_ids_inscription($id_evenement, $id_activite);
    if ($id_activite) {
        $data_activite = (strpos($mode, 'multi') !== false)
            ? preparer_chargement_modification_inscription_multi($id_activite, strpos($mode, 'public') !== false ? 'public' : 'prive')
            : preparer_chargement_modification_inscription($id_activite);
        ie_rehydrater_requete_modification($data_activite);
    }
    // Ne plus dépendre de $_POST brut: reconstruire un payload attendu via _request
    $post = ie_requete_attendue($mode, $id_evenement, $id_activite, is_array($post) ? $post : array());

    // En multi-etapes, `famille[]` peut etre portée par cvtm_prev_post.
    $post_normalise = ie_convertir_post($post);
    if (!empty($post_normalise['cvtm_prev_post']) && is_array($post_normalise['cvtm_prev_post'])) {
        $post_normalise = array_merge($post_normalise['cvtm_prev_post'], $post_normalise);
        unset($post_normalise['cvtm_prev_post']);
    }
    $erreurs = array();
    $log_canal = 'association' . _LOG_DEBUG;
    $trace_id = 'ie_verify_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
    // Propager un identifiant de trace pour corréler verifier/traiter/formatage.
    set_request('ie_trace_id', $trace_id);

    // données contextuelles
    $data_form = ie_format_post($mode, $id_evenement, $post);
    // En modification, certains champs d'identité ne sont pas réaffichés et
    // ne reviennent donc pas dans POST. Réutiliser uniquement l'identité déjà
    // portée par l'activité ciblée au lieu de rejeter une modification valide.
    if (!empty($id_activite)
        && trim((string) ($data_form['premier_inscrit']['nom'] ?? '')) === ''
    ) {
        $activite_identite = sql_fetsel(
            'id_evenement,prenom_inscrit,nom_inscrit,email_inscrit,tel_inscrit',
            'spip_asso_activites',
            'id_activite=' . intval($id_activite)
        );
        if (intval($activite_identite['id_evenement'] ?? 0) === intval($id_evenement)) {
            $data_form['premier_inscrit'] = array(
                'prenom' => trim((string) ($activite_identite['prenom_inscrit'] ?? '')),
                'nom' => trim((string) ($activite_identite['nom_inscrit'] ?? '')),
                'email' => trim((string) ($activite_identite['email_inscrit'] ?? '')),
                'tel' => trim((string) ($activite_identite['tel_inscrit'] ?? '')),
            );
        }
    }
    $categorie_result = $data_form['categorie_result'] ?? ($post['categorie'] ?? null);
    $gestions_places = gestions_places($id_evenement);
    $affichage_dans_activites = affichage_dans_activites($id_evenement);
    $regles_effectives = association_inscription_regles_effectives(
        $mode,
        $affichage_dans_activites,
        $gestions_places,
        $GLOBALS['visiteur_session'] ?? array()
    );
    $affichage_dans_activites = $regles_effectives['affichage'];
    $gestions_places = $regles_effectives['gestions_places'];
    $is_payant = !empty($affichage_dans_activites['payant']) && $affichage_dans_activites['payant'] == true;
    if ($is_payant) {
        $erreurs_tarifs = ie_verifier_tarifs_compatibles($id_evenement, $mode, $post_normalise, $data_form);
        if (!empty($erreurs_tarifs)) {
            $erreurs = array_merge($erreurs, $erreurs_tarifs);
            $erreurs['message_erreur'] = _T('association:erreur_message_erreur');
        }
    }

    // utilisateur connecté
    $id_auteur_connecte = (isset($GLOBALS['visiteur_session']) && is_array($GLOBALS['visiteur_session']) && isset($GLOBALS['visiteur_session']['id_auteur'])) ? $GLOBALS['visiteur_session']['id_auteur'] : '';

    // configuration accompagnants
    $config_accompagnants = isset($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']) ? $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] : 'tout';

    // famille active si config et visiteur connecté
    if ($config_accompagnants == 'membre_famille' && $id_auteur_connecte) {
        $saisie_famille_active = 'oui';
    } else {
        $saisie_famille_active = 'non';
    }

    $radio_type_adherent = $GLOBALS['visiteur_session']['radio_type_adherent'] ?? false;

    $mode_multi = (strpos($mode, 'multi') !== false);

    $famille_selection = $post_normalise['famille'] ?? ($post['famille'] ?? null);
    $nb_famille_cochee = is_array($famille_selection) ? count($famille_selection) : (!empty($famille_selection) ? 1 : 0);

    // Consolidation secondaire avec le formateur multi (etapes precedentes decodees).
    $nb_famille_data_form = 0;
    if ($mode_multi && $saisie_famille_active == 'oui' && !empty($data_form['array_post_inscrits']) && is_array($data_form['array_post_inscrits'])) {
        $cles_famille_selectionnees = array_flip(ie_aplatir_liste_valeurs((array) $famille_selection));
        foreach (array_keys($data_form['array_post_inscrits']) as $cle_participant) {
            if (isset($cles_famille_selectionnees[$cle_participant])) {
                $nb_famille_data_form++;
            }
        }
    }
    $nb_famille_total = max(intval($nb_famille_cochee), intval($nb_famille_data_form));
    $categorie_type = is_array($categorie_result) ? 'array' : gettype($categorie_result);
    $categorie_count = is_array($categorie_result) ? count($categorie_result) : 0;
    spip_log('[IE_VERIFY][' . $trace_id . '] sources mode_multi=' . ($mode_multi ? 'oui' : 'non')
        . ' famille_active=' . $saisie_famille_active
        . ' nb_famille_cochee=' . intval($nb_famille_cochee)
        . ' nb_famille_data_form=' . intval($nb_famille_data_form)
        . ' nb_famille_total=' . intval($nb_famille_total)
        . ' data_form_nombre_participants=' . intval($data_form['nombre_participants'] ?? 0)
        . ' categorie_type=' . $categorie_type
        . ' categorie_count=' . intval($categorie_count), $log_canal);

    // Calcul du nombre d'inscrits selon configuration
    $nombre_inscrits = isset($data_form['nombre_participants']) ? intval($data_form['nombre_participants']) : 0;
    if (!empty($affichage_dans_activites['payant']) && $affichage_dans_activites['payant'] == true) {
        if ($nombre_inscrits <= 0) {
            if (!empty($affichage_dans_activites['accompagnants'])) {
                if ($saisie_famille_active == 'oui') {
                    if (is_array($categorie_result) && !empty($categorie_result)) {
                        $nombre_inscrits = 0;
                        foreach ($categorie_result as $sel) {
                            if (is_array($sel)) {
                                $nombre_inscrits += count($sel);
                            } elseif ($sel !== '' && $sel !== null) {
                                $nombre_inscrits += 1;
                            }
                        }
                    } else {
                        $nombre_inscrits = $nb_famille_cochee;
                    }
                } else {
                    if (is_array($categorie_result) && !empty($categorie_result)) {
                        $nombre_inscrits = 0;
                        foreach ($categorie_result as $v) {
                            $nombre_inscrits += intval($v);
                        }
                    }
                }
            } else {
                $nombre_inscrits = 1;
            }
        }
    } else {
        if (empty($affichage_dans_activites['accompagnants'])) {
            $nombre_inscrits = 1;
        } elseif ($saisie_famille_active == 'non') {
            $nombre_inscrits = isset($post['nb_inscrits']) ? intval($post['nb_inscrits']) : 0;
        } else {
            $nombre_inscrits = $nb_famille_cochee;
        }
    }

    // En mode famille multi, la source de vérité est le nombre de membres sélectionnés
    // consolidé entre la requête courante et les données d'étapes précédentes.
    if ($mode_multi && $saisie_famille_active == 'oui' && $nb_famille_total > 0) {
        $nombre_inscrits = $nb_famille_total;
        // Ajouter les invités hors famille au décompte
        $nb_invite_quota = max(0, intval($post['nb_invite'] ?? $post_normalise['nb_invite'] ?? 0));
        if ($nb_invite_quota > 0) {
            $nombre_inscrits += $nb_invite_quota;
        }
    }

    spip_log('[IE_VERIFY][' . $trace_id . '] calcul nombre_inscrits=' . intval($nombre_inscrits), $log_canal);

    // Doublons pour la famille
    if ($saisie_famille_active == 'oui' && is_array($categorie_result)) {
        $id_participants = array();
        foreach ($categorie_result as $selection_categorie) {
            if (is_array($selection_categorie)) {
                $id_participants = array_merge($id_participants, $selection_categorie);
            }
        }
        $nb_resulat = count($id_participants);
        $filtre_doublon_resultats = array_unique($id_participants);
        if ($nb_resulat !== count($filtre_doublon_resultats)) {
            $erreurs['nb_accompagnants'] = _T('association:erreur_participant_famille_doublon');
        }
    }

    // Si le mode famille est actif (ou si le formater a détecté une saisie
    // famille effective) et qu'aucun membre n'a été sélectionné, renvoyer
    // une erreur explicite pour guider l'utilisateur.
    $famille_a_verifier = ($saisie_famille_active == 'oui') || (!empty($data_form['famille_selection_vide']));
    if ($famille_a_verifier && !$mode_multi) {
        $id_participants_verif = isset($data_form['id_participants']) ? $data_form['id_participants'] : array();
        if (empty($id_participants_verif) || (is_array($id_participants_verif) && count($id_participants_verif) === 0)) {
            $erreurs['famille'] = _T('association:erreur_aucun_membre_selectionne');
        }
    }

    if (strpos($mode, 'public') === false) {
        $post_etapes = (isset($post_normalise['cvtm_prev_post']) && is_array($post_normalise['cvtm_prev_post']))
            ? array_merge($post_normalise['cvtm_prev_post'], $post_normalise)
            : $post_normalise;
        $select_type_inscrit = $post['select_type_inscrit'] ?? $post_etapes['select_type_inscrit'] ?? '';
        if ($select_type_inscrit === 'membre' && empty($post['membre']) && empty($post_etapes['membre'])) {
            $erreurs['membre'] = _T('info_obligatoire');
        } elseif ($select_type_inscrit === 'non_membre' && empty($post['non_membre']) && empty($post_etapes['non_membre'])) {
            $erreurs['non_membre'] = _T('info_obligatoire');
        }
    }

    // Vérification doublon inscription pour l'auteur réellement porté par le formulaire.
    // En FO: auteur de session (ou 0). En BO: auteur sélectionné (ou 0).
    $id_auteur_doublon = intval($data_form['id_auteur'] ?? 0);
    if ($id_auteur_doublon > 0 && empty($id_activite)) {
        $query_activite_email = sql_fetsel('*', 'spip_asso_activites', "id_evenement=$id_evenement AND id_auteur = $id_auteur_doublon AND statut != 'desinscrit'");
        if (!empty($query_activite_email)) {
            $erreurs['doublon'] = _T('association:erreur_doublon');
        }
    }

    // En multi public anonyme, le premier email saisi doit servir de garde-fou:
    // - ne pas laisser créer un doublon anonyme sur le même événement
    // - rediriger implicitement vers la connexion si cet email appartient déjà à un adhérent
    if ($mode_multi && strpos($mode, 'public') !== false && empty($id_activite) && intval($data_form['id_auteur'] ?? 0) <= 0) {
        $email_multi_public = trim((string) ($post_normalise['email_inscrit_1'] ?? $post['email_inscrit_1'] ?? $data_form['premier_inscrit']['email'] ?? ''));
        if ($email_multi_public !== '') {
            $query_activite_email = sql_fetsel(
                '*',
                'spip_asso_activites',
                "id_evenement=$id_evenement AND email_inscrit=" . sql_quote($email_multi_public) . " AND statut != 'desinscrit'"
            );
            if (!empty($query_activite_email)) {
                $erreurs['email_inscrit_1'] = _T('association:erreur_email_inscrit_doublon');
            } else {
                $query_auteur_email = sql_fetsel('*', 'spip_auteurs', "email=" . sql_quote($email_multi_public));
                if (!empty($query_auteur_email['id_auteur'])) {
                    $erreurs['email_inscrit_1'] = _T('association:erreur_email_inscrit_adherent', array(
                        'login' => generer_url_public('login'),
                    ));
                }
            }
        }
    }

    // Déterminer un nombre de participants à utiliser pour les vérifications de quota.
    $nombre_a_verifier = intval($nombre_inscrits);
    if ($nombre_a_verifier <= 0 && !empty($data_form['nombre_participants'])) {
        $nombre_a_verifier = intval($data_form['nombre_participants']);
    }
    if ($mode_multi && $saisie_famille_active == 'oui' && $nb_famille_total > 0) {
        $nombre_a_verifier = $nb_famille_total;
        // Inclure les invités hors famille dans le calcul du quota
        $nb_invite_quota = max(0, intval($post['nb_invite'] ?? $post_normalise['nb_invite'] ?? 0));
        if ($nb_invite_quota > 0) {
            $nombre_a_verifier += $nb_invite_quota;
        }
    }
    if ($mode_multi && $nombre_a_verifier <= 0 && !empty($data_form['array_post_inscrits']) && is_array($data_form['array_post_inscrits'])) {
        $nombre_a_verifier = count($data_form['array_post_inscrits']);
    }
    if ($mode_multi && $nombre_a_verifier <= 0 && isset($post['nb_inscrits'])) {
        $nombre_a_verifier = intval($post['nb_inscrits']);
    }
    // Lors de la validation du recapitulatif CVT, nb_inscrits n'est plus dans
    // la requete courante : il provient des etapes precedentes consolidees dans
    // post_normalise. Utiliser cette source avant de conclure a tort qu'aucun
    // participant n'a ete saisi.
    if ($mode_multi && $nombre_a_verifier <= 0 && isset($post_normalise['nb_inscrits'])) {
        $nombre_a_verifier = intval($post_normalise['nb_inscrits']);
    }
    // En BO multi, l'étape 1 identifie déjà au moins le participant principal
    // via le sélecteur d'adhérent. Pour un événement payant, les catégories ne
    // sont cependant saisies qu'à l'étape suivante : ne pas convertir cette
    // absence temporaire de tarif en inscription à zéro.
    if ($mode_multi
        && strpos($mode, 'public') === false
        && intval($etape_validation) === 1
        && $nombre_a_verifier <= 0
        && intval($data_form['id_auteur'] ?? 0) > 0
    ) {
        $nombre_a_verifier = 1;
    }
    // Certains récapitulatifs CVT republient explicitement nb_inscrits=0 alors
    // que l'identité complète du premier participant, saisie à l'étape
    // précédente, est bien consolidée. Cette identité prouve au minimum une
    // inscription réelle et doit primer sur le compteur caché obsolète.
    if ($mode_multi
        && $nombre_a_verifier <= 0
        && !empty($data_form['premier_inscrit'])
        && trim((string) ($data_form['premier_inscrit']['nom'] ?? '')) !== ''
    ) {
        $nombre_a_verifier = 1;
    }
    if (!$mode_multi && $nombre_a_verifier <= 0 && is_array($categorie_result) && !empty($categorie_result)) {
        $tmp = 0;
        if ($saisie_famille_active == 'oui') {
            foreach ($categorie_result as $v) {
                if (is_array($v)) {
                    $tmp += count($v);
                } elseif (is_scalar($v)) {
                    $tmp += 1;
                }
            }
        } else {
            foreach ($categorie_result as $v) {
                $tmp += intval($v);
            }
        }
        if ($tmp > 0) {
            $nombre_a_verifier = $tmp;
        }
    }

    // Au récapitulatif d'un CVT multi-étapes, la requête courante ne contient
    // volontairement aucune saisie métier : SPIP a déjà rejoué et validé les
    // étapes précédentes avant d'appeler la validation finale. Ne pas confondre
    // cette requête vide avec une inscription à zéro participant. La barrière
    // de traiter() reste l'ultime protection et interdit toute persistance à 0.
    $est_recapitulatif_multi = $mode_multi
        && (intval($etape_validation) === 4 || !empty($post['ie_recapitulatif']));

    // A la derniere etape, Saisies conserve les valeurs precedentes dans
    // l'environnement du formulaire mais ne les republie pas necessairement
    // dans _request(). Le jeton cvtm_prev_post reste alors opaque et signe :
    // l'absence locale de categorie ne signifie pas que le tarif affiche dans
    // le recapitulatif n'a pas ete selectionne et valide a l'etape precedente.
    // Toute categorie explicitement republiee reste controlee ci-dessous et
    // traiter() conserve sa barriere defensive avant toute persistance.
    $recapitulatif_cvt_opaque = $est_recapitulatif_multi
        && !empty($post_normalise['cvtm_prev_post'])
        && !is_array($post_normalise['cvtm_prev_post']);

    $doit_verifier_tarif = $is_payant
        && !($recapitulatif_cvt_opaque && empty($categorie_result))
        && (
            !$mode_multi
            || intval($etape_validation) > 1
            || $est_recapitulatif_multi
            || ($etape_validation === null
                && trim((string) ($data_form['premier_inscrit']['nom'] ?? '')) !== '')
        );
    if ($doit_verifier_tarif) {
        $analyse_tarifs = analyser_selection_tarifs_evenement($id_evenement, $categorie_result);
        if (empty($categorie_result)) {
            $erreurs['categorie'] = _T('association:erreur_categorie_tarif_obligatoire');
        } elseif (empty($analyse_tarifs['valide'])) {
            $erreurs['categorie'] = _T('association:erreur_categorie_tarif_invalide');
        } elseif (intval($analyse_tarifs['nombre_participants']) !== intval($nombre_a_verifier)) {
            $erreurs['categorie'] = _T('association:erreur_paiement_inscription_incoherent');
        } elseif (array_key_exists('montant_total', $post)
            && abs(floatval($post['montant_total']) - floatval($analyse_tarifs['montant_total'])) > 0.0001
        ) {
            $erreurs['categorie'] = _T('association:erreur_paiement_inscription_incoherent');
        } elseif (!empty($post['transaction'])) {
            $erreurs['categorie'] = _T('association:erreur_paiement_inscription_incoherent');
        }
    }

    // Une quantité multi ne doit jamais être ramenée implicitement au nombre
    // d'identités hydratées. En mode libre, toutes les personnes demandées
    // doivent être matérialisées avant le récapitulatif.
    $motifs_nb_inscrits = array();
    $quantite_demandee = max(0, intval($data_form['nombre_participants_demandes'] ?? 0));
    $participants_reels = max(0, intval($data_form['nombre_participants_reels'] ?? 0));
    $controle_identites_multi = $est_recapitulatif_multi
        || intval($etape_validation) >= 2
        || ($etape_validation === null && $participants_reels > 0);
    if ($mode_multi && $saisie_famille_active != 'oui') {
        if (empty($affichage_dans_activites['accompagnants']) && $quantite_demandee > 1) {
            $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
            $motifs_nb_inscrits[] = 'AC0';
        } elseif ($controle_identites_multi
            && $quantite_demandee > 0
            && $participants_reels > 0
            && $participants_reels !== $quantite_demandee
        ) {
            $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
            $motifs_nb_inscrits[] = 'ID';
        }
    }

    // Hors récapitulatif, une inscription ne doit jamais être validée sans
    // participant réel. Ne pas laisser la normalisation des quotas transformer
    // silencieusement 0 en 1.
    $aucune_quantite_tarifaire = !empty($affichage_dans_activites['payant'])
        && !empty($affichage_dans_activites['accompagnants'])
        && is_array($categorie_result)
        && count(array_filter($categorie_result, function ($quantite) {
            return intval($quantite) > 0;
        })) === 0;
    if ($nombre_a_verifier <= 0 && !$est_recapitulatif_multi && !$aucune_quantite_tarifaire) {
        $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
        $motifs_nb_inscrits[] = 'ZE';
    }
    $nom_premier_inscrit = trim((string) ($data_form['premier_inscrit']['nom'] ?? ''));
    // En multi, la première étape peut ne contenir que le membre sélectionné
    // et le nombre de participants. Les champs d'identité inscrit_N sont alors
    // générés à l'étape suivante. Ne pas exiger un champ encore absent ;
    // le contrôle redevient obligatoire dès que l'étape d'identité a été
    // soumise, y compris lorsqu'elle provient de cvtm_prev_post.
    $identite_premier_inscrit_a_verifier = !$mode_multi;
    if ($mode_multi) {
        $post_identite = (isset($post_normalise['cvtm_prev_post']) && is_array($post_normalise['cvtm_prev_post']))
            ? array_merge($post_normalise['cvtm_prev_post'], $post_normalise)
            : $post_normalise;
        if ($etape_validation !== null) {
            // Étape 1 : identité pas encore affichée. Étape 4 : SPIP a déjà
            // validé les étapes métier ; traiter() garde la barrière ultime
            // contre toute persistance sans identité.
            $numero_etape = intval($etape_validation);
            $identite_premier_inscrit_a_verifier = ($numero_etape > 1 && $numero_etape < 4);
        } else {
            foreach (array(
                'prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit',
                'prenom_inscrit_1', 'nom_inscrit_1', 'email_inscrit_1',
                'telephone_inscrit_1', 'categorie_inscrit_1'
            ) as $champ_identite) {
                if (trim((string) ($post_identite[$champ_identite] ?? '')) !== '') {
                    $identite_premier_inscrit_a_verifier = true;
                    break;
                }
            }
        }
    }
    if ($identite_premier_inscrit_a_verifier && $nom_premier_inscrit === '') {
        $cle_nom_obligatoire = $mode_multi ? 'nom_inscrit_1' : 'nom_inscrit';
        $erreurs[$cle_nom_obligatoire] = _T('info_obligatoire');
    }

    // Cas simple payant avec accompagnants (hors famille) : nb_inscrits n'est pas saisi,
    // il est dérivé des quantités par tarif. Si aucune quantité > 0 n'est sélectionnée,
    // renvoyer une erreur explicite côté UI.
    $selection_tarif_vide = false;
    if (($identite_premier_inscrit_a_verifier || !$mode_multi)
        && !empty($affichage_dans_activites['payant'])
        && !empty($affichage_dans_activites['accompagnants'])
        && $saisie_famille_active != 'oui'
    ) {
        $total_categories_selectionnees = 0;
        if (is_array($categorie_result)) {
            foreach ($categorie_result as $qte) {
                $total_categories_selectionnees += is_array($qte)
                    ? count(array_filter($qte))
                    : max(0, intval($qte));
            }
        }
        if ($total_categories_selectionnees <= 0) {
            $selection_tarif_vide = true;
            $erreurs['categorie'] = _T('association:erreur_nb_inscrits_categorie');
        }
    }

    // Une sélection tarifaire vide représente réellement zéro participant.
    // Conserver cette information pour ne pas déclencher en plus un faux
    // message de quota en transformant artificiellement 0 en 1.
    $demande_participants_positive = intval($nombre_a_verifier) > 0 && !$selection_tarif_vide;
    if ($aucune_quantite_tarifaire) {
        $demande_participants_positive = false;
    }
    $nombre_a_verifier = max(1, intval($nombre_a_verifier));

    // Normaliser places_limites en entier
    $places_limites = isset($gestions_places['places_limites']) ? intval($gestions_places['places_limites']) : 0;
    $places_limites = ($places_limites <= 0) ? 10000 : $places_limites;

    // En multi famille, rattacher l'erreur directement au champ etape 1.
    $erreur_quota_cle = ($mode_multi && $saisie_famille_active == 'oui') ? 'famille' : 'nb_accompagnants';

    if ($demande_participants_positive && $places_limites < $nombre_a_verifier) {
        $erreurs[$erreur_quota_cle] = _T('association:erreur_nb_accompagnants');
        if ($erreur_quota_cle !== 'famille') {
            $erreurs['nb_accompagnants'] = _T('association:erreur_nb_accompagnants');
        }
    }

    // En formulaire simple (hors multi/famille), exiger la saisie libre des noms
    // des participants dès qu'il y a plus d'un inscrit.
    if (!$mode_multi
        && $saisie_famille_active != 'oui'
        && !empty($affichage_dans_activites['accompagnants'])
        && intval($nombre_a_verifier) > 1
    ) {
        // Règle stricte: le champ s'appelle uniquement `nom_participants`.
        // Pas de fallback legacy ici.
        $nom_participants = trim((string) ($post_normalise['nom_participants'] ?? ($post['nom_participants'] ?? _request('nom_participants') ?? '')));

        spip_log('[IE_VERIFY_NOM_PARTICIPANTS][' . $trace_id . '] ' . json_encode(array(
            'mode' => $mode,
            'nombre_a_verifier' => intval($nombre_a_verifier),
            'post_has_nom_participants' => (is_array($post) && array_key_exists('nom_participants', $post)) ? 'oui' : 'non',
            'post_normalise_has_nom_participants' => (is_array($post_normalise) && array_key_exists('nom_participants', $post_normalise)) ? 'oui' : 'non',
            'nom_participants_longueur' => strlen($nom_participants),
            'nom_participants_vide' => ($nom_participants === '' ? 'oui' : 'non'),
        )), 'association' . _LOG_DEBUG);

        if ($nom_participants === '') {
            $erreurs['nom_participants'] = _T('association:erreur_nom_participants');
        }
    }

    // places disponibles et places en attente (normalisées en entiers)
    $places_disponibles = intval((empty($gestions_places['places_evenement']) || $gestions_places['places_evenement'] == 0) ? 10000 : $gestions_places['places_disponibles']);
    $places_en_attentes_disponible = intval(!isset($gestions_places['places_en_attentes_disponible']) ? 0 : $gestions_places['places_en_attentes_disponible']);

    // Vérification de la capacité globale uniquement lorsque l'événement active
    // explicitement la gestion des places. Sans cette option, gestions_places()
    // peut légitimement renvoyer 0 et ne doit pas transformer un événement
    // illimité en événement complet.
    $capacite_places_active = !empty($affichage_dans_activites['places']);
    $attente_hors_quota_illimitee = !empty($affichage_dans_activites['attentes_illimite']);
    $capacite_places_bloquante = $capacite_places_active && !$attente_hors_quota_illimitee;
    if ($demande_participants_positive && $capacite_places_bloquante) {
        if (empty($id_activite)) {
            if ($places_disponibles < $nombre_a_verifier && $places_en_attentes_disponible < $nombre_a_verifier) {
                $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
                $motifs_nb_inscrits[] = 'PC';
            }
        } else {
            $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite=$id_activite");
            $nombre_inscrits_pre_modif = $query_activite['nombre_inscrits'] ?? 0;
            $statut_pre_modif = $query_activite['statut'] ?? '';
            if ($statut_pre_modif == 'preinscrit' && (($places_disponibles + $nombre_inscrits_pre_modif) < $nombre_a_verifier) && ($places_en_attentes_disponible < $nombre_a_verifier)) {
                $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
                $motifs_nb_inscrits[] = 'PM';
            } elseif ($statut_pre_modif == 'ok' && (($places_disponibles + $nombre_inscrits_pre_modif) < $nombre_a_verifier) && ($places_en_attentes_disponible < $nombre_a_verifier)) {
                $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
                $motifs_nb_inscrits[] = 'PM';
            } elseif ($statut_pre_modif == 'liste_attente' && (($places_en_attentes_disponible + $nombre_inscrits_pre_modif) < $nombre_a_verifier) && $places_disponibles < $nombre_a_verifier) {
                $erreurs['nb_inscrits'] = _T('association:erreur_nb_inscrits');
                $motifs_nb_inscrits[] = 'PM';
            }
        }
    }

    // Anti-spam via wrapper : choisir les bons champs selon le type de formulaire.
    // - simple: prenom_inscrit / nom_inscrit
    // - multi : prenom_inscrit_1 / nom_inscrit_1 (premier participant)
    $prenom_field_spam = $mode_multi ? 'prenom_inscrit_1' : 'prenom_inscrit';
    $nom_field_spam = $mode_multi ? 'nom_inscrit_1' : 'nom_inscrit';
    $id_auteur_spam = intval($data_form['id_auteur'] ?? ($GLOBALS['visiteur_session']['id_auteur'] ?? 0));
    $spam_erreurs = ie_verifier_spam($post, 'email_inscrit', $prenom_field_spam, $nom_field_spam, true, $id_auteur_spam);
    if ($spam_erreurs) {
        set_request('erreur_spam', $spam_erreurs);
        $erreurs['spam'] = _T('association:erreur_message_erreur');
    }

    // email valide
    if (!empty(_request('email_inscrit')) && !email_valide(_request('email_inscrit'))) {
        $erreurs['email_inscrit'] = _T('association:erreur_email_inscrit_invalide');
    }

    // Extension metier: permet d'ajouter/adapter les erreurs de verification.
    $flux_verifier = pipeline('association_inscription_evenement_verifier', array(
        'args' => array(
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'id_activite' => intval($id_activite),
            'trace_id' => $trace_id,
            'post' => $post_normalise,
        ),
        'data' => array(
            'erreurs' => $erreurs,
            'data_form' => $data_form,
            'affichage_dans_activites' => $affichage_dans_activites,
            'saisie_famille_active' => $saisie_famille_active,
            'mode_multi' => $mode_multi,
        ),
    ));
    if (is_array($flux_verifier) && isset($flux_verifier['data']) && is_array($flux_verifier['data']) && isset($flux_verifier['data']['erreurs']) && is_array($flux_verifier['data']['erreurs'])) {
        $erreurs = $flux_verifier['data']['erreurs'];
    }

    spip_log('[IE_VERIFY][' . $trace_id . '] quotas nombre_a_verifier=' . intval($nombre_a_verifier)
        . ' places_limites=' . intval($places_limites)
        . ' places_disponibles=' . intval($places_disponibles)
        . ' places_attente=' . intval($places_en_attentes_disponible)
        . ' capacite_places_active=' . ($capacite_places_active ? 'oui' : 'non')
        . ' attente_hors_quota_illimitee=' . ($attente_hors_quota_illimitee ? 'oui' : 'non')
        . ' erreur_quota_cle=' . $erreur_quota_cle
        . ' erreurs=' . implode(',', array_keys($erreurs)), $log_canal);

    if (empty($erreurs)) {
        // Pas d'erreurs détectées: ne rien renvoyer (CVT attend un tableau vide)
        spip_log('[IE_VERIFY_RETURN_NONE][' . $trace_id . '] aucune_erreur_detectee', $log_canal);
        return array();
    }

    if (!empty($erreurs)) {
        $categorie_resume = '';
        if (is_array($categorie_result)) {
            $categorie_sum = 0;
            foreach ($categorie_result as $val) {
                if (is_array($val)) {
                    $categorie_sum += count($val);
                } else {
                    $categorie_sum += intval($val);
                }
            }
            $categorie_resume = 'array(count=' . count($categorie_result) . ',sum=' . $categorie_sum . ')';
        } else {
            $categorie_resume = gettype($categorie_result) . '(' . strval($categorie_result) . ')';
        }

        $payload_debug = array(
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'id_activite' => intval($id_activite),
            'nombre_a_verifier' => intval($nombre_a_verifier),
            'nombre_inscrits_calcule' => intval($nombre_inscrits),
            'quantite_demandee' => intval($quantite_demandee),
            'participants_reels' => intval($participants_reels),
            'motifs_nb_inscrits' => array_values(array_unique($motifs_nb_inscrits)),
            'categorie' => $categorie_resume,
            'nom_participants_renseigne' => (!empty(trim((string)($post_normalise['nom_participants'] ?? ($post['nom_participants'] ?? '')))) ? 'oui' : 'non'),
            'erreurs' => array_keys($erreurs),
        );

        spip_log('[IE_VERIFY_BLOCK][' . $trace_id . '] ' . json_encode($payload_debug), 'association' . _LOG_DEBUG);
        spip_log('[IE_VERIFY_RETURN][' . $trace_id . '] retour=' . (empty($erreurs) ? 'OK' : 'KO_' . count($erreurs) . '_erreurs'), 'association' . _LOG_DEBUG);
    }

    spip_log('[IE_VERIFY_RETURN_FINAL][' . $trace_id . '] ' . json_encode(array(
        'mode' => $mode,
        'id_evenement' => intval($id_evenement),
        'erreurs_count' => count($erreurs),
        'erreurs_keys' => array_keys($erreurs),
        'will_block_form' => (count($erreurs) > 0 ? 'oui' : 'non'),
    )), 'association' . _LOG_DEBUG);

    spip_log('[IE_VERIFY_ERREURS_ARRAY][' . $trace_id . '] erreurs_complet=' . json_encode($erreurs), 'association' . _LOG_DEBUG);

    // Validation du nombre d'invités hors famille (si feature activée pour cet événement)
    if (!empty($affichage_dans_activites['invites'])) {
        $nb_invite_soumis = max(0, intval($post['nb_invite'] ?? $post_normalise['nb_invite'] ?? 0));
        $limite_invites = ie_limite_invites_effective(
            $gestions_places,
            $affichage_dans_activites,
            $id_activite,
            max(1, $nb_famille_total)
        );
        if ($nb_invite_soumis > $limite_invites) {
            $erreurs['nb_invite'] = _T('association:erreur_nb_invite_depasse', array('max' => $limite_invites));
        } else {
            // Vérifier que prenom et nom sont remplis pour chaque invité déclaré
            for ($j = 1; $j <= $nb_invite_soumis; $j++) {
                $suffix = 'inscrit_' . $j;
                $prenom_val = trim((string) ($post['prenom_' . $suffix] ?? ''));
                $nom_val    = trim((string) ($post['nom_' . $suffix] ?? ''));
                if ($prenom_val === '') {
                    $erreurs['prenom_' . $suffix] = _T('association:erreur_message_erreur');
                }
                if ($nom_val === '') {
                    $erreurs['nom_' . $suffix] = _T('association:erreur_message_erreur');
                }
            }
        }
    }

    // Consolidation des messages d'erreur pour éviter les doublons affichés
    // Objectif : afficher systématiquement un message générique en haut du formulaire
    // et ne reporter les messages spécifiques que s'ils ne sont pas liés à un
    // champ affiché (les saisies/CVT afficheront automatiquement l'erreur par champ).
    $consolidated = array();
    $generic_message = _T('association:erreur_message_erreur');

    // Rassembler les messages qui ne correspondent pas à un champ affiché
    $non_field_messages = array();
    foreach ($erreurs as $k => $v) {
        // ignorer toute clé top-level déjà 'message_erreur' (nous la reconstruirons)
        if ($k === 'message_erreur') {
            continue;
        }

        // si la clé correspond à un champ affiché, la laisser telle quelle
        if (ie_champ_affiche_dans_formulaire($k, $post, $post_normalise, $data_form, $affichage_dans_activites, $saisie_famille_active, $mode_multi)) {
            $consolidated[$k] = ie_normaliser_erreur_affichage($v);
        } else {
            // sinon l'ajouter à la liste des messages non liés à un champ
            // Saisies peut fournir un message deja enveloppe dans un
            // <span role="alert">, parfois encode en entites HTML. Le bandeau
            // CVT attend ici du texte : conserver uniquement son contenu.
            $message_non_champ = ie_message_erreur_texte($v);
            if ($message_non_champ !== '') {
                $non_field_messages[] = $message_non_champ;
            }
        }
    }

    // Le meme texte historique couvre plusieurs refus sans rapport avec le
    // quota. Donner au webmestre le calcul exact, sans exposer les identites.
    if (association_diagnostic_inscription_webmestre() && !empty($motifs_nb_inscrits)) {
        $non_field_messages[] = 'Diagnostic validation — ' . ie_diagnostic_validation_code(array(
            'motifs' => $motifs_nb_inscrits,
            'quantite_demandee' => $quantite_demandee,
            'participants_reels' => $participants_reels,
            'nombre_a_verifier' => $nombre_a_verifier,
            'places_limites' => $places_limites,
            'places_disponibles' => $places_disponibles,
            'places_attente' => $places_en_attentes_disponible,
        ));
    }

    // Construire le message générique et y concaténer les messages non-champs
    $message_top = $generic_message;
    if (!empty($non_field_messages)) {
        // conserver les valeurs uniques pour éviter répétitions
        $message_top .= ' : ' . implode(' / ', array_unique($non_field_messages));
    }
    $consolidated['message_erreur'] = $message_top;

    spip_log('[IE_VERIFY_ERREURS_CONSOLIDE][' . $trace_id . '] retour=' . json_encode($consolidated), 'association' . _LOG_DEBUG);

    return $consolidated;
}

function ie_nombre_inscrits_contexte($data_activite = array()) {
    $nb_inscrits = intval(_request('nb_inscrits'));
    if ($nb_inscrits <= 0) {
        $precedentes = _request('cvtm_prev_post');
        if (!is_array($precedentes) && $precedentes !== null && $precedentes !== '') {
            $post_normalise = ie_convertir_post(array('cvtm_prev_post' => $precedentes));
            $precedentes = $post_normalise['cvtm_prev_post'] ?? array();
        }
        if (is_array($precedentes)) {
            $nb_inscrits = intval($precedentes['nb_inscrits'] ?? 0);
        }
    }
    if ($nb_inscrits <= 0 && is_array($data_activite)) {
        $nb_inscrits = intval($data_activite['nb_inscrits'] ?? 0);
    }
    return max(0, $nb_inscrits);
}

function ie_diagnostic_validation_code($valeurs) {
    $motifs = array_values(array_unique(array_filter((array) ($valeurs['motifs'] ?? array()))));
    return implode('-', array(
        'QV' . (!empty($motifs) ? implode('.', $motifs) : 'X'),
        'ND' . association_diagnostic_inscription_nombre_code($valeurs['quantite_demandee'] ?? 0, 2),
        'NR' . association_diagnostic_inscription_nombre_code($valeurs['participants_reels'] ?? 0, 2),
        'NV' . association_diagnostic_inscription_nombre_code($valeurs['nombre_a_verifier'] ?? 0, 2),
        'PL' . association_diagnostic_inscription_nombre_code($valeurs['places_limites'] ?? 0, 2),
        'PD' . association_diagnostic_inscription_nombre_code($valeurs['places_disponibles'] ?? 0, 3),
        'AE' . association_diagnostic_inscription_nombre_code($valeurs['places_attente'] ?? 0, 3),
    ));
}

/**
 * Normaliser aussi les erreurs rattachees aux champs, affichees directement
 * par les gabarits CVT/Saisies.
 *
 * @param mixed $erreur
 * @return mixed
 */
function ie_normaliser_erreur_affichage($erreur) {
    if (is_array($erreur)) {
        return array_map('ie_normaliser_erreur_affichage', $erreur);
    }

    return is_scalar($erreur) || $erreur === null
        ? ie_message_erreur_texte($erreur)
        : $erreur;
}

/**
 * Traitement commun pour les formulaires d'inscription.
 * Stub : comportement minimal pour permettre l'intégration progressive.
 *
 * @param string $mode
 * @param int $id_evenement
 * @param int|null $id_activite
 * @param array|null $post
 * @return array Résultat (ex: ['redirect'=>..., 'id_activite'=>...])
 */
function ie_traiter_commons($mode, $id_evenement = 0, $id_activite = null, $post = null) {
    list($id_evenement, $id_activite) = ie_resoudre_ids_inscription($id_evenement, $id_activite);
    if ($id_activite) {
        $data_activite = (strpos($mode, 'multi') !== false)
            ? preparer_chargement_modification_inscription_multi($id_activite, strpos($mode, 'public') !== false ? 'public' : 'prive')
            : preparer_chargement_modification_inscription($id_activite);
        ie_rehydrater_requete_modification($data_activite);
    }
    // Ne plus dépendre de $_POST brut: reconstruire un payload attendu via _request
    $post = ie_requete_attendue($mode, $id_evenement, $id_activite, is_array($post) ? $post : array());
    $trace_id = _request('ie_trace_id');
    if (!$trace_id) {
        $trace_id = 'ie_traiter_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
        set_request('ie_trace_id', $trace_id);
    }

    // Formatage des données
    $data_form = ie_format_post($mode, $id_evenement, $post);
    ie_log_formater_multi($trace_id, $mode, $post, $data_form, 'verifier');

    // Extension metier: enrichissement des donnees avant persistance.
    $flux_traiter_pre = pipeline('association_inscription_evenement_traiter', array(
        'args' => array(
            'etape' => 'avant',
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'id_activite' => intval($id_activite),
            'trace_id' => $trace_id,
        ),
        'data' => array(
            'post' => $post,
            'data_form' => $data_form,
        ),
    ));
    if (is_array($flux_traiter_pre) && isset($flux_traiter_pre['data']) && is_array($flux_traiter_pre['data'])) {
        if (isset($flux_traiter_pre['data']['post']) && is_array($flux_traiter_pre['data']['post'])) {
            $post = $flux_traiter_pre['data']['post'];
        }
        if (isset($flux_traiter_pre['data']['data_form']) && is_array($flux_traiter_pre['data']['data_form'])) {
            $data_form = $flux_traiter_pre['data']['data_form'];
        }
    }
    ie_log_formater_multi($trace_id, $mode, $post, $data_form, 'traiter');

    // Barrière défensive : le traitement peut être appelé directement sans que
    // CVT ait exécuté verifier(). Ne créer ni transaction ni activité invalide.
    $affichage_traitement = affichage_dans_activites($id_evenement);
    $is_payant_traitement = !empty($affichage_traitement['payant']);
    $analyse_tarifs = $is_payant_traitement
        ? analyser_selection_tarifs_evenement($id_evenement, $data_form['categorie_result'] ?? array())
        : array('valide' => true, 'nombre_participants' => intval($data_form['nombre_participants'] ?? 0));
    if ($is_payant_traitement
        && !empty($analyse_tarifs['valide'])
        && intval($data_form['nombre_participants'] ?? 0) <= 0
    ) {
        $data_form['nombre_participants'] = intval($analyse_tarifs['nombre_participants']);
    }
    $nombre_participants = intval($data_form['nombre_participants'] ?? 0);
    $nom_premier_inscrit = trim((string) ($data_form['premier_inscrit']['nom'] ?? ''));
    $affichage_traitement = affichage_dans_activites($id_evenement);
    $mode_multi = strpos($mode, 'multi') !== false;
    $mode_famille = !empty($data_form['mode_famille']);
    $quantite_demandee = max(0, intval($data_form['nombre_participants_demandes'] ?? 0));
    $participants_reels = max(0, intval($data_form['nombre_participants_reels'] ?? 0));
    $quantite_incoherente = $mode_multi && !$mode_famille && (
        (empty($affichage_traitement['accompagnants']) && $quantite_demandee > 1)
        || ($quantite_demandee > 0 && $participants_reels !== $quantite_demandee)
    );
    if ($nombre_participants <= 0 || $nom_premier_inscrit === '' || $quantite_incoherente) {
        spip_log('[IE_TRAITER_BLOCK][' . $trace_id . '] ' . json_encode(array(
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'nombre_participants' => $nombre_participants,
            'quantite_demandee' => $quantite_demandee,
            'participants_reels' => $participants_reels,
            'quantite_incoherente' => $quantite_incoherente ? 'oui' : 'non',
            'nom_premier_inscrit_present' => ($nom_premier_inscrit !== '' ? 'oui' : 'non'),
        )), 'association' . _LOG_DEBUG);
        return array(
            'editable' => true,
            'message_erreur' => _T('association:erreur_message_erreur'),
        );
    }

    if ($is_payant_traitement
        && (empty($analyse_tarifs['valide'])
            || intval($analyse_tarifs['nombre_participants']) !== $nombre_participants
            || (array_key_exists('montant_total', $post)
                && abs(floatval($post['montant_total']) - floatval($analyse_tarifs['montant_total'])) > 0.0001)
            || !empty($post['transaction']))
    ) {
        spip_log('[IE_TRAITER_BLOCK][' . $trace_id . '] ' . json_encode(array(
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'raison' => 'tarif_invalide_ou_incoherent',
            'nombre_participants' => $nombre_participants,
            'nombre_tarifs' => intval($analyse_tarifs['nombre_participants'] ?? 0),
        )), 'association' . _LOG_DEBUG);
        return array(
            'editable' => true,
            'message_erreur' => _T('association:erreur_paiement_inscription_incoherent'),
        );
    }
    if (!empty($affichage_traitement['payant']) && !empty($affichage_traitement['accompagnants'])) {
        $total_categories_selectionnees = 0;
        foreach ((array) ($data_form['categorie_result'] ?? array()) as $qte) {
            $total_categories_selectionnees += is_array($qte)
                ? count(array_filter($qte))
                : max(0, intval($qte));
        }
        if ($total_categories_selectionnees <= 0) {
            spip_log('[IE_TRAITER_BLOCK][' . $trace_id . '] ' . json_encode(array(
                'mode' => $mode,
                'id_evenement' => intval($id_evenement),
                'raison' => 'aucune_categorie_selectionnee',
            )), 'association' . _LOG_DEBUG);
            return array(
                'editable' => true,
                'message_erreur' => _T('association:erreur_nb_inscrits_categorie'),
            );
        }
    }

    spip_log('[IE_TRAITER_INPUT][' . $trace_id . '] ' . json_encode(array(
        'mode' => $mode,
        'id_evenement' => intval($id_evenement),
        'id_activite' => intval($id_activite),
        'nombre_participants' => intval($data_form['nombre_participants'] ?? 0),
        'categorie_result' => $data_form['categorie_result'] ?? null,
        'montant_total_data_form' => floatval($data_form['montant_total'] ?? 0),
        'transaction_data_form' => $data_form['transaction'] ?? array(),
    )), 'association' . _LOG_DEBUG);

    // Calcul du statut d'enregistrement
    // Résoudre id_activite depuis toutes les sources disponibles
    if (!$id_activite) {
        $id_activite = intval($data_form['id_activite'] ?? 0)
            ?: intval(_request('id_activite'))
            ?: null;
    }
    $nombre_participants = $data_form['nombre_participants'] ?? 0;
    // appel direct au calculator (suppression des function_exists)
    $cal_result = activite_enregistrement_calculator($id_evenement, $nombre_participants, '', '', $id_activite);

    // Préparer transaction si payant
    $affichage = affichage_dans_activites($id_evenement);
    $is_payant = !empty($affichage['payant']) && $affichage['payant'] == true;

    $nouvelle_transaction = '';
    $id_transaction = 0;
    if ($is_payant) {
        // Une transaction existante ne peut venir que de l'activité modifiée.
        // Refuser toute valeur fournie par le navigateur qui désigne autre chose.
        $id_transaction_soumis = intval($post['id_transaction'] ?? $data_form['transaction_id'] ?? 0);
        if (!empty($id_activite)) {
            $activite_existante = sql_fetsel('id_evenement,id_transaction', 'spip_asso_activites', 'id_activite=' . intval($id_activite));
            if (intval($activite_existante['id_evenement'] ?? 0) !== intval($id_evenement)) {
                return array('editable' => true, 'message_erreur' => _T('association:erreur_paiement_inscription_incoherent'));
            }
            $id_transaction = intval($activite_existante['id_transaction'] ?? 0);
            if ($id_transaction_soumis > 0 && $id_transaction_soumis !== $id_transaction) {
                return array('editable' => true, 'message_erreur' => _T('association:erreur_paiement_inscription_incoherent'));
            }
        } elseif ($id_transaction_soumis > 0) {
            return array('editable' => true, 'message_erreur' => _T('association:erreur_paiement_inscription_incoherent'));
        }
        $calc_tr = $analyse_tarifs;
        $montant_total = $calc_tr['montant_total'] ?? ($data_form['montant_total'] ?? 0);
        // Sérialiser APRÈS calcul pour avoir la transaction complète (formater_post_form_multi
        // ne calcule pas la transaction, on utilise donc celle retournée par ie_calculer_transaction)
        $nouvelle_transaction = serialize($calc_tr['transaction'] ?? ($data_form['transaction'] ?? array()));
        spip_log('[IE_TRAITER_MONTANT][' . $trace_id . '] ' . json_encode(array(
            'is_payant' => 'oui',
            'montant_total_calc_tr' => floatval($calc_tr['montant_total'] ?? 0),
            'montant_total_data_form' => floatval($data_form['montant_total'] ?? 0),
            'montant_total_retenu' => floatval($montant_total),
            'transaction_calc_tr' => $calc_tr['transaction'] ?? array(),
            'transaction_data_form' => $data_form['transaction'] ?? array(),
            'id_transaction_avant' => intval($id_transaction),
        )), 'association' . _LOG_DEBUG);
        $handle = ie_handle_transaction($montant_total, $data_form['id_auteur'] ?? 0, $id_transaction);
        $id_transaction = $handle['id_transaction'];
        $transaction_persistante = $id_transaction > 0
            ? sql_fetsel('id_transaction,montant', 'spip_transactions', 'id_transaction=' . intval($id_transaction))
            : array();
        if (empty($transaction_persistante['id_transaction'])
            || abs(floatval($transaction_persistante['montant'] ?? 0) - floatval($montant_total)) > 0.0001
        ) {
            return array('editable' => true, 'message_erreur' => _T('association:erreur_paiement_inscription_incoherent'));
        }
        spip_log('[IE_TRAITER_TRANSACTION][' . $trace_id . '] ' . json_encode(array(
            'id_transaction_apres' => intval($id_transaction),
            'transaction_created' => !empty($handle['created']) ? 'oui' : 'non',
        )), 'association' . _LOG_DEBUG);
    }

    // Message journal (appel direct)
    $message_journal = preparer_entree_journal($cal_result['statut'] ?? 'ok', (strpos($mode, 'public') !== false ? 'public' : 'prive'));

    // Persister activité
    $ip_client = $_SERVER['REMOTE_ADDR'] ?? '';
    $params = array(
        'id_evenement' => $id_evenement,
        'data_form' => $data_form,
        'cal_result' => $cal_result,
        'id_transaction' => $id_transaction,
        'nouvelle_transaction' => $nouvelle_transaction,
        'message_journal' => $message_journal,
        'id_activite' => $id_activite,
        'ip_client' => $ip_client,
    );

    $id_activite_result = ie_persist_activite($params);
    spip_log('[IE_TRAITER_PERSIST][' . $trace_id . '] ' . json_encode(array(
        'id_activite_result' => intval($id_activite_result),
        'statut_calcule' => $cal_result['statut'] ?? '',
        'id_transaction' => intval($id_transaction),
    )), 'association' . _LOG_DEBUG);

    // Post-processing : notifications, mail subscriber, comptes
    if (!empty($id_activite_result)) {
        // notifications (appel direct)
        // Le calculateur conserve le statut fonctionnel de l'inscription
        // (ok, preinscrit, liste_attente), mais une activite existante doit
        // toujours utiliser la notification dediee a la modification.
        $type = !empty($id_activite) ? 'modification' : ($cal_result['statut'] ?? 'ok');
        $prive_ou_public = (strpos($mode, 'public') !== false) ? 'public' : 'prive';
        $doit_notifier = ($prive_ou_public !== 'prive')
            || !empty($post['notifier_adherent'])
            || !empty($post['notifier']);
        if ($doit_notifier) {
            notifier_inscription_activite($id_activite_result, $id_evenement, $type, $prive_ou_public);
        }

        // inscription mailing list en asynchrone pour fluidifier la validation
        if (!empty($data_form['premier_inscrit']['email'])) {
            $job_id = job_queue_add(
                'inscrire_participant_mailsubscriber',
                'Newsletter - inscription activite - ' . intval($id_activite_result),
                array($data_form),
                'formulaires/inc/inscription_evenement',
                false,
                0,
                0
            );
            if (!$job_id) {
                spip_log('ie_traiter_commons: echec mise en file mailsubscriber id_activite=' . intval($id_activite_result), 'association' . _LOG_ERREUR);
            }
        }

        // comptes (comptabilite)
        if (!empty($GLOBALS['association_metas']['comptes']) && $is_payant) {
            if (empty($id_activite)) {
                inserer_compte_activite($id_activite_result, gestions_places($id_evenement));
            } else {
                modifier_compte_activite($id_activite_result, $id_transaction);
            }
        }
    }

    // Cookie pour public si nécessaire
    if (strpos($mode, 'public') !== false) {
        // Ces helpers ne sont appelés qu'en mode public pour éviter des requêtes inutiles en BO.
        $eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);
        $gestions_places = gestions_places($id_evenement);
        if ((($affichage['type_inscrits_evenement'] ?? '') == 'public' && empty($data_form['id_auteur'])) || ($eligibilite_inscription_evenement['eligibilite_token_inscription'] ?? '') == 'oui') {
            $nom_cookie = "id_evenement_$id_evenement";
            $value_cookie = md5($data_form['premier_inscrit']['email'] ?? '');
            $date_debut_evenement = isset($gestions_places['evenement_date_debut']) ? affdate($gestions_places['evenement_date_debut'], 'Y-m-d') : '';
            $timestamp = $date_debut_evenement ? strtotime($date_debut_evenement) : 0;
            include_spip('inc/cookie');
            spip_setcookie($nom_cookie, $value_cookie, $timestamp);
        }
    }

    // Redirection centralisée
    $resultat_traitement = ie_resoudre_redirection_inscription(
        $mode,
        $id_evenement,
        $id_activite_result,
        $cal_result,
        $affichage,
        $is_payant,
        $id_transaction
    );

    // Extension metier: finaliser/adapter le resultat CVT.
    $flux_traiter_post = pipeline('association_inscription_evenement_traiter', array(
        'args' => array(
            'etape' => 'apres',
            'mode' => $mode,
            'id_evenement' => intval($id_evenement),
            'id_activite' => intval($id_activite_result),
            'trace_id' => $trace_id,
        ),
        'data' => array(
            'resultat' => $resultat_traitement,
            'post' => $post,
            'data_form' => $data_form,
            'cal_result' => $cal_result,
            'id_transaction' => $id_transaction,
        ),
    ));
    if (is_array($flux_traiter_post) && isset($flux_traiter_post['data']) && is_array($flux_traiter_post['data']) && isset($flux_traiter_post['data']['resultat']) && is_array($flux_traiter_post['data']['resultat'])) {
        return $flux_traiter_post['data']['resultat'];
    }

    return $resultat_traitement;
}

/**
 * Résoudre la redirection finale d'une inscription événement.
 *
 * Règles:
 * - BO (modes non _public): toujours voir_activites
 * - FO (_public): paiement si payant + éligible, sinon fiche événement
 */
function ie_resoudre_redirection_inscription($mode, $id_evenement, $id_activite_result, $cal_result, $affichage, $is_payant, $id_transaction) {
    $res = array();
    $statut = $cal_result['statut'] ?? '';
    $est_public = (strpos($mode, 'public') !== false);

    if (!$est_public) {
        $res['redirect'] = generer_url_ecrire('voir_activites', 'id=' . intval($id_evenement));
        $res['id_activite'] = $id_activite_result;
        $res['statut'] = $statut;
        return $res;
    }

    $validation_active = !empty($affichage['validation']);
    $validation_sur_paiement = (($affichage['validation_sur_paiement'] ?? 'non') === 'oui');
    $rediriger_vers_paiement = (
        $is_payant
        && $statut !== 'liste_attente'
        && (!$validation_active || $validation_sur_paiement)
    );

    if ($rediriger_vers_paiement && !empty($id_transaction)) {
        $querie_transaction = sql_fetsel('transaction_hash', 'spip_transactions', 'id_transaction = ' . intval($id_transaction));
        $transaction_hash = $querie_transaction['transaction_hash'] ?? '';
        $args = 'id_transaction=' . intval($id_transaction) . '&transaction_hash=' . $transaction_hash;
        $res['redirect'] = generer_url_public('paiement', $args);
        $res['id_activite'] = $id_activite_result;
        $res['statut'] = $statut;
        return $res;
    }

    $res['redirect'] = generer_url_public('evenement', 'id_evenement=' . intval($id_evenement));
    $res['id_activite'] = $id_activite_result;
    $res['statut'] = $statut;
    return $res;
}

/**
 * Wrapper minimal pour le formatage des données POST.
 * Utilise les fonctions existantes `formater_post_form` et `formater_post_form_multi`.
 */
function ie_format_post($mode, $id_evenement = 0, $post = null) {
    $post = ie_requete_attendue($mode, $id_evenement, intval(_request('id_activite')) ?: null, is_array($post) ? $post : array());

    // Decode/normalize cvtm_prev_post if present (multi-step Saisies payload)
    $post = ie_convertir_post($post);
    if (!empty($post['cvtm_prev_post']) && is_array($post['cvtm_prev_post'])) {
        // merge decoded previous step values before formatting
        $prev = $post['cvtm_prev_post'];
        // do not overwrite current step values
        $post = array_merge($prev, $post);
        // unset helper key
        unset($post['cvtm_prev_post']);
        // merged decoded cvtm_prev_post keys (debug suppressed)
    }

    // Déterminer public/prive selon le mode
    $public_or_prive = (strpos($mode, 'public') !== false) ? 'public' : 'prive';

    // Si mode multi, utiliser le formateur multi (appel direct)
    if (strpos($mode, 'multi') !== false) {
        $res_multi = formater_post_form_multi($post, $public_or_prive);
        // formater_post_form_multi used (debug suppressed)
        return $res_multi;
    }

    // mode simple (non-multi) : on a besoin de l'affichage des activités (appel direct)
    $affichage = affichage_dans_activites(intval($id_evenement));
    $res_simple = formater_post_form($id_evenement, $post, $affichage, $public_or_prive);
    // formater_post_form used (debug suppressed)
    return $res_simple;
}

/**
 * Wrapper pour l'anti-spam centralisé (honeypot, blacklist, nom==prenom)
 * Délègue à `verifier_spam_formulaire_inscription` si disponible.
 *
 * @param array|null $post
 * @param string $email_field
 * @param string $prenom_field
 * @param string $nom_field
 * @param bool $check_identical_names
 * @param int $id_auteur
 * @return array|false
 */
function ie_verifier_spam($post = null, $email_field = 'email_inscrit', $prenom_field = 'prenom_inscrit', $nom_field = 'nom_inscrit', $check_identical_names = true, $id_auteur = 0) {
    if ($post === null) {
        $post = $_POST;
    }

    // Appel direct au vérificateur anti-spam
    return verifier_spam_formulaire_inscription($post, $email_field, $prenom_field, $nom_field, $check_identical_names, $id_auteur);
}

/**
 * Calculer transaction (montant_total et transaction détaillée) en s'appuyant sur les helpers existants.
 *
 * @param int $id_evenement
 * @param array $data_form
 * @return array ['montant_total'=>float,'transaction'=>array]
 */
function ie_calculer_transaction($id_evenement, $data_form) {
    $trace_id = _request('ie_trace_id') ?: ('ie_calc_tr_' . date('YmdHis') . '_' . mt_rand(1000, 9999));
    // Si le formater a déjà calculé montant_total, l'utiliser
    if (!empty($data_form['montant_total']) || isset($data_form['transaction'])) {
        spip_log('[IE_CALC_TR_FASTPATH][' . $trace_id . '] ' . json_encode(array(
            'id_evenement' => intval($id_evenement),
            'montant_total_data_form' => floatval($data_form['montant_total'] ?? 0),
            'transaction_data_form' => $data_form['transaction'] ?? array(),
        )), 'association' . _LOG_DEBUG);
        return array(
            'montant_total' => isset($data_form['montant_total']) ? $data_form['montant_total'] : 0,
            'transaction' => isset($data_form['transaction']) ? $data_form['transaction'] : array()
        );
    }

    $categorie_result = isset($data_form['categorie_result']) ? $data_form['categorie_result'] : (isset($data_form['categorie']) ? $data_form['categorie'] : false);
    $nombre_participants = isset($data_form['nombre_participants']) ? $data_form['nombre_participants'] : 0;
    spip_log('[IE_CALC_TR_FALLBACK][' . $trace_id . '] ' . json_encode(array(
        'id_evenement' => intval($id_evenement),
        'categorie_result' => $categorie_result,
        'nombre_participants' => intval($nombre_participants),
    )), 'association' . _LOG_DEBUG);
    $res = calculer_montant_total($id_evenement, $categorie_result, $nombre_participants);
    return array(
        'montant_total' => isset($res['montant_total']) ? $res['montant_total'] : 0,
        'transaction' => isset($res['transaction']) ? $res['transaction'] : array()
    );
}

/**
 * Créer ou modifier une transaction en DB.
 *
 * @param float $montant_total
 * @param int $id_auteur
 * @param int|null $id_transaction
 * @return array ['id_transaction'=>int,'created'=>bool]
 */
function ie_handle_transaction($montant_total, $id_auteur, $id_transaction = null) {
    $trace_id = _request('ie_trace_id') ?: ('ie_handle_tr_' . date('YmdHis') . '_' . mt_rand(1000, 9999));
    // Si pas de montant, rien à faire
    $id_transaction = intval($id_transaction);
    if ($id_transaction < 1) {
        $id_new = inserer_transaction_activites($montant_total, $id_auteur);
        spip_log('[IE_HANDLE_TR_INSERT][' . $trace_id . '] ' . json_encode(array(
            'montant_total' => floatval($montant_total),
            'id_auteur' => intval($id_auteur),
            'id_transaction' => intval($id_new),
        )), 'association' . _LOG_DEBUG);
        return array('id_transaction' => $id_new, 'created' => true);
    } else {
        // modifier
        modifier_transaction_activites($montant_total, $id_transaction);
        spip_log('[IE_HANDLE_TR_UPDATE][' . $trace_id . '] ' . json_encode(array(
            'montant_total' => floatval($montant_total),
            'id_auteur' => intval($id_auteur),
            'id_transaction' => intval($id_transaction),
        )), 'association' . _LOG_DEBUG);
        return array('id_transaction' => $id_transaction, 'created' => false);
    }
}

/**
 * Persister une activité : insert ou update selon présence de id_activite.
 * Params attendus : id_evenement, data_form, cal_result, id_transaction, nouvelle_transaction, message_journal, id_activite, ip_client
 *
 * @param array $params
 * @return int id_activite
 */
function ie_persist_activite($params) {
    $id_evenement = $params['id_evenement'] ?? 0;
    $data_form = $params['data_form'] ?? array();
    $cal_result = $params['cal_result'] ?? array();
    $id_transaction = $params['id_transaction'] ?? 0;
    $nouvelle_transaction = $params['nouvelle_transaction'] ?? '';
    $message_journal = $params['message_journal'] ?? '';
    $ip_client = $params['ip_client'] ?? '';
    $id_activite = $params['id_activite'] ?? null;

    if (empty($id_activite)) {
        // insérer
        return inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal, $ip_client);
    } else {
        // modifier
        modifier_asso_activites($id_activite, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal, $ip_client);
        return $id_activite;
    }
}

/**
 * Post-processing minimal (notifications, mail subscribe, comptes).
 * Actuellement no-op.
 */
function ie_postprocess($id_activite, $data_form, $conf = array()) {
    return true;
}

/**
 * Convertir et normaliser les données POST pour les formulaires.
 * Décode uniquement le format attendu de cvtm_prev_post (base64 + serialize).
 *
 * @param array $post
 * @return array
 */
function ie_convertir_post($post) {
    $post = is_array($post) ? $post : array();
    if (empty($post['cvtm_prev_post']) || is_array($post['cvtm_prev_post'])) {
        return $post;
    }

    $decoded = @base64_decode($post['cvtm_prev_post'], true);
    if ($decoded === false || $decoded === '') {
        return $post;
    }

    $parsed = @unserialize($decoded);
    if (is_array($parsed)) {
        $post['cvtm_prev_post'] = $parsed;
    }

    return $post;
}


/**
 * Générer la liste simple des fieldsets à afficher dans la navigation par étapes.
 *
 * @param array $saisies Tableau complet des saisies (incluant les fieldsets).
 * @return array Tableau des étapes (nom + libellé) pour l'affichage CVT.
 */
function ie_generer_saisies_par_etapes($saisies) {
    $etapes = array();
    $position = 0;
    foreach ((array) $saisies as $saisie) {
        if (!isset($saisie['saisie']) || $saisie['saisie'] !== 'fieldset') {
            continue;
        }
        $positions_options = $saisie['options'] ?? array();
        $nom = isset($positions_options['nom']) ? $positions_options['nom'] : 'etape_' . ++$position;
        $label = isset($positions_options['label']) ? $positions_options['label'] : ucfirst(str_replace('_', ' ', $nom));
        $etapes[] = array(
            'nom' => $nom,
            'label' => $label,
        );
    }
    return $etapes;
}


function ie_options_etapes_saisies($activer = true, $texte_submit = null) {
    $options = array(
        'texte_submit' => $texte_submit ?: _T('association:validation_inscription'),
        'etapes_presentation' => 'defaut',
        'etapes_activer' => $activer,
        'etapes_suivant' => 'Suivant',
        'etapes_precedent' => 'Précédent',
        'etapes_navigation' => 'on',
        'etapes_precedent_suivant_titrer' => '',
    );
    return array('options' => $options);
}

/**
 * Assemble the canonical _saisies structure for multi-step CVT forms.
 *
 * @param array $opts Array returned by ie_options_etapes_saisies()
 * @param array $fieldsets Numeric array of fieldset definitions
 * @param array $hidden Numeric array of hidden fields
 * @return array Assembled saisies with 'options' as associative key first
 */
function ie_assembler_saisies_multi($opts, $fieldsets = array(), $hidden = array()) {
    $saisies = array();
    if (isset($opts['options'])) {
        $saisies['options'] = $opts['options'];
    }
    // Add fieldsets as numeric entries
    foreach ((array) $fieldsets as $f) {
        $saisies[] = $f;
    }
    // Add hidden fields after fieldsets
    foreach ((array) $hidden as $h) {
        $saisies[] = $h;
    }
    return $saisies;
}

/**
 * Reconstruit un payload de requete a partir des champs attendus (_request)
 * afin d'eviter de consommer $_POST brut.
 */
function ie_requete_attendue($mode, $id_evenement = 0, $id_activite = null, $seed = array()) {
    $res = is_array($seed) ? $seed : array();

    // Conserver les champs honeypot dynamiques pour ne pas casser l'anti-spam.
    $hour_now = date('YmdH');
    $hour_prev = date('YmdH', time() - 3600);
    $honeypots = array(
        'input_' . md5($hour_now),
        'checkbox_' . md5($hour_now),
        'input_' . md5($hour_prev),
        'checkbox_' . md5($hour_prev),
        'nobot',
        'input_nobot',
        'checkbox_nobot',
    );
    foreach ($honeypots as $hp) {
        $val = _request($hp);
        if ($val !== null) {
            $res[$hp] = $val;
        }
    }

    $champs_communs = array(
        'id_evenement',
        'id_activite',
        'modif',
        'select_type_inscrit',
        'membre',
        'non_membre',
        'famille',
        'prenom_inscrit',
        'nom_inscrit',
        'email_inscrit',
        'tel_inscrit',
        'nb_inscrits',
        'nb_invite',
        'nom_participants',
        'categorie',
        'montant_total',
        'transaction',
        'id_transaction',
        'commentaire',
        'annotation',
        'notifier',
        'notifier_adherent',
        'association',
        'autre_association',
        'conditions_generales',
        'condition_inscription',
        'ie_recapitulatif',
        'cvtm_prev_post',
        'nobot',
    );

    foreach ($champs_communs as $champ) {
        $val = _request($champ);
        if ($val !== null) {
            $res[$champ] = $val;
        }
    }

    // Normaliser le contexte evenement/activite si absent de la requete
    if (empty($res['id_evenement']) && intval($id_evenement) > 0) {
        $res['id_evenement'] = intval($id_evenement);
    }
    if (empty($res['id_activite']) && intval($id_activite) > 0) {
        $res['id_activite'] = intval($id_activite);
    }

    // Champs dynamiques des formulaires multi (inscrit_1..N)
    if (strpos($mode, 'multi') !== false) {
        $nb_inscrits = intval(_request('nb_inscrits'));
        $post_etapes_precedentes = $res['cvtm_prev_post'] ?? array();
        if (!is_array($post_etapes_precedentes) && $post_etapes_precedentes !== '') {
            $post_cvt_normalise = ie_convertir_post(array('cvtm_prev_post' => $post_etapes_precedentes));
            $post_etapes_precedentes = $post_cvt_normalise['cvtm_prev_post'] ?? array();
        }
        if ($nb_inscrits <= 0 && is_array($post_etapes_precedentes)) {
            $nb_inscrits = intval($post_etapes_precedentes['nb_inscrits'] ?? 0);
        }
        // La première étape CVT peut poster directement inscrit_1 sans champ
        // nb_inscrits. Collecter alors cette identité sans inventer une valeur
        // de nb_inscrits : le formateur et le vérificateur restent responsables
        // du comptage et du contrôle des catégories.
        $nb_inscrits_a_collecter = $nb_inscrits;
        if ($nb_inscrits_a_collecter <= 0) {
            foreach (array('prenom_inscrit_1', 'nom_inscrit_1', 'email_inscrit_1', 'telephone_inscrit_1', 'categorie_inscrit_1') as $champ_premier_inscrit) {
                if (_request($champ_premier_inscrit) !== null) {
                    $nb_inscrits_a_collecter = 1;
                    break;
                }
            }
        }
        if ($nb_inscrits_a_collecter > 0) {
            for ($i = 1; $i <= $nb_inscrits_a_collecter; $i++) {
                $suffix = 'inscrit_' . $i;
                $dyn = array(
                    'prenom_' . $suffix,
                    'nom_' . $suffix,
                    'email_' . $suffix,
                    'telephone_' . $suffix,
                    'date_naissance_' . $suffix,
                    'nationalite_' . $suffix,
                    'fonction_' . $suffix,
                    'entreprise_' . $suffix,
                    'type_document_identite_' . $suffix,
                    'numero_document_identite_' . $suffix,
                    'date_expiration_document_identite_' . $suffix,
                    'lieu_naissance_' . $suffix,
                    'association_' . $suffix,
                    'autre_association_' . $suffix,
                    'categorie_' . $suffix,
                );
                foreach ($dyn as $champ) {
                    $val = _request($champ);
                    if ($val !== null) {
                        $res[$champ] = $val;
                    }
                }
            }
        }

        // Champs dynamiques du mode famille
        $membres_famille = array('adherent', 'conjoint', 'enfant_1', 'enfant_2', 'enfant_3', 'enfant_4', 'enfant_5', 'invite_1', 'invite_2');
        foreach ($membres_famille as $membre) {
            $dyn = array(
                'prenom_' . $membre,
                'nom_' . $membre,
                'email_' . $membre,
                'telephone_' . $membre,
                'date_naissance_' . $membre,
                'association_' . $membre,
                'autre_association_' . $membre,
                'categorie_' . $membre,
            );
            foreach ($dyn as $champ) {
                $val = _request($champ);
                if ($val !== null) {
                    $res[$champ] = $val;
                }
            }
        }

        // Champs dynamiques des invités externes (inscrit_N en mode famille).
        // En mode public, ils sont couverts par la boucle nb_inscrits ci-dessus.
        // En mode famille, nb_inscrits = 0 → boucle séparée sur nb_invite.
        $nb_invite = max(0, intval(_request('nb_invite') ?? 0));
        for ($j = 1; $j <= $nb_invite; $j++) {
            $suffix = 'inscrit_' . $j;
            $dyn_invite = array(
                'prenom_' . $suffix,
                'nom_' . $suffix,
                'email_' . $suffix,
                'telephone_' . $suffix,
                'date_naissance_' . $suffix,
                'nationalite_' . $suffix,
                'fonction_' . $suffix,
                'entreprise_' . $suffix,
                'type_document_identite_' . $suffix,
                'numero_document_identite_' . $suffix,
                'date_expiration_document_identite_' . $suffix,
                'lieu_naissance_' . $suffix,
                'categorie_' . $suffix,
            );
            foreach ($dyn_invite as $champ) {
                $val = _request($champ);
                if ($val !== null) {
                    $res[$champ] = $val;
                }
            }
        }
    }

    return $res;
}

/**
 * Détermine si une clé d'erreur correspond à un champ affiché dans le formulaire.
 *
 * Règle pragmatique : on considère qu'un champ est affiché si :
 * - la clé existe dans le payload soumis ($post) ou dans la version normalisée ($post_normalise)
 * - ou si la clé existe dans les valeurs formatées ($data_form)
 * - ou si la clé fait partie d'une liste de champs connus affichés par ce formulaire
 *
 * Ceci permet d'éviter d'ajouter un message d'erreur spécifique lorsque SPIP/Saisies
 * affiche déjà l'erreur sur le champ correspondant (doublon visuel).
 *
 * @param string $cle
 * @param array $post
 * @param array $post_normalise
 * @param array $data_form
 * @param array $affichage_dans_activites
 * @param string $saisie_famille_active
 * @param bool $mode_multi
 * @return bool
 */
function ie_champ_affiche_dans_formulaire($cle, $post, $post_normalise, $data_form, $affichage_dans_activites, $saisie_famille_active, $mode_multi) {
    // Protection
    if (!is_string($cle) || $cle === '') {
        return false;
    }

    // Vérifier présence explicite dans les payloads
    if (is_array($post) && array_key_exists($cle, $post)) {
        return true;
    }
    if (is_array($post_normalise) && array_key_exists($cle, $post_normalise)) {
        return true;
    }
    if (is_array($data_form) && array_key_exists($cle, $data_form)) {
        return true;
    }

    // Liste pragmatique de champs connus pour ce formulaire
    $champs_connus = array(
        'prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit',
        'nb_inscrits', 'nom_participants', 'categorie', 'famille',
        'membre', 'non_membre', 'association', 'autre_association',
        'id_transaction', 'id_activite'
    );

    if (in_array($cle, $champs_connus, true)) {
        return true;
    }

    // Gestion des champs multi (prenom_inscrit_1, nom_inscrit_2, ...)
    if ($mode_multi && (strpos($cle, 'prenom_inscrit_') === 0 || strpos($cle, 'nom_inscrit_') === 0 || preg_match('/^prenom_inscrit_\d+$/', $cle) || preg_match('/^nom_inscrit_\d+$/', $cle))) {
        return true;
    }

    // Par défaut: non affiché
    return false;
}
