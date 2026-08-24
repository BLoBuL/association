<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre execute dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/association_compta_ecritures');
include_spip('inc/association_compta_maintenance');
$empreinte = function () {
	return hash('sha256', json_encode(array(
		sql_allfetsel('*', 'spip_asso_comptes', '', '', 'id_compte'),
		sql_allfetsel('*', 'spip_asso_destination_op', '', '', 'id_compte,id_destination'),
	)));
};
$avant = $empreinte();
$comptes = sql_allfetsel('*', 'spip_asso_comptes', '', '', 'id_compte') ?: array();
foreach ($comptes as $compte) {
	if (association_compta_ecriture_lire((int) $compte['id_compte']) !== $compte) {
		fwrite(STDERR, "La lecture API d une ecriture differe de la table.\n");
		exit(1);
	}
}
$ids_auteurs = array_values(array_filter(array_unique(array_map('intval', array_column($comptes, 'id_auteur')))));
$simulation = asso_supprimer_comptes_auteurs($ids_auteurs, true);
$attendues = $ids_auteurs ? (int) sql_countsel('spip_asso_comptes', sql_in('id_auteur', $ids_auteurs)) : 0;
$apres = $empreinte();
if ((int) ($simulation['supprimes'] ?? -1) !== $attendues || $avant !== $apres) {
	fwrite(STDERR, "La maintenance comptable simulee est incoherente ou modifie la base.\n");
	exit(1);
}
echo json_encode(array(
	'ok' => true,
	'ecritures_comparees' => count($comptes),
	'suppressions_simulees' => (int) $simulation['supprimes'],
	'empreinte_stable' => $apres,
), JSON_UNESCAPED_SLASHES) . "\n";
