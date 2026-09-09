<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function facteur_envoyer_notification_gis($id_auteur, $nom_auteur, $string_recherche, $nouvelle_adresse, $id_gis, $action) {

	if ($action == 'echec') {
		$sujet = _T('association_adhesions:email_notification_gis_echec_sujet', ['nom_adherent' => $nom_auteur]);
		$modele = 'notifications/notification_echec_gis';
	} elseif ($action == 'modification') {
		$sujet = _T('association_adhesions:email_notification_gis_modification_sujet', ['nom_adherent' => $nom_auteur]);
		$modele = 'notifications/notification_modification_gis';
	}

	if (is_array($nouvelle_adresse)) {
		$nouvelle_adresse = $nouvelle_adresse['street'] . ' ' . $nouvelle_adresse['housenumber'] . ', ' . $nouvelle_adresse['district'] . ', ' . $nouvelle_adresse['state'] . ', ' . $nouvelle_adresse['postcode'] . ', ' . $nouvelle_adresse['country'];
	}

	$fond_content = [
		'id_auteur' => $id_auteur,
		'nom_adherent' => $nom_auteur,
		'id_gis' => $id_gis,
		'string_recherche' => $string_recherche,
		'nouvelle_adresse' => $nouvelle_adresse,
	];
	$corps = recuperer_fond($modele, $fond_content);

	$destinataire_meta = $GLOBALS['association_metas']['notification_gis_config_email'] ?? '';
	$destinataires = parser_emails_depuis_config($destinataire_meta);
	// parser_emails_depuis_config retourne false si aucune adresse valide
	if ($destinataires === false) {
		association_log('gis', 'Aucun destinataire valide pour notification GIS (meta notification_gis_config_email)', 'critique');
		return false;
	}
	// Envoi : si un seul destinataire utiliser la string, sinon le tableau
	$dest = (count($destinataires) == 1) ? $destinataires[0] : $destinataires;
	$envoyer = facteur_envoyer_app($dest, $sujet, $corps, false, ['enqueued' => true, 'use_queue' => false]);

	return $envoyer;
}
