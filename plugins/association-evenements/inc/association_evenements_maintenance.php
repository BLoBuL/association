<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Maintenance des inscriptions et participations, propriété du plugin Événements.
 */

function asso_trouver_inscriptions_non_validees_anciennes($limite, $lot = 1000) {
    $rows = [];
    $res = sql_select(
        'id_activite,id_transaction',
        'spip_asso_activites',
        "statut IN ('ok','attente') AND date < " . sql_quote($limite),
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $rows[] = [
            'id_activite' => intval($row['id_activite']),
            'id_transaction' => intval($row['id_transaction'])
        ];
    }
    return $rows;
}

/**
 * Supprimer des inscriptions par leurs identifiants.
 *
 * @param int[] $ids_activite
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_inscriptions_par_ids(array $ids_activite, $dry_run = true) {
    if (!$ids_activite) return ['supprimees' => 0];
    $in = sql_in('id_activite', $ids_activite);
    $nb = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);
    return ['supprimees' => intval($nb)];
}

/**
 * Supprimer les transactions liées à des inscriptions (si table existante).
 *
 * @param array $inscriptions Lignes avec `id_transaction`
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_transactions_inscriptions(array $inscriptions, $dry_run = true) {
    $ids_tx = array_values(array_unique(array_filter(array_map('intval', array_column($inscriptions, 'id_transaction')))));
    if (!$ids_tx) return ['supprimes' => 0, 'ids_activite_supprimables' => array_column($inscriptions, 'id_activite')];
    include_spip('inc/association_evenements_paiements');
    $resultat = association_evenements_transactions_supprimer_non_encaissees($ids_tx, $dry_run);
    $protegees = array_flip($resultat['protegees'] ?? array());
    $resultat['ids_activite_supprimables'] = array_values(array_map('intval', array_column(array_filter(
        $inscriptions,
        function ($inscription) use ($protegees) {
            $id_transaction = (int) ($inscription['id_transaction'] ?? 0);
            return !$id_transaction || !isset($protegees[$id_transaction]);
        }
    ), 'id_activite')));
    return $resultat;
}

/**
 * Anonymiser les inscriptions d'une liste d'auteurs (RGPD).
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_anonymiser_inscriptions_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['anonymisees' => 0];
    $in = sql_in('id_auteur', $ids_auteurs);
    if ($dry_run) {
        return [
            'anonymisees' => intval(sql_countsel('spip_asso_activites', $in)),
            'dry_run' => true,
        ];
    }
    $maj = [
        'nom_inscrit' => '',
        'prenom_inscrit' => '',
        'email_inscrit' => '',
        'tel_inscrit' => '',
        'ip_inscrit' => '',
    ];
    $n = sql_updateq('spip_asso_activites', $maj, $in);
    if ($n === false) {
        return ['anonymisees' => 0, 'dry_run' => false, 'erreur' => 'anonymisation_inscriptions_echouee'];
    }
    return ['anonymisees' => intval($n), 'dry_run' => false];
}


/**
 * Supprimer les cotisations orphelines (sans auteur) non rattachées à une transaction encaissée.
 * Maintenant: si une transaction liée existe et n'est PAS réglée (statut <> 'ok'), on la supprime aussi.
 *
 * Ne PAS supprimer la cotisation si une transaction liée est `ok`.
 *
 * Retour:
 *  - supprimees: nombre de cotisations supprimées
 *  - ids: ids des cotisations supprimées
 *  - protegees: nombre de cotisations conservées car transaction encaissée
 *  - transactions_supprimees: nombre de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param bool $dry_run
 * @param int  $lot
 * @return array
 */

function asso_supprimer_participations_evenements_orphelines($dry_run = true, $lot = 1000) {
    // Participations dont l'événement est orphelin et non réglées
    $ids = [];
    $tx_ids = [];

    $from = 'spip_asso_activites AS a
             LEFT JOIN spip_evenements AS e ON e.id_evenement=a.id_evenement';

    $where = 'e.id_evenement IS NULL';

    $res = sql_select(
        'a.id_activite, a.id_transaction',
        $from,
        $where,
        '',
        ''
    );
    $candidats = [];
    while ($row = sql_fetch($res)) {
        $candidats[] = $row;
        if (!empty($row['id_transaction'])) $tx_ids[] = intval($row['id_transaction']);
    }
    include_spip('inc/association_evenements_paiements');
    $transactions = association_evenements_transactions_lire($tx_ids);
    foreach ($candidats as $row) {
        $id_transaction = (int) ($row['id_transaction'] ?? 0);
        if ($id_transaction && (($transactions[$id_transaction]['statut'] ?? '') === 'ok')) continue;
        $ids[] = intval($row['id_activite']);
    }

    if (!$ids) return ['supprimees' => 0];

    // Dédoublonnage et limitation par lot
    $ids = array_values(array_unique($ids));
    if (count($ids) > $lot) {
        $ids = array_slice($ids, 0, $lot);
    }

    $in = sql_in('id_activite', $ids);
    $nb = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);

    if ($nb === false) {
        return [
            'supprimees' => 0,
            'ids' => $ids,
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_participations_orphelines_echouee'
        ];
    }

    // Supprimer les transactions liées non encaissées
    $tx_ids_final = array_values(array_unique(array_filter(array_map(
        function ($row) use ($ids) {
            return in_array((int) $row['id_activite'], $ids, true) ? (int) $row['id_transaction'] : 0;
        },
        $candidats
    ))));
    $suppression_transactions = association_evenements_transactions_supprimer_non_encaissees($tx_ids_final, $dry_run);
    if (!empty($suppression_transactions['erreur'])) {
        return [
            'supprimees' => intval($nb),
            'ids' => $ids,
            'transactions_supprimees' => 0,
            'transactions_ids' => $suppression_transactions['ids'],
            'erreur' => 'suppression_transactions_participations_orphelines_echouee'
        ];
    }

    return [
        'supprimees' => intval($nb),
        'ids' => $ids,
        'transactions_supprimees' => intval($suppression_transactions['supprimes']),
        'transactions_ids' => $suppression_transactions['ids']
    ];
}

