<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_test_logs'] = array();
$GLOBALS['association_test_rgpd_calls'] = array();
$GLOBALS['association_test_fail_delete_tables'] = array();
$GLOBALS['association_test_fail_update_tables'] = array();

function include_spip($path) { return true; }
function pipeline($nom, $flux) {
    $handlers = array(
        'association_maintenance_auteurs_encaisses' => array(
            'association_compta_association_maintenance_auteurs_encaisses',
            'association_paiements_association_maintenance_auteurs_encaisses',
        ),
        'association_maintenance_supprimer_donnees_auteurs' => array(
            'association_communication_association_maintenance_supprimer_donnees_auteurs',
            'association_paiements_association_maintenance_supprimer_donnees_auteurs',
            'association_compta_association_maintenance_supprimer_donnees_auteurs',
        ),
    );
    foreach ($handlers[$nom] ?? array() as $handler) {
        $flux = $handler($flux);
    }
    return $flux['data'];
}
function association_log($journal, $message, $niveau = 'info') {
    $GLOBALS['association_test_logs'][] = array($journal, $message, $niveau);
}
function test_plugin_actif($plugin) { return false; }
function sql_quote($value) { return "'" . str_replace("'", "\\'", (string)$value) . "'"; }
function sql_in($field, $values) {
    $values = array_values($values);
    return $field . ' IN (' . implode(',', array_map('intval', $values)) . ')';
}
function ecrire_fichier($path, $content) { return true; }

function association_rgpd_anonymiser_auteur($id_auteur, $auteur) {
    $GLOBALS['association_test_rgpd_calls'][] = intval($id_auteur);
    return array('ok' => true, 'resume' => array('id_auteur' => intval($id_auteur)));
}

function association_test_make_result($rows) {
    return array('rows' => array_values($rows), 'index' => 0);
}

function association_test_extract_ids_from_in($where) {
    if (preg_match('/IN\s*\(([^)]*)\)/', $where, $m)) {
        $parts = preg_split('/\s*,\s*/', trim($m[1]));
        return array_values(array_filter(array_map('intval', $parts)));
    }
    return array();
}

function association_test_match_simple_where($row, $where) {
    $where = trim((string)$where);
    if ($where === '') {
        return true;
    }
    if (preg_match("/^id_auteur='?([0-9]+)'?$/", $where, $m)) {
        return intval($row['id_auteur'] ?? 0) === intval($m[1]);
    }
    if (preg_match("/^id_mailsubscriber IN \(([^)]*)\)$/", $where, $m)) {
        $ids = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', $m[1]))));
        return in_array(intval($row['id_mailsubscriber'] ?? 0), $ids, true);
    }
    if (preg_match("/^id_transaction IN \(([^)]*)\)$/", $where, $m)) {
        $ids = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', $m[1]))));
        return in_array(intval($row['id_transaction'] ?? 0), $ids, true);
    }
    return true;
}

