<?php
// Test direct call to champs_saisies_famille
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('PLUGIN_ROOT', dirname(__DIR__));
function include_spip($path) {
    $try = PLUGIN_ROOT . '/' . $path . '.php';
	if (!file_exists($try)) $try = PLUGIN_ROOT . '/plugins/association-evenements/' . $path . '.php';
    if (file_exists($try)) include_once $try;
}
function _request($k = null) { global $_R; if ($k === null) return $_R; return isset($_R[$k]) ? $_R[$k] : null; }
function set_request($k,$v){ global $_R; $_R[$k]=$v; }
function _T($k,$a=array()){ return $k; }
function spip_log($m,$n=''){ echo "LOG[$n]: $m\n"; }
function sql_fetsel($s,$f,$w){ return array(
    'id_auteur'=>1, 'prenom'=>'Jean', 'nom_famille'=>'Dupont', 'statut_interne'=>'ok', 'statut'=>'1comite',
    'email'=>'jean@example.test', 'mobile'=>'0600000001',
    'prenom_conjoint'=>'Marie', 'nom_conjoint'=>'Dupont', 'email_conjoint'=>'marie@example.test', 'mobile_conjoint'=>'0600000002',
    'prenom_enfant_1'=>'Alice', 'date_naissance_enfant_1'=>'2015-05-01',
    'prenom_enfant_2'=>'', 'date_naissance_enfant_2'=>'',
); }

// include the saisies implementation
include_spip('formulaires/inc/inscription_evenement_saisies');

// Prepare affichage_dans_activites minimal structure
$aff = array('payant' => true, 'accompagnants' => true, 'montant' => array(), 'info_supplementaire' => 'email,telephone', 'montant_symbole' => '€');

// Simulate family data and a selection of two members
$data_famille = array('adherent' => array('prenom'=>'Jean','nom'=>'Dupont'), 'conjoint' => array('prenom'=>'Marie','nom'=>'Dupont'), 'enfant_1' => array('prenom'=>'Alice','nom'=>'Dupont'));
$selected = array('adherent','enfant_1');
set_request('famille', $selected);

$res = champs_saisies_famille(1, $aff, $selected);

echo "Returned fieldsets count: " . count($res) . "\n";
foreach ($res as $k => $v) {
    echo "Key: $k\n";
    if (isset($v['options']['nom'])) echo "  name=" . $v['options']['nom'] . "\n";
    if (isset($v['saisies'])) echo "  saisies_count=" . count($v['saisies']) . "\n";
}

$ok = count($res) === 2 && isset($res['adherent'], $res['enfant_1']);
echo ($ok ? '[OK]' : '[KO]') . " saisies famille selectionnees\n";
exit($ok ? 0 : 1);

