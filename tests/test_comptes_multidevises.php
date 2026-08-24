<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_metas'] = array(
    'meta_cfg_cotisations_multidevises' => 'oui',
);

function include_spip($chemin) { return true; }
function lire_config($cle) { return $cle === 'intl/devise_defaut' ? 'EUR' : null; }
function intl_devise_defaut() { return 'EUR'; }
function intl_lister_devises() {
    return array(
        'CNY' => array('symbole' => '¥'),
        'EUR' => array('symbole' => '€'),
    );
}
function pipeline($nom, $flux) {
    if ($nom === 'association_compta_ecritures_devises') {
        return array(1 => 'CNY', 2 => 'EUR');
    }
    return $flux['data'];
}
function sql_allfetsel($select, $table, $where = '') {
    if (strpos($table, 'spip_asso_comptes c') !== false) {
        return array(
            array('id_compte' => 1, 'id_transaction' => 101, 'recette' => 100, 'depense' => 0),
            array('id_compte' => 2, 'id_transaction' => 0, 'recette' => 50, 'depense' => 0),
            array('id_compte' => 3, 'id_transaction' => 0, 'recette' => 0, 'depense' => 10),
        );
    }
    return array();
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations_devises.php';
include_once PLUGIN_ROOT . '/plugins/association-compta/inc/fonctions/comptes.php';

function test_assert($condition, $message) {
    if (!$condition) {
        echo "ECHEC: $message\n";
        exit(1);
    }
    echo "OK: $message\n";
}

$totaux = association_comptes_totaux_par_devise('c.vu=1');
test_assert($totaux['CNY']['recettes'] === 100.0, 'les recettes CNY restent séparées');
test_assert($totaux['EUR']['recettes'] === 50.0, 'les recettes EUR restent séparées');
test_assert($totaux['EUR']['depenses'] === 10.0, 'une écriture générique utilise la devise Intl du site');
test_assert($totaux['EUR']['solde'] === 40.0, 'le solde est calculé dans chaque devise');
test_assert(count($totaux) === 2, 'aucune addition CNY/EUR n’est produite');
$source_comptes = file_get_contents(PLUGIN_ROOT . '/plugins/association-compta/inc/fonctions/comptes.php');
$debut_totaux = strpos($source_comptes, 'function association_comptes_totaux_par_devise(');
$fin_totaux = strpos($source_comptes, 'function association_comptes_start_year_from_date(', $debut_totaux);
test_assert(strpos(substr($source_comptes, $debut_totaux, $fin_totaux - $debut_totaux), 'spip_transactions') === false, 'le bilan ne joint plus directement Bank');

$racine_compta = PLUGIN_ROOT . '/plugins/association-compta';
$contenu = file_get_contents($racine_compta . '/prive/squelettes/contenu/comptes.html');
$navigation = file_get_contents($racine_compta . '/prive/squelettes/navigation/comptes.html');
$ligne_compte = file_get_contents($racine_compta . '/prive/objets/liste/item_compte.html');
test_assert(strpos($contenu, 'table_valeur{par_devise}') !== false, 'les cartes comptables principales affichent les totaux par devise');
test_assert(strpos($navigation, 'table_valeur{par_devise}') !== false, 'la navigation comptable affiche les totaux par devise');
test_assert(strpos($ligne_compte, "#SET{devise,''}") !== false, 'une ligne comptable laisse la catégorie surcharger la devise du site');

echo "Tous les tests de comptabilité multidevises sont passés.\n";
