<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

if (!defined('_SPIP_VERSION_ID')) {
    define('_SPIP_VERSION_ID', 40400);
}

if (!function_exists('formulaires_editer_objet_charger')) {
    function formulaires_editer_objet_charger($objet, $id_objet, $id_parent, $lier_trad, $retour, $config_fonc, $row, $hidden) {
        return array(
            'id_evenement' => $id_objet,
            'id_article' => $id_parent,
            'titre' => 'Evenement de test',
            'date_debut' => '2026-09-10 10:00:00',
            'date_fin' => '2026-09-10 11:00:00',
            'horaire' => 'oui',
            'timezone_affiche' => 'Europe/Paris',
            'places' => 20,
        );
    }
}

if (!function_exists('formulaires_editer_objet_traiter')) {
    function formulaires_editer_objet_traiter($objet, $id_objet, $id_parent, $lier_trad, $retour, $config_fonc, $row, $hidden) {
        $id_evenement = 99;
        $GLOBALS['association_test_scenario']['db']['spip_evenements'][$id_evenement] = $GLOBALS['_TEST_REQUEST'];
        $GLOBALS['association_test_scenario']['db']['spip_evenements'][$id_evenement]['id_evenement'] = $id_evenement;
        $GLOBALS['association_test_scenario']['db']['spip_evenements'][$id_evenement]['id_article'] = $id_parent;
        return array('id_evenement' => $id_evenement, 'redirect' => '');
    }
}

if (!function_exists('verifier_corriger_date_saisie')) {
    function verifier_corriger_date_saisie($suffixe, $horaire, &$erreurs) {
        return strtotime($GLOBALS['_TEST_REQUEST']['date_' . $suffixe]);
    }
}

if (!function_exists('objet_test_si_publie')) {
    function objet_test_si_publie($objet, $id_objet) {
        return false;
    }
}

if (!function_exists('evenement_modifier')) {
    function evenement_modifier($id_evenement, $set) {
        foreach ($set as $cle => $valeur) {
            $GLOBALS['association_test_scenario']['db']['spip_evenements'][$id_evenement][$cle] = $valeur;
        }
        return true;
    }
}

if (!function_exists('parametre_url')) {
    function parametre_url($url, $cle, $valeur) {
        return $url;
    }
}

require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/editer_evenement.php';

function association_test_evenement_defauts_scenario() {
    return array(
        'association_metas' => array(
            'meta_cfg_event_inscription' => 'oui',
            'meta_cfg_event_type_inscrits_evenement' => 'only_strict',
            'meta_cfg_event_afficher_liste_inscrits' => 'jamais',
            'meta_cfg_event_ouverture_differe' => '14',
            'meta_cfg_event_inscription_deadline' => '48h',
            'meta_cfg_event_validation' => 'oui',
            'meta_cfg_event_accompagnants' => 'non',
            'meta_cfg_event_limite_nb_accompagnants' => '7',
            'meta_cfg_event_invites' => 'non',
            'meta_cfg_event_file_attente' => 'non',
            'meta_cfg_event_validation_auto' => 'non',
            'meta_cfg_event_limite_places_file_attente' => '9',
            'meta_cfg_event_condition_inscription' => 'toujours',
            'message_condition_inscription_defaut' => 'Conditions de test',
        ),
        'db' => array(
            'spip_evenements' => array(),
            'spip_articles' => array(),
        ),
        'plugins' => array(),
    );
}

function association_test_run_evenement_defauts_suite() {
    return association_test_run_cases('evenement_defauts', array(
        'configuration_globale_chargee_dans_un_evenement_neuf' => function () {
            association_test_reset_env(association_test_evenement_defauts_scenario());
            $_SERVER['REQUEST_TIME'] = strtotime('2026-09-01 10:00:00');

            $valeurs = formulaires_editer_evenement_charger_dist('new', 10);

            $attendus = array(
                'inscription' => 1,
                'type_inscrits_evenement' => 'strict',
                'afficher_liste_inscrits' => '0',
                'ouverture_differe' => '14',
                'fermeture_inscription' => '48h',
                'validation' => 'oui',
                'accompagnants' => 'non',
                'limite_places' => '7',
                'invites' => 'non',
                'file_attentes' => 'non',
                'validation_attente_automatique' => 'non',
                'attentes' => '9',
                'condition_inscription' => 'oui',
                'message_condition_inscription' => 'Conditions de test',
            );
            foreach ($attendus as $champ => $attendu) {
                association_test_assert_same($attendu, $valeurs[$champ] ?? null, "Le defaut $champ doit venir de la configuration globale");
            }
        },
        'accompagnants_non_persiste_sans_intervention' => function () {
            association_test_reset_env(association_test_evenement_defauts_scenario());
            $_SERVER['REQUEST_TIME'] = strtotime('2026-09-01 10:00:00');

            $valeurs = formulaires_editer_evenement_charger_dist('new', 10);
            association_test_set_request($valeurs);
            formulaires_editer_evenement_traiter_dist('new', 10);

            $evenement = $GLOBALS['association_test_scenario']['db']['spip_evenements'][99];
            association_test_assert_same('non', $evenement['accompagnants'] ?? null, 'Le nouvel evenement doit conserver accompagnants=non');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_evenement_defauts_suite());
}
