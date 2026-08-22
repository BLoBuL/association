<?php
// Tests pour ie_verifier_commons() - scénarios payant + saisie famille (non-multi)
// Usage: php tests/test_verifier_commons.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PLUGIN_ROOT', dirname(__DIR__));
if (!defined('_ECRIRE_INC_VERSION')) define('_ECRIRE_INC_VERSION', true);
if (!function_exists('pipeline')) { function pipeline($nom, $flux) { return $flux; } }

// --- Stubs minimalistes SPIP / helpers (simples, suffisants pour verifier) ---
if (!defined('_LOG_DEBUG')) define('_LOG_DEBUG', 'DEBUG');
if (!defined('_LOG_CRITIQUE')) define('_LOG_CRITIQUE', 'CRIT');

if (!function_exists('include_spip')) {
    function include_spip($path) {
        $try = PLUGIN_ROOT . '/' . str_replace('\\','/',$path) . '.php';
        if (file_exists($try)) { include_once $try; return true; }
        $try2 = PLUGIN_ROOT . '/' . str_replace('\\','/',$path);
        if (file_exists($try2)) { include_once $try2; return true; }
        return false;
    }
}

$GLOBALS['_TEST_REQUEST'] = array();
if (!function_exists('_request')) {
    function _request($k=null){ global $_TEST_REQUEST; if ($k===null) return $_TEST_REQUEST; return array_key_exists($k, $_TEST_REQUEST)?$_TEST_REQUEST[$k]:null; }
}
if (!function_exists('set_request')) {
    function set_request($k,$v){ global $_TEST_REQUEST; $_TEST_REQUEST[$k]=$v; }
}
if (!function_exists('spip_log')) {
    function spip_log($m,$n=''){ echo "LOG[$n]: $m\n"; }
}
if (!function_exists('_T')) {
    function _T($k,$a=array()){ return $k; }
}
if (!function_exists('autoriser')) {
    function autoriser(){ return true; }
}
if (!function_exists('affdate')) {
    function affdate($d,$f=null){ return $d; }
}
if (!function_exists('email_valide')) {
    function email_valide($e){ return filter_var($e,FILTER_VALIDATE_EMAIL)!==false; }
}

// SQL stubs minimal
if (!function_exists('sql_fetsel')) {
    function sql_fetsel($sel,$from,$where){ return array(); }
}
if (!function_exists('sql_select')) {
    function sql_select($sel,$from,$where='',$group='',$order=''){ return false; }
}
if (!function_exists('sql_fetch')) {
    function sql_fetch($r){ return false; }
}
if (!function_exists('sql_free')) {
    function sql_free($r){}
}

// Metas
$GLOBALS['association_metas'] = array();
// Minimal display helpers used by formater_post_form
if (!function_exists('affichage_dans_activites')) {
    function affichage_dans_activites($id) { return array('payant' => true, 'accompagnants' => true, 'montant' => array(10 => array('id_categorie'=>10,'montant'=>10,'quantite'=>1)), 'type_inscrits_evenement'=>'public'); }
}
if (!function_exists('gestions_places')) {
    function gestions_places($id) { return array('places_limites'=>100,'places_disponibles'=>100,'places_evenement'=>100,'places_en_attentes_disponible'=>0); }
}

// Include real code
include_once PLUGIN_ROOT . '/formulaires/inc/inscription_evenement.php';
include_once PLUGIN_ROOT . '/inc/diagnostic_formulaire_inscription.php';
include_once PLUGIN_ROOT . '/formulaires/inc/inscription_evenement_backend.php';

// Helper to run scenario
function run_scenario($name, $post_seed, $meta_cfg_accompagnants, $visiteur_session = array(), $expect_keys = array(), $deny_keys = array()){
    echo "\n--- Scenario: $name ---\n";

    // set globals
    $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] = $meta_cfg_accompagnants;
    $GLOBALS['visiteur_session'] = $visiteur_session;

    // Call verifier: pass $post_seed as seed so ie_requete_attendue uses it
    $res = ie_verifier_commons('public', 1, null, $post_seed);

    echo "Result: "; var_export($res); echo "\n";

    $ok = true;
    foreach ($expect_keys as $k){
        if (!is_array($res) || !array_key_exists($k,$res)){
            echo "ERREUR: attendue key '$k' manquante\n";
            $ok = false;
        }
    }
    foreach ($deny_keys as $k){
        if (is_array($res) && array_key_exists($k,$res)){
            echo "ERREUR: la key '$k' NE DOIT PAS être présente mais elle l'est\n";
            $ok = false;
        }
    }
    echo ($ok ? "OK\n" : "FAILED\n");
    return $ok;
}

$all_ok = true;

// Scenario 1: payant + famille active, AUCUNE categorie sélectionnee
// Simulate: no categorie in post
$scenario1_post = array();
$all_ok &= run_scenario('payant_famille_no_selection', $scenario1_post, 'membre_famille', array('id_auteur'=>123,'statut'=>'1comite'), array('famille'), array());

// Scenario 2: payant + famille active, 1 checkbox selected in category 5 (adherent)
// Saisies send categorie[5][]=adherent
$scenario2_post = array('categorie' => array(5 => array('adherent')));
$all_ok &= run_scenario('payant_famille_one_selected', $scenario2_post, 'membre_famille', array('id_auteur'=>123,'statut'=>'1comite'), array(), array('famille'));

// Scenario 3: payant + famille active, 2 selected in same category (5: adherent + enfant_1)
$scenario3_post = array('categorie' => array(5 => array('adherent','enfant_1')));
$all_ok &= run_scenario('payant_famille_two_same_cat', $scenario3_post, 'membre_famille', array('id_auteur'=>123,'statut'=>'1comite'), array(), array('famille'));

// Scenario 4: payant + famille active, 1 selected but config not set to membre_famille (detect effective family)
$scenario4_post = array('categorie' => array(10 => array('adherent')));
$all_ok &= run_scenario('payant_family_effective_without_meta', $scenario4_post, 'tout', array('id_auteur'=>123,'statut'=>'1comite'), array(), array('famille'));

// Result summary
if ($all_ok){
    echo "\nTous les scénarios ont réussi.\n";
    exit(0);
} else {
    echo "\nUn ou plusieurs scénarios ont échoué.\n";
    exit(1);
}


