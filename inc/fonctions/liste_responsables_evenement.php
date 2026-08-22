<?php
if(!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Fonction `liste_responsables_evenement`
 *
 * Cette fonction permet de récupérer la liste des responsables d'un événement ou d'un article
 * en fonction des paramètres fournis. Elle retourne un tableau formaté contenant les identifiants
 * des auteurs responsables.
 *
 * @param int|string $id_evenement (optionnel) L'identifiant de l'événement.
 * @param int|string $id_article (optionnel) L'identifiant de l'article.
 *
 * @return array|string Retourne un tableau contenant les identifiants des auteurs responsables
 *                      ou une chaîne vide si aucun responsable n'est trouvé.
 */
function liste_responsables_evenement($id_evenement='', $id_article='') {
    $query_inscription = array(); // initialisation pour éviter "undefined variable"

    // Vérifie si un identifiant d'événement est fourni
    if (!empty($id_evenement)) {
        // Récupère les informations d'inscription et de responsables pour l'événement
        $query_inscription = sql_fetsel('inscription,responsables', 'spip_evenements', "id_evenement=$id_evenement");
        if (!is_array($query_inscription)) {
            $query_inscription = array();
        }
    }

    // Si des responsables sont définis et que l'inscription est activée pour l'événement
    if (!empty($query_inscription['responsables']) AND $query_inscription['inscription'] == 1) {
        // Récupère les responsables de l'événement
        $query_evenement = sql_fetsel('responsables', 'spip_evenements', "id_evenement=$id_evenement");

        // Si des responsables sont trouvés
        if (!empty($query_evenement['responsables'])) {
            // Sélectionne les auteurs responsables de l'événement
            $query_liste_responsables_evenement_selection = sql_select("*", "spip_auteurs", 'id_auteur IN('. implode(',', $query_evenement) .')');
            $query_liste_responsables_evenement = $query_liste_responsables_evenement_selection;
        } else {
            // Aucun responsable trouvé
            $query_liste_responsables_evenement = array();
        }
    }
    // Si un identifiant d'article est fourni et que l'inscription n'est pas activée
    elseif (!empty($id_article) AND $query_inscription['inscription'] != 1) {
        // Recherche les auteurs liés à l'article
        $query_liste_responsables_evenement_preselection = sql_select(
            "*",
            "spip_auteurs AS auteurs, spip_auteurs_liens AS lien",
            "lien.objet= 'article' AND lien.id_objet=". $id_article ." AND auteurs.id_auteur=lien.id_auteur  AND  auteurs.statut_interne='ok'"
        );
        $query_liste_responsables_evenement = ($query_liste_responsables_evenement_preselection) ? $query_liste_responsables_evenement_preselection : false;
    } else {
        // Aucun événement ou article valide fourni
        $query_liste_responsables_evenement = array();
    }

    // Si des responsables sont trouvés
    if (sql_count($query_liste_responsables_evenement) > 0) {
        $auteur_array = array();
        // Parcourt les résultats pour extraire les identifiants des auteurs
        while ($data = sql_fetch($query_liste_responsables_evenement)) {
            $auteur_array[] = $data["id_auteur"];
        }
        $id_result['auteur_array'] = $auteur_array;
        $implode = 'id_auteur IN(' . implode(',', $auteur_array) . ')';
    } else {
        // Aucun responsable trouvé
        return $id_result = '';
    }

    // Retourne le tableau formaté des responsables
    return $id_result;
}
