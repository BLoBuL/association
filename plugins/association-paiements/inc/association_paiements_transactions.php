<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Lire les informations non sensibles nécessaires aux modules consommateurs.
 */
function association_paiements_transactions_lire(array $ids_transactions) {
	$ids_transactions = array_values(array_filter(array_unique(array_map('intval', $ids_transactions))));
	if (!$ids_transactions) {
		return array();
	}
	$rows = sql_allfetsel(
		'id_transaction,id_auteur,id_commande,statut,mode,montant_ht,montant,devise,montant_regle,reglee,finie,date_transaction,date_paiement,transaction_hash',
		'spip_transactions',
		sql_in('id_transaction', $ids_transactions)
	);
	$index = array();
	foreach ($rows ?: array() as $row) {
		$index[(int) $row['id_transaction']] = $row;
	}
	return $index;
}

function association_paiements_transaction_commande_lire($id_commande) {
	$id_commande = (int) $id_commande;
	if ($id_commande <= 0) return array();
	$id_transaction = (int) sql_getfetsel('id_transaction', 'spip_transactions', 'id_commande=' . $id_commande, '', 'id_transaction DESC');
	return association_paiements_transaction_lire($id_transaction);
}

function association_paiements_transactions_auteur_lire($id_auteur, array $statuts = array()) {
	$id_auteur = (int) $id_auteur;
	if ($id_auteur <= 0) return array();
	$where = array('id_auteur=' . $id_auteur);
	$statuts = array_values(array_unique(array_filter(array_map('strval', $statuts), 'strlen')));
	if ($statuts) $where[] = sql_in('statut', $statuts);
	$ids = array_column(sql_allfetsel('id_transaction', 'spip_transactions', $where) ?: array(), 'id_transaction');
	return association_paiements_transactions_lire($ids);
}

function association_paiements_transaction_lire($id_transaction) {
	$id_transaction = (int) $id_transaction;
	if ($id_transaction <= 0) {
		return array();
	}
	$transactions = association_paiements_transactions_lire(array($id_transaction));
	return $transactions[$id_transaction] ?? array();
}

function association_paiements_transaction_modifier($id_transaction, array $donnees) {
	$id_transaction = (int) $id_transaction;
	if ($id_transaction <= 0) {
		return false;
	}
	$autorises = array('statut', 'mode', 'montant_ht', 'montant', 'devise', 'montant_regle', 'reglee', 'finie', 'date_paiement', 'message');
	$donnees = array_intersect_key($donnees, array_flip($autorises));
	if (!$donnees) {
		return true;
	}
	return sql_updateq('spip_transactions', $donnees, 'id_transaction=' . $id_transaction) !== false;
}

function association_paiements_transaction_supprimer_non_encaissee($id_transaction) {
	$resultat = association_paiements_transactions_supprimer_non_encaissees(array($id_transaction), false);
	return $resultat['supprimes'] === 1;
}

/**
 * Simuler ou supprimer en lot des transactions non encaissées.
 *
 * Les transactions encaissées sont toujours protégées et les identifiants
 * absents sont ignorés.
 */
function association_paiements_transactions_supprimer_non_encaissees(array $ids_transactions, $dry_run = true) {
	$transactions = association_paiements_transactions_lire($ids_transactions);
	$eligibles = array();
	$protegees = array();
	foreach ($transactions as $id_transaction => $transaction) {
		if (($transaction['statut'] ?? '') === 'ok') {
			$protegees[] = (int) $id_transaction;
		} else {
			$eligibles[] = (int) $id_transaction;
		}
	}
	$nb = count($eligibles);
	if (!$dry_run && $eligibles) {
		$supprimees = sql_delete(
			'spip_transactions',
			sql_in('id_transaction', $eligibles) . " AND statut<>'ok'"
		);
		if ($supprimees === false) {
			return array(
				'supprimes' => 0,
				'ids' => $eligibles,
				'protegees' => $protegees,
				'erreur' => 'suppression_transactions_echouee',
			);
		}
		$nb = (int) $supprimees;
	}
	return array(
		'supprimes' => $nb,
		'ids' => $eligibles,
		'protegees' => $protegees,
	);
}
