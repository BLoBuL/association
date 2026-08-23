<?php
// debug runner for multi-step famille selection
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('PLUGIN_ROOT', dirname(__DIR__));
if (!defined('_LOG_DEBUG')) define('_LOG_DEBUG', 'DEBUG');
if (!defined('_LOG_CRITIQUE')) define('_LOG_CRITIQUE', 'CRIT');
function include_spip($path) {
    $try = PLUGIN_ROOT . '/' . $path . '.php';
    if (file_exists($try)) include_once $try;
}
function _request($k = null) { global $_R; if ($k === null) return $_R; return isset($_R[$k]) ? $_R[$k] : null; }
function set_request($k,$v){ global $_R; $_R[$k]=$v; }
function spip_log($m,$n=''){ echo "LOG[$n]: $m\n"; }
function _T($k,$a=array()){ return $k; }
function autoriser(){ return true; }
function affdate($d,$f=null){ return $d; }
if (!function_exists('sql_fetsel')) {
    function sql_fetsel($s,$f,$w){ return array('id_auteur'=>1,'prenom'=>'Jean','nom_famille'=>'Dupont','statut_interne'=>'ok','email'=>'j@example.com'); }
}

// Minimal stubs required by the backend
if (!function_exists('affichage_dans_activites')) {
    function affichage_dans_activites($id_evenement) {
        return array('payant' => true, 'accompagnants' => true, 'montant' => array(array('id_categorie'=>1,'titre'=>'Standard','montant_symbole'=>'€','quantite'=>1,'type_inscrit'=>'indifferent')), 'type_inscrits_evenement' => 'public', 'montant_symbole' => '€');
    }
}
if (!function_exists('gestions_places')) {
    function gestions_places($id_evenement) {
        return array('places_limites' => 10, 'places_disponibles' => 10, 'places_evenement' => 10, 'places_en_attentes_disponible' => 0, 'evenement_date_debut' => date('Y-m-d'));
    }
}
if (!function_exists('eligibilite_inscription_evenement')) {
    function eligibilite_inscription_evenement($id) { return array('eligible_inscription' => 'oui', 'id_activite' => null, 'eligibilite_token_inscription' => false); }
}
if (!function_exists('ouverture_inscription_evenement')) {
    function ouverture_inscription_evenement($id) { return array('inscription_ouverte' => 'oui', 'statut_ouverture_inscription' => 'inscription_ouverte'); }
}
if (!function_exists('preparer_chargement_modification_inscription')) {
    function preparer_chargement_modification_inscription($id) { return array(); }
}
if (!function_exists('preparer_chargement_modification_inscription_multi')) {
    function preparer_chargement_modification_inscription_multi($id, $mode) { return array(); }
}
if (!function_exists('generer_saisies_info_public')) {
    function generer_saisies_info_public($mode) { return array(); }
}
if (!function_exists('champs_saisie_nb_inscrits')) {
    function champs_saisie_nb_inscrits($gestions_places, $aff) { return array('saisie'=>'hidden','options'=>array('nom'=>'nb_inscrits','defaut'=>1)); }
}
if (!function_exists('champs_saisies_selection_membres_famille')) {
    function champs_saisies_selection_membres_famille($data, $flag) { return array('saisie' => 'selection', 'options' => array('nom' => 'famille', 'label' => 'Famille test', 'datas' => is_array($data) ? $data : array(), 'defaut' => '')); }
}
if (!function_exists('generer_famille_adherent')) {
    function generer_famille_adherent($id) { global $_TEST_GENERER_FAMILLE; return $_TEST_GENERER_FAMILLE ?? array(); }
}

// include backend
include_once PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inc/inscription_evenement_backend.php';
// Ensure saisies helpers are available for the isolated test
if (!function_exists('champs_saisies_famille')) {
    include_once PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inc/inscription_evenement_saisies.php';
}
// Minimal SQL stubs for the test harness
global $_TEST_SQL_RESULT, $_TEST_SQL_INDEX;
$_TEST_SQL_RESULT = array(array('id_auteur'=>1,'prenom'=>'Jean','nom_famille'=>'Dupont','statut_interne'=>'ok'));
$_TEST_SQL_INDEX = 0;
function sql_select($select, $from, $where = '', $group = '', $order = '') { global $_TEST_SQL_RESULT, $_TEST_SQL_INDEX; $_TEST_SQL_INDEX = 0; return 'TEST'; }
function sql_fetch($res) { global $_TEST_SQL_RESULT, $_TEST_SQL_INDEX; if (isset($_TEST_SQL_RESULT[$_TEST_SQL_INDEX])) return $_TEST_SQL_RESULT[$_TEST_SQL_INDEX++]; return false; }
function sql_free($res) { return true; }

// prepare environment
$GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']='membre_famille';
$GLOBALS['_TEST_GENERER_FAMILLE'] = array('adherent'=>'Jean','conjoint'=>'Marie','enfant_1'=>'Alice');

// simulate BO selection of member
set_request('select_type_inscrit','membre');
set_request('membre', 1);

// simulate cvtm_prev_post (previous step with two family members selected)
$prev = array('famille' => array('adherent','enfant_1'));
$encoded = base64_encode(serialize($prev));
set_request('cvtm_prev_post', $encoded);

$res = ie_charger_commons('multi_prive', 1, null, array());

echo "--- _saisies_par_etapes ---\n";
var_export($res['_saisies_par_etapes']);
echo "\n--- fieldsets list ---\n";
foreach($res['_saisies'] as $k=>$v){
    if (is_int($k) && isset($v['options']['nom'])) {
        echo "$k: " . $v['options']['nom'] . "\n";
    }
}

echo "\n--- Dump participant fieldsets (those with 'fieldset_inscrit_' in name) ---\n";
foreach($res['_saisies'] as $v){
    if (is_array($v) && isset($v['options']['nom']) && strpos($v['options']['nom'],'fieldset_inscrit_') === 0) {
        var_export($v);
        echo "\n---\n";
    }
}

