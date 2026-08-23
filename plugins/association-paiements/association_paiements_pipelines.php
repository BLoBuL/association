<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }


function association_trig_bank_notifier_reglement($flux)
{
    // Vérifie si le règlement est réussi. Si ce n'est pas le cas, retourne le flux sans modification.
    if (!is_successful_reglement($flux['args'])) {
        return $flux;
    }
    // Récupère l'identifiant de la transaction.
    $id_transaction = $flux['args']['id_transaction'];

    // Récupère les détails de la transaction depuis la table `spip_transactions`.
    $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=$id_transaction");

    // Vérifie si la transaction est liée à une activité.
    if ($query_activite = sql_fetsel('*', 'spip_asso_activites', "id_transaction=$id_transaction")) {
        // Met à jour les informations de participation à l'activité.
        mise_a_jour_participation($query_activite, $query_transaction);
    }
    // Sinon, vérifie si la transaction est liée à une cotisation.
    elseif ($query_cotisation = sql_fetsel('*', 'spip_asso_comptes', "id_transaction=$id_transaction")) {
        // Inclut les fichiers nécessaires pour gérer les cotisations.
        include_spip('inc/cotisations');
        include_spip('inc/api_cotisations');

        // Met à jour les informations de la cotisation.
        mise_a_jour_cotisation($query_cotisation, $query_transaction);
    }
    // Sinon, vérifie si la transaction est liée à une commande Blobul générique.
    elseif (!empty($query_transaction['id_commande']) && function_exists('association_commande_comptable_synchroniser')) {
        association_commande_comptable_synchroniser(
            intval($query_transaction['id_commande']),
            array(
                'id_transaction' => intval($id_transaction),
                'forcer_paiement' => true,
                'source' => 'trig_bank_notifier_reglement',
            )
        );
    }

    // Retourne le flux après traitement.
    return $flux;
}

function is_successful_reglement($reglement)
{
    return $reglement['succes']
        and $reglement['type'] == 'acte'
        and $reglement['id_transaction'];
}

function mise_a_jour_participation($query_activite, $query_transaction)
{
    $date = date('Y-m-d H:i:s');

    // Récupère l'identifiant de l'événement et les informations associées
    $id_transaction = $query_activite['id_transaction'];
    $id_evenement = $query_activite['id_evenement'];
    $query_evenement = sql_fetsel('*', 'spip_evenements', "id_evenement=$id_evenement");
    $id_activite = $query_activite['id_activite'];

    // Vérifie si l'inscription doit être validée automatiquement après le paiement
    if ($query_activite['statut'] != 'ok' && $query_evenement['validation_sur_paiement'] == 'oui') {
        $entree_journal = "$date : " . _T('association:journal_inscription_validation_paiement') . '<br>' . $query_activite['journal'];
        sql_updateq('spip_asso_activites', ["statut" => 'ok', "journal" => $entree_journal], "id_transaction=$id_transaction");

        // Ajoute une tâche pour envoyer une notification de validation d'inscription
        job_queue_add(
            'facteur_envoyer_mail_activites',
            'Notification - Validation inscription automatique suite à un paiement réussi',
            [$id_evenement, "inscription_frontend", [$id_activite]],
            '',
            false,
            0,
            0
        );
    } else {
        // Met à jour le journal pour indiquer l'encaissement du paiement
        $entree_journal = "$date : " . _T('association:journal_encaissement_paiement') . '<br>' . $query_activite['journal'];
        sql_updateq('spip_asso_activites', ["journal" => $entree_journal], "id_transaction=$id_transaction");
    }

    // Valide les comptes associés à l'activité si la gestion des comptes est activée
    if ($GLOBALS['association_metas']['comptes']) {
        valider_compte_activite($id_transaction);
    }



    // Envoie un reçu de paiement si l'option est activée
    if ($GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_participation'] == 'oui') {
        job_queue_add(
            'facteur_envoyer_recu_participation',
            'Notification - Recu encaissement',
            [$query_activite['email_inscrit'], $id_transaction, $id_activite, 'encaissement', ''],
            '',
            true,
            0,
            0
        );
    }
}

function association_bank_redirige_apres_retour_transaction($flux)
{
    ## PERMET DE REDIRIGER OU ON VEUT ##
    $id_transaction = $flux['args']['id_transaction'];
    // Recherche si on est sur l'espace privé
    if ($id_transaction
        and isset($GLOBALS['visiteur_session']['id_auteur'])
        and test_espace_prive()
        and include_spip("inc/autoriser")
        and autoriser("regler", "transaction", $id_transaction)) {
        if ($query_activite = sql_fetsel('*', 'spip_asso_activites', "id_transaction=".intval($id_transaction))) {
            // Redirection vers la fiche de l'activité.
            //$flux['data'] = generer_url_ecrire('editer_asso_activite', 'id='.$query_activite['id_activite']);
            $flux['data'] = generer_url_ecrire('voir_activites', 'id='.$query_activite['id_evenement']);
        } else {
            // Redirection vers la fiche de l'auteur.
            $id_auteur = $flux['args']['row']['id_auteur'];
            $flux['data'] = generer_url_ecrire('voir_adherent', 'id_auteur='.$id_auteur);
        }
    }
    return $flux;
}

function association_paiements_association_maintenance_auteurs_encaisses($flux) {
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? array()))));
	if (!$ids) { return $flux; }
	$res = sql_select(
		'DISTINCT t.id_auteur',
		'spip_transactions AS t',
		sql_in('id_auteur', $ids) . ' AND t.statut=' . sql_quote('ok')
		. ' AND ('
		. 'EXISTS (SELECT 1 FROM spip_asso_comptes AS c WHERE c.id_transaction = t.id_transaction)'
		. ' OR EXISTS (SELECT 1 FROM spip_asso_activites AS a WHERE a.id_transaction = t.id_transaction)'
		. ')'
	);
	while ($row = sql_fetch($res)) { $flux['data'][] = intval($row['id_auteur']); }
	$flux['data'] = array_values(array_unique(array_map('intval', (array) $flux['data'])));
	return $flux;
}

function association_paiements_association_maintenance_supprimer_donnees_auteurs($flux) {
	include_spip('inc/association_paiements_maintenance');
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? array()))));
	$flux['data']['supprimer_transactions_auteurs'] = asso_supprimer_transactions_auteurs($ids, (bool) ($flux['args']['dry_run'] ?? true));
	return $flux;
}
