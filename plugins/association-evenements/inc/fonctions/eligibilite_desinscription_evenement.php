<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
/**
 * VERIFICATION DE PREREQUIS POUR LA DESINSCRIPTION A UNE ACTIVITE
 *
 On vérifie les critères selon la configuration et le statut du paiement
 - 
 *
 * @param string $id_evenement
 * @return array
 */
function eligibilite_desinscription_evenement($id_activite){
    $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite = $id_activite AND statut !='desinscrit'");
    $id_evenement = $query_activite['id_evenement'];
    $id_auteur = $query_activite['id_auteur'];
    if(intval($query_activite['id_transaction'])){
        $id_transaction = $query_activite['id_transaction'];
		include_spip('inc/association_paiements_transactions');
		$query_transaction = association_paiements_transaction_lire($id_transaction);
    }else{
        $query_transaction = '';
    }
    $affichage_dans_activites =affichage_dans_activites($id_evenement);    
    if($GLOBALS['association_metas']['meta_cfg_event_desinscription_inscription'] == 'souple'){
            /* DESINSCRIPTION SOUPLE */
            $statut_desinscription= 'possible';   
    }elseif(($affichage_dans_activites['validation'] == false) && ($affichage_dans_activites['payant'] == false)){
            /*NO VALIDATION & GRATUIT*/   
            $statut_desinscription= 'possible';        
    }elseif(($affichage_dans_activites['validation'] == false) && ($affichage_dans_activites['payant'] == true) && ($query_transaction['statut'] != 'ok' )){
            /*NO VALIDATION & PAYANT & TRANSACTION PAS PAYEE*/   
            $statut_desinscription= 'possible';
    }elseif($affichage_dans_activites['validation'] && ($query_activite['statut'] != 'ok')){
            /*VALIDATION & PAS INSCRIT*/
            $statut_desinscription= 'possible';
    }elseif($query_activite['liste_attente'] ){
            /* LISTE ATTENTE */
            $statut_desinscription= 'possible';
    }else{
            $statut_desinscription= 'pas_possible';
    }
    return $statut_desinscription;
}
