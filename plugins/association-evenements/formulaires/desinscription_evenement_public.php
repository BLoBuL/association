<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/association_evenements_comptabilite');
function formulaires_desinscription_evenement_public_charger_dist($id_evenement){      
    $affichage_dans_activites =affichage_dans_activites($id_evenement);
    $gestions_places = gestions_places($id_evenement);    
    $ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);      
    
    $nom_cookie = 'id_evenement_'.$id_evenement;
    $cookie_email_inscrit = !empty($_COOKIE[$nom_cookie]) ? $_COOKIE[$nom_cookie] : '';  
    $id_auteur_connecte = isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : '';
   
 
    $query_activite = sql_select('*', 'spip_asso_activites', "id_evenement = $id_evenement AND statut !='desinscrit'");
    while ($asso_activite = sql_fetch($query_activite)) {
            $hash_emails = md5($asso_activite['email_inscrit']);           
            $id_auteur_inscrit = isset($asso_activite['id_auteur']) ? $asso_activite['id_auteur'] : '';        
            if($cookie_email_inscrit == $hash_emails){                
                $id_activite = $asso_activite['id_activite'];       
            }    
            elseif($id_auteur_connecte == $id_auteur_inscrit){                
                $id_activite = $asso_activite['id_activite'];       
            }    
    }
                                    
    if(isset($id_activite)){
        $desinscription_possible = eligibilite_desinscription_evenement($id_activite);
        $valeurs = array('id_activite' =>$id_activite,
                                    'desinscription_possible' => $desinscription_possible,
                                    'statut_ouverture_inscription' => $ouverture_inscription_evenement['statut_ouverture_inscription']);
    }else{
        $valeurs = array('desinscription_possible' => 'pas_inscrit');
    }   
	return $valeurs;
}
/*function formulaires_desinscription_evenement_public_verifier_dist($id_evenement){
    $erreurs = array();
    return $erreurs;
}*/
function formulaires_desinscription_evenement_public_traiter_dist($id_evenement){
    include_spip('inc/comptes');
    $id_evenement = intval($id_evenement);
    $affichage_dans_activites =affichage_dans_activites($id_evenement);
    $id_activite = intval(_request('id_activite'));
    $query_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite=$id_activite AND id_evenement=$id_evenement AND statut!='desinscrit'");

    $id_auteur_connecte = intval($GLOBALS['visiteur_session']['id_auteur'] ?? 0);
    $nom_cookie = 'id_evenement_' . $id_evenement;
    $cookie_inscription = (string)($_COOKIE[$nom_cookie] ?? '');
    $proprietaire_connecte = $id_auteur_connecte > 0 && $id_auteur_connecte === intval($query_activite['id_auteur'] ?? 0);
    $proprietaire_anonyme = $cookie_inscription !== ''
        && hash_equals(md5((string)($query_activite['email_inscrit'] ?? '')), $cookie_inscription);

    if (empty($query_activite) || (!$proprietaire_connecte && !$proprietaire_anonyme)) {
        return array(
            'message_erreur' => _T('association_evenements:erreur_action_non_autorisee'),
            'redirect' => generer_url_public('evenement', 'id_evenement=' . $id_evenement),
        );
    }
    
    $message_journal = date('d/m/Y H:i') . " : " . _T('association_evenements:journal_desinscription_site_public') . "<br>" . $query_activite['journal'];
    
    sql_updateq('spip_asso_activites', array(
            'statut' => 'desinscrit',
            'journal' => $message_journal
        ),"id_activite=$id_activite");
    $id_transaction = intval($query_activite['id_transaction'] ?? 0);
	include_spip('inc/association_paiements_transactions');
	$query_transaction = $id_transaction > 0 ? association_paiements_transaction_lire($id_transaction) : array();

    if($id_transaction > 0 && ($query_transaction['statut'] ?? '') != 'ok'){
		association_paiements_transaction_modifier($id_transaction, array('statut' => 'abandon'));
    }
    // Suppression ENREGISTREMENT COMPTABLE SI LA COMPTABILITE EST ACTIVE
    if(!empty($GLOBALS['association_metas']['comptes']) && !empty($affichage_dans_activites['payant'])){
        association_evenements_comptes_supprimer_inscription($id_activite);
    }

    include_spip('inc/cookie');
    spip_setcookie($nom_cookie,"",time() - 3600);
    // Envoi d'un email de deconfirmation a l'inscrit et aux organisateurs
    job_queue_add('facteur_envoyer_mail_activites', 'Notification - desinscription_frontend - Activite :' . $id_activite.'', $arguments = array($id_evenement, $type  = 'desinscription_frontend', 
      array($id_activite)), $file = '', $no_duplicate = FALSE, $time=0, $priority=0);        
    
   if(($affichage_dans_activites['validation_attente_automatique'] ?? '') == 'oui'){
   //Ici on execute la validation automatique des éventuelles inscrits en attente
    job_queue_add('validation_attente_automatique', 'Validation automatique après désinscription- Activite :' . $id_activite.'', $arguments = array($id_evenement,'form_desinscription_public'), $file = '', $no_duplicate = FALSE, $time=0, $priority=0);
   }
    //On renvoi vers la page de l'evenement
    $res['redirect'] = generer_url_public('evenement', "id_evenement=" . $id_evenement);
    return $res;
}
