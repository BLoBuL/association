<?php
if (!defined('_ECRIRE_INC_VERSION')) return;


function formulaires_supprimer_asso_cotisation_charger_dist()
{
    
    $valeurs = array();
    $id_compte = _request('id_compte');
    
    
    return $valeurs;
}
function formulaires_supprimer_asso_cotisation_verifier_dist()
 {
	$erreurs = array();
    $id_compte = _request('id_compte');
	$raison = _request('supprimer_transaction');
    
    
	if ($supprimer_transaction){
		$erreurs['supprimer_transaction'] = _T('info_obligatoire');
	}
	return $erreurs;
}
    function formulaires_supprimer_asso_cotisation_traiter_dist()
    {
         $res = array();
        $url_retour = _request('url_retour');
            
        $id_compte = _request('id_compte');
       $supprimer_transaction = _request('supprimer_transaction');
        
       $query_transaction = sql_fetsel("id_transaction","spip_asso_comptes",'id_compte=' . $id_compte);
        
       
       if ($supprimer_transaction == 'oui'){            
            sql_delete('spip_transactions', 'id_transaction=' . $query_transaction['id_transaction']);
        }
        else {
           sql_updateq('spip_transactions', array("statut" => 'abandon', "message" => 'Cotisation supprimée'),'id_transaction=' .$query_transaction['id_transaction']);
       }
        
       sql_delete('spip_asso_comptes', 'id_compte=' . $id_compte);
        
         // $res['message_erreur'] = "Un probleme a été rencontré, impossible d'enregistrer votre saisie";
        $res['message_ok'] = "La cotisation à bien été supprimé.";
        $res['redirect'] = $url_retour;
        
        
        /* on efface de la table destination_op toutes les entrees correspondant a cette operation */
        //sql_delete('spip_asso_destination_op', 'id_compte=' . $id_compte);
    	return $res;
}
