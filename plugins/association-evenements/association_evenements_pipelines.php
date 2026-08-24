<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_association_paiements_reglement_traiter($flux) {
	if (!empty($flux['data']['traite'])) {
		return $flux;
	}
	$id_transaction = (int) ($flux['args']['id_transaction'] ?? 0);
	$activite = $id_transaction
		? sql_fetsel('*', 'spip_asso_activites', 'id_transaction=' . $id_transaction)
		: array();
	if (!$activite) {
		return $flux;
	}
	include_spip('inc/association_evenements_paiements');
	association_evenements_reglement_traiter($activite, $flux['args']['transaction'] ?? array());
	$flux['data'] = array('traite' => true, 'domaine' => 'evenements');
	return $flux;
}

function association_evenements_association_paiements_redirection_transaction($flux) {
	if (is_string($flux['data']) && $flux['data'] !== '') {
		return $flux;
	}
	$id_transaction = (int) ($flux['args']['id_transaction'] ?? 0);
	$id_evenement = $id_transaction
		? (int) sql_getfetsel('id_evenement', 'spip_asso_activites', 'id_transaction=' . $id_transaction)
		: 0;
	if ($id_evenement) {
		$flux['data'] = generer_url_ecrire('voir_activites', 'id=' . $id_evenement);
	}
	return $flux;
}

function association_evenements_association_paiements_remboursement_traiter($flux) {
	if (!empty($flux['data']['traite'])) {
		return $flux;
	}
	$id_transaction = (int) ($flux['args']['id_transaction'] ?? 0);
	$activite = $id_transaction
		? sql_fetsel('id_activite,email_inscrit', 'spip_asso_activites', 'id_transaction=' . $id_transaction)
		: array();
	if (!$activite) {
		return $flux;
	}

	include_spip('inc/association_evenements_comptabilite');
	association_evenements_compte_remboursement_creer($id_transaction, (int) $activite['id_activite']);
	if (!empty($flux['args']['notifier']) && !empty($activite['email_inscrit'])) {
		job_queue_add(
			'facteur_envoyer_recu_participation',
			'Notification - Recu remboursement',
			array(
				$activite['email_inscrit'],
				$id_transaction,
				(int) $activite['id_activite'],
				'remboursement',
				(string) ($flux['args']['raison'] ?? ''),
			),
			'',
			true,
			0,
			0
		);
	}
	$flux['data'] = array('traite' => true, 'domaine' => 'evenements');
	return $flux;
}

function association_evenements_association_compta_migration_metiers($flux) {
	if (($flux['args']['mode'] ?? '') !== 'auto') {
		return $flux;
	}
	include_spip('inc/association_evenements_migration_compta');
	$flux['data']['ecritures_evenements_migrees'] = association_evenements_migration_compta_automatique();
	include_spip('action/synchroniser_comptabilite_evenement');
	$evenements = sql_allfetsel('DISTINCT id_evenement', 'spip_asso_activites', 'id_transaction>0');
	$nb = 0;
	foreach ($evenements as $evenement) {
		$id_evenement = (int) ($evenement['id_evenement'] ?? 0);
		if ($id_evenement > 0) {
			synchroniser_comptabilite_evenement($id_evenement);
			$nb++;
		}
	}
	$flux['data']['evenements_synchronises'] = $nb;
	return $flux;
}

function association_evenements_association_compta_objets_lister($flux) {
	if (($flux['args']['objet'] ?? '') !== 'evenement') {
		return $flux;
	}
	$evenements = sql_select('id_evenement,titre,date_debut', 'spip_evenements', '', '', 'date_debut DESC');
	while ($evenement = sql_fetch($evenements)) {
		$flux['data'][(int) $evenement['id_evenement']] = affdate_court($evenement['date_debut']) . ' - ' . $evenement['titre'];
	}
	return $flux;
}

function association_evenements_association_config_cli_registre($flux) {
	include_spip('inc/association_evenements_config_cli');
	$flux['data'] = association_config_cli_ajouter_definitions($flux['data'], association_evenements_config_cli_definitions());
	return $flux;
}

function association_evenements_association_configuration_saisies($flux) {
	include_spip('formulaires/inc/configurer_association_evenements');
	$flux['data'][] = array('ordre' => 30, 'saisies' => association_evenements_configurer_saisies(
		$flux['args']['config'] ?? '', (bool) ($flux['args']['disable_meta_admin'] ?? true)
	));
	return $flux;
}

