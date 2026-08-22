<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

if (!function_exists('association_log')) {
    function association_log($message, $niveau = '') { return true; }
}
if (!function_exists('sql_delete')) {
    function sql_delete($table, $where = '') {
        $GLOBALS['association_test_traiter_traces']['deletes'][] = array('table' => $table, 'where' => $where);
        return true;
    }
}
if (!function_exists('valider_compte_activite')) {
    function valider_compte_activite($id_transaction) {
        $GLOBALS['association_test_traiter_traces']['comptes_valides'][] = intval($id_transaction);
    }
}
if (!function_exists('supprimer_compte_activite')) {
    function supprimer_compte_activite($id_activite) {
        $GLOBALS['association_test_traiter_traces']['comptes_supprimes'][] = intval($id_activite);
    }
}

require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/association_pipelines.php';
include_spip('formulaires/desinscription_evenement_public');
include_spip('inc/fonctions/validation_attente_automatique');

function association_test_apres_assert($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function association_test_apres_jobs($fonction) {
    return array_values(array_filter(
        $GLOBALS['association_test_traiter_traces']['jobs'] ?? array(),
        function ($job) use ($fonction) { return ($job['fonction'] ?? '') === $fonction; }
    ));
}

$cas = array();

$cas['paiement_valide_inscription_et_recu'] = function () {
    association_test_charger_appliquer_fixture(array(
        'fixture' => array(
            'activites' => array(501 => array(
                'id_activite' => 501, 'id_evenement' => 1, 'id_transaction' => 901,
                'id_auteur' => 1, 'email_inscrit' => 'jean@example.test',
                'statut' => 'preinscrit', 'journal' => 'Journal initial', 'nombre_inscrits' => 1,
            )),
            'transactions' => array(901 => array('id_transaction' => 901, 'statut' => 'ok')),
            'evenements' => array(1 => array('id_evenement' => 1, 'validation_sur_paiement' => 'oui')),
            'comptes' => array(1001 => array('id_compte' => 1001, 'id_transaction' => 901)),
        ),
        'metas' => array(
            'comptes' => true,
            'pc_activites_paiement' => '101',
            'meta_cfg_envoi_recu_paiement_participation' => 'oui',
        ),
    ));
    $flux = array('args' => array('succes' => true, 'type' => 'acte', 'id_transaction' => 901));
    association_trig_bank_notifier_reglement($flux);
    $updates = $GLOBALS['association_test_traiter_traces']['update_activites'];
    association_test_apres_assert(($updates[0]['payload']['statut'] ?? '') === 'ok', 'Le paiement doit valider la participation.');
    association_test_apres_assert(count(association_test_apres_jobs('facteur_envoyer_mail_activites')) === 1, 'La notification de validation manque.');
    association_test_apres_assert(count(association_test_apres_jobs('facteur_envoyer_recu_participation')) === 1, 'Le reçu de paiement manque.');
    association_test_apres_assert(count($GLOBALS['association_test_traiter_traces']['update_comptes'] ?? array()) === 1, 'Le compte activité doit être validé.');
};

$cas['paiement_echoue_sans_effet'] = function () {
    association_test_charger_appliquer_fixture(array());
    association_trig_bank_notifier_reglement(array('args' => array('succes' => false, 'type' => 'acte', 'id_transaction' => 901)));
    association_test_apres_assert(empty($GLOBALS['association_test_traiter_traces']['update_activites']), 'Un paiement échoué ne doit pas modifier une activité.');
    association_test_apres_assert(empty($GLOBALS['association_test_traiter_traces']['jobs']), 'Un paiement échoué ne doit pas notifier.');
};

$cas['desinscription_payee_preserve_transaction'] = function () {
    association_test_charger_appliquer_fixture(array(
        'fixture' => array(
            'affichage' => array('payant' => true, 'validation_attente_automatique' => 'oui'),
            'activites' => array(501 => array(
                'id_activite' => 501, 'id_evenement' => 1, 'id_transaction' => 901,
                'id_auteur' => 1, 'email_inscrit' => 'jean@example.test',
                'statut' => 'ok', 'journal' => 'Journal initial', 'nombre_inscrits' => 1,
            )),
            'transactions' => array(901 => array('id_transaction' => 901, 'statut' => 'ok')),
        ),
        'metas' => array('comptes' => true),
        'session' => array('id_auteur' => 1, 'statut' => '1comite'),
        'request' => array('id_activite' => 501),
    ));
    $res = formulaires_desinscription_evenement_public_traiter_dist(1);
    association_test_apres_assert(strpos($res['redirect'] ?? '', 'public:evenement') === 0, 'La redirection événement manque.');
    association_test_apres_assert(($GLOBALS['association_test_traiter_traces']['update_activites'][0]['payload']['statut'] ?? '') === 'desinscrit', 'La participation doit être désinscrite.');
    association_test_apres_assert(empty($GLOBALS['association_test_traiter_traces']['update_transactions']), 'Une transaction payée doit être conservée.');
    association_test_apres_assert(count(association_test_apres_jobs('validation_attente_automatique')) === 1, 'La promotion de la file d’attente doit être planifiée.');
};

$cas['desinscription_refuse_usurpation'] = function () {
    association_test_charger_appliquer_fixture(array(
        'fixture' => array('activites' => array(501 => array(
            'id_activite' => 501, 'id_evenement' => 1, 'id_auteur' => 1,
            'email_inscrit' => 'jean@example.test', 'statut' => 'ok', 'journal' => '',
        ))),
        'session' => array('id_auteur' => 2, 'statut' => '6forum'),
        'request' => array('id_activite' => 501),
    ));
    $res = formulaires_desinscription_evenement_public_traiter_dist(1);
    association_test_apres_assert(!empty($res['message_erreur']), 'Une activité tierce doit être refusée.');
    association_test_apres_assert(empty($GLOBALS['association_test_traiter_traces']['update_activites']), 'Une activité tierce ne doit pas être modifiée.');
};

$cas['promotion_file_attente_respecte_places'] = function () {
    association_test_charger_appliquer_fixture(array(
        'fixture' => array(
            'affichage' => array('validation' => false),
            'gestions_places' => array('places_disponibles' => 2),
            'activites' => array(
                801 => array('id_activite' => 801, 'id_evenement' => 1, 'id_auteur' => 1, 'statut' => 'liste_attente', 'nombre_inscrits' => 1, 'journal' => 'A'),
                802 => array('id_activite' => 802, 'id_evenement' => 1, 'id_auteur' => 2, 'statut' => 'liste_attente', 'nombre_inscrits' => 2, 'journal' => 'B'),
                803 => array('id_activite' => 803, 'id_evenement' => 1, 'id_auteur' => 3, 'statut' => 'liste_attente', 'nombre_inscrits' => 1, 'journal' => 'C'),
            ),
        ),
    ));
    validation_attente_automatique(1, 'test');
    $ids = array_map(function ($update) {
        preg_match('/id_activite=([0-9]+)/', $update['where'], $m);
        return intval($m[1] ?? 0);
    }, $GLOBALS['association_test_traiter_traces']['update_activites']);
    association_test_apres_assert($ids === array(801, 803), 'La promotion doit respecter la capacité disponible.');
    association_test_apres_assert(count(association_test_apres_jobs('facteur_envoyer_mail_activites')) === 2, 'Chaque promotion doit être notifiée.');
};

$echecs = 0;
foreach ($cas as $nom => $test) {
    try {
        $test();
        echo '[OK] ' . $nom . "\n";
    } catch (Throwable $e) {
        $echecs++;
        echo '[KO] ' . $nom . ' - ' . $e->getMessage() . "\n";
    }
}

exit($echecs > 0 ? 1 : 0);
