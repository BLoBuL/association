<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre lance dans le contexte SPIP.\n");
	exit(1);
}

include_spip('inc/autoriser');
include_spip('inc/association_paiements_transactions');

$lignes = sql_allfetsel('id_transaction,statut', 'spip_transactions', '', '', 'id_transaction') ?: array();
$statuts = array();
$autorisables = array();
foreach ($lignes as $ligne) {
	$id_transaction = (int) $ligne['id_transaction'];
	$statut = (string) $ligne['statut'];
	$statuts[$statut] = ($statuts[$statut] ?? 0) + 1;
	if (autoriser('supprimer', 'transaction', $id_transaction)) {
		$autorisables[] = $id_transaction;
		if ($statut !== 'abandon') {
			fwrite(STDERR, "ECHEC: transaction non abandonnee autorisee: {$id_transaction}.\n");
			exit(1);
		}
	}
}

echo json_encode(array(
	'total' => count($lignes),
	'statuts' => $statuts,
	'ids_suppression_autorisee' => $autorisables,
), JSON_UNESCAPED_SLASHES) . "\n";
