<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Cree un envoi Mailshot a partir d'adresses deja resolues.
 */
function association_communication_mailshot_creer($sujet, $html, array $emails, array $options = []) {
	$destinataires = [];
	foreach ($emails as $email) {
		$email = strtolower(trim((string) $email));
		if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$destinataires[$email] = $email;
		}
	}
	if (!$destinataires || trim((string) $sujet) === '' || trim((string) $html) === '') {
		return 0;
	}
	$date = (string) ($options['date'] ?? date('Y-m-d H:i:s'));
	$donnees = [
		'sujet' => (string) $sujet,
		'html' => (string) $html,
		'listes' => (string) ($options['listes'] ?? 'Temporaire'),
		'total' => count($destinataires),
		'date' => $date,
		'date_start' => (string) ($options['date_start'] ?? $date),
		'statut' => 'init',
		'composition_lock' => 0,
	];
	foreach (['id_evenement', 'from_name', 'from_email'] as $champ) {
		if (array_key_exists($champ, $options)) {
			$donnees[$champ] = $options[$champ];
		}
	}
	$id_mailshot = (int) sql_insertq('spip_mailshots', $donnees);
	if (!$id_mailshot) {
		return 0;
	}
	$inserees = 0;
	foreach ($destinataires as $email) {
		if (sql_insertq('spip_mailshots_destinataires', [
			'id_mailshot' => $id_mailshot,
			'email' => $email,
			'date' => $date,
			'statut' => 'todo',
		])) {
			$inserees++;
		}
	}
	if ($inserees !== count($destinataires)) {
		sql_updateq('spip_mailshots', ['total' => $inserees], 'id_mailshot=' . $id_mailshot);
	}
	ecrire_meta('mailshot_processing', 'oui');
	include_spip('inc/genie');
	genie_queue_watch_dist();
	return $id_mailshot;
}

/**
 * Retourne les inscriptions valides d'un evenement, sans exposer leur email.
 */
function association_email_collectif_inscriptions_evenement($id_evenement) {
	$id_evenement = intval($id_evenement);
	if (!$id_evenement) {
		return [];
	}

	$flux = pipeline('association_communication_email_collectif_evenement', [
		'args' => ['operation' => 'inscriptions', 'id_evenement' => $id_evenement],
		'data' => [],
	]);
	$inscriptions = is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? [])
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
					['nombre' => $nombre_inscrits]
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
	$emails = [];
	$id_auteurs = array_values(array_filter(array_unique(array_map('intval', $id_auteurs))));
	$id_activites = array_values(array_filter(array_unique(array_map('intval', $id_activites))));

	if ($id_auteurs) {
		$res = sql_select('email', 'spip_auteurs', sql_in('id_auteur', $id_auteurs));
		while ($row = sql_fetch($res)) {
			$emails[] = $row['email'] ?? '';
		}
	}

	if ($id_activites && intval($id_evenement)) {
		$flux = pipeline('association_communication_email_collectif_evenement', [
			'args' => [
				'operation' => 'emails',
				'id_evenement' => intval($id_evenement),
				'id_activites' => $id_activites,
			],
			'data' => [],
		]);
		$emails_evenement = is_array($flux) && array_key_exists('args', $flux)
			? (array) ($flux['data'] ?? [])
			: (array) $flux;
		$emails = array_merge($emails, $emails_evenement);
	}

	$destinataires = [];
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
		return [];
	}

	$id_auteurs = _request('selecteur_adherent');
	$id_auteurs = is_array($id_auteurs) ? $id_auteurs : array_filter([$id_auteurs]);
	$id_activites = [];

	if ($mode === 'evenement') {
		$id_activites = _request('selecteur_activite_evenement');
		$id_activites = is_array($id_activites) ? $id_activites : array_filter([$id_activites]);
		if ($type_destinataires_evenement === 'adherents_association') {
			$id_activites = [];
		} else {
			$id_auteurs = [];
		}
	}

	if (!association_email_collectif_resoudre_destinataires($id_auteurs, $id_activites, $id_evenement)) {
		return [
			'selecteur_adherent' => _T('association_communication:email_collectif_aucun_destinataire'),
			'message_erreur' => _T('association_communication:email_collectif_aucun_destinataire'),
		];
	}

	return [];
}

/**
 * Prepare un message editable a partir d'un gabarit evenementiel.
 */
function association_email_collectif_gabarit_evenement($id_evenement, $type = 'libre') {
	$id_evenement = intval($id_evenement);
	$types = ['libre', 'rappel', 'annulation', 'report', 'modification'];
	$type = in_array($type, $types, true) ? $type : 'libre';
	$flux = $id_evenement ? pipeline('association_communication_email_collectif_evenement', [
		'args' => ['operation' => 'evenement', 'id_evenement' => $id_evenement],
		'data' => [],
	]) : [];
	$evenement = is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? [])
		: (array) $flux;
	if (!$evenement) {
		return [];
	}

	if ($type === 'libre') {
		return [
			'id_evenement' => $id_evenement,
			'gabarit_evenement' => 'libre',
			'sujet' => '',
			'titre' => '',
			'chapeau' => '',
			'texte' => '',
		];
	}

	$titre = extraire_multi((string) ($evenement['titre'] ?? ''));
	$titre = trim(supprimer_tags($titre));
	$date = !empty($evenement['date_debut']) ? affdate($evenement['date_debut'], 'd/m/Y H:i') : '';
	$lieu = trim((string) ($evenement['lieu'] ?? ''));

	$details = array_filter([$date, $lieu]);
	$variables = ['titre' => $titre];

	return [
		'id_evenement' => $id_evenement,
		'gabarit_evenement' => $type,
		'sujet' => _T('association_communication:email_collectif_evenement_' . $type . '_sujet', $variables),
		'titre' => $titre,
		'chapeau' => implode(' - ', $details),
		'texte' => '',
	];
}

function association_email_collectif_rappel_evenement($id_evenement) {
	return association_email_collectif_gabarit_evenement($id_evenement, 'rappel');
}
