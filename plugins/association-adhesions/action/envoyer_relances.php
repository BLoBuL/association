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

function action_envoyer_relances() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$count = $securiser_action();

	// Let's get the dates
  $date_start = $date = date('Y-m-d H:i:s');

	//$date_start = date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($date)));
	//$from_email = session_get("email");
	//$from_name  = session_get("prenom"). ' ' .session_get("nom_famille");

	// Than the subject and message
        $titre=$_POST['titre'];
        $sujet=$_POST['sujet'];
        $chapeau=$_POST['chapeau'];
		$texte=$_POST['texte'] ;
        $ajouter_information_paiement=$_POST['ajouter_information_paiement'] ;

	// Model message
	/*$message = recuperer_fond('emails/texte', array(
			"sujet" => $sujet,
			"html" => html_entity_decode(_request('message'), ENT_QUOTES, 'UTF-8')
		));*/

    $html= recuperer_fond("notifications/email_collectif_adherent", array('titre' =>$titre,'chapeau' => $chapeau,'texte' => $texte,'ajouter_information_paiement' => $ajouter_information_paiement));


	// List des authors ID's
	$statut_tab	=(isset($_POST["statut"])) ? $_POST["statut"] : array(); /* contient un tableau id_auteur => statut_interne */
	$total = count($statut_tab);

	// Create the mailshot job
	$id_mailshot = sql_insertq('spip_mailshots', array(
									"sujet" 						=> $sujet,
									"html" 							=> $html,
									"listes" 						=> "Temporaire",
									"total" 						=> $total,
									"date" 							=> $date,
									"date_start" 				=> $date_start,
									//"from_name" 				=> $from_name,
									//"from_email" 				=> $from_email,
									"statut" 						=> "init",
									"composition_lock" 	=> 0,
							));

	// Add author's list on mailshot
	$id_auteurs_list 	= sql_in('id_auteur', array_keys($statut_tab));
	$auteurs_info 		= sql_select('id_auteur, email', 'spip_auteurs', $id_auteurs_list);
	$emails_vus = array();

	while ($auteur_info = sql_fetch($auteurs_info)) {
		$email = strtolower(trim((string)($auteur_info['email'] ?? '')));
		if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($emails_vus[$email])) {
			$emails_vus[$email] = true;
			sql_insertq('spip_mailshots_destinataires',array(
				'id_mailshot' => $id_mailshot,
				'email' 			=> $email,
				'date'				=> $date,
				'statut' 			=> 'todo',
			));
		}
	}

  ecrire_meta("mailshot_processing",'oui');
  // reprogrammer le cron
  include_spip('inc/genie');
  genie_queue_watch_dist();

	// That's all folks
}
