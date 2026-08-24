<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/comptes');
include_spip('inc/destinations');
include_spip('inc/config');

// Déclaration des saisies
function formulaires_migrer_asso_comptabilite_saisies_dist(){
    $saisies = array();

    $options_saisies = array(
        'options' => array(
            'texte_submit' => 'Valider',
            'etapes_suivant' => 'Suivant',
            'etapes_precedent' => 'Précédent',
            'etapes_navigation' => 'on',
            'etapes_precedent_suivant_titrer' => '',
        ),
    );

    $liste_compte_imputation = preparer_liste_compte_imputation();
    $saisies = $options_saisies;

    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'form_migrer_asso_comptabilite_fieldset',
            'label' => '<:association_compta:form_migrer_asso_comptabilite_label:>',
            'explication' => '<:association_compta:form_migrer_asso_comptabilite_explication:>',
        ),
        'saisies' => array(
            // Choix du mode de migration
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'type_migration',
                    'label' => '<:association_compta:form_migrer_asso_comptabilite_mode_label:>',
                    'explication' => '<:association_compta:form_migrer_asso_comptabilite_mode_explication:>',
                    'data' => array(
                        'auto' => _T('association_compta:form_migrer_asso_comptabilite_mode_auto'),
                        'manuelle' => _T('association_compta:form_migrer_asso_comptabilite_mode_manuelle'),
                    ),
                    'defaut' => 'auto',
                    'obligatoire' => 'oui',
                ),
            ),
            // Bloc options manuelles (affiché seulement si mode = manuelle)
            array(
                'saisie' => 'checkbox',
                'options' => array(
                    'nom' => 'imputations_existantes',
                    'label' => '<:association_compta:form_migrer_asso_comptabilite_imputation_label:>',
                    'data' => saisies_tableau2chaine($liste_compte_imputation),
                    'obligatoire' => 'oui',
                    'afficher_si' => '@type_migration@ == "manuelle"',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'pc_cotisations_creance',
                    'label' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
                    'explication' => _T('association_config:form_migrer_asso_comptabilite_compte_creance_label'),
                    'data' => preparer_liste_asso_plan_compte('data_saisies','1'),
                    'defaut' => '',
                    'afficher_si' => '@type_migration@ == "manuelle"',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'pc_cotisations_paiement',
                    'label' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
                    'explication' => _T('association_compta:form_migrer_asso_comptabilite_compte_creance_label'),
                    'data' => preparer_liste_asso_plan_compte('data_saisies','7'),
                    'defaut' => '',
                    'afficher_si' => '@type_migration@ == "manuelle"',
                ),
            ),
        ),
    );

    return $saisies;
}

// Chargement
function formulaires_migrer_asso_comptabilite_charger_dist(){
    return array();
}

