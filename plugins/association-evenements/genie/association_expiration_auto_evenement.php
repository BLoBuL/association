<?php
/*
 * GESTION DES TÃ‚CHES CRON
 * VERSION = 0.1b
 */

/**
 * Expire automatiquement les préinscriptions non réglées à temps pour les événements.
 *
 * Cette fonction est exécutée via une tâche CRON et effectue les actions suivantes :
 * - Identifie les événements concernés par l'expiration automatique.
 * - Vérifie les préinscriptions non réglées dans le délai imparti.
 * - Désinscrit automatiquement les participants concernés.
 * - Met à jour les journaux et les statuts des transactions associées.
 * - Envoie des notifications pour les désinscriptions automatiques.
 *
 * @param array $flux Flux de données passé à la fonction (non utilisé ici).
 * @return bool Retourne `true` une fois la tâche terminée.
 */
function genie_association_expiration_auto_evenement_dist($flux){
	include_spip('inc/association_evenements_paiements');

    // Vérifie si un délai d'expiration est configuré
    if($GLOBALS['association_metas']['meta_cfg_event_delai_expiration'] >= 1){
        $date_actuel = date("Y-m-d H:i:s");
        $nombres_jours = $GLOBALS['association_metas']['meta_cfg_event_delai_expiration'];

        // Identifie les événements concernés
        $criteres_payant = "validation_sur_paiement = 'oui' AND places > 1 AND date_debut >= '". $date_actuel ."'";
        $query_evenements = sql_select("id_evenement", 'spip_evenements', $criteres_payant);
        $id_evenements = array();

        while($query_evenement = sql_fetch($query_evenements)){
            $id_evenement = $query_evenement['id_evenement'];
            $gestion_place = gestions_places($id_evenement);
            $places_a_valider = $gestion_place['places_a_valider'];

            if($places_a_valider >= 1 ){
                $id_evenements[] = $id_evenement;
            }
        }

        // Si des événements sont concernés
        if(!empty($id_evenements)){
            $criteres_id_evenement = implode(',', $id_evenements);
            $criteres = "id_evenement IN ($criteres_id_evenement) AND statut = 'preinscrit'";
            $id_activite = array();
            $id_evenements_concernes_array = array();

            if ($query_asso_activites = sql_select("id_activite,id_evenement,id_transaction,statut,date,journal", 'spip_asso_activites', $criteres)){
                while( $asso_activites = sql_fetch($query_asso_activites)){
                    $id_activite = $asso_activites['id_activite'];
                    $id_evenement = $asso_activites['id_evenement'];
                    $id_transaction = $asso_activites['id_transaction'];
                    $journal = $asso_activites['journal'];

                    $date_inscription = $asso_activites['date'];
                    $date_expiration_inscription = date('Y-m-d H:i:s',strtotime($date_inscription) + (24*3600*$nombres_jours));

                    // Vérifie si la préinscription a expiré
                    if($date_actuel >= $date_expiration_inscription){
                        $id_activite_array[] = $asso_activites['id_activite'];
                        $id_evenements_concernes_array[] = $asso_activites['id_evenement'];
						$query_transaction = association_evenements_transaction_lire($id_transaction);
                        $type = 'expiration_automatique';

                        // Ajoute une tâche pour envoyer une notification
                        job_queue_add('facteur_envoyer_mail_activites', 'Notification - expiration_automatique', $arguments = array($id_evenement, $type, $id_activite_array, $file = '', $no_duplicate = FALSE, $time=0, $priority=0)) ;
                        unset($id_activite_array);

                        // Met à jour le journal et le statut
                        $entree_journal = date('d/m/Y H:i') . ' : ' . _T('association_evenements:journal_expiration_automatique') . '<br>' . $journal;
                        sql_updateq('spip_asso_activites', array("statut" => 'desinscrit', "journal" => $entree_journal),"id_activite=$id_activite");
                        if($query_transaction['statut'] != 'ok'){
							association_evenements_transaction_modifier($id_transaction, array('statut' => 'abandon'));
                        }

                    }
                }

                // Valide automatiquement les événements concernés
                foreach($id_evenements_concernes_array as $id_evenement_concerne){
                    $affichage_dans_activites  = affichage_dans_activites($id_evenement_concerne);
                    if($affichage_dans_activites['validation_attente_automatique'] == 'oui'){
                        validation_attente_automatique($id_evenement_concerne,'cron_expiration');
                    }
                }
            }
        }
    }

    // Enregistrement de la tâche dans le log
    association_log('cron', "Associaspip : Expiration auto evenement cron terminés", 'info');
    return true;
}
