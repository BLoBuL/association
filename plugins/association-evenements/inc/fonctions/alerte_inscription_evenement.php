<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
/**
* // *
 * @param string $id_evenement
 * @return false|string
 */
function alerte_inscription_evenement($id_evenement,$id_activite){
    /*Infos sur l'événement*/    
    $affichage_dans_activites = affichage_dans_activites($id_evenement);
    $gestions_places = gestions_places($id_evenement);
    
    // Initialisation de la variable d'alerte à false par défaut
    $alerte_inscription_evenement = false;

    // Vérification de sécurité pour s'assurer que les données nécessaires existent
    if (!is_array($affichage_dans_activites) || !is_array($gestions_places)) {
        return $alerte_inscription_evenement;
    }

    //PLUS BEAUCOUP DE PLACE
    //(Si le nombre d'inscrit permis est supérieur aux places dispo)
    if (!empty($affichage_dans_activites['accompagnants'])
        AND isset($gestions_places['places_disponibles'])
        AND isset($gestions_places['places_limites'])
        AND $gestions_places['places_disponibles'] < $gestions_places['places_limites']
        AND !empty($affichage_dans_activites['places'])
        AND $affichage_dans_activites['places'] == true
        AND $gestions_places['places_disponibles'] != 0) {

        $alerte_inscription_evenement = _T('association_evenements:alerte_inscription_evenement',
                                           array(
                                               'places_disponibles' => $gestions_places['places_disponibles'],
                                               'places_limites' => $gestions_places['places_limites']));
        
    }

    return $alerte_inscription_evenement;
}
