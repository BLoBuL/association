<?php
/**
 * Fonctions pour l'analyse comptable des activités
 *
 * Ce fichier fournit des fonctions helper pour l'affichage et le calcul
 * des statistiques comptables des événements/activités avec support du filtrage.
 *
 * @package SPIP\Association\Comptabilite\Analyse
 */

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Récupère les statistiques d'un exercice comptable avec filtrage optionnel
 *
 * Cette fonction étend stats_compta_activites_exercice() en ajoutant
 * le support du filtrage par type d'opération (recettes/dépenses).
 *
 * @param int $exercice Année de l'exercice comptable
 * @param string $type Filtre: 'toutes', 'recette', 'depense'
 * @return array Statistiques avec les clés:
 *               - total_operations (int)
 *               - total_recettes (float)
 *               - total_depenses (float)
 *               - solde (float)
 *               - montant_moyen (float)
 *               - par_statut (array)
 *               - par_mode (array)
 */
function analyse_compta_activites_stats_exercice($exercice, $type = 'toutes', $vu = '1') {
    $stats = array(
        'total_operations' => 0,
        'total_recettes' => 0.0,
        'total_depenses' => 0.0,
        // 'solde' calculé après l'agrégation
        'montant_moyen' => 0.0,
        'par_statut' => array(),
        'par_mode' => array(),
    );
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];

    // Construire clause vu : supporte '>=0' pour inclure non validées
    $vu_clause = '';
    if (is_numeric($vu)) {
        $vu_clause = " AND c.vu=" . intval($vu);
    } elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
        $vu_clause = " AND c.vu >= 0";
    }

    // Nouveau WHERE : inclure les écritures liées aux activités ET aux événements
    $where = "(c.objet=" . sql_quote('evenement') . ")" .
             " AND c.date >= " . sql_quote($date_debut) .
             " AND c.date < " . sql_quote($date_fin) .
             $vu_clause;

    $res = sql_select(
        'c.id_compte, c.recette, c.depense, c.id_transaction, t.statut, t.mode',
        'spip_asso_comptes c LEFT JOIN spip_transactions t ON t.id_transaction = c.id_transaction',
        $where
    );

    while ($row = sql_fetch($res)) {
        $r = floatval($row['recette']);
        $d = floatval($row['depense']);
        // Ignorer totalement les lignes vides (ni recette ni depense)
        if ($r == 0 && $d == 0) {
            continue;
        }
        $inclure = true;
        if ($type === 'recette' && $r <= 0) { $inclure = false; }
        if ($type === 'depense' && $d <= 0) { $inclure = false; }

        // Totaux globaux (indépendants du filtre)
        $stats['total_recettes'] += $r;
        $stats['total_depenses'] += $d;
        if ($inclure) {
            $stats['total_operations']++;
            // Statut & mode : distinguer les écritures sans transaction
            $statut = ($row['id_transaction'] ? ($row['statut'] ?: 'inconnu') : 'hors_transaction');
            $mode   = ($row['id_transaction'] ? ($row['mode'] ?: 'inconnu') : 'hors_transaction');
            if (!isset($stats['par_statut'][$statut])) {
                $stats['par_statut'][$statut] = array('count' => 0, 'montant' => 0.0); }
            $stats['par_statut'][$statut]['count']++;
            $stats['par_statut'][$statut]['montant'] += ($r - $d);
            if (!isset($stats['par_mode'][$mode])) {
                $stats['par_mode'][$mode] = array('count' => 0, 'montant' => 0.0); }
            $stats['par_mode'][$mode]['count']++;
            $stats['par_mode'][$mode]['montant'] += ($r - $d);
        }
    }
    $stats['solde'] = $stats['total_recettes'] - $stats['total_depenses'];
    if ($stats['total_operations'] > 0) {
        $stats['montant_moyen'] = $stats['solde'] / $stats['total_operations'];
    }

    return $stats;
}

/**
 * Filtre SPIP pour stats_compta_activites_exercice avec filtrage
 */
function filtre_analyse_compta_activites_stats_exercice($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_stats_exercice($exercice, $type, $vu);
}

