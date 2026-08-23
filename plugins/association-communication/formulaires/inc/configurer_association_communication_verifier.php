<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Vérifie les listes d'adresses utilisées par les notifications de la suite.
 */
function association_communication_configurer_verifier($config) {
	$erreurs = array();
	$champs = array(
		'email',
		'config_destinataires_creation_cotisation_adh',
		'config_destinataires_creation_cotisation_tresorier',
		'config_envoi_email_notif_defaut',
		'notification_gis_config_email',
		'config_envoi_recu_adhesion_cc',
		'config_envoi_recu_participation_cc',
	);

	foreach ($champs as $champ) {
		$valeur = trim((string) _request($champ));
		if ($valeur === '') {
			continue;
		}
		foreach (preg_split('/[;,\s]+/', $valeur, -1, PREG_SPLIT_NO_EMPTY) as $email) {
			if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
				$erreurs[$champ] = _T('association_config:erreur_emails_invalides');
				break;
			}
		}
	}

	return $erreurs;
}
