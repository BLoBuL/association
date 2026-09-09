<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
/**
 * Envoie un email avec un contenu HTML et des options supplémentaires.
 *
 * Cette fonction accepte désormais comme destinataire :
 *  - une chaîne (email unique),
 *  - un tableau d'adresses (plusieurs destinataires),
 *  - une chaîne contenant plusieurs adresses (séparateurs , ; espace).
 *
 * Elle met en queue une tâche si nécessaire. Lors de l'exécution réelle (ou
 * si $options['enqueued'] est true), elle enverra un email pour chaque adresse
 * valide trouvée.
 *
 * Options reconnues :
 * - use_queue (bool) : si présent, contrôle explicitement l'utilisation de la queue (true = mettre en file).
 * - enqueued (bool) : interne, indique que l'appel provient d'un job et doit procéder à l'envoi (évite boucle de re-queue).
 * - direct (bool) : compatibilité legacy ; si présent et use_queue absent, direct=true force l'envoi immédiat.
 * - autres options passées au corps (pièces jointes, cc, ...)
 *
 * @param string|array $destinataire
 * @param string $sujet
 * @param string $html
 * @param string|array|false $bcc
 * @param array $options
 * @return array ['success'=>bool, 'message'=>string]
 */
function facteur_envoyer_app($destinataire, $sujet, $html, $bcc = false, $options = []) {
	include_spip('inc/filtres');

	$options = is_array($options) ? $options : [];

	// Normaliser les destinataires en tableau d'adresses
	$emails = [];
	if (is_array($destinataire)) {
		$emails = $destinataire;
	} else {
		$str = (string) $destinataire;
		// si la chaîne est vide, on la traitera plus bas
		if ($str !== '') {
			$parsed = parser_emails_depuis_config($str);
			if ($parsed !== false) {
				$emails = $parsed;
			}
		}
	}

	// Filtrer les emails valides
	$emails = array_values(array_unique(array_filter(array_map('trim', $emails), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))));

	if (empty($emails)) {
		return ['success' => false, 'message' => 'Aucun destinataire valide'];
	}

	if (empty($sujet)) {
		return ['success' => false, 'message' => 'Sujet manquant'];
	}
	if (empty($html)) {
		return ['success' => false, 'message' => 'Contenu du message manquant'];
	}

	// Décider si on doit mettre en file
	// Par défaut : async (use_queue=true) sauf si explicitement désactivé ou déjà en cours d'exécution (enqueued)
	if (array_key_exists('use_queue', $options)) {
		$use_queue = (bool) $options['use_queue'];
	} else {
		$use_queue = true; // défaut : toujours async sauf demande explicite
	}

	// Si la tâche n'a pas encore été enqueued et que l'on doit utiliser la queue, on la place
	if ($use_queue && empty($options['enqueued'])) {
		// Marquer l'argument comme enqueued pour le worker
		$opts = $options;
		$opts['enqueued'] = true;
		$titre = 'Notification - facteur_envoyer_app ' . substr($sujet, 0, 80);
		// Arguments : destinataires (tableau), sujet, html, bcc, options
		job_queue_add('facteur_envoyer_app', $titre, [$emails, $sujet, $html, $bcc, $opts], '', false, 0, 0);
		association_log('email', 'facteur_envoyer_app : job mis en queue pour ' . implode(', ', $emails) . ' sujet: ' . $sujet, 'info');
		return ['success' => true, 'message' => 'Email mis en file (job_queue)'];

	}

	// Préparation du corps du message
	$corps = array_merge(
		['html' => $html],
		$options
	);

	if ($bcc) {
		$corps['bcc'] = $bcc;
	}

	$sent = 0;
	$errors = [];

	try {
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
		foreach ($emails as $to) {
			try {
				$resultat = $envoyer_mail($to, $sujet, $corps);
				if ($resultat) {
					association_log('email', "Email envoyé avec succès à $to sujet: $sujet", 'info');
					$sent++;
				} else {
					association_log('email', "Échec de l'envoi d'email à $to sujet: $sujet", 'erreur');
					$errors[] = "Échec envoi $to";
				}
			} catch (Throwable $e) {
				association_log('email', "Exception lors de l'envoi d'email à $to: " . $e->getMessage(), 'erreur');
				$errors[] = $e->getMessage();
			}
		}

		if ($sent > 0) {
			return ['success' => true, 'message' => 'Email(s) envoyé(s) : ' . $sent . (count($emails) > 1 ? ' / ' . count($emails) : '')];
		}
		return ['success' => false, 'message' => 'Aucun envoi réussi : ' . implode(' ; ', $errors)];

	} catch (Exception $e) {
		association_log('email', "Exception lors de l'envoi d'email : " . $e->getMessage(), 'erreur');
		return ['success' => false, 'message' => $e->getMessage()];
	}
}