/**
 * Liste les événements payants d'un exercice avec filtrage optionnel
 *
 * Un événement est considéré "payant" s'il possède au moins une opération
 * avec recette > 0 ou dépense > 0.
 *
 * @param int $exercice Année de l'exercice comptable
 * @param string $type Filtre: 'toutes', 'recette', 'depense'
 * @return array Tableau d'événements avec les clés:
 *               - id_evenement (int)
 *               - titre (string)
 *               - date_evenement (string)
 *               - recettes (float)
 *               - depenses (float)
 *               - solde (float)
 *               - operations (int) : nombre d'opérations
 *               - rentabilite_percent (float|null)
 */
function analyse_compta_activites_lister_evenements_exercice($exercice, $type = 'toutes', $vu = '1') {
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];
    $rows_index = array(); // index par id_evenement

    // Construire clause vu
    $vu_clause = '';
    if (is_numeric($vu)) {
        $vu_clause = " AND c.vu=" . intval($vu);
    } elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
        $vu_clause = " AND c.vu >= 0";
    }

    // 1) Écritures via ACTIVITES -> événements (objet='activite')
    $where_activite = "c.objet=" . sql_quote('activite') .
                      " AND c.date >= " . sql_quote($date_debut) .
                      " AND c.date < " . sql_quote($date_fin) .
                      $vu_clause;
    $select_act = "a.id_evenement, e.titre, e.date_debut, e.date_fin, "
                . "SUM(c.recette) AS total_recettes, SUM(c.depense) AS total_depenses, COUNT(c.id_compte) AS nb_ops";
    $from_act = "spip_asso_comptes c "
              . "INNER JOIN spip_asso_activites a ON a.id_activite = c.id_objet "
              . "LEFT JOIN spip_evenements e ON e.id_evenement = a.id_evenement";
    $res_act = sql_select($select_act, $from_act, $where_activite, "a.id_evenement", "e.date_debut ASC");
    while ($row = sql_fetch($res_act)) {
        $id = intval($row['id_evenement']);
        $r = floatval($row['total_recettes']);
        $d = floatval($row['total_depenses']);
        if (!isset($rows_index[$id])) {
            $rows_index[$id] = array(
                'id_evenement' => $id,
                'titre' => $row['titre'],
                'date_evenement' => $row['date_debut'],
                'recettes' => 0.0,
                'depenses' => 0.0,
                'operations' => 0,
            );
        }
        $rows_index[$id]['recettes'] += $r;
        $rows_index[$id]['depenses'] += $d;
        $rows_index[$id]['operations'] += intval($row['nb_ops']);
    }

    // 2) Écritures directes sur EVENEMENTS (objet='evenement')
    $where_evt = "c.objet=" . sql_quote('evenement') .
                 " AND c.date >= " . sql_quote($date_debut) .
                 " AND c.date < " . sql_quote($date_fin) .
                 $vu_clause;
    $select_evt = "c.id_objet AS id_evenement, e.titre, e.date_debut, e.date_fin, "
                 . "SUM(c.recette) AS total_recettes, SUM(c.depense) AS total_depenses, COUNT(c.id_compte) AS nb_ops";
    $from_evt = "spip_asso_comptes c LEFT JOIN spip_evenements e ON e.id_evenement = c.id_objet";
    $res_evt = sql_select($select_evt, $from_evt, $where_evt, "c.id_objet", "e.date_debut ASC");
    while ($row = sql_fetch($res_evt)) {
        $id = intval($row['id_evenement']);
        $r = floatval($row['total_recettes']);
        $d = floatval($row['total_depenses']);
        if (!isset($rows_index[$id])) {
            $rows_index[$id] = array(
                'id_evenement' => $id,
                'titre' => $row['titre'],
                'date_evenement' => $row['date_debut'],
                'recettes' => 0.0,
                'depenses' => 0.0,
                'operations' => 0,
            );
        }
        $rows_index[$id]['recettes'] += $r;
        $rows_index[$id]['depenses'] += $d;
        $rows_index[$id]['operations'] += intval($row['nb_ops']);
    }

    // Transformation + filtrage selon type
    $rows = array();
    foreach ($rows_index as $evt) {
        $r = $evt['recettes'];
        $d = $evt['depenses'];
        if ($r == 0 && $d == 0) { continue; }
        if ($type === 'recette' && $r <= 0) { continue; }
        if ($type === 'depense' && $d <= 0) { continue; }
        $solde = $r - $d;
        $rentabilite = ($d > 0) ? ($solde / $d * 100) : null;
        $evt['solde'] = $solde;
        $evt['rentabilite_percent'] = $rentabilite;
        $rows[] = $evt;
    }

    // Tri par date_evenement asc (déjà mais après fusion possible désordre)
    usort($rows, function($a, $b){
        return strcmp($a['date_evenement'], $b['date_evenement']);
    });
    return $rows;
}

