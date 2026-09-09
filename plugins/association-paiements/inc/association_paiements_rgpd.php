<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Exporte une transaction Bank sans exposer de données hors de son schéma.
 */
function association_paiements_rgpd_export_transaction($id_transaction) {
	$id_transaction = intval($id_transaction);
	if ($id_transaction <= 0) {
		return [];
	}

	$row = sql_fetsel('*', 'spip_transactions', 'id_transaction=' . $id_transaction);
	if (!$row) {
		return [];
	}

	return [
		'id_transaction' => intval($row['id_transaction'] ?? 0),
		'id_auteur' => intval($row['id_auteur'] ?? 0),
		'auteur_id' => (string) ($row['auteur_id'] ?? ''),
		'auteur' => (string) ($row['auteur'] ?? ''),
		'date_transaction' => association_rgpd_export_date($row['date_transaction'] ?? ''),
		'montant_ht' => (string) ($row['montant_ht'] ?? ''),
		'montant' => (string) ($row['montant'] ?? ''),
		'devise' => (string) ($row['devise'] ?? ''),
		'mode' => (string) ($row['mode'] ?? ''),
		'autorisation_id' => (string) ($row['autorisation_id'] ?? ''),
		'refcb' => (string) ($row['refcb'] ?? ''),
		'validite' => (string) ($row['validite'] ?? ''),
		'abo_uid' => (string) ($row['abo_uid'] ?? ''),
		'montant_regle' => (string) ($row['montant_regle'] ?? ''),
		'date_paiement' => association_rgpd_export_date($row['date_paiement'] ?? ''),
		'statut' => (string) ($row['statut'] ?? ''),
		'reglee' => (string) ($row['reglee'] ?? ''),
		'finie' => intval($row['finie'] ?? 0),
		'message' => (string) ($row['message'] ?? ''),
		'id_panier' => intval($row['id_panier'] ?? 0),
		'id_commande' => intval($row['id_commande'] ?? 0),
		'id_facture' => intval($row['id_facture'] ?? 0),
		'cadeau_email' => (string) ($row['cadeau_email'] ?? ''),
		'cadeau_message' => (string) ($row['cadeau_message'] ?? ''),
	];
}
