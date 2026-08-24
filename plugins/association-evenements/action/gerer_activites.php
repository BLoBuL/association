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


if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/association_evenements_comptabilite');
include_spip('inc/association_evenements_paiements');
function action_gerer_activites_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	$id_evenement = (int) _request('id_evenement');
	if (!$id_evenement || !autoriser('associer', 'activites')) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	$id_activites = array_values(array_unique(array_filter(array_map('intval', (array) _request('selecteur_id_activite')))));
	$id_activites = array_column(sql_allfetsel('id_activite', 'spip_asso_activites', array('id_evenement=' . $id_evenement, sql_in('id_activite', $id_activites))), 'id_activite');
	if (!$id_activites) {
		return;
	}
	$notify_the_members = (int) _request('notify_the_members');

    if($GLOBALS['association_metas']['comptes']) {
        include_spip('inc/comptes');
    }

 if($_POST['action_activites'] == 'valider_activites'){
    	$type = 'inscription_backend';
     foreach ( $id_activites as  $id_activite) {
            $id_activite_selection = array();
             $id_activite_selection[] = $id_activite;
            $query_asso_activites = sql_fetsel("id_transaction,statut,journal", 'spip_asso_activites', "id_activite = $id_activite");
             if($notify_the_members == 1) {
                job_queue_add('facteur_envoyer_mail_activites', 'Notification - valider_activites', $arguments = array($id_evenement, $type, $id_activite_selection), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
            }

            $entree_journal = date('d/m/Y H:i') . ' : ' . _T('association_evenements:journal_inscription_site_prive') . '<br>' . $query_asso_activites['journal'];
            sql_updateq('spip_asso_activites', array(
                                    "statut" => 'ok',
                                    "journal" => $entree_journal
                                ),
		  "id_activite=$id_activite");
         };
 }
 if($_POST['action_activites'] == 'devalider_activites'){
    	$type = 'preinscription_backend';
     foreach ( $id_activites as  $id_activite) {
            $id_activite_selection = array();
             $id_activite_selection[] = $id_activite;
            $query_asso_activites = sql_fetsel("id_transaction,statut,journal", 'spip_asso_activites', "id_activite = $id_activite");
             if($notify_the_members == 1) {
                  job_queue_add('facteur_envoyer_mail_activites', 'Notification - devalider_activites', $arguments = array($id_evenement, $type, $id_activite_selection), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
            }
            $entree_journal = date('d/m/Y H:i') . ' : ' . _T('association_evenements:journal_preinscription_site_prive') . '<br>' . $query_asso_activites['journal'];
            $date = date('Y-m-d H:i:s');
            sql_updateq('spip_asso_activites', array(
                                "date" => $date,
                                "statut" => 'preinscrit',
                                "journal" => $entree_journal
                                ),
		  "id_activite=$id_activite");
         };
}
 if($_POST['action_activites'] == 'mettre_en_attente_activites'){
	$type = 'attente_backend';
     foreach ( $id_activites as  $id_activite) {
            $id_activite_selection = array();
             $id_activite_selection[] = $id_activite;
            $query_asso_activites = sql_fetsel("id_transaction,statut,journal", 'spip_asso_activites', "id_activite = $id_activite");
             if($notify_the_members == 1) {
                 job_queue_add('facteur_envoyer_mail_activites', 'Notification - mettre_attente_activites', $arguments = array($id_evenement, $type, $id_activite_selection), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
            }
            $entree_journal = date('d/m/Y H:i') . ' : ' . _T('association_evenements:journal_liste_attente_site_prive') . '<br>' . $query_asso_activites['journal'];
            sql_updateq('spip_asso_activites', array(
                                    "statut" => 'liste_attente',
                                    "journal" => $entree_journal
                                ),
		  "id_activite=$id_activite");
         };
    }
 if($_POST['action_activites'] == 'desinscrire_activites'){
     $type = 'desinscription_backend';
     foreach ( $id_activites as  $id_activite) {
            $id_activite_selection = array();
             $id_activite_selection[] = $id_activite;
            $query_asso_activites = sql_fetsel("id_transaction,statut,journal", 'spip_asso_activites', "id_activite = $id_activite");
            $id_transaction = intval($query_asso_activites['id_transaction']);
			$query_transaction = $id_transaction > 0 ? association_evenements_transaction_lire($id_transaction) : array();

            if($notify_the_members == 1) {
                 job_queue_add('facteur_envoyer_mail_activites', 'Notification - desinscription_backend', $arguments = array($id_evenement, $type, $id_activite_selection), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
            }
            $entree_journal = date('d/m/Y H:i') . ' : ' . _T('association_evenements:journal_desinscription_site_prive') . '<br>' . $query_asso_activites['journal'];
            sql_updateq('spip_asso_activites', array("statut" => 'desinscrit', "journal" => $entree_journal),"id_activite=$id_activite");
             if($id_transaction > 0 && isset($query_transaction['statut']) && $query_transaction['statut'] != 'ok'){
				association_evenements_transaction_modifier($id_transaction, array('statut' => 'abandon'));
            }

         };
    }
 if($_POST['action_activites'] == 'reactiver_activites'){
     $type_reactivation = _request('type_reactivation'); // 'preinscrit' ou 'liste_attente'

     foreach ( $id_activites as  $id_activite) {
         $id_activite_selection = array();
         $id_activite_selection[] = $id_activite;
         $query_asso_activites = sql_fetsel("id_transaction,statut,journal", 'spip_asso_activites', "id_activite = $id_activite");
         $id_transaction = intval($query_asso_activites['id_transaction']);
		 $query_transaction = $id_transaction > 0 ? association_evenements_transaction_lire($id_transaction) : array();

         $nouveau_statut = ($type_reactivation === 'liste_attente') ? 'liste_attente' : 'preinscrit';

         if($nouveau_statut === 'liste_attente') {
             $message_journal = _T('association_evenements:journal_reactivation_liste_attente_site_prive');
             $type = 'attente_backend';
         } else {
             $message_journal = _T('association_evenements:journal_reactivation_preinscription_site_prive');
             $type = 'preinscription_backend';
         }

         $entree_journal = date('d/m/Y H:i') . ' : ' . $message_journal . '<br>' . $query_asso_activites['journal'];
         $date = date('Y-m-d H:i:s');

         sql_updateq('spip_asso_activites', array(
             "date" => $date,
             "statut" => $nouveau_statut,
             "journal" => $entree_journal
         ),"id_activite=$id_activite");

         if(
             $id_transaction > 0
             && isset($query_transaction['statut'])
             && in_array($query_transaction['statut'], array('abandon', 'echec'))
         ) {
			 association_evenements_transaction_modifier($id_transaction, array('statut' => 'attente'));
         }

         if($notify_the_members == 1) {
             job_queue_add('facteur_envoyer_mail_activites', 'Notification - reactiver_activites', $arguments = array($id_evenement, $type, $id_activite_selection), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
         }
     };
 }
 if($_POST['action_activites'] == 'supprimer_activites'){
        foreach ( $id_activites as  $id_activite) {
            $query_asso_activites = sql_fetsel('id_transaction', 'spip_asso_activites', "id_activite = $id_activite");

			association_evenements_transaction_supprimer_non_encaissee((int) $query_asso_activites['id_transaction']);
            // Suppression ENREGISTREMENT COMPTABLE SI LA COMPTABILITE EST ACTIVE
            if($GLOBALS['association_metas']['comptes']){
                association_evenements_comptes_supprimer_inscription($id_activite);
            }
        }

        $query_activite = sql_in('id_activite', $id_activites);
        sql_delete('spip_asso_activites', $query_activite);
    }
    }
