<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * GESTION DU FORMULAIRE ACTIVITE ET DES MESSAGES EN TENANT COMPTE DES OPTIONS
 *
 * @since 3.1.0
 * @version 1.0
 *
 * @param int $id_auteur
 * @param int $id_evenement
 * @param boolean $front_end
 *
 * @return array
 */
function activite_calculator($id_auteur, $id_evenement, $front_end = false){
    $query_activite = '';
    $gestion = affichage_dans_activites($id_evenement);
    $contexte['id_evenement'] = $id_evenement;
    $contexte['id_auteur'] = $id_auteur;
    $contexte['place_illimite'] = (!$gestion['places'])? true : false;  
    // Affichage de l'activité
    if(!empty($id_auteur)){
        if(!$gestion['payant']) {
            $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_evenement = $id_evenement AND id_auteur = $id_auteur AND statut !='desinscrit'");
        } else {
            $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_evenement = $id_evenement AND id_auteur = $id_auteur AND statut !='desinscrit'");
            if ($query_activite) {
                include_spip('inc/association_paiements_transactions');
                $transaction = association_paiements_transaction_lire((int) $query_activite['id_transaction']);
                // Le SELECT * historique donnait la priorité aux champs homonymes de la transaction.
                $query_activite = $transaction ? array_merge($query_activite, $transaction) : array();
            }
        }
    }
    $contexte['payant'] = $gestion['payant'];
    $gestion_places = gestions_places($id_evenement);
    if ($query_activite AND $front_end){
        if($query_activite['responsable'])
            $contexte['responsable'] = true;
        else {
            if($query_activite['statut'] == 'ok')
                $contexte['inscrit'] = true;
            elseif($query_activite['statut'] == 'liste_attente')
                $contexte['en_attente'] =	$contexte['supprimer'] = $contexte['liste_responsable'] = true;
            elseif($query_activite['statut'] == 'preinscrit')
                $contexte['preinscrit'] = $contexte['supprimer'] = $contexte['liste_responsable'] = true;
			elseif($query_activite['statut'] == 'desinscrit')
                $contexte['inscrit'] = false;
        }
        $contexte['infos'] = true;
        $contexte['date_inscription'] = association_datefr($query_activite['date']);
        if($gestion['montant']){
            $contexte['montant_active'] = true;
            $contexte['montant_adherent'] = $query_activite['montant'];
            $contexte['montant_payer'] = ($query_activite['reglee']);
        }
        if($gestion['accompagnants']){
            $contexte['accompagnants_active'] = true;
            $contexte['nombre_inscrits'] = $query_activite['nombre_inscrits'];
            $contexte['nom_participants'] = $query_activite['nom_participants'];
        }
    }  
    else {
        $contexte['requiere_validation'] = ($gestion['validation'])? 'validation' : 'pas_validation';
		$contexte['validation_sur_paiement'] = ($gestion['validation_sur_paiement'])? 'oui' : 'non';
        $contexte['attente_dispo'] = ($gestion['attentes'])? true : false;
        $contexte['activation_accompagnant'] = true; // ACCOMPAGNANT ACTIVE ???
        if(!$gestion['places']){
            /*
			 *  PAS DE PLACES - PAS D'ATTENTE - ACCOMPAGNANTS
			 * 	RESULTATS DES TESTS : PASSED
			 */
            $contexte['limite_place'] = $gestion_places['places_limites']; // CAS Places illimités
            $contexte['place_info'] = 'illimite';
            $contexte['plein'] = 'formulaire';
            $contexte['activation_accompagnant'] = false;
            if ($gestion['accompagnants'])
                $contexte['activation_accompagnant'] = true;
        }
        elseif ($gestion['places'] AND !$gestion['attentes'] AND $gestion['accompagnants']){
            /*
                     *  PLACES - PAS D'ATTENTE - ACCOMPAGNANTS
                     * 	RESULTATS DES TESTS : PASSED
                     */
            $place_disponible_adherent = $gestion_places['places_disponibles'];
            if(!empty($id_auteur)){
                $nombre_inscrit_adherent = $query_activite['nombre_inscrits'];
                $contexte['statut_adherent'] = $query_activite['statut'];
                if($contexte['statut_adherent'] == 'preinscrit') // Cas modification préinscrit
                    $limite_place_modification = ($nombre_inscrit_adherent >=  $gestion_places['places_disponibles'])? $query_activite['nombre_inscrits'] : $place_disponible_adherent; // Juste les places de l'adhérent ou les places_dispo - l'adhérent
                else // Cas modification inscrit
                    $limite_place_modification = $query_activite['nombre_inscrits'] + $gestion_places['places_disponibles']; // Places prises par l'adhérent ID + places dispo - l'adhérent
            } else // Cas nouvelle inscription
                $limite_place_modification = $place_disponible_adherent; // Places prises par l'adhérent ID + places dispo - l'adhérent
            $contexte['limite_place'] = ($limite_place_modification >= $gestion_places['places_limites'])? $gestion_places['places_limites']: $limite_place_modification; // CAS Places limité mais pas de place d'attente (limité à ce qu'on a définie)
            $contexte['warning_place_superieur'] = ($contexte['limite_place'] > $place_disponible_adherent AND $contexte['statut_adherent'] == 'preinscrit')? true : false; // Cas avec un nombre de preinscrit supérieur au places disponibles
            $contexte['warning_place'] = ($gestion_places['places_disponibles'] <= 5 AND $gestion_places['places_disponibles'] > 0)? true : false; // Attention reste places
            $contexte['plein'] = ($gestion_places['places_disponibles'] == 0)? 'plein' : 'formulaire'; // Plus de place disponible
            $contexte['place_info'] = 'limiteSansAttente'; // infos sur le type d'inscritpion
            $contexte['place_illimite'] = false;
            $contexte['activation_accompagnant'] = true;
        } elseif ($gestion['places'] AND $gestion['attentes'] AND $gestion['accompagnants']){
            /*
                     *  PLACES - ATTENTE - ACCOMPAGNANTS
                     */
            $limite_place_modification = (!empty($id_auteur))? $query_activite['nombre_inscrits'] : 0;
            if (!empty($id_auteur) AND $query_activite['statut'] == 'liste_attente')
                $places_total = $gestion_places['places_en_attentes_disponible'] + $limite_place_modification;
            else
                $places_total = (($gestion_places['places_disponibles'] > $gestion_places['places_en_attentes_disponible']))? $gestion_places['places_disponibles'] + $limite_place_modification : $gestion_places['places_en_attentes_disponible']; // On récupère le plus grand des deux (pour ne pas dépasser la limite)
            $contexte['limite_place'] = ($places_total >= $gestion_places['places_limites'])? $gestion_places['places_limites'] : $places_total; // CAS Places limité avec place d'attente (limité à ce qu'on a définie)
            $contexte['limite_place_enregistrement'] = ($places_total >= $gestion_places['places_limites'])? $gestion_places['places_limites'] : $places_total; // CAS Places limité avec place d'attente (limité à ce qu'on a définie)
            $contexte['warning_place'] = ($gestion_places['places_disponibles'] <= 5 AND $gestion_places['places_disponibles']  > 0)? true: false; // Attention reste places
            $contexte['plein_attente'] = ($gestion_places['places_disponibles'] == 0)? true : false; // Plus de place disponible
            $contexte['plein'] = ($places_total <= 0)? 'plein' : 'formulaire'; // Plus de place en attente
            $contexte['place_info'] = 'limiteavecAttente';
            $contexte['place_illimite'] = false;
            $contexte['activation_accompagnant'] = true;
        } elseif ($gestion['accompagnants']) {
            $contexte['limite_place'] = 10; // CAS Places limité avec place d'attente (limité à ce qu'on a définie)
            $contexte['place_info'] = 'illimite';
            $contexte['place_illimite'] = false;
            $contexte['activation_accompagnant'] = true;
        } elseif ($gestion['attentes']) {
            $places_total = $gestion_places['places_en_attentes_disponible'];
            $contexte['limite_place'] = 0; // CAS accompagnants désactivés
            $contexte['plein_attente'] = ($gestion_places['places_disponibles'] == 0)? true : false; // Plus de place disponible
            $contexte['plein'] = (($gestion_places['places_non_disponibles'] >= $gestion_places['places_disponibles']) AND ($places_total == 0))? 'plein' :'formulaire';
            $contexte['activation_accompagnant'] = false;
            $contexte['place_illimite'] = false;
        } else {
            $places_total = $gestion_places['places_en_attentes_disponible'];
            $contexte['limite_place'] = 0; // CAS accompagnants désactivés
            $contexte['place_info'] = 'desactive';
            $contexte['plein'] = ($gestion_places['places_disponibles'] == 0)? 'plein' :'formulaire';
            $contexte['limite_place'] = ($places_total >= $gestion_places['places_limites'])? $gestion_places['places_limites'] : $places_total; // CAS Places limité avec place d'attente (limité à ce qu'on a définie)
            $contexte['activation_accompagnant'] = false;
            $contexte['place_illimite'] = false;
        }
        $contexte['formulaire'] = ($contexte['plein'] == 'formulaire')? true : false;
        $contexte['plein'] = ($contexte['plein'] == 'plein')? true : false;
    }
    // Infos places
    $contexte['places_evenement'] = $gestion_places['places_evenement'];
    $contexte['places_disponibles'] = $gestion_places['places_disponibles'];
    $contexte['nombre_place_attente'] = $gestion_places['places_en_attentes_disponible'];
    $contexte['attente_illimite'] = $gestion['attentes_illimite'];
    // Info prix
    $contexte['montant'] = $gestion['montant'];
    return $contexte;
}
