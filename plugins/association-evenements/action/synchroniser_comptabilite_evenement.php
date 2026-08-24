<?php
/**
 * Action métier de synchronisation de la comptabilité d'un événement
 *
 * Compare les inscriptions payantes d'une activité avec les enregistrements comptables
 * existants, puis ajoute ou supprime des écritures via l'API Comptabilité.
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/comptes');
include_spip('inc/association_compta_ecritures');
include_spip('inc/association_paiements_transactions');

/**
 * Action de synchronisation de la comptabilité d'un événement
 */
function action_synchroniser_comptabilite_evenement_dist() {
    // Récupération de l'identifiant de l'événement
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();

    // Extraction de l'ID de l'événement
    if (preg_match(',^id_evenement=(\d+)$,', $arg, $regs)) {
        $id_evenement = intval($regs[1]);
    } else {
        return false;
    }

    association_log('comptabilite', "Action de synchronisation de la comptabilité pour l'événement ID $id_evenement", 'info');
    // Synchronisation de la comptabilité pour cet événement
    $resultat = synchroniser_comptabilite_evenement($id_evenement);

    // Redirection vers la page d'origine
    if ($redirect = _request('redirect')) {
        include_spip('inc/headers');

        $redirect = html_entity_decode($redirect, ENT_QUOTES, 'UTF-8'); // -> & au lieu de &amp;
        redirige_par_entete($redirect);
    }

    return $resultat;
}


/**
 * Synchronise la comptabilité d'un événement en évitant les doublons
 *
 * @param int $id_evenement Identifiant de l'événement
 * @return array Résultats de la synchronisation
 */
