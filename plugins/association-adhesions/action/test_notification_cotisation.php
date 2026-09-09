<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// Charger helpers
include_spip('inc/cotisations'); // contient notifier_cotisation_adherent et notifier_cotisation_preparer_contexte

function action_test_notification_cotisation_dist() {
	// Autorisation
	$autorise = function_exists('notifications_cotisation_autoriser_test')
		? notifications_cotisation_autoriser_test()
		: (autoriser('webmestre') || autoriser('configurer'));

	if (!$autorise) {
		action_test_notification_cotisation_redirect(_request('redirect'), _T('association_adhesions:erreur_test_notification_interdit'), false);
	}

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	// format attendu désormais : notification:type:lang:delai:id_compte:id_auteur:email
	[$notification, $type, $lang, $delai, $id_compte, $id_auteur, $email] = array_pad(explode(':', $arg, 7), 7, '');

	$notification = trim($notification);
	$type = $type ?: 'adherent';
	$lang = $lang ?: null;
	$delai = ($delai !== '' && $delai !== null) ? intval($delai) : null;
	$email = $email ?: ($GLOBALS['meta']['email_webmaster'] ?? '');

	// Remplacer l'ancienne logique basée sur une "email de test" configurée globalement
	// par l'adresse de l'auteur ayant déclenché l'action (préférence input_email_membres_asso puis email).
	// Si un email explicite est passé en argument, on le conserve.
	if (empty($email)) {
		// déterminer l'auteur cible : priorité à l'argument id_auteur, sinon session visiteur
		$current_id_auteur = 0;
		if (!empty($id_auteur)) {
			$current_id_auteur = intval($id_auteur);
		} elseif (!empty($GLOBALS['visiteur_session']['id_auteur'])) {
			$current_id_auteur = intval($GLOBALS['visiteur_session']['id_auteur']);
		}

		if ($current_id_auteur > 0) {
			$auteur = sql_fetsel('email, input_email_membres_asso', 'spip_auteurs', 'id_auteur=' . intval($current_id_auteur));
			if ($auteur) {
				// préférence pour input_email_membres_asso (peut contenir plusieurs adresses/format config)
				$raw = trim((string) ($auteur['input_email_membres_asso'] ?? ''));
				if ($raw !== '') {
					$parsed = parser_emails_depuis_config($raw);
					if (!empty($parsed)) {
						// prendre la première adresse valide
						$email = $parsed[0];
					}
					if (empty($email)) {
						association_log('notifications', 'test_notification_cotisation: input_email_membres_asso present mais aucun email valide pour auteur ' . $current_id_auteur, 'erreur');
					}
				}

				// fallback vers le champ email standard
				if (empty($email) && !empty($auteur['email']) && filter_var($auteur['email'], FILTER_VALIDATE_EMAIL)) {
					$email = $auteur['email'];
					association_log('notifications', 'test_notification_cotisation: utilisation du champ email standard pour auteur ' . $current_id_auteur, 'info');
				}
			}
		}

		// dernier recours: garder email_webmaster si toujours vide
		if (empty($email) && !empty($GLOBALS['meta']['email_webmaster'])) {
			$email = $GLOBALS['meta']['email_webmaster'];
			association_log('notifications', 'test_notification_cotisation: aucun email d\'auteur valide trouve, fallback vers email_webmaster', 'erreur');
		}
	}

	// Préparer le contexte via le helper central (si possible).
	// Si un id_compte ou id_auteur est fourni, tenter de récupérer les données depuis la base.
	$contexte = null;
	$id_compte = intval($id_compte ?: 0);
	$id_auteur = intval($id_auteur ?: 0);
	include_spip('inc/cotisations_stockage');
	if ($id_compte > 0) {
		$query_cot = association_cotisation_lire_par_compte($id_compte);
		if ($query_cot) {
			$query_cat = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . intval($query_cot['id_categorie'] ?? 0));
			include_spip('inc/association_adhesions_integrations');
			$query_tx = association_adhesions_transaction_lire((int) ($query_cot['id_transaction'] ?? 0));
			if (function_exists('notifier_cotisation_preparer_contexte')) {
				$contexte = notifier_cotisation_preparer_contexte($query_cot, $query_cat ?: [], $query_tx ?: []);
			} else {
				// Fallback minimal context built from SQL rows to avoid fatal when helper missing
				$contexte = [
					'id_compte' => intval($query_cot['id_compte'] ?? 0),
					'id_auteur' => intval($query_cot['id_auteur'] ?? 0),
					'type_adherent' => $query_cat['type_adherent'] ?? 'adherent',
					'type_cotisation' => $query_cat['titre'] ?? $query_cat['nom'] ?? '',
					'montant' => $query_tx['montant'] ?? $query_cot['montant'] ?? 0,
					'id_transaction' => $query_tx['id_transaction'] ?? $query_cot['id_transaction'] ?? '',
					'validite' => $query_cot['validite'] ?? '',
				];
			}
		}
	}
	// Si pas de contexte via id_compte, mais id_auteur fourni, tenter de prendre le dernier compte de l'auteur
	if (!$contexte && $id_auteur > 0) {
		$last_id_compte = sql_getfetsel('id_compte', 'spip_asso_cotisations', 'id_auteur=' . intval($id_auteur), '', 'id_cotisation DESC');
		if ($last_id_compte) {
			$query_cot = association_cotisation_lire_par_compte($last_id_compte);
			if ($query_cot) {
				$query_cat = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . intval($query_cot['id_categorie'] ?? 0));
				include_spip('inc/association_adhesions_integrations');
				$query_tx = association_adhesions_transaction_lire((int) ($query_cot['id_transaction'] ?? 0));
				if (function_exists('notifier_cotisation_preparer_contexte')) {
					$contexte = notifier_cotisation_preparer_contexte($query_cot, $query_cat ?: [], $query_tx ?: []);
				} else {
					$contexte = [
						'id_compte' => intval($query_cot['id_compte'] ?? 0),
						'id_auteur' => intval($query_cot['id_auteur'] ?? 0),
						'type_adherent' => $query_cat['type_adherent'] ?? 'adherent',
						'type_cotisation' => $query_cat['titre'] ?? $query_cat['nom'] ?? '',
						'montant' => $query_tx['montant'] ?? $query_cot['montant'] ?? 0,
						'id_transaction' => $query_tx['id_transaction'] ?? $query_cot['id_transaction'] ?? '',
						'validite' => $query_cot['validite'] ?? '',
					];
				}
			}
		}
	}

	// Remplacer par la version qui accepte notification/type/lang/delai si existante
	if (!$contexte && function_exists('notifications_cotisation_preparer_contexte')) {
		// compat: utiliser l'ancien utilitaire si présent
		$contexte = notifications_cotisation_preparer_contexte($notification, $type, $lang, $delai);
	}

	// Construire des structures factices attendues par notifier_cotisation_adherent / notifier_cotisation_admin
	$fake_query_cotisation = [
		'id_compte' => isset($contexte['id_compte']) ? intval($contexte['id_compte']) : ($id_compte ?: 0),
		'id_auteur' => isset($contexte['id_auteur']) ? intval($contexte['id_auteur']) : ($id_auteur ?: 0),
		'reinscription' => $contexte['reinscription'] ?? '',
		'id_transaction' => $contexte['id_transaction'] ?? '',
		'recette' => $contexte['montant'] ?? 0,
	];
	$fake_query_categories = [
		'type_adherent' => $contexte['type_adherent'] ?? $type,
		'valeur' => $contexte['type_cotisation'] ?? '',
		'devise' => $contexte['devise'] ?? '',
	];
	$fake_query_transaction = [
		'id_transaction' => $contexte['id_transaction'] ?? '',
		'montant' => $contexte['montant'] ?? 0,
		'devise' => $contexte['devise'] ?? '',
	];

	// Déduire le type et la cible (adherent vs admin) pour l'envoi
	$type_map = null;
	$target_admin = false;
	if (strpos($notification, 'echeances') !== false) {
		$type_map = (strpos($notification, 'echu') !== false) ? 'echeance_echu' : 'echeance_preventive';
	} elseif (strpos($notification, 'attente_paiement') !== false) {
		$type_map = 'attente_paiement';
		// 'cotisation-attente_admin' should be admin
		if (strpos($notification, 'admin') !== false) {
			$target_admin = true;
		}
	} elseif (strpos($notification, 'validation_post-paiement') !== false) {
		$type_map = 'validation_post-paiement';
	} elseif (strpos($notification, 'validation_pre-paiement') !== false) {
		$type_map = 'validation_pre-paiement';
	} elseif (strpos($notification, 'activation') !== false) {
		$type_map = 'activation';
	} elseif (strpos($notification, 'justificatifs-a-revoir') !== false) {
		$type_map = 'justificatifs-a-revoir';
	}
	// Admin-specific notifications
	if (!$type_map) {
		if (strpos($notification, 'demande_admin') !== false || strpos($notification, 'demande') !== false) {
			$target_admin = true;
			$type_map = 'attente_validation';
		} elseif (strpos($notification, 'attente_admin') !== false) {
			$target_admin = true;
			$type_map = 'attente_paiement';
		} elseif (strpos($notification, 'encaisse') !== false) {
			$target_admin = true;
			$type_map = 'encaissement_paiement';
		}
	}

	$message = '';
	$ok = false;

	if ($type_map) {
		// Appel direct à la fonction centralisée. Passer le contexte et demander envoi direct.
		if ($target_admin) {
			$opts = [];
			// priorité : email explicite passé en argument
			if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$opts['email_override'] = $email;
			} else {
				// Aucun email explicite : tenter de ré-utiliser l'email déterminé plus haut (auteur ou meta webmaster)
				if (!empty($email)) {
					// si l'email contient plusieurs adresses/formats, parser
					$parsed = parser_emails_depuis_config($email);
					if (!empty($parsed)) {
						$opts['email_override'] = $parsed;
					} else {
						// si parsing vide, tenter l'email brut
						if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
							$opts['email_override'] = [$email];
						}
					}
					if (empty($opts['email_override'])) {
						association_log('notifications', 'test_notification_cotisation: email_determine present mais aucun destinataire valide', 'erreur');
					} else {
						association_log('notifications', 'test_notification_cotisation: envoi de la notification admin vers l\'auteur/destinataire determine: ' . implode(',', (array) $opts['email_override']), 'info');
					}
				}
			}
			// Forcer l'envoi direct pour les tests via UI (bypass queue)
			$opts['use_queue'] = false;
			$ok = notifier_cotisation_admin($fake_query_cotisation, $fake_query_categories, $fake_query_transaction, $type_map, $opts);
		} else {
			// Do not pass a full 'context' array to notifier_cotisation_adherent to avoid injecting
			// nested arrays or serialized strings into the template context.
			// Instead, ensure $fake_query_* contain the minimal scalar identifiers and pass scalar options.
			// If we have a prepared $contexte, prefer to populate fake query arrays from it.
			if (!empty($contexte) && is_array($contexte)) {
				if (isset($contexte['id_compte'])) {
					$fake_query_cotisation['id_compte'] = intval($contexte['id_compte']);
				}
				if (isset($contexte['id_auteur'])) {
					$fake_query_cotisation['id_auteur'] = intval($contexte['id_auteur']);
				}
				if (isset($contexte['reinscription'])) {
					$fake_query_cotisation['reinscription'] = (string) $contexte['reinscription'];
				}
				if (isset($contexte['id_transaction'])) {
					$fake_query_cotisation['id_transaction'] = $contexte['id_transaction'];
					$fake_query_transaction['id_transaction'] = $contexte['id_transaction'];
				}
				if (isset($contexte['montant'])) {
					$fake_query_transaction['montant'] = $contexte['montant'];
				}
				if (isset($contexte['type_cotisation'])) {
					$fake_query_categories['valeur'] = $contexte['type_cotisation'];
				}
				if (isset($contexte['type_adherent'])) {
					$fake_query_categories['type_adherent'] = $contexte['type_adherent'];
				}
			}

			// If we still don't have an id_auteur but we have an email, try to find the author
			if (empty($fake_query_cotisation['id_auteur']) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$found = sql_getfetsel('id_auteur', 'spip_auteurs', 'email=' . sql_quote($email));
				if ($found) {
					$fake_query_cotisation['id_auteur'] = intval($found);
				}
			}

			$opts = ['use_queue' => false];
			if ($delai !== null) {
				$opts['nb_jour_differences'] = intval($delai);
			}
			if (!empty($lang)) {
				$opts['lang'] = $lang;
			}
			if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$opts['email_override'] = $email;
			}

			$ok = notifier_cotisation_adherent($fake_query_cotisation, $fake_query_categories, $fake_query_transaction, $type_map, $opts);
		}
		if ($ok) {
			$message = _T('association_adhesions:info_notification_test_envoyee', ['notification' => $notification, 'email' => $email]);
		} else {
			$message = _T('association_adhesions:erreur_envoi_email');
		}
	} else {
		// Fallback : essayer l'ancien helper d'envoi de test si disponible
		if (function_exists('notifications_cotisation_envoyer_test')) {
			$ok = notifications_cotisation_envoyer_test($notification, $type, $email, $lang, $delai, $message);
		} else {
			$message = _T('association_adhesions:erreur_envoi_email');
			$ok = false;
		}
	}

	action_test_notification_cotisation_redirect(_request('redirect'), $message ?: _T('association_adhesions:erreur_envoi_email'), $ok);
}

function action_test_notification_cotisation_redirect($redirect, $message, $ok) {
	if ($redirect) {
		include_spip('inc/headers');
		$param = $ok ? 'message_ok' : 'message_erreur';
		$redirect = parametre_url($redirect, $param, rawurlencode($message));
		redirige_par_entete($redirect);
	}

	include_spip('inc/minipres');
	echo minipres('', $message);
	exit;
}
function notifications_cotisation_autoriser_test() {
	return autoriser('webmestre') || autoriser('configurer');
}