/**
 * Filtre SPIP pour lister les événements payants avec filtrage
 */
function filtre_analyse_compta_activites_lister_evenements_exercice($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);
}

/**
 * Calcule les totaux agrégés pour le tableau des événements
 *
 * Cette fonction parcourt la liste d'événements et calcule :
 * - Total des recettes
 * - Total des dépenses
 * - Solde global
 * - Rentabilité globale
 *
 * @param array $evenements Tableau d'événements retourné par analyse_compta_activites_lister_evenements_exercice()
 * @return array Totaux avec les clés:
 *               - total_recettes (float)
 *               - total_depenses (float)
 *               - solde (float)
 *               - rentabilite_percent (float|null)
 *               - nb_evenements (int)
 */
function analyse_compta_activites_totaux_evenements($evenements) {
    $totaux = array(
        'total_recettes' => 0.0,
        'total_depenses' => 0.0,
        'solde' => 0.0,
        'rentabilite_percent' => null,
        'nb_evenements' => 0,
    );

    if (!is_array($evenements) || empty($evenements)) {
        return $totaux;
    }

    foreach ($evenements as $evt) {
        $totaux['total_recettes'] += floatval($evt['recettes']);
        $totaux['total_depenses'] += floatval($evt['depenses']);
        $totaux['nb_evenements']++;
    }

    $totaux['solde'] = $totaux['total_recettes'] - $totaux['total_depenses'];

    if ($totaux['total_depenses'] > 0) {
        $totaux['rentabilite_percent'] = ($totaux['solde'] / $totaux['total_depenses']) * 100;
    }

    return $totaux;
}

/**
 * Filtre SPIP pour calculer les totaux d'événements
 */
function filtre_analyse_compta_activites_totaux_evenements($evenements) {
    return analyse_compta_activites_totaux_evenements($evenements);
}

/**
 * Normalise le paramètre de filtre 'type'
 *
 * @param string $type Valeur brute du paramètre
 * @return string 'toutes', 'recette' ou 'depense'
 */
function analyse_compta_activites_normaliser_type($type) {
    // Gestion des valeurs nulles, vides ou variantes
    if (is_null($type) || $type === '' || $type === 'tout') {
        $type = 'toutes';
    }

    // Validation : on n'accepte que des valeurs valides
    if (!in_array($type, ['toutes', 'recette', 'depense'], true)) {
        $type = 'toutes';
    }

    return $type;
}

/**
 * Filtre SPIP pour normaliser le type
 */
function filtre_analyse_compta_activites_normaliser_type($type) {
    return analyse_compta_activites_normaliser_type($type);
}

/**
 * Retourne le libellé français du filtre de type
 *
 * @param string $type 'toutes', 'recette' ou 'depense'
 * @return string Libellé en français
 */
function analyse_compta_activites_libelle_type($type) {
    switch ($type) {
        case 'recette':
            return 'Recettes uniquement';
        case 'depense':
            return 'Dépenses uniquement';
        case 'toutes':
        default:
            return 'Toutes les opérations';
    }
}

/**
 * Filtre SPIP pour le libellé du type
 */
function filtre_analyse_compta_activites_libelle_type($type) {
    return analyse_compta_activites_libelle_type($type);
}

/**
 * Compte le nombre d'événements payants pour un exercice
 *
 * @param int $exercice Année de l'exercice
 * @param string $type Filtre optionnel
 * @return int Nombre d'événements
 */
function analyse_compta_activites_compter_evenements($exercice, $type = 'toutes', $vu = '1') {
    $evenements = analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);
    return count($evenements);
}

/**
 * Filtre SPIP pour compter les événements
 */
function filtre_analyse_compta_activites_compter_evenements($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_compter_evenements($exercice, $type, $vu);
}