function sql_select($select, $from, $where = '', $group = '', $order = '', $limit = '') {
    if ($from === 'spip_asso_comptes') {
        $ids = association_test_extract_ids_from_in($where);
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_asso_comptes'] as $row) {
            if (in_array(intval($row['id_auteur']), $ids, true) && floatval($row['recette']) > 0) {
                $rows[] = array('id_auteur' => intval($row['id_auteur']));
            }
        }
        return association_test_make_result($rows);
    }

    if ($from === 'spip_transactions AS t') {
        $ids = association_test_extract_ids_from_in($where);
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_transactions'] as $row) {
            if (!in_array(intval($row['id_auteur']), $ids, true) || ($row['statut'] ?? '') !== 'ok') {
                continue;
            }
            $transaction_associee = false;
            foreach ($GLOBALS['association_test_tables']['spip_asso_comptes'] as $compte) {
                if (intval($compte['id_transaction']) === intval($row['id_transaction'])) {
                    $transaction_associee = true;
                    break;
                }
            }
            if (!$transaction_associee) {
                foreach ($GLOBALS['association_test_tables']['spip_asso_activites'] as $activite) {
                    if (intval($activite['id_transaction']) === intval($row['id_transaction'])) {
                        $transaction_associee = true;
                        break;
                    }
                }
            }
            if ($transaction_associee) {
                $rows[] = array('id_auteur' => intval($row['id_auteur']));
            }
        }
        return association_test_make_result($rows);
    }

    if ($from === 'spip_auteurs') {
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_auteurs'] as $row) {
            if (association_test_match_simple_where($row, $where)) {
                $rows[] = $row;
            }
        }
        return association_test_make_result($rows);
    }

    if ($from === 'spip_mailsubscribers') {
        $emails = array();
        preg_match_all("/'([^']+)'/", $where, $matches);
        if (!empty($matches[1])) {
            $emails = $matches[1];
        }
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_mailsubscribers'] as $row) {
            if (in_array($row['email'], $emails, true)) {
                $rows[] = array('id_mailsubscriber' => intval($row['id_mailsubscriber']));
            }
        }
        return association_test_make_result($rows);
    }

    if ($from === 'spip_asso_comptes AS c LEFT JOIN spip_auteurs AS a ON a.id_auteur=c.id_auteur') {
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_asso_comptes'] as $row) {
            $id_auteur = intval($row['id_auteur'] ?? 0);
            if (!isset($GLOBALS['association_test_tables']['spip_auteurs'][$id_auteur])) {
                $rows[] = array(
                    'id_compte' => intval($row['id_compte']),
                    'id_transaction' => intval($row['id_transaction'] ?? 0),
                );
            }
        }
        return association_test_make_result($rows);
    }

    if ($from === 'spip_transactions') {
        $ids = association_test_extract_ids_from_in($where);
        $rows = array();
        foreach ($GLOBALS['association_test_tables']['spip_transactions'] as $row) {
            if ($ids && !in_array(intval($row['id_transaction']), $ids, true)) {
                continue;
            }
            if (str_contains($where, "statut<>'ok'") && ($row['statut'] ?? '') === 'ok') {
                continue;
            }
            $rows[] = array('id_transaction' => intval($row['id_transaction']));
        }
        return association_test_make_result($rows);
    }

    return association_test_make_result(array());
}

function sql_fetch(&$result) {
    if (!is_array($result) || !isset($result['rows'])) {
        return false;
    }
    if ($result['index'] >= count($result['rows'])) {
        return false;
    }
    return $result['rows'][$result['index']++];
}

function sql_fetsel($select, $from, $where = '', $group = '', $order = '') {
    if ($from !== 'spip_auteurs') {
        return false;
    }
    foreach ($GLOBALS['association_test_tables']['spip_auteurs'] as $row) {
        if (association_test_match_simple_where($row, $where)) {
            return $row;
        }
    }
    return false;
}

function sql_countsel($table, $where = '') {
    $count = 0;
    foreach ($GLOBALS['association_test_tables'][$table] ?? array() as $row) {
        if (association_test_match_simple_where($row, $where)) {
            $count++;
        }
    }
    return $count;
}

function sql_updateq($table, $set, $where) {
    if (in_array($table, $GLOBALS['association_test_fail_update_tables'], true)) {
        return false;
    }
    $updated = 0;
    foreach ($GLOBALS['association_test_tables'][$table] ?? array() as $id => $row) {
        if (association_test_match_simple_where($row, $where)) {
            $GLOBALS['association_test_tables'][$table][$id] = array_merge($row, $set);
            $updated++;
        }
    }
    return $updated;
}

function sql_delete($table, $where) {
    if (in_array($table, $GLOBALS['association_test_fail_delete_tables'], true)) {
        return false;
    }
    $deleted = 0;
    foreach ($GLOBALS['association_test_tables'][$table] ?? array() as $id => $row) {
        if (association_test_match_simple_where($row, $where)) {
            unset($GLOBALS['association_test_tables'][$table][$id]);
            $deleted++;
        }
    }
    return $deleted;
}

