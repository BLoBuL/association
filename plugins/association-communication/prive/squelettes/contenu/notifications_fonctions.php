<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// S'assurer que la fonction centralisée des sujets est disponible
include_spip('inc/cotisations');
include_spip('inc/notifications_cotisations_audit');

/**
 * Catalogue extensible des notifications métier fournies par les plugins actifs.
 */
function association_notifications_metiers(): array {
	$groupes = pipeline('association_notifications_metiers', [
		'args' => [
			'exec' => 'notifications',
		],
		'data' => [],
	]);
	return is_array($groupes) ? $groupes : [];
}

function radio_type_adherent() {
	$flux = pipeline('association_notification_exemple', [
		'args' => ['operation' => 'types_adherents'],
		'data' => [],
	]);
	return is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? [])
		: (array) $flux;
}
function exemple_adherent_par_type($radio_type_adherent) {
	$flux = pipeline('association_notification_exemple', [
		'args' => ['operation' => 'cotisation', 'type_adherent' => (string) $radio_type_adherent],
		'data' => [],
	]);
	return is_array($flux) && array_key_exists('args', $flux)
		? (array) ($flux['data'] ?? [])
		: (array) $flux;
}
function exemple_activite_par_statut($statut, $nombre_inscrits = 1, $payant = 0) {
	$flux = pipeline('association_notification_exemple', [
		'args' => [
			'operation' => 'activite',
			'statut' => (string) $statut,
			'nombre_inscrits' => (int) $nombre_inscrits,
			'payant' => (bool) $payant,
		],
		'data' => 0,
	]);
	$id_activite = is_array($flux) && array_key_exists('args', $flux)
		? (int) ($flux['data'] ?? 0)
		: (int) $flux;
	return $id_activite ?: false;
}

