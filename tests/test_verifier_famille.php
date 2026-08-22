<?php
// Test ie_verifier_commons: cas famille active sans membre sélectionné
// Usage: php tests/test_verifier_famille.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PLUGIN_ROOT', dirname(__DIR__));
if (!defined('_ECRIRE_INC_VERSION')) define('_ECRIRE_INC_VERSION', true);
if (!function_exists('pipeline')) { function pipeline($nom, $flux) { return $flux; } }

// Minimal stubs
function include_spip($path) {
    $try = PLUGIN_ROOT . '/' . str_replace('\\','/',$path) . '.php';
    if (file_exists($try)) {
        include_once $try;
        return true;
    }
    $try2 = PLUGIN_ROOT . '/' . str_replace('\\','/',$path);
    if (file_exists($try2)) {
        include_once $try2;
        return true;
    }
    return false;
}
function _request($k = null) {
    global $_TEST_REQ;
    if ($k === null) return $_TEST_REQ;
    return $_TEST_REQ[$k] ?? null;
}
function set_request($k, $v) { global $_TEST_REQ; $_TEST_REQ[$k] = $v; }
function spip_log($m,$n=''){ echo "LOG[$n]: $m\n"; }
function _T($k,$a=array()){ return $k; }
function autoriser(){ return true; }
function affichage_dans_activites($id){ return array('payant'=>false,'accompagnants'=>true,'montant'=>array(),'type_inscrits_evenement'=>'public'); }
function gestions_places($id){ return array('places_limites'=>10,'places_disponibles'=>10,'places_evenement'=>10,'places_en_attentes_disponible'=>0); }
function eligibilite_inscription_evenement($id){ return array('eligible_inscription'=>'oui','id_activite'=>null); }
function ouverture_inscription_evenement($id){ return array('inscription_ouverte'=>'oui','statut_ouverture_inscription'=>'inscription_ouverte'); }
if (!function_exists('generer_famille_adherent')) {
        // Le stub de `generer_famille_adherent` sera défini plus bas **seulement** si
        // le fichier réel n'est pas disponible (évite les redéclarations).
}
function email_valide($e){ return filter_var($e,FILTER_VALIDATE_EMAIL)!==false; }
function sql_fetsel($a,$b,$c){ return array(); }
function sql_select($a,$b,$c){ return false; }
function sql_fetch($r){ return false; }
function sql_free($r){}

// Define logging constants early
if (!defined('_LOG_DEBUG')) define('_LOG_DEBUG', 'DEBUG');
if (!defined('_LOG_CRITIQUE')) define('_LOG_CRITIQUE', 'CRIT');

// Inclure l'implémentation réelle des helpers avant le backend pour
// éviter les redéclarations lors de l'inclusion des fichiers.
include_once PLUGIN_ROOT . '/formulaires/inc/inscription_evenement.php';

// Include backend
include_once PLUGIN_ROOT . '/inc/diagnostic_formulaire_inscription.php';
include_once PLUGIN_ROOT . '/formulaires/inc/inscription_evenement_backend.php';

// Setup scenario: famille active, visiteur connecté, mais aucune sélection
$GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] = 'membre_famille';
$GLOBALS['visiteur_session'] = array('id_auteur' => 123, 'statut' => '1comite');

// Ensure no famille selection explicite dans la requete
$_TEST_REQ = array('famille' => array());

// Call verifier (public non-multi)
$erreurs = ie_verifier_commons('public', 1, null, array());

echo "Retour verifier:\n";
var_export($erreurs);

// Assert attendu : en mode famille actif sans membre selectionne, une erreur famille est attendue
$ok = is_array($erreurs) && array_key_exists('famille', $erreurs);
if ($ok) {
    echo "TEST OK: erreur 'famille' detectee : " . $erreurs['famille'] . "\n";
    exit(0);
} else {
    echo "TEST FAILED: la cle 'famille' n'a pas ete renvoyee\n";
    exit(1);
}