/**
 * Supprimer les participations aux événements obsolètes :
 * Inscriptions pour des événements passés depuis plus de N jours
 * et dont le statut n'est pas 'ok'.
 * Ne rien supprimer si l'inscription est liée à une transaction réglée (statut='ok').
 * Supprimer les transactions liées si elles existent et ne sont pas réglées.
 *
 * Retour:
 *  - supprimees: nb d'inscriptions supprimées
 *  - ids: ids des inscriptions supprimées
 *  - protegees: nb d'inscriptions conservées car transaction réglée
 *  - transactions_supprimees: nb de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_participations_evenements_obsoletes($dry_run = true, $lot = 1000, $jours = 90) {
    // Utiliser la valeur fournie ($jours) ou 90 par défaut
    $jours = intval($jours) > 0 ? intval($jours) : 90;

    // Calculer la date limite (activites dont date < limite seront considérées obsolètes)
    $limite = date('Y-m-d H:i:s', time() - ($jours * 86400));

    $ids = [];
    $tx_ids = [];
    $protegees = [];

    $from = 'spip_asso_activites AS a
             LEFT JOIN spip_evenements AS e ON e.id_evenement=a.id_evenement';

    // Critères : événement passé (e.date < limite) OR si pas de table evenements on utilise a.date < limite
    // et statut de l'inscription différent de 'ok'
    $where = "( (e.date_fin IS NOT NULL AND e.date_fin < " . sql_quote($limite) . ") OR (e.id_evenement IS NULL AND a.date < " . sql_quote($limite) . ") )"
        . " AND (a.statut IS NULL OR a.statut<>" . sql_quote('ok') . ")";

    $res = sql_select(
        'a.id_activite, a.id_transaction',
        $from,
        $where,
        '',
        '',
        intval($lot)
    );

    $candidats = [];
    while ($row = sql_fetch($res)) {
        $candidats[] = $row;
        if (!empty($row['id_transaction'])) $tx_ids[] = (int) $row['id_transaction'];
    }
    include_spip('inc/association_evenements_paiements');
    $transactions = association_evenements_transactions_lire($tx_ids);
    foreach ($candidats as $row) {
        $id_act = intval($row['id_activite']);
        $ids[] = $id_act;
        $id_tx = !empty($row['id_transaction']) ? intval($row['id_transaction']) : 0;
        if ($id_tx) {
            // Si la transaction est réglée, on protège l'inscription
            if (($transactions[$id_tx]['statut'] ?? '') === 'ok') {
                $protegees[] = $id_act;
            } else {
                $tx_ids[] = $id_tx;
            }
        }
    }

    if (!$ids) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => 0,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    // Exclure les inscriptions protégées (liées à transaction 'ok')
    $ids_a_supprimer = array_values(array_diff(array_unique($ids), array_unique($protegees)));
    if (!$ids_a_supprimer) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    // Limiter au lot
    if (count($ids_a_supprimer) > $lot) {
        $ids_a_supprimer = array_slice($ids_a_supprimer, 0, $lot);
    }

    // Supprimer les inscriptions
    $in = sql_in('id_activite', $ids_a_supprimer);
    $nb_suppr = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);

    if ($nb_suppr === false) {
        return [
            'supprimees' => 0,
            'ids' => $ids_a_supprimer,
            'protegees' => count(array_unique($protegees)),
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_participations_obsoletes_echouee'
        ];
    }

    // Supprimer les transactions non réglées associées aux inscriptions supprimées
    $tx_candidats = array_values(array_unique(array_filter(array_map(
        function ($row) use ($ids_a_supprimer) {
            return in_array((int) $row['id_activite'], $ids_a_supprimer, true) ? (int) $row['id_transaction'] : 0;
        },
        $candidats
    ))));
    $suppression_transactions = association_evenements_transactions_supprimer_non_encaissees($tx_candidats, $dry_run);
    if (!empty($suppression_transactions['erreur'])) {
        return [
            'supprimees' => intval($nb_suppr),
            'ids' => $ids_a_supprimer,
            'protegees' => count(array_unique($protegees)),
            'transactions_supprimees' => 0,
            'transactions_ids' => $suppression_transactions['ids'],
            'erreur' => 'suppression_transactions_participations_obsoletes_echouee'
        ];
    }

    return [
        'supprimees' => intval($nb_suppr),
        'ids' => $ids_a_supprimer,
        'protegees' => count(array_unique($protegees)),
        'transactions_supprimees' => intval($suppression_transactions['supprimes']),
        'transactions_ids' => $suppression_transactions['ids']
    ];
}

/**
 * Supprimer les urls de redirection obsolètes.
 * On boucle sur type et id_objet pour vérifier l'existence de l'objet.
 * Les urls n'ont pas d'ID, on utilise l'url elle-même comme identifiant.
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */

