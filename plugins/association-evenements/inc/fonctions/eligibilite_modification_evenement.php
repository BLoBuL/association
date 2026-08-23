<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
/**
 * VERIFICATION DE PREREQUIS POUR LA DESINSCRIPTION A UNE ACTIVITE
 *
 On vérifie les critères selon la configuration et le statut du paiement
 *
 * @param string $id_evenement
 * @return array
 */
function eligibilite_modification_evenement($id_activite){
    $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite = $id_activite AND statut !='desinscrit'");
    if (empty($query_activite)) {
        return 'pas_possible';
    }
    $id_evenement = $query_activite['id_evenement'];
    $id_auteur = $query_activite['id_auteur'];
    if(intval($query_activite['id_transaction'])){
        $id_transaction = $query_activite['id_transaction'];
        $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction = $id_transaction");
    }else{
        $query_transaction = '';
    }
    $affichage_dans_activites =affichage_dans_activites($id_evenement);
    $ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);

    if($GLOBALS['association_metas']['meta_cfg_event_modification_inscription'] != 'oui'){
            /* ON NE PERMETS PAS AUX PERSONNES DE SE DESINSCRIRE PAR CONFIGURATION */
            $statut_modification= 'pas_possible';
    }elseif($ouverture_inscription_evenement['inscription_ouverte'] == 'non'){
            /* EVENEMENT TERMINE*/
            $statut_modification= 'pas_possible';
    }elseif(($affichage_dans_activites['validation'] == false) && ($affichage_dans_activites['payant'] == false)){
            /*NO VALIDATION & GRATUIT*/
            $statut_modification= 'possible';
    }elseif(($affichage_dans_activites['validation'] == false) && ($affichage_dans_activites['payant'] == true) && ($query_transaction['statut'] != 'ok' )){
            /*NO VALIDATION & PAYANT & TRANSACTION PAS PAYEE*/
            $statut_modification= 'possible';
    }elseif($affichage_dans_activites['validation'] && ($query_activite['statut'] != 'ok')){
            /*VALIDATION & PAS INSCRIT*/
            $statut_modification= 'possible';
    }elseif($query_activite['liste_attente'] ){
            /* LISTE ATTENTE */
            $statut_modification= 'possible';
    }else{
            $statut_modification= 'pas_possible';
    }

    return $statut_modification;
}