function association_evenements_association_config_cli_snapshot_v1_options($flux) {
	return array_merge($flux, array(
		'evenement.inscription',
		'evenement.selection_famille',
		'evenement.informations_supplementaires',
		'evenement.accompagnants',
		'evenement.invites',
		'evenement.limite_accompagnants',
		'evenement.type_inscrits',
		'evenement.validation',
		'evenement.quota',
		'evenement.liste_attente',
		'evenement.validation_liste_attente',
		'evenement.limite_liste_attente',
	));
}

/**
 * Résout l'événement associé à une inscription sans exposer sa table aux autres modules.
 */
function association_evenements_association_evenement_resoudre_contexte($flux) {
	$id_activite = (int) ($flux['args']['id_activite'] ?? 0);
	if ($id_activite <= 0) {
		return $flux;
	}
	$inscription = sql_fetsel('id_evenement', 'spip_asso_activites', 'id_activite=' . $id_activite);
	if ($inscription) {
		$flux['data'] = (int) $inscription['id_evenement'];
	}
	return $flux;
}

function association_evenements_association_configuration_navigation($flux) {
	$flux['data']['evenement'] = ['ordre' => 40, 'label' => 'association_config:navigation_config_evenement'];
	$flux['data']['evenement_defaut'] = ['ordre' => 50, 'label' => 'association_config:navigation_config_evenement_defaut'];
	return $flux;
}

function association_evenements_association_maintenance_bdd_configurer($flux) {
	$source = $flux['args']['source'] ?? array();
	$flux['data']['jours_inscriptions_en_attente'] = intval(association_maintenance_lire_source(
		$source,
		'meta_cfg_maintenance_jours_inscriptions_attente',
		90
	));
	foreach (array(
		'supprimer_inscriptions_non_validees',
		'anonymiser_inscriptions_inactifs',
		'supprimer_participations_orphelines',
		'supprimer_participations_obsoletes',
	) as $action) {
		$flux['data']['actions'][$action] = association_maintenance_valeur_booleenne(
			association_maintenance_lire_source($source, 'meta_cfg_maintenance_' . $action, true)
		);
	}
	return $flux;
}

function association_evenements_association_maintenance_bdd_verifier_configuration($flux) {
	$champ = 'meta_cfg_maintenance_jours_inscriptions_attente';
	if ($erreur = association_config_maintenance_verifier_entier($champ)) {
		$flux['data'][$champ] = $erreur;
	}
	return $flux;
}

function association_evenements_association_maintenance_bdd_executer($flux) {
	include_spip('inc/association_evenements_maintenance');
	$options = (array) ($flux['args']['options'] ?? array());
	$actions = (array) ($options['actions'] ?? array());
	$inactifs = (array) ($flux['args']['inactifs'] ?? array());
	$maintenant = intval($flux['args']['maintenant'] ?? time());
	$lot = intval($options['lot'] ?? 1000);
	$dry_run = (bool) ($options['dry_run'] ?? true);
	$jours = intval($options['jours_inscriptions_en_attente'] ?? 90);
	$limite = date('Y-m-d H:i:s', $maintenant - ($jours * 86400));
	$anciennes = asso_trouver_inscriptions_non_validees_anciennes($limite, $lot);

	$flux['data']['inscriptions_non_validees_anciennes'] = array(
		'nombre' => count($anciennes),
		'ids_activite' => array_column($anciennes, 'id_activite'),
	);
	if (!empty($actions['supprimer_inscriptions_non_validees']) && $anciennes) {
		$transactions = asso_supprimer_transactions_inscriptions($anciennes, $dry_run);
		$flux['data']['supprimer_transactions_inscriptions'] = $transactions;
		if (asso_resultat_en_echec($transactions)) {
			$flux['data']['supprimer_inscriptions_non_validees'] = array('skipped' => true, 'raison' => 'suppression_transactions_echec');
		} else {
			$flux['data']['supprimer_inscriptions_non_validees'] = asso_supprimer_inscriptions_par_ids(array_column($anciennes, 'id_activite'), $dry_run);
		}
	} else {
		$flux['data']['supprimer_transactions_inscriptions'] = array('skipped' => true);
		$flux['data']['supprimer_inscriptions_non_validees'] = array('skipped' => true);
	}
	if ($inactifs) {
		$flux['data']['anonymiser_inscriptions_inactifs'] = !empty($actions['anonymiser_inscriptions_inactifs'])
			? asso_anonymiser_inscriptions_auteurs($inactifs, $dry_run)
			: array('skipped' => true);
	}
	$flux['data']['supprimer_participations_evenements_orphelines'] = !empty($actions['supprimer_participations_orphelines'])
		? asso_supprimer_participations_evenements_orphelines($dry_run, $lot)
		: array('skipped' => true);
	$flux['data']['supprimer_participations_evenements_obsoletes'] = !empty($actions['supprimer_participations_obsoletes'])
		? asso_supprimer_participations_evenements_obsoletes($dry_run, $lot, $jours)
		: array('skipped' => true);

	return $flux;
}

function association_evenements_taches_generales_cron($taches) {
	$taches['association_expiration_auto_evenement'] = 30 * 60;
	return $taches;
}

function association_evenements_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_evenements_rgpd');
	$flux['data']['inscriptions_evenements'] = association_evenements_rgpd_export_inscriptions(
		intval($flux['args']['id_auteur'] ?? 0),
		(string)($flux['args']['email'] ?? '')
	);
	return $flux;
}

function association_evenements_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$email = trim((string) ($flux['args']['email'] ?? ''));
	$where = array('id_auteur=' . $id);
	if ($email !== '') { $where[] = 'email_inscrit=' . sql_quote($email); }
	$flux['data']['activites_anonymisees'] = association_rgpd_updateq('spip_asso_activites', association_rgpd_filtrer_champs('spip_asso_activites', array(
		'nom_inscrit' => 'Anonyme', 'prenom_inscrit' => '', 'email_inscrit' => '', 'tel_inscrit' => '',
		'ip_inscrit' => '', 'nom_participants' => '', 'commentaire' => '',
	)), '(' . implode(' OR ', $where) . ')');
	return $flux;
}

function association_evenements_declarer_champs_extras($champs) {
	include_once __DIR__ . '/base/association_champs_extras.php';
	$champs = association_declarer_champs_extras_impl($champs);
	foreach ($champs as $table => $saisies) {
		if (is_array($saisies)) {
			$champs[$table] = association_evenements_champs_extras_dedoublonner_afficher_si($saisies);
		}
	}
	return $champs;
}

function association_evenements_champs_extras_dedoublonner_afficher_si($saisies, $condition_parent = '') {
	foreach ($saisies as $cle => $saisie) {
		if (!is_array($saisie)) { continue; }
		foreach (($saisie['options'] ?? array()) as $option => $valeur) {
			if (is_string($valeur) && strpos($valeur, '->doc ') !== false) {
				$saisies[$cle]['options'][$option] = preg_replace('/->doc\s+(\d+)/', '->doc$1', $valeur);
			}
		}
		$condition = trim((string) ($saisie['options']['afficher_si'] ?? ''));
		if ($condition_parent !== '' && $condition === $condition_parent) {
			unset($saisies[$cle]['options']['afficher_si']);
			$condition = '';
		}
		if (!empty($saisie['saisies']) && is_array($saisie['saisies'])) {
			$saisies[$cle]['saisies'] = association_evenements_champs_extras_dedoublonner_afficher_si($saisie['saisies'], $condition !== '' ? $condition : $condition_parent);
		}
	}
	return $saisies;
}

function association_champs_extras_dedoublonner_afficher_si($saisies, $condition_parent = '') {
	return association_evenements_champs_extras_dedoublonner_afficher_si($saisies, $condition_parent);
}

function association_evenements_formulaire_charger($flux) {
    if (($flux['args']['form'] ?? '') !== 'editer_evenement' || !($id_evenement = intval($flux['data']['id_evenement'] ?? 0))) {
        return $flux;
    }
    $categories = sql_select('*', 'spip_asso_categories_activites');
    while ($categorie = sql_fetch($categories)) {
        $id_categorie = intval($categorie['id_categorie']);
        $montant = sql_getfetsel('montant', 'spip_asso_categories_activites_liens', 'id_evenement=' . $id_evenement . ' AND id_categorie=' . $id_categorie);
        $flux['data']['categorie_prix_' . $id_categorie] = ($montant === null || $montant === false) ? '' : $montant;
    }
    return $flux;
}

function association_evenements_formulaire_traiter($flux) {
    if (($flux['args']['form'] ?? '') !== 'editer_evenement' || !($id_evenement = intval($flux['data']['id_evenement'] ?? 0))) {
        return $flux;
    }
    $categories = sql_select('*', 'spip_asso_categories_activites');
    while ($categorie = sql_fetch($categories)) {
        $id_categorie = intval($categorie['id_categorie']);
        $where = 'id_evenement=' . $id_evenement . ' AND id_categorie=' . $id_categorie;
        $insertion = array(
            'id_evenement' => $id_evenement,
            'id_categorie' => $id_categorie,
            'montant' => is_numeric(_request('categorie_prix_' . $id_categorie)) ? _request('categorie_prix_' . $id_categorie) : '',
        );
        if (sql_fetsel('id_evenement', 'spip_asso_categories_activites_liens', $where)) {
            sql_updateq('spip_asso_categories_activites_liens', $insertion, $where);
        } else {
            sql_insertq('spip_asso_categories_activites_liens', $insertion);
        }
        unset($_REQUEST['categorie_prix_' . $id_categorie], $_REQUEST['cextra_categorie_prix_' . $id_categorie]);
    }
    association_sync_repetitions_tarifs($id_evenement);
    return $flux;
}

function association_evenements_normaliser_requete() {
    foreach (array('mode_paiement', 'responsables') as $champ) {
        $valeur = _request($champ);
        if (is_array($valeur)) {
            set_request($champ, implode(',', $valeur));
        }
    }
}

function association_evenements_pre_edition($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_evenements') {
        association_evenements_normaliser_requete();
    }
    return $flux;
}

function association_evenements_pre_insertion($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_evenements' && _request('inscription') == '1') {
        association_evenements_normaliser_requete();
    }
    return $flux;
}

function association_evenements_post_edition($flux) {
    if (($flux['args']['table'] ?? '') !== 'spip_evenements') {
        return $flux;
    }
    $id_evenement = intval($flux['args']['id_objet'] ?? $flux['data']['id_evenement'] ?? $flux['data']['id_objet'] ?? 0);
    $source = $id_evenement ? sql_fetsel('id_evenement_source,payant', 'spip_evenements', 'id_evenement=' . $id_evenement) : false;
    if ($source && intval($source['id_evenement_source']) === 0 && intval($source['payant']) === 1) {
        association_sync_repetitions_tarifs($id_evenement);
    }
    return $flux;
}

function association_evenements_post_insertion($flux) {
    if (($flux['args']['table'] ?? '') !== 'spip_evenements' || _request('inscription') != '1') {
        return $flux;
    }
    $id_evenement = intval($flux['args']['id_objet'] ?? 0);
    association_evenements_normaliser_requete();
    $id_source = intval($flux['data']['id_evenement_source'] ?? 0);
    $source = $id_source ? sql_fetsel('id_evenement_source,payant', 'spip_evenements', 'id_evenement=' . $id_source) : false;
    if (!$id_evenement || !$source || intval($source['id_evenement_source']) !== 0 || intval($source['payant']) !== 1) {
        return $flux;
    }
    $liens = sql_select('*', 'spip_asso_categories_activites_liens', 'id_evenement=' . $id_source);
    while ($lien = sql_fetch($liens)) {
        if ($lien['montant'] !== '' && $lien['montant'] !== null) {
            sql_insertq('spip_asso_categories_activites_liens', array('id_evenement' => $id_evenement, 'id_categorie' => $lien['id_categorie'], 'montant' => $lien['montant']));
        }
    }
    return $flux;
}

function association_evenements_afficher_contenu_objet($flux) {
    if (($flux['args']['type'] ?? '') !== 'evenement' || !($id_evenement = intval($flux['args']['id_objet'] ?? 0))) {
        return $flux;
    }
    if (!sql_getfetsel('payant', 'spip_evenements', 'id_evenement=' . $id_evenement)) {
        return $flux;
    }
    $categories = sql_select('*', 'spip_asso_categories_activites');
    $flux['data'] .= '<h3>' . propre(_T('association_evenements:evenement_montant_label')) . '</h3>';
    while ($categorie = sql_fetch($categories)) {
        $montant = sql_getfetsel('montant', 'spip_asso_categories_activites_liens', 'id_evenement=' . $id_evenement . ' AND id_categorie=' . intval($categorie['id_categorie']), '', 'montant DESC');
        if ($montant === null || $montant === false || $montant === '') {
            continue;
        }
        $valeur = ($montant == 0) ? _T('association_evenements:montant_gratuit') : affiche_monnaie($montant);
        $flux['data'] .= '<div>' . propre($categorie['valeur'] . ' : ' . $valeur) . '</div>';
    }
    sql_free($categories);
    return $flux;
}


function association_get_liens_map($id_evenement)
{
    $id_evenement = intval($id_evenement);
    if (!$id_evenement) {
        return array();
    }

    // Utilise sql_allfetsel pour récupérer en une seule requête
    $rows = sql_allfetsel('id_categorie, montant', 'spip_asso_categories_activites_liens', 'id_evenement=' . $id_evenement);
    $map = array();
    foreach ($rows as $r) {
        $map[intval($r['id_categorie'])] = $r['montant'];
    }
    return $map;
}

function association_sync_repetitions_tarifs($id_evenement)
{
    $id_evenement = intval($id_evenement);
    if (!$id_evenement) {
        return;
    }

    static $processing_sync = array();
    if (!empty($processing_sync[$id_evenement])) {
        association_log('sync', "association_sync_repetitions_tarifs: skip already processing id_evenement={$id_evenement}", 'info');
        return;
    }
    $processing_sync[$id_evenement] = true;

    // Vérifier l'événement source
    $evenement_source = sql_fetsel('id_evenement,id_evenement_source,payant', 'spip_evenements', 'id_evenement=' . $id_evenement);
    if (!$evenement_source
        || intval($evenement_source['id_evenement_source']) !== 0
        || intval($evenement_source['payant']) !== 1
    ) {
        unset($processing_sync[$id_evenement]);
        return;
    }

    // Récupérer la map des liens du source (id_categorie => montant)
    $liens_source_map = association_get_liens_map($id_evenement);
    if (empty($liens_source_map)) {
        // Rien à propager
        unset($processing_sync[$id_evenement]);
        return;
    }

    // Récupérer toutes les répétitions (id uniquement)
    $repetitions = sql_allfetsel('id_evenement', 'spip_evenements', 'id_evenement_source=' . $id_evenement);
    foreach ($repetitions as $rep_row) {
        $id_rep = intval($rep_row['id_evenement']);
        if (!$id_rep) {
            continue;
        }

        // Récupérer la map actuelle des liens sur la répétition
        $liens_rep_map = association_get_liens_map($id_rep);

        // Calculer diff : insert, update, delete
        $to_insert = array(); // cat => montant
        $to_update = array(); // cat => montant
        $to_delete = array(); // list of cat

        // Source -> rep : insert or update
        foreach ($liens_source_map as $cat => $mont) {
            if (!array_key_exists($cat, $liens_rep_map)) {
                // insérer seulement si montant non vide
                if ($mont !== '' && $mont !== null) {
                    $to_insert[$cat] = $mont;
                }
            } else {
                // mettre à jour si différent (comparaison en string pour prudence)
                if ((string)$liens_rep_map[$cat] !== (string)$mont) {
                    $to_update[$cat] = $mont;
                }
            }
        }
        // Rep contient des catégories absentes du source -> supprimer
        foreach ($liens_rep_map as $cat => $mont) {
            if (!array_key_exists($cat, $liens_source_map)) {
                $to_delete[] = $cat;
            }
        }

        // Appliquer les changements (simple, sans transaction)
        if (!empty($to_delete)) {
            sql_delete(
                'spip_asso_categories_activites_liens',
                'id_evenement=' . $id_rep . ' AND ' . sql_in('id_categorie', array_map('intval', $to_delete))
            );
        }

        foreach ($to_update as $cat => $mont) {
            sql_updateq(
                'spip_asso_categories_activites_liens',
                array('montant' => $mont),
                'id_evenement=' . $id_rep . ' AND id_categorie=' . intval($cat)
            );
        }

        foreach ($to_insert as $cat => $mont) {
            sql_insertq(
                'spip_asso_categories_activites_liens',
                array(
                    'id_evenement' => $id_rep,
                    'id_categorie' => intval($cat),
                    'montant'      => $mont
                )
            );
        }

        association_log('sync', "association_sync_repetitions_tarifs: synced id_evenement_source={$id_evenement} -> id_rep={$id_rep} (ins:" . count($to_insert) . " upd:" . count($to_update) . " del:" . count($to_delete) . ")", 'info');
    }

    unset($processing_sync[$id_evenement]);
}