function listes_notifications($type_notifications) {
	if ($type_notifications == 'creation') {
		$res = [
			'auteur_inscription_admin' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_inscription_auteur' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_inscription_pass' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'validation') {
		$res = [
			'auteur_inscription_confirmer' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_inscription_confirmer_admin' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_inscription_valider' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_inscription_verifier_admin' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_invalide' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_invalide_admin' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_valide' => [
				'destinataire' => '',
				'label' => ''],
			'auteur_valide_admin' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'adhesion') {
		$res = [
			'cotisation-attente_admin' => [
				'destinataire' => '',
				'label' => ''],
			'cotisation-demande_admin' => [
				'destinataire' => '',
				'label' => ''],
			'cotisation-attente_paiement' => [
				'destinataire' => '',
				'label' => ''],
			'cotisation-activation' => [
				'destinataire' => '',
				'label' => ''],

			'cotisation-validation_post-paiement' => [
				'destinataire' => '',
				'label' => ''],
			'cotisation-validation_pre-paiement' => [
				'destinataire' => '',
				'label' => ''],
			'cotisation-justificatifs-a-revoir' => [
				'destinataire' => '',
				'label' => ''],

			/*            'nouvel_adherent_email_paiement_instructions' => array(
																'destinataire' => '',
																'label' => ''),
			'nouvel_adherent_email_paiement_valide_instructions' => array(
																'destinataire' => '',
																'label' => ''),*/
		];
	} elseif ($type_notifications == 'echeances') {
		$res = [
			'notification_echeances_adherent' => [
				'destinataire' => '',
				'label' => ''],
			'notification_echeances_adherent_echu' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'preinscription_activite') {
		$res = [
			'preinscription_activite_backend' => [
				'destinataire' => '',
				'label' => ''],
			'preinscription_activite_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'preinscription_activite_responsable_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'preinscription_automatique_activite' => [
				'destinataire' => '',
				'label' => ''],
			'preinscription_automatique_activite_responsable' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'inscription_activite') {
		$res = [
			'inscription_activite_backend' => [
				'destinataire' => '',
				'label' => ''],
			'inscription_activite_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'inscription_activite_responsable_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'inscription_automatique_activite' => [
				'destinataire' => '',
				'label' => ''],
			'inscription_automatique_activite_responsable' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'desinscription_activite') {
		$res = [
			'desinscription_activite_backend' => [
				'destinataire' => '',
				'label' => ''],
			'desinscription_activite_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'desinscription_activite_responsable_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'expiration_automatique_activite' => [
				'destinataire' => '',
				'label' => ''],
			'expiration_automatique_activite_responsable' => [
				'destinataire' => '',
				'label' => ''],
		];
	} elseif ($type_notifications == 'attente_activite') {
		$res = [
			'attente_activite_backend' => [
				'destinataire' => '',
				'label' => ''],
			'attente_activite_frontend' => [
				'destinataire' => '',
				'label' => ''],
			'attente_activite_responsable_frontend' => [
				'destinataire' => '',
				'label' => ''],
		];
	}

	// Enrichir chaque entrée d'un 'sujet' résolu via les fichiers de langue
	if (is_array($res)) {
		foreach ($res as $cle => $infos) {
			$sujet = '';

			// Si la fonction centralisée existe, utiliserla pour les types cotisation / échéances
			if (function_exists('notifications_cotisation_trouver_sujet')) {
				// déterminer si la clé correspond à une notification d'adhésion/échéance
				$cle_us = str_replace('-', '_', $cle);
				$type_map = null;

				// correspondances directes
				if (strpos($cle, 'notification_echeances') !== false) {
					// preventive vs echu
					if (strpos($cle, 'echu') !== false) {
						$type_map = 'echeance_echu';
					} else {
						$type_map = 'echeance_preventive';
					}
				}

				// cotisation-* keys (cotisation-activation, cotisation-attente_paiement, ...)
				if ($type_map === null && strpos($cle, 'cotisation-') === 0) {
					$suffix = substr($cle, strlen('cotisation-'));
					$suf_us = str_replace('-', '_', $suffix);
					if (strpos($suf_us, 'attente_paiement') !== false) {
						$type_map = 'attente_paiement';
					} elseif (strpos($suf_us, 'validation_post') !== false || strpos($suf_us, 'validation_post-paiement') !== false) {
						$type_map = 'validation_post-paiement';
					} elseif (strpos($suf_us, 'validation_pre') !== false || strpos($suf_us, 'validation_pre-paiement') !== false) {
						$type_map = 'validation_pre-paiement';
					} elseif (strpos($suf_us, 'activation') !== false) {
						$type_map = 'activation';
					} elseif (strpos($suf_us, 'attente_admin') !== false || strpos($suf_us, 'attente-admin') !== false) {
						$type_map = 'attente_admin';
					} elseif (strpos($suf_us, 'demande_admin') !== false || strpos($suf_us, 'demande-admin') !== false || strpos($suf_us, 'demande') !== false) {
						// 'cotisation-demande_admin' or variants
						$type_map = 'demande_admin';
					} elseif (strpos($suf_us, 'encaissement') !== false) {
						$type_map = 'encaissement_admin';
					} elseif (strpos($suf_us, 'justificatifs_a_revoir') !== false) {
						$type_map = 'justificatifs-a-revoir';
					}
				}

				// templates 'nouvel_adherent_email_*' mapping
				if ($type_map === null) {
					if ($cle === 'nouvel_adherent_email_paiement_instructions') {
						$type_map = 'attente_paiement';
					} elseif ($cle === 'nouvel_adherent_email_paiement_valide_instructions') {
						$type_map = 'activation';
					}
				}

				// si on a une correspondance, appeler la fonction centralisée
				if ($type_map) {
					// contexte minimal possible (nom/type vide) — la fonction gère les variantes entreprise/adherent selon le contexte
					$contexte_min = [];
					$sujet = notifications_cotisation_trouver_sujet($type_map, $contexte_min);
				}
			}

			// fallback : essayer directement la clé de langue 'notifications:cle_sujet' puis renvoyer une chaîne lisible
			if (!$sujet) {
				// Special-case mapping for well-known template keys -> language keys
				// 1) notification_echeances_* => email_notification_echeances_sujet / email_notification_echeance_echu_sujet
				if (strpos($cle, 'notification_echeances') === 0) {
					if (strpos($cle, 'echu') !== false) {
						$try_keys = ['notifications:email_notification_echeance_echu_sujet', 'email_notification_echeance_echu_sujet'];
					} else {
						$try_keys = ['notifications:email_notification_echeances_sujet', 'email_notification_echeances_sujet'];
					}
					foreach ($try_keys as $tk) {
						$val = _T($tk);
						if ($val && $val !== $tk) {
							$sujet = $val;
							break;
						}
					}
				}

				// 2) cotisation-* templates might map to keys like 'attente_paiement_sujet' or 'validation_post_paiement_sujet'
				if (!$sujet && strpos($cle, 'cotisation-') === 0) {
					// normalize suffix
					$suffix = substr($cle, strlen('cotisation-'));
					$suffix = str_replace('-', '_', $suffix);
					$candidates = [
						'notifications:' . $suffix . '_sujet',
						$suffix . '_sujet',
						// common aliases
						'notifications:cotisation_' . $suffix . '_sujet',
						'cotisation_' . $suffix . '_sujet',
					];
					foreach ($candidates as $cand) {
						$val = _T($cand);
						if ($val && $val !== $cand) {
							$sujet = $val;
							break;
						}
					}
				}

				// normaliser et essayer la clé de langue classique
				if (!$sujet) {
					$cle_norm = str_replace('-', '_', $cle);
					$try = 'notifications:' . $cle_norm . '_sujet';
					$val = _T($try);
					if ($val && $val !== $try) {
						$sujet = $val;
					} else {
						// dernier recours lisible pour debug
						$sujet = 'Sujet: ' . $cle_norm;
					}
				}
			}

			$res[$cle]['sujet'] = $sujet;
		}
	}

	return $res;
}
