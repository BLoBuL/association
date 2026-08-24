<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_envoyer_relances() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	$selection = _request('statut');
	$selection = is_array($selection) ? $selection : array();
	$html = recuperer_fond('notifications/email_collectif_adherent', array(
		'titre' => (string) _request('titre'),
		'chapeau' => (string) _request('chapeau'),
		'texte' => (string) _request('texte'),
		'ajouter_information_paiement' => _request('ajouter_information_paiement'),
	));

	include_spip('inc/email_collectif');
	$destinataires = association_email_collectif_resoudre_destinataires(array_keys($selection), array());
	$date = date('Y-m-d H:i:s');
	association_communication_mailshot_creer(
		(string) _request('sujet'),
		$html,
		$destinataires,
		array('date' => $date, 'date_start' => $date)
	);
}
