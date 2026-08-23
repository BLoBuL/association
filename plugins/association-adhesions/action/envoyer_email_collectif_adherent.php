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

function action_envoyer_email_collectif_adherent() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$count = $securiser_action();

	// Let's get the dates
  $date_start = $date = date('Y-m-d H:i:s');

	//$date_start = date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($date)));
	//$from_email = session_get("email");
	//$from_name  = session_get("prenom"). ' ' .session_get("nom_famille");

	// Than the subject and message
        //$titre=$_POST['titre'];
        $sujet=$_POST['sujet'];
        $chapeau=$_POST['chapeau'];
		//$texte=$_POST['texte'] ;
        $html= $_POST['html'] ;
        //$ajouter_information_paiement=$_POST['ajouter_information_paiement'] ;

	// Model message
	/*$message = recuperer_fond('emails/texte', array(
			"sujet" => $sujet,
			"html" => html_entity_decode(_request('message'), ENT_QUOTES, 'UTF-8')
		));*/

    //$html= recuperer_fond("notifications/email_collectif_adherent", array('titre' =>$titre,'chapeau' => $chapeau,'texte' => $texte,'ajouter_information_paiement' => $ajouter_information_paiement));


	// Utiliser strictement la sélection `selecteur_adherent` fournie par le formulaire.
	// Pas de fallback : si l'utilisateur n'a coché personne, la liste sera vide et c'est voulu.
	$ids = array();
	if (isset($_POST['selecteur_adherent'])) {
		$sel = $_POST['selecteur_adherent'];
		if (!is_array($sel)) {
			$sel = $sel ? array($sel) : array();
		}
		$sel = array_map('intval', $sel);
		$ids = array_values(array_filter($sel, function($v){ return $v > 0; }));
	}

	// Respecter le token sécurisé : si $count vaut 0 l'intention peut être d'envoyer à 0 destinataire
	if (intval($count) === 0) {
		$ids = array();
	}

	$total = count($ids);

	// Create the mailshot job (total reflète le nombre d'IDs valides déterminés ci‑dessus)
	$id_mailshot = sql_insertq('spip_mailshots', array(
									"sujet" => $sujet,
									"html" => $html,
									"listes" => "Temporaire",
									"total" => $total,
									"date" => $date,
									"date_start" => $date_start,
									"statut" => "init",
									"composition_lock" => 0,
							));

	// Add author's list on mailshot — n'insérer que si on a des ids valides
	$inserted = 0;
	$emails_vus = array();
	if (!empty($ids)) {
		$id_auteurs_list = sql_in('id_auteur', $ids);
		$auteurs_info = sql_select('id_auteur, email', 'spip_auteurs', $id_auteurs_list);
		while ($auteur_info = sql_fetch($auteurs_info)) {
			$email = strtolower(trim((string)($auteur_info['email'] ?? '')));
			if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($emails_vus[$email])) {
				$emails_vus[$email] = true;
				sql_insertq('spip_mailshots_destinataires',array(
					'id_mailshot' => $id_mailshot,
					'email' => $email,
					'date' => $date,
					'statut' => 'todo',
				));
				$inserted++;
			}
		}
	}

	// Recalculer le total réel depuis la table des destinataires
	if ($id_mailshot) {
		$real_total = sql_countsel('spip_mailshots_destinataires', 'id_mailshot=' . intval($id_mailshot));
		sql_updateq('spip_mailshots', array('total' => intval($real_total)), 'id_mailshot=' . intval($id_mailshot));
	}

  ecrire_meta("mailshot_processing",'oui');
  // reprogrammer le cron
  include_spip('inc/genie');
  genie_queue_watch_dist();

	// That's all folks
}
