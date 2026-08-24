<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_paiements_association_config_cli_registre($flux) {
	include_spip('inc/association_paiements_config_cli');
	$flux['data'] = association_config_cli_ajouter_definitions($flux['data'], association_paiements_config_cli_definitions());
	return $flux;
}

function association_paiements_association_configuration_saisies($flux) {
	include_spip('formulaires/inc/configurer_association_paiements');
	$flux['data'][] = array('ordre' => 40, 'saisies' => association_paiements_configurer_saisies(
		$flux['args']['config'] ?? '', (bool) ($flux['args']['disable_meta_admin'] ?? true)
	));
	return $flux;
}

function association_paiements_association_compta_migration_metiers($flux) {
	if (($flux['args']['mode'] ?? '') !== 'auto') {
		return $flux;
	}
	include_spip('inc/association_paiements_maintenance');
	$flux['data']['transactions_orphelines'] = asso_supprimer_transactions_orphelines(
		false,
		(int) ($flux['args']['lot'] ?? 100000)
	);
	return $flux;
}


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

    pipeline('association_paiements_reglement_traiter', array(
        'args' => array(
            'id_transaction' => intval($id_transaction),
            'transaction' => is_array($query_transaction) ? $query_transaction : array(),
        ),
        'data' => array('traite' => false, 'domaine' => ''),
    ));

    // Retourne le flux après traitement.
    return $flux;
}

function is_successful_reglement($reglement)
{
    return $reglement['succes']
        and $reglement['type'] == 'acte'
        and $reglement['id_transaction'];
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
        $redirection = pipeline('association_paiements_redirection_transaction', array(
            'args' => array(
                'id_transaction' => (int) $id_transaction,
                'transaction' => is_array($flux['args']['row'] ?? null) ? $flux['args']['row'] : array(),
            ),
            'data' => '',
        ));
        $flux['data'] = is_string($redirection) && $redirection !== ''
            ? $redirection
            : generer_url_ecrire('transaction', 'id_transaction=' . (int) $id_transaction);
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

function association_paiements_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$email = trim((string) ($flux['args']['email'] ?? ''));
	$where = array('id_auteur=' . $id);
	if ($email !== '') { $where[] = 'auteur=' . sql_quote($email); }
	$flux['data']['transactions_anonymisees'] = association_rgpd_updateq('spip_transactions', association_rgpd_filtrer_champs('spip_transactions', array(
		'auteur_id' => (string) $id, 'auteur' => (string) ($flux['args']['anon'] ?? ('anonyme_' . $id)),
		'refcb' => '', 'validite' => '', 'abo_uid' => '', 'pay_id' => '', 'cadeau_email' => '',
		'cadeau_message' => '', 'url_retour' => '', 'token' => '', 'message' => '', 'erreur' => '',
	)), '(' . implode(' OR ', $where) . ')');
	return $flux;
}

function association_paiements_association_configuration_navigation($flux) {
	$flux['data']['mode_paiement'] = ['ordre' => 60, 'label' => 'association_config:navigation_config_mode_paiement'];
	return $flux;
}

function association_paiements_association_maintenance_bdd_configurer($flux) {
	$source = $flux['args']['source'] ?? array();
	$flux['data']['actions']['supprimer_transactions_orphelines'] = association_maintenance_valeur_booleenne(
		association_maintenance_lire_source($source, 'meta_cfg_maintenance_supprimer_transactions_orphelines', true)
	);
	return $flux;
}

function association_paiements_association_maintenance_bdd_executer($flux) {
	include_spip('inc/association_paiements_maintenance');
	$options = (array) ($flux['args']['options'] ?? array());
	$actions = (array) ($options['actions'] ?? array());
	$flux['data']['supprimer_transactions_orphelines'] = !empty($actions['supprimer_transactions_orphelines'])
		? asso_supprimer_transactions_orphelines((bool) ($options['dry_run'] ?? true), intval($options['lot'] ?? 1000))
		: array('skipped' => true);
	return $flux;
}