function association_test_reset_tables() {
    $GLOBALS['association_test_tables'] = array(
        'spip_auteurs' => array(
            1 => array('id_auteur' => 1, 'nom' => 'Alice', 'email' => 'alice@example.test', 'login' => 'alice'),
            2 => array('id_auteur' => 2, 'nom' => 'Bob', 'email' => 'bob@example.test', 'login' => 'bob'),
        ),
        'spip_asso_comptes' => array(
            1 => array('id_compte' => 1, 'id_auteur' => 1, 'recette' => 25, 'id_transaction' => 101),
        ),
        'spip_transactions' => array(
            101 => array('id_transaction' => 101, 'id_auteur' => 1, 'statut' => 'ok'),
            202 => array('id_transaction' => 202, 'id_auteur' => 2, 'statut' => 'ok'),
        ),
        'spip_asso_activites' => array(),
        'spip_mailsubscribers' => array(
            1 => array('id_mailsubscriber' => 1, 'email' => 'bob@example.test'),
        ),
        'spip_mailsubscriptions' => array(
            1 => array('id_mailsubscriber' => 1),
        ),
        'spip_mailshots_destinataires' => array(
            1 => array('id_mailsubscriber' => 1),
        ),
    );
    $GLOBALS['association_test_rgpd_calls'] = array();
    $GLOBALS['association_test_fail_delete_tables'] = array();
    $GLOBALS['association_test_fail_update_tables'] = array();
}

include_once PLUGIN_ROOT . '/plugins/association-evenements/inc/association_evenements_maintenance.php';
include_once PLUGIN_ROOT . '/plugins/association-communication/inc/association_communication_maintenance.php';
include_once PLUGIN_ROOT . '/plugins/association-compta/inc/association_compta_maintenance.php';
include_once PLUGIN_ROOT . '/plugins/association-paiements/inc/association_paiements_maintenance.php';
include_once PLUGIN_ROOT . '/plugins/association-compta/association_compta_pipelines.php';
include_once PLUGIN_ROOT . '/plugins/association-paiements/association_paiements_pipelines.php';
include_once PLUGIN_ROOT . '/plugins/association-communication/association_communication_pipelines.php';
include_once PLUGIN_ROOT . '/genie/association_maintenance_bdd.php';

function association_test_assert($condition, $message) {
    if (!$condition) {
        echo "ECHEC: $message\n";
        exit(1);
    }
    echo "OK: $message\n";
}

association_test_reset_tables();
$resultat = asso_anonymiser_auteurs(array(1, 2), true);
association_test_assert(($resultat['dry_run'] ?? false) === true, 'le dry-run des auteurs est déclaré comme simulation');
association_test_assert($GLOBALS['association_test_tables']['spip_auteurs'][1]['nom'] === 'Alice', 'le dry-run des auteurs ne modifie pas la table auteurs');
association_test_assert(count($GLOBALS['association_test_rgpd_calls']) === 0, 'le dry-run des auteurs ne déclenche pas l anonymisation RGPD');

association_test_reset_tables();
$resultat = asso_anonymiser_inscriptions_auteurs(array(1, 2), true);
association_test_assert(($resultat['dry_run'] ?? false) === true, 'le dry-run des inscriptions est déclaré comme simulation');

association_test_reset_tables();
list($sans_paiements, $avec_paiements) = asso_separer_auteurs_par_encaissements(array(1, 2));
sort($sans_paiements);
sort($avec_paiements);
association_test_assert($avec_paiements === array(1), 'seuls les paiements rattachés au périmètre association classent un auteur comme encaissé');
association_test_assert($sans_paiements === array(2), 'une transaction ok hors périmètre association ne protège pas l auteur');

association_test_reset_tables();
$GLOBALS['association_test_fail_delete_tables'] = array('spip_transactions');
$resultat = asso_supprimer_auteurs(array(2), false);
association_test_assert(($resultat['suppression_auteurs_skippee'] ?? false) === true, 'la suppression de l auteur est bloquée si une suppression associée échoue');
association_test_assert(isset($GLOBALS['association_test_tables']['spip_auteurs'][2]), 'l auteur est conservé en cas d échec partiel');

association_test_reset_tables();
$GLOBALS['association_test_tables']['spip_asso_comptes'][2] = array('id_compte' => 2, 'id_auteur' => 999, 'recette' => 0, 'id_transaction' => 303);
$GLOBALS['association_test_tables']['spip_transactions'][303] = array('id_transaction' => 303, 'id_auteur' => 999, 'statut' => 'attente');
$GLOBALS['association_test_fail_delete_tables'] = array('spip_transactions');
$resultat = asso_supprimer_cotisations_orphelines(false, 1000);
association_test_assert(($resultat['erreur'] ?? '') === 'suppression_transactions_cotisations_orphelines_echouee', 'une erreur explicite remonte si la suppression des transactions liées échoue');

echo "Tous les tests maintenance BDD ont réussi.\n";
