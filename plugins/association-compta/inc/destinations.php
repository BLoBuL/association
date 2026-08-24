<?php
include_spip('inc/association/utils');

/**
 * fonction permettant d'ajouter/modifier les destinations comptables (presente dans $_POST)
 * a une operation comptable.
 * The destinations comptables are taken from the request.
 * @param int $id_compte
 * @param float $recette
 * @param float $depense
 * @param array $repartion
 *    Tableau des id_destination=>montant a ventiler.
 *  Quand vide, les ventilations sont recherchees dans $_POST['id_dest'] et $_POST['montant_dest']
 */
function ajouter_destinations($id_compte, $recette, $depense, $destination_map = []): void
{
    if (!destinations_are_enabled()) {
        return;
    }

    if ($recette>0) {
        $attribution_montant = "recette";
    } else {
        $attribution_montant = "depense";
    }

    if (count($destination_map)) { // usage normal
        $destinationIds = array_keys($destination_map);
        $destinationMontants = array_values($destination_map);
    } else { // donnees de formulaire Associaspip
        $destinationIds = _request('id_dest');
        $destinationMontants = _request('montant_dest');
    }

    if (!count($destinationIds)
        || !count($destinationMontants)
    ) {
        return;
    }

    /* on efface de la table destination_op toutes les entrees correspondant a cette operation  si on en trouve*/
    sql_delete("spip_asso_destination_op", "id_compte=$id_compte");

    if (count($destinationIds) > 1) {
        foreach ($destinationIds as $index => $id_destination) {
            $montant = association_recupere_montant($destinationMontants[$index]);
            sql_insertq('spip_asso_destination_op', array(
                'id_compte' => $id_compte,
                'id_destination' => $id_destination,
                $attribution_montant => $montant));
        }
    } else {
        // beware first index of destination is 1, not 0!
        // une seule destination, le montant peut ne pas avoir ete precise,
        // on entre directement le total recette+depense
        sql_insertq('spip_asso_destination_op', array(
            'id_compte' => $id_compte,
            'id_destination' => $destinationIds[0],
            $attribution_montant => $depense+$recette));
    }
}

/**
 * Activites and cotisations have always the default destination for the given montant.
 * If not specified, we ignore destinations.
 * @param $montant
 * @param $dc_name
 * @return array
 */
function create_destination_map_for_montant(
    $dc_name,
    $montant
) {
    // dc_activites avail from spip2.2
    // no destination in activites form
    if (destinations_are_enabled() and default_destination_is_set($dc_name)) {
        return [
            $GLOBALS['association_metas'][$dc_name] => $montant
        ];
    }
    return [];
}

/* les destinations comptables sont activees */
// TODO: on a per-user basis? here global..
function destinations_are_enabled()
{
    return association_valeur_bdd_est_vraie($GLOBALS['association_metas']['destinations']);
}

function default_destination_is_set($dc_name)
{
    return array_key_exists($dc_name, $GLOBALS['association_metas'])
        and strlen($GLOBALS['association_metas'][$dc_name]);
}

// Fonction pour preparer la liste des destinations comptables
function preparer_liste_asso_destination_comptable($format='data_saisies'){
    $asso_destinations= sql_allfetsel('id_destination,intitule','spip_asso_destination','');
// Initialize an empty array
    $asso_destinations_array = array();
// Loop through the fetched data and reformat it
    foreach ($asso_destinations as $asso_destination) {
        $asso_destinations_array[$asso_destination['id_destination']] = $asso_destination['intitule'];
    }

    if($format == 'data_saisies') {
        return saisies_tableau2chaine($asso_destinations_array);
    }else{
        return $asso_destinations_array;
    }
}
