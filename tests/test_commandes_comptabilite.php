<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PLUGIN_ROOT', dirname(__DIR__));

function include_spip($path) {
    return true;
}

function association_log($journal, $message, $niveau = 'info') {
    $GLOBALS['association_test_logs'][] = array($journal, $message, $niveau);
}

$GLOBALS['association_metas'] = array(
    'comptes' => 1,
    'pc_activites_creance' => '417',
    'pc_ventes' => '701',
);

$GLOBALS['association_test_tables'] = array(
    'spip_commandes' => array(
        'field' => array(
            'id_commande' => 'bigint',
            'reference' => 'varchar',
            'id_auteur' => 'bigint',
            'statut' => 'varchar',
            'date' => 'datetime',
            'date_envoi' => 'datetime',
            'date_paiement' => 'datetime',
            'commentaire' => 'text',
        ),
        'rows' => array(
            1 => array(
                'id_commande' => 1,
                'reference' => 'CMD-TEST',
                'id_auteur' => 7,
                'statut' => 'attente',
                'date' => '2026-01-01 00:00:00',
                'date_envoi' => '0000-00-00 00:00:00',
                'date_paiement' => '0000-00-00 00:00:00',
                'commentaire' => '{"client":"Client test"}',
            ),
        ),
    ),
    'spip_transactions' => array(
        'field' => array(
            'id_transaction' => 'bigint',
            'id_commande' => 'bigint',
            'id_auteur' => 'bigint',
            'statut' => 'varchar',
            'montant' => 'decimal',
            'date_paiement' => 'datetime',
        ),
        'rows' => array(
            10 => array(
                'id_transaction' => 10,
                'id_commande' => 1,
                'id_auteur' => 7,
                'statut' => 'attente',
                'montant' => 120.50,
                'date_paiement' => '0000-00-00 00:00:00',
            ),
        ),
    ),
    'spip_commandes_details' => array(
        'field' => array(
            'id_commande' => 'bigint',
            'montant' => 'decimal',
        ),
        'rows' => array(),
    ),
    'spip_asso_comptes' => array(
        'field' => array(
            'id_compte' => 'bigint',
            'date' => 'date',
            'recette' => 'float',
            'depense' => 'float',
            'justification' => 'text',
            'imputation' => 'text',
            'journal' => 'text',
            'id_auteur' => 'bigint',
            'id_objet' => 'bigint',
            'objet' => 'varchar',
            'id_transaction' => 'bigint',
            'vu' => 'tinyint',
        ),
        'rows' => array(),
        'next_id' => 1,
    ),
    'spip_auteurs' => array(
        'field' => array('id_auteur' => 'bigint', 'nom' => 'text'),
        'rows' => array(7 => array('id_auteur' => 7, 'nom' => 'Auteur test')),
    ),
);

function sql_showtable($table, $serveur = true) {
    return $GLOBALS['association_test_tables'][$table] ?? array();
}

function association_test_match_where($row, $where) {
    $where = trim((string)$where);
    if ($where === '') {
        return true;
    }
    $parts = preg_split('/\s+AND\s+/i', $where);
    foreach ($parts as $part) {
        $part = trim($part);
        if (!preg_match("/^([a-zA-Z0-9_]+)=('([^']*)'|[0-9]+)$/", $part, $m)) {
            return false;
        }
        $champ = $m[1];
        $valeur = isset($m[3]) ? $m[3] : $m[2];
        if ((string)($row[$champ] ?? '') !== (string)$valeur) {
            return false;
        }
    }
    return true;
}

function sql_fetsel($select, $from, $where = '', $group = '', $order = '') {
    $rows = $GLOBALS['association_test_tables'][$from]['rows'] ?? array();
    if ($order && stripos($order, 'DESC') !== false) {
        krsort($rows);
    }
    foreach ($rows as $row) {
        if (association_test_match_where($row, $where)) {
            return $row;
        }
    }
    return false;
}

function sql_allfetsel($select, $from, $where = '') {
    $rows = array();
    foreach (($GLOBALS['association_test_tables'][$from]['rows'] ?? array()) as $row) {
        if (association_test_match_where($row, $where)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function sql_getfetsel($select, $from, $where = '') {
    $row = sql_fetsel($select, $from, $where);
    return $row ? ($row[$select] ?? '') : '';
}

function sql_insertq($table, $set) {
    $id = $GLOBALS['association_test_tables'][$table]['next_id']++;
    $set['id_compte'] = $id;
    $GLOBALS['association_test_tables'][$table]['rows'][$id] = $set;
    return $id;
}

function sql_updateq($table, $set, $where) {
    foreach ($GLOBALS['association_test_tables'][$table]['rows'] as $id => $row) {
        if (association_test_match_where($row, $where)) {
            $GLOBALS['association_test_tables'][$table]['rows'][$id] = array_merge($row, $set);
        }
    }
    return true;
}

include_once PLUGIN_ROOT . '/plugins/association-compta/inc/comptes.php';

function association_test_assert($condition, $message) {
    if (!$condition) {
        echo "ERREUR: $message\n";
        exit(1);
    }
    echo "OK: $message\n";
}

$id_compte = association_commande_comptable_synchroniser(1);
association_test_assert($id_compte === 0, 'aucune ecriture sans date d envoi ni paiement');
association_test_assert(count($GLOBALS['association_test_tables']['spip_asso_comptes']['rows']) === 0, 'la table comptable reste vide avant envoi');

$GLOBALS['association_test_tables']['spip_commandes']['rows'][1]['date_envoi'] = '2026-02-10 00:00:00';
$id_compte = association_commande_comptable_synchroniser(1);
association_test_assert($id_compte === 1, 'creation de l ecriture a l envoi de la commande');
$compte = $GLOBALS['association_test_tables']['spip_asso_comptes']['rows'][1];
association_test_assert($compte['vu'] === 0, 'ecriture de commande envoyee non validee');
association_test_assert($compte['imputation'] === '417', 'imputation de creance utilisee avant paiement');
association_test_assert(abs($compte['recette'] - 120.50) < 0.001, 'montant repris depuis la transaction');

$GLOBALS['association_test_tables']['spip_transactions']['rows'][10]['statut'] = 'ok';
$GLOBALS['association_test_tables']['spip_transactions']['rows'][10]['date_paiement'] = '2026-02-15 00:00:00';
$id_compte_paye = association_commande_comptable_synchroniser(1, array('id_transaction' => 10, 'forcer_paiement' => true));
association_test_assert($id_compte_paye === 1, 'validation sans creation de doublon au paiement');
$compte = $GLOBALS['association_test_tables']['spip_asso_comptes']['rows'][1];
association_test_assert($compte['vu'] === 1, 'ecriture validee au paiement');
association_test_assert($compte['imputation'] === '701', 'imputation de paiement utilisee apres encaissement');
association_test_assert($compte['date'] === '2026-02-15', 'date comptable alignee sur la date de paiement');

echo "Tous les tests commandes/comptabilite ont reussi.\n";
