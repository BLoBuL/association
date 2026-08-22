<?php
// PHP
/**
 * Fonctions pour l'export CSV de la comptabilité d'un évènement.
 */
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('base/abstract_sql');

/**
 * Retourne un montant signé pour une ligne comptable.
 * - Recette > 0 -> montant positif
 * - Dépense > 0 -> montant négatif
 * - Sinon 0
 *
 * Utilisation dans le squelette:
 * [(#RECETTE|montant_signe{#DEPENSE})]
 *
 * @param float|string $recette
 * @param float|string $depense
 * @return float|int
 */
function montant_signe($recette, $depense) {
    $r = floatval($recette);
    $d = floatval($depense);
    if ($r > 0) {
        return $r;
    }
    if ($d > 0) {
        return -$d;
    }
    return 0;
}

/**
 * Total des recettes d'un évènement.
 *
 * @param int $id_evenement
 * @return float
 */
function total_recettes_evenement($id_evenement) {
    $id = intval($id_evenement);
    $sum = sql_getfetsel('SUM(recette)', 'spip_asso_comptes', 'id_objet=' . $id . ' AND objet="evenement"');
    return $sum ? floatval($sum) : 0.0;
}

/**
 * Total des dépenses d'un évènement.
 *
 * @param int $id_evenement
 * @return float
 */
function total_depenses_evenement($id_evenement) {
    $id = intval($id_evenement);
    $sum = sql_getfetsel('SUM(depense)', 'spip_asso_comptes', 'id_objet=' . $id . ' AND objet="evenement"');
    return $sum ? floatval($sum) : 0.0;
}

/**
 * Solde d'un évènement = recettes - dépenses.
 *
 * @param int $id_evenement
 * @return float
 */
function solde_evenement($id_evenement) {
    return total_recettes_evenement($id_evenement) - total_depenses_evenement($id_evenement);
}

/**
 * Filtre: retravaille la justification.
 * Si journal commence par "activite" et contient un identifiant numérique,
 * retourne "Participation de NOM Prenom" en récupérant les infos de spip_asso_activites.
 *
 * Utilisation dans le squelette:
 * [(#JUSTIFICATION|justification_compte{#JOURNAL})]
 *
 * @param string $justification
 * @param string $journal
 * @return string
 */
function filtre_justification_compte_dist($justification, $journal = '') {
    $journal = (string)$journal;

    // Uniquement le format "activite|<ID>"
    if ($journal !== '' && preg_match('/^\s*activite\|([0-9]+)\s*$/i', $journal, $m)) {
        $id_activite = intval($m[1]);

        if ($id_activite > 0) {
            $row = sql_fetsel(
                'nom_inscrit, prenom_inscrit',
                'spip_asso_activites',
                'id_activite=' . $id_activite
            );

            if ($row) {
                $nom = trim((string)$row['nom_inscrit']);
                $prenom = trim((string)$row['prenom_inscrit']);

                if ($nom !== '' || $prenom !== '') {
                    $identite = trim($nom . ' ' . $prenom);
                    return 'Participation de ' . $identite;
                }
            }
        }
    }

    // Par défaut, conserver la justification d'origine
    return (string)$justification;
}
