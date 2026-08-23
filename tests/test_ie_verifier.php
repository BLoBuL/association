<?php
// Test harness minimal pour ie_verifier_commons
// Définitions minimales des fonctions SPIP/utilitaires pour exécuter la vérification
if (!defined('_ECRIRE_INC_VERSION')) define('_ECRIRE_INC_VERSION', '1');
require_once dirname(__DIR__) . '/plugins/association-evenements/association_evenements_fonctions.php';
if (!function_exists('pipeline')) { function pipeline($nom, $flux) { return $flux; } }

if (!function_exists('include_spip')){
    function include_spip($p) {
        $base = realpath(__DIR__ . '/..');
        // Normaliser le nom en chemin relatif
        $path = $p;
        if (substr($path, -4) !== '.php') $path .= '.php';
        // remplacer les préfixes 'formulaires/' ou 'inc/' si fournis
        $try1 = $base . DIRECTORY_SEPARATOR . $path;
        if (file_exists($try1)) { require_once $try1; return true; }
		$tryModule = $base . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'association-evenements' . DIRECTORY_SEPARATOR . $path;
		if (file_exists($tryModule)) { require_once $tryModule; return true; }
        // tenter chemins usuels
        $try2 = $base . DIRECTORY_SEPARATOR . 'formulaires' . DIRECTORY_SEPARATOR . $path;
        if (file_exists($try2)) { require_once $try2; return true; }
        $try3 = $base . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . $path;
        if (file_exists($try3)) { require_once $try3; return true; }
        return false;
    }
}
if (!function_exists('spip_log')){
    function spip_log($msg, $chan = '') {
        echo "LOG[$chan]: ".(is_string($msg)?$msg:print_r($msg,true))."\n";
    }
}
if (!function_exists('sql_fetsel')){
    function sql_fetsel($sel, $table, $where) { return array(); }
}
if (!function_exists('sql_select')){ function sql_select($a,$b,$c){return false;} }
if (!function_exists('sql_fetch')){ function sql_fetch($q){return false;} }
if (!function_exists('sql_free')){ function sql_free($q){} }

if (!function_exists('email_valide')){
    function email_valide($e){ return (filter_var($e, FILTER_VALIDATE_EMAIL)!==false); }
}
// verifier_spam_formulaire_inscription est définie par le plugin; ne pas la redéfinir ici.

// helpers stubs
// Ne pas redéfinir ie_requete_attendue, ie_convertir_post ou ie_format_post :
// elles sont fournies par le backend (refactor). Le test fournira les
// paramètres nécessaires via ie_requete_attendue si besoin.

// Certaines fonctions sont définies par les fichiers inclus du plugin.
// Ne redéfinir ici que les helpers vraiment manquants si besoin.

// Minimal request stack used by SPIP helpers
global $TEST_REQUEST_STACK;
$TEST_REQUEST_STACK = array();
if (!function_exists('_request')){
    function _request($key){
        global $TEST_REQUEST_STACK;
        return $TEST_REQUEST_STACK[$key] ?? null;
    }
}
if (!function_exists('set_request')){
    function set_request($k,$v){ global $TEST_REQUEST_STACK; $TEST_REQUEST_STACK[$k]=$v; }
}
// Constantes de log minimal
if (!defined('_LOG_DEBUG')) define('_LOG_DEBUG', '_DEBUG');
if (!defined('_LOG_CRITIQUE')) define('_LOG_CRITIQUE', '_CRITIQUE');
if (!defined('_LOG_ERREUR')) define('_LOG_ERREUR', '_ERREUR');

// stub affichage_dans_activites controllable par le test
if (!function_exists('affichage_dans_activites')){
    function affichage_dans_activites($id_evenement){ global $TEST_AFFICHAGE; return isset($TEST_AFFICHAGE) ? $TEST_AFFICHAGE : array('payant'=>true,'accompagnants'=>true,'montant'=>array()); }
}

if (!function_exists('gestions_places')){
    function gestions_places($id_evenement){
        // retour simple pour tests: places disponibles 10, limite par personne 0 (illimité)
        return array('places_evenement'=>10,'places_disponibles'=>10,'places_limites'=>0,'places_en_attentes_disponible'=>0,'evenement_date_debut'=>date('Y-m-d'));
    }
}
// stub localisation minimale
if (!function_exists('_T')){
    function _T($k, $args = array()){ return isset($args) && !empty($args) ? strtr($k, $args) : $k; }
}

// Inclure le backend modifié
require_once __DIR__ . '/../plugins/association-evenements/formulaires/inc/inscription_evenement_backend.php';

function run_test($name, $mode, $id_evenement, $id_activite, $post) {
    echo "\n=== TEST: $name ===\n";
    // appel
    $res = ie_verifier_commons($mode, $id_evenement, $id_activite, $post);
    echo "Result (is_array=".(is_array($res)?'yes':'no').") :\n";
    print_r($res);
}

// Tests
// 1) FO payant, accompagnants, categorie 5 => 2 (nb_inscrits>1) nom_participants vide => doit renvoyer erreur nom_participants
$TEST_AFFICHAGE = array('payant'=>true,'accompagnants'=>true,'montant'=>array(5=>array('id_categorie'=>5,'titre'=>'T1','montant_symbole'=>'20')));
$post1 = array('categorie'=>array('5'=>'2','8'=>'','10'=>''));
run_test('FO payant 2 inscrits nom_participants vide', 'public', 224, 0, $post1);

// 2) même mais nom_participants renseigné => pas d'erreurs
$post2 = $post1; $post2['nom_participants']='Dupont Jean, Martin Paul';
run_test('FO payant 2 inscrits nom_participants rempli', 'public', 224, 0, $post2);

// 3) FO payant accompagnants mais aucune categorie sélectionnée => erreur categorie
$post3 = array('categorie'=>array('5'=>'0','8'=>'','10'=>''));
run_test('FO payant zero categories', 'public', 224, 0, $post3);

// 4) Evenement gratuit sans accompagnants => no nom_participants
$TEST_AFFICHAGE = array('payant'=>false,'accompagnants'=>false,'montant'=>array());
$post4 = array();
run_test('FO gratuit sans accompagnants', 'public', 300, 0, $post4);

// 5) FO payant non famille, selection 1 participant on two different selectors => simulate categorie 5=>1,10=>1 => require nom_participants
$TEST_AFFICHAGE = array('payant'=>true,'accompagnants'=>true,'montant'=>array(5=>array('id_categorie'=>5),'10'=>array('id_categorie'=>10)));
$post5 = array('categorie'=>array('5'=>'1','8'=>'','10'=>'1'));
run_test('FO payant two selectors 1 and 1 nom_participants empty', 'public', 224, 0, $post5);
$post5b = $post5; $post5b['nom_participants']='A B, C D';
run_test('FO payant two selectors 1 and 1 nom_participants provided', 'public', 224, 0, $post5b);

echo "\nAll tests done.\n";