function synchroniser_comptabilite_evenement($id_evenement) {
    // Initialisation des compteurs
    $ajoutes = 0;
    $supprimes = 0;
    $maj = 0;
    $doublons_supprimes = 0;

    // Si l'événement est gratuit, on supprime les écritures liées et on s'arrête
    $payant = sql_getfetsel('payant', 'spip_evenements', 'id_evenement=' . intval($id_evenement));
    if ($payant !== null && intval($payant) === 0) {
		$ecritures = association_compta_ecritures_objet_lister('evenement', $id_evenement);
		foreach ($ecritures as $ecriture) {
			if ((int) ($ecriture['id_transaction'] ?? 0) > 0 && association_compta_ecriture_supprimer((int) $ecriture['id_compte'])) {
				$supprimes++;
			}
		}
        return array(
            'status' => 'success',
            'ajoutes' => 0,
            'maj' => 0,
            'supprimes' => $supprimes,
            'doublons_supprimes' => 0,
            'id_evenement' => $id_evenement
        );
    }

    // 1. Nettoyer les doublons existants
    $doublons_supprimes = nettoyer_doublons_comptabilite($id_evenement);

    // 2. Récupérer toutes les activités avec transactions (inclure la date d'inscription)
	$activites = sql_allfetsel(
		'id_activite,id_transaction,statut AS statut_activite,`date` AS date_inscription',
		'spip_asso_activites',
		'id_evenement=' . (int) $id_evenement . ' AND id_transaction>0'
	);
	$transactions = association_paiements_transactions_lire(array_column($activites ?: array(), 'id_transaction'));

    // 3. Pour chaque activité avec transaction
    // Cache pour éviter plusieurs corrections du montant pour la même transaction
    $transactions_amount_synced = array();

    foreach ($activites as $activite) {
        $id_transaction = $activite['id_transaction'];
        $id_activite = $activite['id_activite'];
        $statut_activite = $activite['statut_activite'];
		$transaction = $transactions[(int) $id_transaction] ?? array();
		$statut_transaction = $transaction['statut'] ?? '';
        $date_inscription = !empty($activite['date_inscription']) ? $activite['date_inscription'] : null;
		$transaction_montant = (float) ($transaction['montant'] ?? 0);
        $est_rembourse = (strpos((string)$statut_transaction, 'rembours') === 0);
        // Critere d'encaissement strict : transaction statut = 'ok'
        $est_encaissee = ($statut_transaction === 'ok');

        // Recuperer les comptes existants pour cette transaction (paiement + remboursement).
		$comptes_transaction = association_compta_ecritures_objet_lister('evenement', $id_evenement, array(
			'id_transaction' => $id_transaction,
		));

        $compte_paiement = null;
        $ids_remboursement = array();
        foreach ($comptes_transaction as $compte) {
            if (floatval($compte['recette']) > 0) {
                if (!$compte_paiement) {
                    $compte_paiement = $compte;
                }
                continue;
            }
            if (floatval($compte['depense']) > 0) {
                $ids_remboursement[] = intval($compte['id_compte']);
            }
        }

        // Si le compte paiement existe mais que son montant diverge de la transaction, on corrige
        if ($compte_paiement && !isset($transactions_amount_synced[$id_transaction])) {
            $compte_recette = floatval($compte_paiement['recette']);
            if (abs($compte_recette - $transaction_montant) > 0.001) {
                // Mettre à jour la recette de l'écriture pour refléter le montant réel encaissé
				association_compta_ecriture_modifier((int) $compte_paiement['id_compte'], array('recette' => $transaction_montant));
                association_log('comptabilite', 'synchroniser_comptabilite_evenement: ajustement montant compte id_compte=' . intval($compte_paiement['id_compte']) . ' de ' . $compte_recette . ' vers ' . $transaction_montant, 'info');
                $maj++;
                // Marquer cette transaction comme ajustée
                $transactions_amount_synced[$id_transaction] = true;
				$compte_paiement['recette'] = $transaction_montant;
            } else {
                $transactions_amount_synced[$id_transaction] = true;
            }
        }

        // Une desinscription non encaissee ne doit pas conserver d'ecriture comptable.
        if ($statut_activite == 'desinscrit' && !$est_encaissee) {
			foreach ($comptes_transaction as $ecriture) {
				if (association_compta_ecriture_supprimer((int) $ecriture['id_compte'])) {
					$supprimes++;
				}
			}
            continue;
        }

        if (!$compte_paiement) {
            $gestions_places = gestions_places($id_evenement);
			include_spip('inc/association_evenements_comptabilite');
			$id_paiement = association_evenements_compte_inscription_creer($id_activite, $gestions_places);
            if ($id_paiement) {
				$compte_paiement = array('id_compte' => $id_paiement, 'vu' => 0, 'date' => $date_inscription ?: '');
                $ajoutes++;
            }
        }

        if ($compte_paiement) {
            if ($est_encaissee && !$compte_paiement['vu']) {
				association_compta_ecriture_modifier((int) $compte_paiement['id_compte'], array('vu' => 1));
				$compte_paiement['vu'] = 1;
                $maj++;
            }
            if ($date_inscription && $compte_paiement['date'] !== $date_inscription) {
				association_compta_ecriture_modifier((int) $compte_paiement['id_compte'], array('date' => $date_inscription));
				$compte_paiement['date'] = $date_inscription;
                $maj++;
            }
        }

        if ($est_rembourse) {
            if (empty($ids_remboursement)) {
				include_spip('inc/association_evenements_comptabilite');
				$id_remboursement = association_evenements_compte_remboursement_creer($id_transaction, $id_activite);
                if ($id_remboursement) {
                    $ajoutes++;
                }
            }
        } elseif (!empty($ids_remboursement)) {
            // La transaction n'est plus remboursee: supprimer l'ecriture de remboursement.
            foreach ($ids_remboursement as $id_compte_remboursement) {
				if (association_compta_ecriture_supprimer($id_compte_remboursement)) {
					$supprimes++;
				}
            }
        }
    }

    // 4. Récupérer les comptes avec transactions pour cet événement
	$comptes = association_compta_ecritures_objet_lister('evenement', $id_evenement, array('champs' => 'id_compte,id_transaction'));

    // 5. Supprimer les comptes obsolètes (transactions qui n'existent plus dans les activités)
    $ids_transactions_activites = array_column($activites, 'id_transaction');

    foreach ($comptes as $compte) {
        // Si la transaction du compte n'est pas dans les activités, supprimer le compte
		if ((int) $compte['id_transaction'] > 0 && !in_array($compte['id_transaction'], $ids_transactions_activites) && association_compta_ecriture_supprimer((int) $compte['id_compte'])) {
			$supprimes++;
        }
    }

    // Retourner les résultats
    return array(
        'status' => 'success',
        'ajoutes' => $ajoutes,
        'maj' => $maj,
        'supprimes' => $supprimes,
        'doublons_supprimes' => $doublons_supprimes,
        'id_evenement' => $id_evenement
    );
}

/**
 * Nettoie les doublons dans les entrées comptables d'un événement
 *
 * @param int $id_evenement Identifiant de l'événement
 * @return int Nombre de doublons supprimés
 */
function nettoyer_doublons_comptabilite($id_evenement) {
    $doublons_supprimes = 0;

	$comptes_evenement = association_compta_ecritures_objet_lister('evenement', $id_evenement, array(
		'ordre' => 'vu DESC,id_compte ASC',
	));
	$par_transaction = array();
	foreach ($comptes_evenement as $compte) {
		$id_transaction = (int) ($compte['id_transaction'] ?? 0);
		if ($id_transaction > 0) {
			$par_transaction[$id_transaction][] = $compte;
		}
	}
	foreach ($par_transaction as $id_transaction => $comptes) {
		if (count($comptes) <= 1) {
			continue;
		}

        $garder_paiement = false;
        $garder_remboursement = false;

        foreach ($comptes as $compte) {
            $is_paiement = (floatval($compte['recette']) > 0 && floatval($compte['depense']) <= 0);
            $is_remboursement = (floatval($compte['depense']) > 0 && floatval($compte['recette']) <= 0);

            if ($is_paiement && !$garder_paiement) {
                $garder_paiement = true;
                continue;
            }
            if ($is_remboursement && !$garder_remboursement) {
                $garder_remboursement = true;
                continue;
            }

            if ($is_paiement || $is_remboursement) {
				if (association_compta_ecriture_supprimer((int) $compte['id_compte'])) {
					$doublons_supprimes++;
				}
            }
        }
    }

    return $doublons_supprimes;
}
