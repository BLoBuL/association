<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Liste (agrégée) des événements payants d'un exercice comptable.
 * Un événement est considéré payant s'il possède au moins une opération (recette ou dépense > 0).
 * Retourne un tableau indexé numériquement de lignes:
 *  id_evenement, titre, date_evenement, recettes, depenses, solde, operations, rentabilite_percent
 */
function stats_compta_activites_lister_evenements_exercice($exercice){
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];
    $rows = array();
    // Agrégation des comptes liés aux activités (journal activite|id_activite) par événement.
    // On passe par la table spip_asso_activites pour obtenir l'id_evenement
    // On ne tient pas compte des lignes sans mouvement (recette = 0 et depense = 0).
    $res = sql_select(
        "a.id_evenement, e.titre, e.date_debut, e.date_fin, SUM(c.recette) AS total_recettes, SUM(c.depense) AS total_depenses, COUNT(c.id_compte) AS nb_ops",
        "spip_asso_comptes c 
         INNER JOIN spip_asso_activites a ON a.id_activite = SUBSTRING(c.journal, 10)
         LEFT JOIN spip_evenements e ON e.id_evenement = a.id_evenement",
        "c.journal LIKE " . sql_quote('activite|%') .
        " AND c.date >= " . sql_quote($date_debut) . " AND c.date < " . sql_quote($date_fin),
        "a.id_evenement",
        "e.date_debut ASC"
    );
    while($row = sql_fetch($res)){
        $r = floatval($row['total_recettes']);
        $d = floatval($row['total_depenses']);
        if($r == 0 && $d == 0){
            continue; // pas payant
        }
        $solde = $r - $d;
        $rentabilite = ($d > 0) ? ($solde / $d * 100) : null; // null si aucune dépense
        $rows[] = array(
            'id_evenement' => intval($row['id_evenement']),
            'titre' => $row['titre'],
            'date_evenement' => $row['date_debut'],
            'recettes' => $r,
            'depenses' => $d,
            'solde' => $solde,
            'operations' => intval($row['nb_ops']),
            'rentabilite_percent' => $rentabilite,
        );
    }
    return $rows;
}
/**
 * Filtre SPIP exposant la liste agrégée des événements payants d'un exercice.
 */
function filtre_stats_compta_activites_lister_evenements_exercice($exercice){
    return stats_compta_activites_lister_evenements_exercice($exercice);
}

