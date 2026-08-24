<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_paiements_autoriser() {
}

/**
 * Autoriser la suppression uniquement pour une transaction abandonnee et un
 * operateur deja habilite a gerer les transactions par Bank.
 */
function autoriser_transaction_supprimer_dist($faire, $type, $id, $qui, $opt) {
	include_spip('inc/association_paiements_transactions');
	$transaction = association_paiements_transaction_lire((int) $id);
	return ($transaction['statut'] ?? '') === 'abandon'
		&& autoriser('regler', 'transaction', (int) $id, $qui);
}
