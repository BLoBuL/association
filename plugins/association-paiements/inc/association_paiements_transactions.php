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
		return [];
	}
	$rows = sql_allfetsel(
		'id_transaction,id_auteur,id_commande,statut,mode,montant_ht,montant,devise,montant_regle,reglee,finie,date_transaction,date_paiement,transaction_hash',
		'spip_transactions',
		sql_in('id_transaction', $ids_transactions)
	);
	$index = [];
	foreach ($rows ?: [] as $row) {
		$index[(int) $row['id_transaction']] = $row;
	}
	return $index;
}

function association_paiements_transaction_commande_lire($id_commande) {
	$id_commande = (int) $id_commande;
	if ($id_commande <= 0) {
		return [];
	}
	$id_transaction = (int) sql_getfetsel('id_transaction', 'spip_transactions', 'id_commande=' . $id_commande, '', 'id_transaction DESC');
	return association_paiements_transaction_lire($id_transaction);
}

function association_paiements_transactions_auteur_lire($id_auteur, array $statuts = []) {
	$id_auteur = (int) $id_auteur;
	if ($id_auteur <= 0) {
		return [];
	}
	$where = ['id_auteur=' . $id_auteur];
	$statuts = array_values(array_unique(array_filter(array_map('strval', $statuts), 'strlen')));
	if ($statuts) {
		$where[] = sql_in('statut', $statuts);
	}
	$ids = array_column(sql_allfetsel('id_transaction', 'spip_transactions', $where) ?: [], 'id_transaction');
	return association_paiements_transactions_lire($ids);
}

function association_paiements_transaction_lire($id_transaction) {
	$id_transaction = (int) $id_transaction;
	if ($id_transaction <= 0) {
		return [];
	}
	$transactions = association_paiements_transactions_lire([$id_transaction]);
	return $transactions[$id_transaction] ?? [];
}

function association_paiements_transaction_modifier($id_transaction, array $donnees) {
	$id_transaction = (int) $id_transaction;
	if ($id_transaction <= 0) {
		return false;
	}
	$autorises = ['statut', 'mode', 'montant_ht', 'montant', 'devise', 'montant_regle', 'reglee', 'finie', 'date_paiement', 'message'];
	$donnees = array_intersect_key($donnees, array_flip($autorises));
	if (!$donnees) {
		return true;
	}
	return sql_updateq('spip_transactions', $donnees, 'id_transaction=' . $id_transaction) !== false;
}

function association_paiements_transaction_supprimer_non_encaissee($id_transaction) {
	$resultat = association_paiements_transactions_supprimer_non_encaissees([$id_transaction], false);
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
	$eligibles = [];
	$protegees = [];
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
			return [
				'supprimes' => 0,
				'ids' => $eligibles,
				'protegees' => $protegees,
				'erreur' => 'suppression_transactions_echouee',
			];
		}
		$nb = (int) $supprimees;
	}
	return [
		'supprimes' => $nb,
		'ids' => $eligibles,
		'protegees' => $protegees,
	];
}
