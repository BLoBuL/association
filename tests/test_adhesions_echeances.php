<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

function include_spip($chemin) { return true; }
function _T($cle, $args = array()) { $GLOBALS['test_traduction_args'] = $args; return $cle . '|' . json_encode($args); }
function bank_devise_defaut() { return array('code' => 'EUR', 'symbole' => '€'); }
function lire_config($cle) { return $cle === 'intl/devise_defaut' ? 'CNY' : null; }
function association_log($canal, $message, $niveau = 'info') { $GLOBALS['test_logs'][] = array($canal, $message, $niveau); }

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations.php';

function test_assert($condition, $message) {
    if (!$condition) { echo "ECHEC: $message\n"; exit(1); }
    echo "OK: $message\n";
}

$GLOBALS['association_metas'] = array(
    'notification_echeance_cotisation' => serialize(array('30', '7', '15', '7', '-1')),
    'notification_echeance_cotisation_entreprise' => '60,30,0',
);

test_assert(
    association_obtenir_delais_echeance('adherent') === array(7, 15, 30),
    'les échéances adhérent sont normalisées, dédupliquées et triées'
);
test_assert(
    association_obtenir_delais_echeance('entreprise') === array(0, 30, 60),
    'les échéances entreprise utilisent leur configuration dédiée'
);

unset($GLOBALS['association_metas']['notification_echeance_cotisation_entreprise']);
test_assert(
    association_obtenir_delais_echeance('entreprise') === array(7, 15, 30),
    'une entreprise reprend les échéances générales en absence de configuration dédiée'
);

$GLOBALS['association_metas']['notification_echeance_cotisation'] = '';
test_assert(association_obtenir_delais_echeance('adherent') === array(), 'une configuration vide ne programme aucune relance');

$sujet = notifications_cotisation_trouver_sujet('activation', array(
    'type_adherent' => 'adherent',
    'reinscription' => 'inscription',
    'nom_adherent' => 'Membre test',
    'montant' => 100,
    'devise' => 'EUR',
));
test_assert(str_starts_with($sujet, 'notifications:activation_cotisation_inscription_sujet|'), 'l’activation initiale utilise le sujet adhérent dédié');
test_assert(($GLOBALS['test_traduction_args']['montant'] ?? '') === '100 EUR', 'le sujet reçoit le montant avec la devise de la cotisation');

$sujet = notifications_cotisation_trouver_sujet('activation', array(
    'type_adherent' => 'entreprise',
    'reinscription' => 'reinscription',
    'nom_entreprise' => 'Entreprise test',
));
test_assert(str_starts_with($sujet, 'notifications:activation_cotisation_reinscription_entreprise_sujet|'), 'le renouvellement entreprise utilise son sujet dédié');

$GLOBALS['test_logs'] = array();
$sujet = notifications_cotisation_trouver_sujet('type_inconnu', array('id_compte' => 7));
test_assert($sujet === '', 'un type de notification inconnu ne fabrique pas de sujet arbitraire');
test_assert(count($GLOBALS['test_logs']) >= 1, 'un type de notification inconnu est journalisé');

test_assert(association_cotisation_doit_activer('public', 'ok', 0), 'une cotisation publique gratuite en statut ok active l’adhérent');
test_assert(!association_cotisation_doit_activer('public', 'attente', 0), 'une cotisation gratuite en attente n’active pas l’adhérent');
test_assert(!association_cotisation_doit_activer('public', 'ok', 10), 'une cotisation publique payante attend la confirmation Bank');
test_assert(association_cotisation_doit_activer('prive', 'ok', 10), 'une validation privée en statut ok active l’adhérent');

echo "Tous les tests d’échéances d’adhésion ont réussi.\n";
