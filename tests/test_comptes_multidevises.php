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
function sql_allfetsel($select, $table, $where = '') {
    if (strpos($table, 'spip_asso_comptes c') !== false) {
        return array(
            array('recette' => 100, 'depense' => 0, 'statut_cotisation' => 'ok', 'transaction_devise' => 'CNY', 'categorie_devise' => 'CNY'),
            array('recette' => 50, 'depense' => 0, 'statut_cotisation' => 'ok', 'transaction_devise' => '', 'categorie_devise' => 'EUR'),
            array('recette' => 0, 'depense' => 10, 'statut_cotisation' => '', 'transaction_devise' => '', 'categorie_devise' => 'CNY'),
        );
    }
    return array();
}

include_once PLUGIN_ROOT . '/inc/cotisations_devises.php';
include_once PLUGIN_ROOT . '/inc/fonctions/comptes.php';

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

$contenu = file_get_contents(PLUGIN_ROOT . '/prive/squelettes/contenu/comptes.html');
$navigation = file_get_contents(PLUGIN_ROOT . '/prive/squelettes/navigation/comptes.html');
$ligne_compte = file_get_contents(PLUGIN_ROOT . '/prive/objets/liste/item_compte.html');
test_assert(strpos($contenu, 'table_valeur{par_devise}') !== false, 'les cartes comptables principales affichent les totaux par devise');
test_assert(strpos($navigation, 'table_valeur{par_devise}') !== false, 'la navigation comptable affiche les totaux par devise');
test_assert(strpos($ligne_compte, "#SET{devise,''}") !== false, 'une ligne comptable laisse la catégorie surcharger la devise du site');

echo "Tous les tests de comptabilité multidevises sont passés.\n";
