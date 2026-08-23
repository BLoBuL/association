<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

if (!function_exists('agenda_jourdecal')) {
    function agenda_jourdecal($date, $decalage, $format) {
        return date($format, strtotime($date . ' ' . intval($decalage) . ' days'));
    }
}

require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/association_fonctions.php';
require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-communication/association_communication_fonctions.php';
require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/inc/evenement_fermeture.php';

function association_test_run_listes_fermeture_suite() {
    return association_test_run_cases('listes_fermeture_evenements', array(
        'webmaster_adherent_conserve_dans_liste_automatique' => function () {
            $condition = association_condition_auteurs_newsletter('ok');
            association_test_assert_true(
                strpos($condition, 'webmestre') === false,
                'Un droit webmaster ne doit pas exclure un adherent a jour'
            );
            association_test_assert_contains("statut_interne='ok'", $condition, 'Le statut metier doit piloter la liste');
            association_test_assert_contains("statut IN ('6forum','1comite','0minirezo')", $condition, 'Seuls les comptes SPIP actifs sont retenus');
        },
        'fermeture_relative_24h_inchangee' => function () {
            $date = association_evenement_calculer_date_fermeture(array(
                'date_debut' => '2026-09-10 18:30:00',
                'fermeture_inscription' => '24h',
            ));
            association_test_assert_same('2026-09-09 18:30:00', $date, 'La fermeture relative historique doit rester stable');
        },
        'fermeture_precise_commune_aux_canaux' => function () {
            $evenement = array(
                'date_debut' => '2026-09-10 18:30:00',
                'fermeture_inscription' => 'dt',
                'fermeture_inscription_date' => '2026-09-08 14:45:00',
            );
            foreach (array('FO connecte', 'FO non connecte', 'BO') as $canal) {
                association_test_assert_same(
                    '2026-09-08 14:45:00',
                    association_evenement_calculer_date_fermeture($evenement),
                    "Le canal $canal doit utiliser la meme fermeture exacte"
                );
            }
        },
        'fermeture_precise_absente_ne_ferme_pas_arbitrairement' => function () {
            $date = association_evenement_calculer_date_fermeture(array(
                'date_debut' => '2026-09-10 18:30:00',
                'fermeture_inscription' => 'dt',
                'fermeture_inscription_date' => '',
            ));
            association_test_assert_same(null, $date, 'Une date exacte absente ne doit pas produire une fermeture inventee');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_listes_fermeture_suite());
}
