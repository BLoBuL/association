<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être exécuté dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/association_adhesions_maintenance_cotisations');

$empreinte = function () {
	return hash('sha256', json_encode([
		'cotisations' => sql_allfetsel('*', 'spip_asso_cotisations', '', '', 'id_cotisation'),
		'comptes' => sql_allfetsel('*', 'spip_asso_comptes', '', '', 'id_compte'),
		'transactions' => sql_allfetsel('id_transaction,statut,montant', 'spip_transactions', '', '', 'id_transaction'),
	]));
};

$avant = $empreinte();
$orphelines = association_adhesions_supprimer_cotisations_orphelines(true, 1000);
$anciennes = association_adhesions_supprimer_cotisations_non_encaissees_anciennes(time(), 12, true, 1000);
$apres = $empreinte();

if ($avant !== $apres) {
	fwrite(STDERR, "Le mode simulation a modifié les données.\n");
	exit(1);
}
if (empty($orphelines['ids']) !== ((int) ($orphelines['supprimees'] ?? 0) === 0)) {
	fwrite(STDERR, "Le rapport des cotisations orphelines est incohérent.\n");
	exit(1);
}
if (empty($anciennes['ids']) !== ((int) ($anciennes['supprimees'] ?? 0) === 0)) {
	fwrite(STDERR, "Le rapport des anciennes cotisations est incohérent.\n");
	exit(1);
}

echo json_encode([
	'ok' => true,
	'orphelines_supprimables' => (int) ($orphelines['supprimees'] ?? 0),
	'orphelines_protegees' => (int) ($orphelines['protegees'] ?? 0),
	'anciennes_non_encaissees' => (int) ($anciennes['supprimees'] ?? 0),
	'dry_run' => true,
	'empreinte' => $apres,
], JSON_UNESCAPED_SLASHES) . "\n";
