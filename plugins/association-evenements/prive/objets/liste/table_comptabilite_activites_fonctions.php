<?php
/**
 * Fonctions pour la liste de comptabilité des activités
 *
 * Ce fichier fournit les fonctions helper pour le filtre d'affichage
 * des opérations comptables des événements (toutes/recettes/dépenses).
 *
 * UTILISATION:
 * Le filtre utilise le paramètre d'URL 'type' avec les valeurs suivantes:
 * - 'toutes' (ou vide) : affiche toutes les opérations
 * - 'recette' : affiche uniquement les opérations avec recette > 0
 * - 'depense' : affiche uniquement les opérations avec depense > 0
 *
 * EXEMPLE D'URL:
 * ?exec=voir_evenement&id_evenement=123&type=recette
 *
 * @package SPIP\Association\Comptabilite
 */

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Récupère le filtre actif pour l'affichage des opérations comptables
 *
 * Cette fonction normalise la valeur du paramètre 'type' depuis l'URL
 * et retourne une valeur sûre pour l'utilisation dans les templates.
 *
 * @return string 'toutes', 'recette' ou 'depense'
 */
function table_comptabilite_activites_get_filtre_sens() {
    $sens = _request('type');

    // Normaliser les variantes et traiter les valeurs nulles/vides
    if (is_null($sens) || $sens === '' || $sens === 'tout') {
        $sens = 'toutes';
    }

    // Valider la valeur
    if (!in_array($sens, ['recette', 'depense', 'toutes'], true)) {
        $sens = 'toutes';
    }

    return $sens;
}

/**
 * Compte le nombre d'opérations selon le filtre actif
 *
 * Cette fonction compte les opérations comptables d'un événement
 * en appliquant le filtre recette/dépense si nécessaire.
 *
 * @param int $id_evenement ID de l'événement
 * @param string $sens 'toutes', 'recette' ou 'depense'
 * @return int Nombre d'opérations correspondantes
 */
function table_comptabilite_activites_compter_operations($id_evenement, $sens = 'toutes', $vu = '1') {
	include_spip('inc/association_compta_ecritures');
	$criteres = array(
		'objet' => 'evenement',
		'id_objet' => (int) $id_evenement,
		'vu' => table_comptabilite_activites_normaliser_vu($vu),
	);
	if (in_array($sens, array('recette', 'depense'), true)) {
		$criteres['sens'] = $sens;
	}
	return count(association_compta_ecritures_lister($criteres, array('champs' => 'id_compte')));
}

function table_comptabilite_activites_normaliser_vu($vu) {
	if (is_numeric($vu)) {
		return (int) $vu;
	}
	return is_string($vu) && preg_match('#^>=\s*0$#', $vu) ? '>=0' : 1;
}

/**
 * Génère les critères de boucle SPIP selon le sens
 *
 * @param string $sens 'toutes', 'recette' ou 'depense'
 * @return string Critères de boucle SPIP (ex: '{recette>0}')
 */
function table_comptabilite_activites_criteres_sens($sens = 'toutes') {
    switch ($sens) {
        case 'recette':
            return '{recette>0}';
        case 'depense':
            return '{depense>0}';
        default:
            return '';
    }
}

/**
 * Retourne les statistiques et les totaux pour un événement
 *
 * Cette fonction retourne à la fois les **nombre d'opérations** par type
 * et les **totaux monétaires** (somme des recettes, somme des dépenses)
 * pour l'événement fourni.
 *
 * @param int $id_evenement ID de l'événement
 * @return array Tableau associatif avec les clés:
 *               - 'nb_recettes' : nombre de recettes
 *               - 'nb_depenses' : nombre de dépenses
 *               - 'nb_toutes' : nombre total d'opérations
 *               - 'total_recettes' : somme des montants de recette (float)
 *               - 'total_depenses' : somme des montants de dépense (float)
 *               - 'solde' : total_recettes - total_depenses (float)
 */
function table_comptabilite_activites_stats($id_evenement, $vu = '1') {
    // Comptages
    $counts = [
        'nb_recettes' => table_comptabilite_activites_compter_operations($id_evenement, 'recette', $vu),
        'nb_depenses' => table_comptabilite_activites_compter_operations($id_evenement, 'depense', $vu),
        'nb_toutes' => table_comptabilite_activites_compter_operations($id_evenement, 'toutes', $vu),
    ];

    // Montants
    $montants = table_comptabilite_activites_montants($id_evenement, $vu);

    // Moyennes (éviter division par zéro)
    $avg_recette = 0.0;
    $avg_depense = 0.0;
    $avg_operation = 0.0;

    if (!empty($counts['nb_recettes'])) {
        $avg_recette = ($montants['total_recettes'] / $counts['nb_recettes']);
    }
    if (!empty($counts['nb_depenses'])) {
        $avg_depense = ($montants['total_depenses'] / $counts['nb_depenses']);
    }
    if (!empty($counts['nb_toutes'])) {
        $avg_operation = (($montants['total_recettes'] + $montants['total_depenses']) / $counts['nb_toutes']);
    }

    $extras = [
        'avg_recette' => $avg_recette,
        'avg_depense' => $avg_depense,
        'avg_operation' => $avg_operation,
    ];

    return array_merge($counts, $montants, $extras);
}

/**
 * Retourne les totaux monétaires (sommes) pour un événement
 *
 * @param int $id_evenement
 * @return array Tableau associatif avec les clés:
 *               - 'total_recettes' (float)
 *               - 'total_depenses' (float)
 *               - 'solde' (float) => total_recettes - total_depenses
 */
function table_comptabilite_activites_montants($id_evenement, $vu = '1') {
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister(array(
		'objet' => 'evenement',
		'id_objet' => (int) $id_evenement,
		'vu' => table_comptabilite_activites_normaliser_vu($vu),
	), array('champs' => 'recette,depense'));
	$total_recettes = 0.0;
	$total_depenses = 0.0;
	foreach ($ecritures as $ecriture) {
		$total_recettes += (float) ($ecriture['recette'] ?? 0);
		$total_depenses += (float) ($ecriture['depense'] ?? 0);
	}

    $solde = $total_recettes - $total_depenses;

    return [
        'total_recettes' => $total_recettes,
        'total_depenses' => $total_depenses,
        'solde' => $solde,
        // backward-compatible key attendu par le template
        'total_solde' => $solde,
    ];
}
