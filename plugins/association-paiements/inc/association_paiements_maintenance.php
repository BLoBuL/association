<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Maintenance des transactions, propriété de Paiements.
 */

function asso_supprimer_transactions_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['supprimes' => 0, 'ignore' => true];
    $in = sql_in('id_auteur', $ids_auteurs);

    $nb = $dry_run ? sql_countsel('spip_transactions', $in) : sql_delete('spip_transactions', $in);
    return ['supprimes' => intval($nb)];
}

/**
 * Anonymiser les auteurs et leurs données métier via l'API RGPD du plugin.
 */

function asso_supprimer_transactions_orphelines($dry_run = true, $lot = 1000) {
    // Préparer une limite temporelle (1 an)
    $limite = date('Y-m-d H:i:s', time() - 365 * 86400);

    // WHERE par type / condition (chacun dans sa variable)
    // Colonnes supposées présentes
    $where_date = 't.date_transaction<=' . sql_quote($limite);

    $where_commandes = '';
    if (test_plugin_actif('commandes')) {
        $where_commandes = '(t.id_commande IS NULL OR t.id_commande=0 OR (t.id_commande>0 AND NOT EXISTS (SELECT 1 FROM spip_commandes AS cmd WHERE cmd.id_commande = t.id_commande)))';
    } else {
        $where_commandes = '(t.id_commande IS NULL OR t.id_commande=0)';
    }

    $where_formidable = '';
    if (test_plugin_actif('formidable')) {
        $where_formidable = 'NOT (t.tracking_id>0 AND t.parrain LIKE ' . sql_quote('formidable:%') . ' AND EXISTS (SELECT 1 FROM spip_formulaires_reponses AS r WHERE r.id_formulaires_reponse = t.tracking_id))';
    } else {
        $where_formidable = 'NOT (t.tracking_id>0 AND t.parrain LIKE ' . sql_quote('formidable:%') . ')';
    }

    // Conditions liées aux objets de l'application : utiliser NOT EXISTS pour éviter les duplications dues aux JOIN
    $where_comptes = 'NOT EXISTS (SELECT 1 FROM spip_asso_comptes AS c WHERE c.id_transaction = t.id_transaction)';
    $where_activites = 'NOT EXISTS (SELECT 1 FROM spip_asso_activites AS a WHERE a.id_transaction = t.id_transaction)';

    // Statut de la transaction
    $where_statut = 't.statut<>' . sql_quote('ok');

    // Composer la clause WHERE finale depuis les morceaux non vides
    $where_parts = array_filter([
        $where_comptes,
        $where_activites,
        $where_statut,
        $where_date,
        $where_commandes,
        $where_formidable
    ]);

    $where = $where_parts ? implode(' AND ', $where_parts) : '0'; // '0' pour sécurité

    $ids = [];
    // On sélectionne uniquement la table des transactions et on laisse le WHERE tester l'existence dans les tables liées
    $res = sql_select(
        't.id_transaction',
        'spip_transactions AS t',
        $where,
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $ids[] = intval($row['id_transaction']);
    }
    if (!$ids) return ['supprimees' => 0];

    $in = sql_in('id_transaction', $ids);
    $nb = $dry_run ? sql_countsel('spip_transactions', $in) : sql_delete('spip_transactions', $in);

    if ($nb === false) {
        association_log('cron', 'Erreur suppression transactions orphelines (' . $where . ')', 'erreur');
    }

    return ['supprimees' => intval($nb), 'ids' => $ids, 'limite' => ($where_date ? $limite : null)];
}

/**
 * Supprimer les cotisations non encaissées trop anciennes, uniquement si
 * aucune transaction encaissée ne leur est liée.
 * Puis supprimer les transactions liées non encaissées.
 *
 * Critères:
 *  - c.objet = 'cotisation'
 *  - c.statut_cotisation <> 'ok'
 *  - c.date <= $limite (N mois en arrière)
 *  - (si table transactions) (t.id_transaction IS NULL OR t.statut <> 'ok')
 *
 * Retour:
 *  - supprimees: nb cotisations supprimées
 *  - ids: ids des cotisations supprimées
 *  - limite: date limite utilisée
 *  - transactions_supprimees: nb de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param int $maintenant Timestamp courant
 * @param int $mois Nombre de mois de rétention
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */

