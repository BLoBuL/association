<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

function association_test_vt_run_verifier_scenarios() {
    association_test_charger_bootstrap();

    $tarifs = array(
        array('id_categorie' => 11, 'titre' => 'Adulte', 'montant_symbole' => '12 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
        array('id_categorie' => 12, 'titre' => 'Enfant', 'montant_symbole' => '8 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
    );

    $scenarios = array(
        array(
            'code' => 'verifier_fo_simple_gratuit_ok',
            'callable' => 'formulaires_inscription_evenement_public_verifier_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => false)),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'nobot' => '',
            ),
            'expect_errors' => array(),
            'deny_errors' => array('message_erreur', 'famille', 'categorie'),
        ),
        array(
            'code' => 'verifier_fo_simple_payant_ok',
            'callable' => 'formulaires_inscription_evenement_public_verifier_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs)),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'categorie' => 11,
                'nobot' => '',
            ),
            'expect_errors' => array(),
            'deny_errors' => array('message_erreur', 'famille', 'categorie', 'email_inscrit'),
        ),
        array(
            'code' => 'verifier_fo_simple_famille_vide',
            'callable' => 'formulaires_inscription_evenement_public_verifier_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true)),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok', 'validite' => '2027-12-31 23:59:59'),
            'request' => array('select_type_inscrit' => 'membre', 'nobot' => ''),
            'expect_errors' => array(),
        ),
        array(
            'code' => 'verifier_bo_simple_doublon_auteur',
            'callable' => 'formulaires_inscription_evenement_verifier_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs)),
            'request' => array('select_type_inscrit' => 'membre', 'membre' => 1, 'categorie' => 11, 'nobot' => ''),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo'),
            'expect_errors' => array(),
        ),
        array(
            'code' => 'verifier_fo_multi_public_famille_ok',
            'callable' => 'formulaires_inscription_evenement_multi_public_verifier_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs)),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok', 'validite' => '2027-12-31 23:59:59'),
            'request' => array(
                'select_type_inscrit' => 'membre',
                'famille' => array('adherent'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'categorie' => array(11 => array('adherent')),
                'nobot' => '',
            ),
            'expect_errors' => array('message_erreur'),
        ),
        array(
            'code' => 'verifier_fo_multi_quota_global_depasse',
            'callable' => 'formulaires_inscription_evenement_multi_public_verifier_dist',
            'fixture' => array(
                'affichage' => array('payant' => false, 'accompagnants' => true),
                'gestions_places' => array(
                    'places_limites' => 4,
                    'places_disponibles' => 1,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 1,
                ),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'nb_inscrits' => 2,
                'prenom_inscrit_1' => 'Paul',
                'nom_inscrit_1' => 'Test',
                'email_inscrit_1' => 'paul@example.test',
                'prenom_inscrit_2' => 'Lea',
                'nom_inscrit_2' => 'Test',
                'email_inscrit_2' => 'lea@example.test',
                'nobot' => '',
            ),
            'expect_errors' => array('message_erreur'),
        ),
        array(
            'code' => 'verifier_fo_modification_reintegre_places_existantes',
            'callable' => 'formulaires_inscription_evenement_public_verifier_dist',
            'fixture' => array(
                'affichage' => array('payant' => false, 'accompagnants' => true),
                'gestions_places' => array(
                    'places_limites' => 4,
                    'places_disponibles' => 1,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 0,
                ),
            ),
            'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok', 'validite' => '2027-12-31 23:59:59'),
            'request' => array(
                'modif' => 'oui',
                'id_activite' => 501,
                'select_type_inscrit' => 'membre',
                'nb_inscrits' => 3,
                'nom_participants' => 'Jean, Marie, Alice',
                'nobot' => '',
            ),
            'args' => array(1, 501),
            'deny_errors' => array('message_erreur', 'nb_accompagnants'),
        ),
    );

    $resultats = array();

    foreach ($scenarios as $scenario) {
        association_test_charger_appliquer_fixture($scenario);
        $res = call_user_func_array($scenario['callable'], $scenario['args'] ?? array(1, null));
        if (!is_array($res)) {
            $res = array('message_erreur' => 'retour_non_tableau');
        }

        $erreurs = array();
        foreach (($scenario['expect_errors'] ?? array()) as $cle) {
            if (!array_key_exists($cle, $res)) {
                $erreurs[] = 'Cle attendue absente: ' . $cle;
            }
        }
        foreach (($scenario['deny_errors'] ?? array()) as $cle) {
            if (array_key_exists($cle, $res)) {
                $erreurs[] = 'Cle inattendue presente: ' . $cle;
            }
        }

        $resultats[] = array(
            'code' => $scenario['code'],
            'ok' => empty($erreurs),
            'erreurs' => $erreurs,
            'retour' => $res,
        );
    }

    return $resultats;
}

