<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
// TODO -> Peut être passer 'modifier_activites' et 'ajouter_activites' en un seul et meme fichier
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('inc/association_paiements_transactions');
function action_ajouter_activites() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
    $id_activite      = $securiser_action();
	$categorie_result   = _request('categorie');
	$date               = _request('date');
	$id_evenement       = _request('id_evenement');
	$id_auteur          = _request('id_auteur');
    $statut_activite = _request('statut_inscription');
	//$valider                  = _request('valider');
    $valider = ( $statut_activite == 'ok')? '1' : '0' ;
	$nom_participants   = _request('nom_participants');
	$commentaire        = _request('commentaire');
	$gratuit_single     = _request('gratuit_single');
    $total_inscrits     = _request('total_inscrits');
    $notify_the_members = _request('notify_the_members');
	activites_insert($categorie_result ,$date, $id_evenement, $id_auteur, $nom_participants, $commentaire, $valider, $gratuit_single, $total_inscrits, $id_activite,$notify_the_members);
}
function activites_insert($categorie_result, $date, $id_evenement, $id_auteur, $nom_participants, $commentaire, $valider, $gratuit_single, $total_inscrits, $id_activite,$notify_the_members)
{
	$nombre_inscrits            = $montant_total = 0;
	$inserer_transaction        = charger_fonction('inserer_transaction','bank');
	$gestions_places            = gestions_places($id_evenement);
	$query_evenement            = sql_fetsel('*', "spip_evenements", "id_evenement=$id_evenement" );
    $query_auteur = sql_fetsel("prenom,nom_famille,email","spip_auteurs","id_auteur=$id_auteur");
	$evenement_payant           = $query_evenement['payant'];
	$accompagnant_desactives    = ($query_evenement['accompagnants'] == 'non')? true : false;
	$validation                 = ($query_evenement['validation'] == 'oui')? true : false;
	$querie_categorie_activite  = sql_select("*", "spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a ON(a.id_categorie=b.id_categorie)", "a.id_evenement=$id_evenement AND b.deleted=0",'',"montant DESC"); 
  if(!$gratuit_single){
    ## 1/ RECUPERATION ET CALCULE
    while ($categories_activite = sql_fetch($querie_categorie_activite)){
      $result = $categorie_result[$categories_activite['id_categorie']];
      if($result){
        $nombre_inscrits += $result;
        $montant_prepa = (!$evenement_payant)? 0 : $categories_activite['montant'];
        $montant_total = ($categories_activite['quantite']>1)? $montant_total + $montant_prepa : $montant_total + ($montant_prepa * $result);
        //Création de la ligne pour la colonne transaction
        if(!empty($result))
          $transaction[$categories_activite['id_categorie']] = array(
            'nombre'    => $result,
            'montant'   =>  $montant_prepa
          );
      }
    }
    sql_free($querie_categorie_activite);
  } else{
    $nombre_inscrits = !empty($total_inscrits)? $total_inscrits : 1;
  }
	## 3/ VERIFICATION DES ERREURS
	$redirect             = false;
	$places_disponibles   = ($gestions_places['places_limites'] == 0)? 500 : $gestions_places['places_disponibles'];
	$result               = 0;
  $activite             = '';
  if(!$gratuit_single)
    foreach($categorie_result as $selection){
      $result = $selection + $result;
    }
  if($id_activite)
    $activite = '&id='.$id_activite;
	if($nombre_inscrits > $gestions_places['places_limites']){
		$args      = 'id_evenement='.$id_evenement.'&erreur=depassement'.$activite;
		$redirect  =  urldecode(generer_url_ecrire('editer_asso_activite',$args));
	} elseif($nombre_inscrits == 0){
		$args      = 'id_evenement='.$id_evenement.'&erreur=selection'.$activite;
		$redirect  = generer_url_ecrire('editer_asso_activite',$args);
	} elseif ($accompagnant_desactives AND 1 < $nombre_inscrits){
		$args      = 'id_evenement='.$id_evenement.'&erreur=accompagnant'.$activite;
		$redirect  =  urldecode(generer_url_ecrire('editer_asso_activite',$args));
	}
	if($redirect)
		return redirige_formulaire( $redirect, '', false );
  if($gratuit_single)
    ## CAS GRATUIT SANS ACCOMPAGNANTS
    $cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, true, '');
	elseif($montant_total == 0){
		## CAS GRATUIT
		if(!$validation OR $valider)
			$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, true, '');
		else
			$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, false, '');
		// 1) Création de la transaction
		$options = array (
			'id_auteur' => $id_auteur,
			'force'     => true,
			'champs'    => array(
				'mode'      => 'gratuit',
				'message'   => 'Activité n°' . $id_evenement,
			)
		);
		$id_transaction = $inserer_transaction('0',$options);
		if(!$cal_result['gestion']['validation'] OR $valider){
			// Si la validation n'est pas obligatoire ou si c'est validé, on valide en plus l'inscrition
			association_paiements_transaction_modifier($id_transaction, array(
				'reglee'         => 'oui',
				'statut'         => 'ok',
				'finie'          => 1,
				'montant_regle'  => '0',
				'date_paiement'  => date('Y-m-d H:i:s'),
			));
		}
	} else {
		#CAS PAYANT
		$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, false, '');
		// 1) Création de la transaction
		$options = array (
			'id_auteur' => $id_auteur,
			'force' => true,
		);
		$id_transaction = $inserer_transaction($montant_total,$options);
	}
	if (empty($id_activite)){
    // 3) Création d'une nouvelle ligne dans activité
    $id_activite = sql_insertq('spip_asso_activites', array(
        'date'              => $date,
        //'valider'           => $cal_result['valider'],
        'id_evenement'      => $id_evenement,
        'id_auteur'         => $id_auteur,
        'nom_inscrit' => $query_auteur['nom_famille'],
        'prenom_inscrit' => $query_auteur['prenom'],
        'email_inscrit' => $query_auteur['email'],
        'statut'            => $cal_result['statut'],
        'nom_participants'  => $nom_participants,
        'nombre_inscrits'   => $nombre_inscrits,
        'id_transaction'    => $id_transaction,
        'commentaire'       => $commentaire,
        'notify_the_members' => $notify_the_members,
        'tarifs_selectionnes' => serialize($transaction))
    );
    // 4) Enregistrement du log
  } else {
    // Mise à jour de l'activité
    sql_updateq('spip_asso_activites', array(
      "statut"            => $cal_result['statut'],
      //"valider"           => $valider,
      "nom_participants"  => $nom_participants,
      "nombre_inscrits"   => $nombre_inscrits,
      'id_transaction'    => $id_transaction,
      "commentaire"       => $commentaire,
      'tarifs_selectionnes' => serialize($transaction)
    ),
      "id_activite=$id_activite");
  }
	// true pour inscription
	$type     = 'preinscription_backend';
	if($cal_result['statut'] == 'ok')
		$type   = "inscription_backend";
	elseif($cal_result['statut'] == 'liste_attente')
		$type   = "attente_backend";
	$id_activite = array($id_activite);
    if($notify_the_members == 1) {
	    //facteur_envoyer_mail_activites($id_evenement, $id_auteur, $type, $id_activite);
         job_queue_add('facteur_envoyer_mail_activites', 'Notification -ajout_activites', $arguments = array($id_evenement, $id_auteur, $type, $id_activite), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
    }
}
