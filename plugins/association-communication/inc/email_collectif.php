<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourne les inscriptions valides d'un evenement, sans exposer leur email.
 */
function association_email_collectif_inscriptions_evenement($id_evenement) {
	$id_evenement = intval($id_evenement);
	if (!$id_evenement) {
		return array();
	}

	$flux = pipeline('association_communication_email_collectif_evenement', array(
		'args' => array('operation' => 'inscriptions', 'id_evenement' => $id_evenement),
		'data' => array(),
	));
	$inscriptions = is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? array())
		: (array) $flux;
	foreach ($inscriptions as $row) {
		$id_activite = intval($row['id_activite'] ?? 0);
		if ($id_activite) {
			$nom = trim((string) ($row['nom_inscrit'] ?? ''));
			$prenom = trim((string) ($row['prenom_inscrit'] ?? ''));
			$nombre_inscrits = max(1, intval($row['nombre_inscrits'] ?? 1));
			$row['libelle'] = trim($prenom . ' ' . $nom);
			if ($nombre_inscrits > 1) {
				$row['libelle'] .= ' — ' . _T(
					'association_communication:email_collectif_inscription_participants',
					array('nombre' => $nombre_inscrits)
				);
			}
			$inscriptions[$id_activite] = $row;
		}
	}

	return $inscriptions;
}

/**
 * Presélectionne les inscriptions une seule fois, avant leur premier affichage.
 */
function association_email_collectif_selection_activites_initiale(array $inscriptions, array $selection, $selection_affichee) {
	$selection = array_values(array_filter(array_map('intval', $selection)));
	if (!$selection_affichee && !$selection) {
		$selection = array_keys($inscriptions);
	}

	return array_values(array_filter(array_map('intval', $selection)));
}

/**
 * Resout les emails au dernier moment depuis les IDs selectionnes.
 */
function association_email_collectif_resoudre_destinataires(array $id_auteurs, array $id_activites, $id_evenement = 0) {
	$emails = array();
	$id_auteurs = array_values(array_filter(array_unique(array_map('intval', $id_auteurs))));
	$id_activites = array_values(array_filter(array_unique(array_map('intval', $id_activites))));

	if ($id_auteurs) {
		$res = sql_select('email', 'spip_auteurs', sql_in('id_auteur', $id_auteurs));
		while ($row = sql_fetch($res)) {
			$emails[] = $row['email'] ?? '';
		}
	}

	if ($id_activites && intval($id_evenement)) {
		$flux = pipeline('association_communication_email_collectif_evenement', array(
			'args' => array(
				'operation' => 'emails',
				'id_evenement' => intval($id_evenement),
				'id_activites' => $id_activites,
			),
			'data' => array(),
		));
		$emails_evenement = is_array($flux) && array_key_exists('args', $flux)
			? (array) ($flux['data'] ?? array())
			: (array) $flux;
		$emails = array_merge($emails, $emails_evenement);
	}

	$destinataires = array();
	foreach ($emails as $email) {
		$email = strtolower(trim((string) $email));
		if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$destinataires[$email] = $email;
		}
	}

	return array_values($destinataires);
}

/**
 * Verifie la selection du parcours demande sans melanger leurs destinataires.
 */
function association_email_collectif_verifier_selection($mode, $etape, $id_evenement = 0) {
	$type_destinataires_evenement = _request('type_destinataires_evenement') ?: 'inscrits_evenement';
	$etape_validation = $mode === 'evenement' && $type_destinataires_evenement === 'inscrits_evenement'
		? 4
		: 5;
	if (intval($etape) < $etape_validation) {
		return array();
	}

	$id_auteurs = _request('selecteur_adherent');
	$id_auteurs = is_array($id_auteurs) ? $id_auteurs : array_filter(array($id_auteurs));
	$id_activites = array();

	if ($mode === 'evenement') {
		$id_activites = _request('selecteur_activite_evenement');
		$id_activites = is_array($id_activites) ? $id_activites : array_filter(array($id_activites));
		if ($type_destinataires_evenement === 'adherents_association') {
			$id_activites = array();
		} else {
			$id_auteurs = array();
		}
	}

	if (!association_email_collectif_resoudre_destinataires($id_auteurs, $id_activites, $id_evenement)) {
		return array(
			'selecteur_adherent' => _T('association_communication:email_collectif_aucun_destinataire'),
			'message_erreur' => _T('association_communication:email_collectif_aucun_destinataire'),
		);
	}

	return array();
}

/**
 * Prepare un message editable a partir d'un gabarit evenementiel.
 */
function association_email_collectif_gabarit_evenement($id_evenement, $type = 'libre') {
	$id_evenement = intval($id_evenement);
	$types = array('libre', 'rappel', 'annulation', 'report', 'modification');
	$type = in_array($type, $types, true) ? $type : 'libre';
	$flux = $id_evenement ? pipeline('association_communication_email_collectif_evenement', array(
		'args' => array('operation' => 'evenement', 'id_evenement' => $id_evenement),
		'data' => array(),
	)) : array();
	$evenement = is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? array())
		: (array) $flux;
	if (!$evenement) {
		return array();
	}

	if ($type === 'libre') {
		return array(
			'id_evenement' => $id_evenement,
			'gabarit_evenement' => 'libre',
			'sujet' => '',
			'titre' => '',
			'chapeau' => '',
			'texte' => '',
		);
	}

	$titre = extraire_multi((string) ($evenement['titre'] ?? ''));
	$titre = trim(supprimer_tags($titre));
	$date = !empty($evenement['date_debut']) ? affdate($evenement['date_debut'], 'd/m/Y H:i') : '';
	$lieu = trim((string) ($evenement['lieu'] ?? ''));

	$details = array_filter(array($date, $lieu));
	$variables = array('titre' => $titre);

	return array(
		'id_evenement' => $id_evenement,
		'gabarit_evenement' => $type,
		'sujet' => _T('association_communication:email_collectif_evenement_' . $type . '_sujet', $variables),
		'titre' => $titre,
		'chapeau' => implode(' - ', $details),
		'texte' => '',
	);
}

function association_email_collectif_rappel_evenement($id_evenement) {
	return association_email_collectif_gabarit_evenement($id_evenement, 'rappel');
}