function association_test_vt_run_traiter_scenarios() {
    association_test_charger_bootstrap();

    $tarifs = array(
        array('id_categorie' => 11, 'titre' => 'Adulte', 'montant_symbole' => '12 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
        array('id_categorie' => 12, 'titre' => 'Enfant', 'montant_symbole' => '8 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
    );

    $scenarios = array(
        array(
            'code' => 'traiter_fo_simple_gratuit_evenement',
            'callable' => 'formulaires_inscription_evenement_public_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => false, 'accompagnants' => false),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'nobot' => '',
            ),
            'expect_redirect_contains' => 'public:evenement?id_evenement=1',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => false,
        ),
        array(
            'code' => 'traiter_fo_simple_payant_paiement',
            'callable' => 'formulaires_inscription_evenement_public_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs, 'validation' => '', 'validation_sur_paiement' => 'non'),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'categorie' => 11,
                'nobot' => '',
            ),
            'expect_redirect_contains' => 'public:paiement?',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => true,
        ),
        array(
            'code' => 'traiter_bo_simple_creation',
            'callable' => 'formulaires_inscription_evenement_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => 11,
                'nobot' => '',
            ),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo'),
            'expect_redirect_contains' => 'ecrire:voir_activites?id=1',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => true,
        ),
        array(
            'code' => 'traiter_bo_simple_modification',
            'callable' => 'formulaires_inscription_evenement_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'modif' => 'oui',
                'id_activite' => 501,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => 11,
                'nobot' => '',
            ),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo'),
            'args' => array(1, 501),
            'expect_redirect_contains' => 'ecrire:voir_activites?id=1',
            'expect_update_activity' => true,
        ),
        array(
            'code' => 'traiter_fo_multi_public_payant',
            'callable' => 'formulaires_inscription_evenement_multi_public_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs, 'validation' => '', 'validation_sur_paiement' => 'non'),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'nb_inscrits' => 2,
                'prenom_inscrit_1' => 'Paul',
                'nom_inscrit_1' => 'Test',
                'email_inscrit_1' => 'paul@example.test',
                'prenom_inscrit_2' => 'Lea',
                'nom_inscrit_2' => 'Test',
                'email_inscrit_2' => 'lea@example.test',
                'categorie' => array(11 => 1, 12 => 1),
                'nobot' => '',
            ),
            'expect_redirect_contains' => 'public:paiement?',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => true,
        ),
        array(
            'code' => 'traiter_fo_payant_validation_manuelle_sans_redirection_paiement',
            'callable' => 'formulaires_inscription_evenement_public_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs, 'validation' => true, 'validation_sur_paiement' => 'non'),
                'calculator' => array('statut' => 'preinscrit'),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'categorie' => 11,
                'nobot' => '',
            ),
            'expect_redirect_contains' => 'public:evenement?id_evenement=1',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => true,
        ),
        array(
            'code' => 'traiter_fo_payant_liste_attente_sans_redirection_paiement',
            'callable' => 'formulaires_inscription_evenement_public_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs, 'places' => true, 'validation' => false, 'validation_sur_paiement' => 'non'),
                'gestions_places' => array(
                    'places_limites' => 4,
                    'places_disponibles' => 0,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 4,
                ),
            ),
            'request' => array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600001000',
                'categorie' => 11,
                'nobot' => '',
            ),
            'expect_redirect_contains' => 'public:evenement?id_evenement=1',
            'expect_insert_activity' => true,
            'expect_insert_transaction' => true,
        ),
        array(
            'code' => 'traiter_bo_modification_met_a_jour_transaction',
            'callable' => 'formulaires_inscription_evenement_traiter_dist',
            'fixture' => array(
                'affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs),
                'calculator' => array('statut' => 'ok'),
            ),
            'request' => array(
                'modif' => 'oui',
                'id_activite' => 501,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => 12,
                'nobot' => '',
            ),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo'),
            'args' => array(1, 501),
            'expect_redirect_contains' => 'ecrire:voir_activites?id=1',
            'expect_update_activity' => true,
            'expect_update_transaction' => true,
        ),
    );

    $resultats = array();

    foreach ($scenarios as $scenario) {
        association_test_charger_appliquer_fixture($scenario);

        $before = array(
            'insert_activites' => count($GLOBALS['association_test_traiter_traces']['insert_activites']),
            'update_activites' => count($GLOBALS['association_test_traiter_traces']['update_activites']),
            'insert_transactions' => count($GLOBALS['association_test_traiter_traces']['insert_transactions']),
            'update_transactions' => count($GLOBALS['association_test_traiter_traces']['update_transactions']),
        );

        $args = $scenario['args'] ?? array(1, null);
        $res = call_user_func_array($scenario['callable'], $args);

        $after = array(
            'insert_activites' => count($GLOBALS['association_test_traiter_traces']['insert_activites']),
            'update_activites' => count($GLOBALS['association_test_traiter_traces']['update_activites']),
            'insert_transactions' => count($GLOBALS['association_test_traiter_traces']['insert_transactions']),
            'update_transactions' => count($GLOBALS['association_test_traiter_traces']['update_transactions']),
        );

        $erreurs = array();
        if (!is_array($res) || empty($res['redirect'])) {
            $erreurs[] = 'Retour traiter invalide (redirect absent).';
        } else {
            if (strpos($res['redirect'], $scenario['expect_redirect_contains']) === false) {
                $erreurs[] = 'Redirect inattendue: ' . $res['redirect'];
            }
        }

        if (!empty($scenario['expect_insert_activity']) && !($after['insert_activites'] > $before['insert_activites'])) {
            $erreurs[] = 'Aucune insertion activite detectee.';
        }

        if (!empty($scenario['expect_update_activity']) && !($after['update_activites'] > $before['update_activites'])) {
            $erreurs[] = 'Aucune mise a jour activite detectee.';
        }

        if (!empty($scenario['expect_insert_transaction']) && !($after['insert_transactions'] > $before['insert_transactions'])) {
            $erreurs[] = 'Aucune insertion transaction detectee.';
        }

        if (!empty($scenario['expect_update_transaction']) && !($after['update_transactions'] > $before['update_transactions'])) {
            $erreurs[] = 'Aucune mise a jour transaction detectee.';
        }

        if (array_key_exists('expect_insert_transaction', $scenario)
            && $scenario['expect_insert_transaction'] === false
            && ($after['insert_transactions'] > $before['insert_transactions'])) {
            $erreurs[] = 'Insertion transaction inattendue.';
        }

        $resultats[] = array(
            'code' => $scenario['code'],
            'ok' => empty($erreurs),
            'erreurs' => $erreurs,
            'retour' => $res,
        );
    }

    return $resultats;
}

function association_test_vt_afficher_rapport($titre, array $resultats) {
    echo "\n" . $titre . "\n";
    echo str_repeat('=', strlen($titre)) . "\n";

    foreach ($resultats as $resultat) {
        echo ($resultat['ok'] ? '[OK] ' : '[KO] ') . $resultat['code'] . "\n";
        if (!$resultat['ok']) {
            foreach ($resultat['erreurs'] as $erreur) {
                echo '  - ' . $erreur . "\n";
            }
        }
    }
}

function association_test_vt_count_failures(array $resultats) {
    $ko = 0;
    foreach ($resultats as $resultat) {
        if (empty($resultat['ok'])) {
            $ko++;
        }
    }
    return $ko;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $verifier = association_test_vt_run_verifier_scenarios();
    $traiter = association_test_vt_run_traiter_scenarios();

    association_test_vt_afficher_rapport('Matrice verifier', $verifier);
    association_test_vt_afficher_rapport('Matrice traiter', $traiter);

    $ko = association_test_vt_count_failures($verifier) + association_test_vt_count_failures($traiter);
    echo "\nTotal echecs: " . $ko . "\n";

    exit($ko > 0 ? 1 : 0);
}

