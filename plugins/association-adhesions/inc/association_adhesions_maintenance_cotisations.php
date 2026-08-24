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
        'spip_asso_comptes AS c LEFT JOIN spip_auteurs AS a ON a.id_auteur=c.id_auteur',
		"c.objet='cotisation' AND a.id_auteur IS NULL",
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
    if ($ids_tx) {
        $in_tx = sql_in('t.id_transaction', $ids_tx);
        $res2 = sql_select(
            't.id_transaction',
            'spip_transactions AS t',
            $in_tx . ' AND t.statut=' . sql_quote('ok')
        );
        $tx_ok = [];
        while ($r = sql_fetch($res2)) {
            $tx_ok[] = intval($r['id_transaction']);
        }
        if ($tx_ok) {
            foreach ($orphans as $id_compte => $id_tx) {
                if ($id_tx && in_array($id_tx, $tx_ok, true)) {
                    $protegees[] = $id_compte;
                }
            }
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

    // Suppression des cotisations
    $in = sql_in('id_compte', $a_supprimer);
    $nb_cotisations = $dry_run
        ? sql_countsel('spip_asso_comptes', $in)
        : sql_delete('spip_asso_comptes', $in);

    if ($nb_cotisations === false) {
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
    $transactions_supprimees = 0;
    $transactions_ids = [];
    // On suppose la présence de la table spip_transactions
    {
         // Collecter les id_transaction des cotisations supprimées
         $tx_ids = [];
         foreach ($a_supprimer as $id_compte) {
             $id_tx = $orphans[$id_compte];
             if ($id_tx) {
                 $tx_ids[] = $id_tx;
             }
         }
         $tx_ids = array_values(array_unique($tx_ids));
         if ($tx_ids) {
             $in_tx = sql_in('id_transaction', $tx_ids) . ' AND statut<>' . sql_quote('ok');
             // Récupérer la liste exacte (filtrée statut <> ok) pour retour
             $res_tx = sql_select('id_transaction', 'spip_transactions', $in_tx);
             while ($r = sql_fetch($res_tx)) {
                 $transactions_ids[] = intval($r['id_transaction']);
             }
             if ($transactions_ids) {
                 $where_del = sql_in('id_transaction', $transactions_ids);
                 $transactions_supprimees = $dry_run
                     ? sql_countsel('spip_transactions', $where_del)
                     : sql_delete('spip_transactions', $where_del);
                 if ($transactions_supprimees === false) {
                     return [
                         'supprimees' => intval($nb_cotisations),
                         'ids' => $a_supprimer,
                         'protegees' => count($protegees),
                         'transactions_supprimees' => 0,
                         'transactions_ids' => $transactions_ids,
                         'erreur' => 'suppression_transactions_cotisations_orphelines_echouee'
                     ];
                 }
             }
         }
    }

    return [
        'supprimees' => intval($nb_cotisations),
        'ids' => $a_supprimer,
        'protegees' => count($protegees),
        'transactions_supprimees' => intval($transactions_supprimees),
        'transactions_ids' => $transactions_ids
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

    // Jointure pour exclure les cotisations liées à une transaction encaissée
    $where = "c.objet='cotisation'"
        . " AND c.statut_cotisation<>" . sql_quote('ok')
        . " AND c.date<=" . sql_quote($limite)
        . " AND (t.id_transaction IS NULL OR t.statut<>" . sql_quote('ok') . ")";
    $res = sql_select(
        'c.id_compte,c.id_transaction',
        'spip_asso_comptes AS c LEFT JOIN spip_transactions AS t ON t.id_transaction=c.id_transaction',
        $where,
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $idc = intval($row['id_compte']);
        $ids_cot[] = $idc;
        $idt = intval($row['id_transaction']);
        if ($idt) {
            $tx_ids_candidates[] = $idt;
        }
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

    // Suppression des cotisations
    $in_cot = sql_in('id_compte', $ids_cot);
    $nb_cot = $dry_run
        ? sql_countsel('spip_asso_comptes', $in_cot)
        : sql_delete('spip_asso_comptes', $in_cot);

    if ($nb_cot === false) {
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
    $transactions_supprimees = 0;
    $transactions_ids = [];
    if ($tx_ids_candidates) {
        $tx_ids_candidates = array_values(array_unique(array_filter($tx_ids_candidates)));
        if ($tx_ids_candidates) {
            $where_tx = sql_in('id_transaction', $tx_ids_candidates) . ' AND statut<>' . sql_quote('ok');
            // Lister exactement celles à supprimer
            $res_tx = sql_select('id_transaction', 'spip_transactions', $where_tx);
            while ($r = sql_fetch($res_tx)) {
                $transactions_ids[] = intval($r['id_transaction']);
            }
            if ($transactions_ids) {
                $in_tx_final = sql_in('id_transaction', $transactions_ids);
                $transactions_supprimees = $dry_run
                    ? sql_countsel('spip_transactions', $in_tx_final)
                    : sql_delete('spip_transactions', $in_tx_final);
                if ($transactions_supprimees === false) {
                    return [
                        'supprimees' => intval($nb_cot),
                        'ids' => $ids_cot,
                        'limite' => $limite,
                        'transactions_supprimees' => 0,
                        'transactions_ids' => $transactions_ids,
                        'erreur' => 'suppression_transactions_cotisations_non_encaissees_echouee'
                    ];
                }
            }
        }
    }

    return [
        'supprimees' => intval($nb_cot),
        'ids' => $ids_cot,
        'limite' => $limite,
        'transactions_supprimees' => intval($transactions_supprimees),
        'transactions_ids' => $transactions_ids
    ];
}
