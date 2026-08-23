<?php
/**
 * Plugin Agenda 4 pour Spip 3.0
 * Licence GPL 3
 *
 * 2006-2011
 * Auteurs : cf paquet.xml
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');

/**
 * Liste les labels des informations supplémentaires pour un événement.
 *
 * @param int $id_evenement L'ID de l'événement.
 * @return string|false Les labels des informations supplémentaires en format CSV, ou false si aucune information supplémentaire n'est trouvée.
 */
function lister_label_info_supplementaire($id_evenement){
    // Récupère les informations d'affichage pour l'événement
    $affichage_dans_activites = affichage_dans_activites($id_evenement);
    // Récupère la liste des informations supplémentaires à afficher
    $liste_info_supplementaire = (!empty($affichage_dans_activites['info_supplementaire'])) ? explode(",", $affichage_dans_activites['info_supplementaire']) : false;

    if ($liste_info_supplementaire) {
        $labels = '';
        // Itère sur chaque information supplémentaire
        foreach ($liste_info_supplementaire as $info_supplementaire) {
            if ($info_supplementaire == 'document_identite') {
                // Ajoute les labels pour les informations de document d'identité
                $labels .= "\"" . utf8_decode(_T("association:export_evenement_type_document_identite")) . "\";\"" . utf8_decode(_T("association:export_evenement_numero_document_identite")) . "\";\"" . utf8_decode(_T("association:export_evenement_date_expiration_document_identite")) . "\";\"" . utf8_decode(_T("association:export_evenement_lieu_naissance")) . "\";";
            } elseif (!empty($info_supplementaire) AND $info_supplementaire != 'email') {
                // Ajoute les labels pour les autres informations supplémentaires
                $labels .= "\"" . utf8_decode(_T("association:export_evenement_$info_supplementaire")) . "\";";
            }
        }
        return $labels;
    } else {
        return false;
    }
}

/**
 * Génère les détails d'inscription pour les accompagnants d'une activité.
 *
 * @param int $id_activite L'ID de l'activité.
 * @return string Les détails d'inscription en format CSV.
 */
function generer_detail_inscription_accompagnant($id_activite){
            // Récupère les détails de l'activité depuis la base de données
            $query_activite = sql_fetsel("*", 'spip_asso_activites', "id_activite=$id_activite");

            // Récupère les informations supplémentaires à afficher pour l'activité
            $affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);
            $id_info_participant = (!empty($affichage_dans_activites['info_supplementaire'])) ? explode(",", $affichage_dans_activites['info_supplementaire']) : array();
            $valeur_inscrit = '';
            $i = 0;

            // Décode les données JSON des participants
            $participants_json = json_decode($query_activite['participants_json'], true);

            // Itère sur chaque participant
            foreach($participants_json as $id_participant => $info_participant){
                $i++;
                $valeur_inscrit .= "#$id_activite;";

                // Supprime 'telephone' si non présent dans les informations supplémentaires
                if(!in_array('telephone', $id_info_participant)){
                    unset($info_participant['telephone']);
                }
                if(!in_array('email', $id_info_participant)){
                    unset($info_participant['email']);
                }

                // Supprime 'categorie' des informations du participant
                unset($info_participant['categorie']);

                // Itère sur chaque information du participant
                foreach($info_participant as $key => $value){

                    if ($key == 'nom') {
                        $valeur_inscrit .= ($value) ? strtoupper($value) . ';' : '"";';
                    }
                    else {
                        $valeur_inscrit .= ($value) ? "\"$value\";" : '"";';
                    }
                }

                // Ajoute une nouvelle ligne pour chaque participant
                $valeur_inscrit .= "\r\n";
            }

            return $valeur_inscrit;
        }