// Vérification
function formulaires_migrer_asso_comptabilite_verifier_dist(){
    $erreurs = array();

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
function formulaires_migrer_asso_comptabilite_traiter_dist(){
    $retour = array();

    $type_migration = _request('type_migration');

    if ($type_migration === 'manuelle') {
        $imputations_existantes = _request('imputations_existantes');
        $pc_cotisations_creance = _request('pc_cotisations_creance');
        $pc_cotisations_paiement = _request('pc_cotisations_paiement');

        appliquer_migration_manuelle($imputations_existantes, $pc_cotisations_creance, $pc_cotisations_paiement);
    } else {
        // Nouveau: en mode auto, on nettoie d'abord la BDD puis on applique la migration et on synchronise les événements
        include_spip('genie/association_maintenance_bdd');

        // 1) Nettoyages liés aux cotisations (exécution réelle: dry_run = false)
        $lot_max = 100000;
        // - cotisations orphelines
        asso_supprimer_cotisations_orphelines(false, $lot_max);
        // - cotisations non encaissées âgées (> 6 mois)
        asso_supprimer_cotisations_non_encaissees_anciennes(time(), 6, false, $lot_max);
        // - transactions orphelines
        asso_supprimer_transactions_orphelines(false, $lot_max);

        // 2) Migration automatique des imputations selon la config
        $cfg = get_config_plan_comptable_migration();
        appliquer_migration_auto($cfg);

		// 3) Laisser chaque plugin métier synchroniser ses propres écritures.
		pipeline('association_compta_migration_metiers', array(
			'args' => array('mode' => 'auto'),
			'data' => array(),
		));
    }

    $retour['message_ok'] = _T('association_compta:message_import_reussi');
    $retour['redirect'] = generer_url_ecrire('comptes');

    return $retour;
}

// Migration manuelle: remplace l'imputation selon statut cotisation (ok = paiement, sinon créance)
function appliquer_migration_manuelle($imputations_existantes, $pc_cotisations_creance, $pc_cotisations_paiement) {
    if (!$imputations_existantes || !is_array($imputations_existantes) || !count($imputations_existantes)) {
        return;
    }

    $where = 'id_categorie > 0 AND ' . sql_in('imputation', $imputations_existantes); // bugfix: espace après AND
    $query_comptes = sql_select('*', 'spip_asso_comptes', $where);

    while ($row = sql_fetch($query_comptes)) {
        $id_compte = intval($row['id_compte']);
        $statut_cotisation = isset($row['statut_cotisation']) ? $row['statut_cotisation'] : '';
        $pc_cible = ($statut_cotisation === 'ok') ? $pc_cotisations_paiement : $pc_cotisations_creance;

        if ($pc_cible && $pc_cible !== $row['imputation']) {
            sql_updateq(
                'spip_asso_comptes',
                array('imputation' => $pc_cible),
                'id_compte=' . $id_compte
            );
        }
    }
}

// Migration automatique: utilise la config du site pour cotisations et activités
function appliquer_migration_auto(array $cfg) {
    // 0) Normalisation: une cotisation ne doit jamais avoir de dépense
    sql_updateq(
        'spip_asso_comptes',
        ['depense' => 0],
        "(objet='cotisation' OR id_categorie>0) AND depense>0"
    );

    // 1) Cotisations (objet='cotisation')
    $res_cot = sql_select('id_compte, imputation, statut_cotisation', 'spip_asso_comptes', "objet='cotisation'");
    while ($row = sql_fetch($res_cot)) {
        $id_compte = intval($row['id_compte']);
        $imputation_actuelle = isset($row['imputation']) ? $row['imputation'] : '';
        $is_paye = (isset($row['statut_cotisation']) && $row['statut_cotisation'] === 'ok');
        $pc_cible = $is_paye ? $cfg['pc_cotisations_paiement'] : $cfg['pc_cotisations_creance'];
        if ($pc_cible && $pc_cible !== $imputation_actuelle) {
            sql_updateq('spip_asso_comptes', ['imputation' => $pc_cible], 'id_compte=' . $id_compte);
        }
    }

    // 2) Activités (inscriptions) liées à un évènement (objet='evenement')
    $res_act = sql_select(
        'c.id_compte, c.imputation, c.id_transaction, t.statut AS statut_tx',
        'spip_asso_comptes AS c LEFT JOIN spip_transactions AS t ON t.id_transaction=c.id_transaction',
        "c.objet='evenement' AND c.id_transaction>0"
    );
    while ($row = sql_fetch($res_act)) {
        $id_compte = intval($row['id_compte']);
        $imputation_actuelle = isset($row['imputation']) ? $row['imputation'] : '';
        $is_paye = (isset($row['statut_tx']) && $row['statut_tx'] === 'ok');
        $pc_cible = $is_paye ? $cfg['pc_activites_paiement'] : $cfg['pc_activites_creance'];
        if ($pc_cible && $pc_cible !== $imputation_actuelle) {
            sql_updateq('spip_asso_comptes', ['imputation' => $pc_cible], 'id_compte=' . $id_compte);
        }
    }

    // 3) Justifications des cotisations: appliquer la règle actif/inactif
    // - Actif: « Cotisation de Nom Prenom #ID_AUTEUR »
    // - Inactif: « Cotisation de #ID_AUTEUR »
    $res_just = sql_select('id_compte,id_auteur', 'spip_asso_comptes', "objet='cotisation' OR id_categorie>0");
    while ($row = sql_fetch($res_just)) {
        $id_compte = intval($row['id_compte']);
        $id_auteur = intval($row['id_auteur']);
        if ($id_compte && $id_auteur) {
            $justification = _migration_generer_justification_cotisation($id_auteur);
            sql_updateq('spip_asso_comptes', ['justification' => $justification], 'id_compte=' . $id_compte);
        }
    }
}

// --- Fonctions utilitaires locales (limitées à ce formulaire/migration) ---
/**
 * Retourne true si l'adhérent est actif:
 *  - auteur existe ET statut != 5poubelle ET validite >= (now - 24 mois)
 */
function _migration_est_adherent_actif($id_auteur) {
    $id_auteur = intval($id_auteur);
    if (!$id_auteur) return false;
    $auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . $id_auteur);
    if (!$auteur) return false; // inexistant
    if (isset($auteur['statut']) && $auteur['statut'] === '5poubelle') return false;
    $validite = isset($auteur['validite']) ? $auteur['validite'] : '';
    if (!$validite || $validite === '0000-00-00 00:00:00') return false;
    try {
        $seuil = new DateTime();
        $seuil->modify('-24 months');
        $dv = new DateTime($validite);
        return ($dv >= $seuil);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Génère la justification pour une cotisation selon l'activité de l'adhérent
 */
function _migration_generer_justification_cotisation($id_auteur) {
    $id_auteur = intval($id_auteur);
    $auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . $id_auteur);
    $nom_prenom = '';
    if ($auteur) {
        if (!empty($auteur['nom_famille']) || !empty($auteur['prenom'])) {
            $nom_prenom = trim(($auteur['nom_famille'] ?? '') . ' ' . ($auteur['prenom'] ?? ''));
        } elseif (!empty($auteur['nom'])) {
            $nom_prenom = trim($auteur['nom']);
        }
    }
    $actif = _migration_est_adherent_actif($id_auteur);
    if ($actif && $nom_prenom !== '') {
        return 'Cotisation de ' . $nom_prenom . ' #' . $id_auteur;
    }
    return 'Cotisation de #' . $id_auteur;
}

// Lit la configuration des comptes pour la migration automatique
function get_config_plan_comptable_migration() {
    $cfg = $GLOBALS['association_metas'];
    return array(
        'pc_cotisations_creance'   => !empty($cfg['pc_cotisations_creance'])   ? $cfg['pc_cotisations_creance']   : '101',
        'pc_cotisations_paiement'  => !empty($cfg['pc_cotisations_paiement'])  ? $cfg['pc_cotisations_paiement']  : '102',
        'pc_activites_creance'     => !empty($cfg['pc_activites_creance'])  ? $cfg['pc_activites_creance'] : '103',
        'pc_activites_paiement'    => !empty($cfg['pc_activites_paiement'])  ? $cfg['pc_activites_paiement'] : '104',
    );
}

// Prépare la liste des comptes d'imputation existants
function preparer_liste_compte_imputation(){
    $res = array();

    $query_comptes = sql_select(
        'imputation, COUNT(*) as nb_occurrences',
        'spip_asso_comptes',
        'id_categorie > 0',
        'imputation'
    );
    while ($row = sql_fetch($query_comptes)) {
        $imputation = ($row['imputation']) ? $row['imputation'] : '0';
        $res[$imputation] = _T('association_compta:compte') . ' ' . $imputation . ' (' . $row['nb_occurrences'] . ' ' . _T('association_compta:nb_occurrences'). ')';
    }
    return $res;
}

