<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Supprimer les transactions liées à des auteurs (si table disponible).
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */

function association_adhesions_supprimer_cotisations_orphelines($dry_run = true, $lot = 1000) {

    $orphans = [];
    // Cotisations orphelines = sans auteur
    $res = sql_select(
        'c.id_compte,c.id_transaction',
        'spip_asso_cotisations AS c LEFT JOIN spip_auteurs AS a ON a.id_auteur=c.id_auteur',
		'a.id_auteur IS NULL',
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $orphans[intval($row['id_compte'])] = intval($row['id_transaction']);
    }
    if (!$orphans) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => 0,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    $protegees = [];
    // Protéger celles liées à une transaction encaissée (statut ok)
    $ids_tx = array_values(array_unique(array_filter($orphans)));
	include_spip('inc/association_adhesions_integrations');
	$transactions = association_adhesions_transactions_lire($ids_tx);
    foreach ($orphans as $id_compte => $id_tx) {
        if ($id_tx && (($transactions[$id_tx]['statut'] ?? '') === 'ok')) {
            $protegees[] = $id_compte;
        }
    }

    // Cotisations à supprimer
    $a_supprimer = array_values(array_diff(array_keys($orphans), $protegees));
    if (!$a_supprimer) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    if (count($a_supprimer) > $lot) {
        $a_supprimer = array_slice($a_supprimer, 0, $lot);
    }

    if (!$dry_run) {
        sql_query('START TRANSACTION');
    }

    // Suppression des cotisations
    $nb_cotisations = association_adhesions_maintenance_supprimer_cotisations($a_supprimer, $dry_run);

    if ($nb_cotisations === false) {
        if (!$dry_run) {
            sql_query('ROLLBACK');
        }
        return [
            'supprimees' => 0,
            'ids' => $a_supprimer,
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_cotisations_orphelines_echouee'
        ];
    }

    // Transactions liées non réglées à supprimer
    $tx_ids = [];
    foreach ($a_supprimer as $id_compte) {
        if (!empty($orphans[$id_compte])) $tx_ids[] = (int) $orphans[$id_compte];
    }
	$suppression_transactions = association_adhesions_transactions_supprimer_non_encaissees($tx_ids, $dry_run);
    if (!empty($suppression_transactions['erreur'])) {
        if (!$dry_run) sql_query('ROLLBACK');
        return [
            'supprimees' => intval($nb_cotisations),
            'ids' => $a_supprimer,
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => $suppression_transactions['ids'],
            'erreur' => 'suppression_transactions_cotisations_orphelines_echouee'
        ];
    }

    if (!$dry_run) {
        sql_query('COMMIT');
    }

    return [
        'supprimees' => intval($nb_cotisations),
        'ids' => $a_supprimer,
        'protegees' => count($protegees),
        'transactions_supprimees' => intval($suppression_transactions['supprimes']),
        'transactions_ids' => $suppression_transactions['ids']
    ];
}


/**
 * Supprimer les transactions orphelines (non liées et non `ok`).
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */

function association_adhesions_supprimer_cotisations_non_encaissees_anciennes($maintenant, $mois, $dry_run = true, $lot = 1000) {
    // Calcul de la date limite (simple: mois courant - N)
    $limit_ts = mktime(date('H',$maintenant), date('i',$maintenant), date('s',$maintenant),
        date('m',$maintenant) - intval($mois), date('d',$maintenant), date('Y',$maintenant));
    $limite = date('Y-m-d H:i:s', $limit_ts);

    $ids_cot = [];
    $tx_ids_candidates = [];

    $where = "c.statut<>" . sql_quote('ok')
        . " AND c.date_creation<=" . sql_quote($limite);
    $candidates = sql_allfetsel('id_compte,id_transaction', 'spip_asso_cotisations AS c', $where) ?: array();
	include_spip('inc/association_adhesions_integrations');
	$transactions = association_adhesions_transactions_lire(array_column($candidates, 'id_transaction'));
    foreach ($candidates as $row) {
        $idt = intval($row['id_transaction'] ?? 0);
        if ($idt && (($transactions[$idt]['statut'] ?? '') === 'ok')) continue;
        $ids_cot[] = intval($row['id_compte']);
        if ($idt) $tx_ids_candidates[] = $idt;
        if (count($ids_cot) >= $lot) break;
    }

    if (!$ids_cot) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'limite' => $limite,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

	if (!$dry_run) {
		sql_query('START TRANSACTION');
	}

    // Suppression des cotisations
    $nb_cot = association_adhesions_maintenance_supprimer_cotisations($ids_cot, $dry_run);

    if ($nb_cot === false) {
		if (!$dry_run) {
			sql_query('ROLLBACK');
		}
        return [
            'supprimees' => 0,
            'ids' => $ids_cot,
            'limite' => $limite,
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_cotisations_non_encaissees_echouee'
        ];
    }

    // Suppression des transactions non encaissées associées
	$suppression_transactions = association_adhesions_transactions_supprimer_non_encaissees($tx_ids_candidates, $dry_run);
    if (!empty($suppression_transactions['erreur'])) {
		if (!$dry_run) sql_query('ROLLBACK');
        return [
            'supprimees' => intval($nb_cot),
            'ids' => $ids_cot,
            'limite' => $limite,
            'transactions_supprimees' => 0,
            'transactions_ids' => $suppression_transactions['ids'],
            'erreur' => 'suppression_transactions_cotisations_non_encaissees_echouee'
        ];
    }

	if (!$dry_run) {
		sql_query('COMMIT');
	}

    return [
        'supprimees' => intval($nb_cot),
        'ids' => $ids_cot,
        'limite' => $limite,
        'transactions_supprimees' => intval($suppression_transactions['supprimes']),
        'transactions_ids' => $suppression_transactions['ids']
    ];
}

/**
 * Supprime les lignes métier puis délègue les écritures à Comptabilité.
 *
 * Les identifiants reçus sont des id_compte afin de préserver le contrat des
 * rapports de maintenance historiques.
 */
function association_adhesions_maintenance_supprimer_cotisations(array $ids_compte, $dry_run = true) {
    $ids_compte = array_values(array_unique(array_filter(array_map('intval', $ids_compte))));
    if (!$ids_compte) {
        return 0;
    }

    $where = sql_in('id_compte', $ids_compte);
    $nombre = (int) sql_countsel('spip_asso_cotisations', $where);
    if ($dry_run || !$nombre) {
        return $nombre;
    }

	$ids_cotisations = array_values(array_filter(array_map('intval', array_column(
		sql_allfetsel('id_cotisation', 'spip_asso_cotisations', $where) ?: array(),
		'id_cotisation'
	))));
    $supprimees = sql_delete('spip_asso_cotisations', $where);
    if ($supprimees === false) {
        return false;
    }

    sql_delete(
        'spip_documents_liens',
        "objet='compte' AND " . sql_in('id_objet', $ids_compte)
    );
	if ($ids_cotisations) {
		sql_delete(
			'spip_documents_liens',
			"objet='cotisation' AND " . sql_in('id_objet', $ids_cotisations)
		);
	}
	if (association_adhesions_integration_module_actif('association_compta')) {
		include_spip('inc/association_compta_ecritures');
		foreach ($ids_compte as $id_compte) {
			association_compta_ecriture_supprimer($id_compte);
		}
	}
    return (int) $supprimees;
}
