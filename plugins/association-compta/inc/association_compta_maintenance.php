<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Maintenance des écritures comptables, propriété de Comptabilité.
 */

function asso_supprimer_comptes_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['supprimes' => 0];
    $in = sql_in('id_auteur', $ids_auteurs);

    $nb = $dry_run ? sql_countsel('spip_asso_comptes', $in) : sql_delete('spip_asso_comptes', $in);
    return ['supprimes' => intval($nb)];
}

