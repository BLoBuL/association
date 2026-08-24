<?php

$racine = dirname(__DIR__);
$source = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_comptabilite.php');
foreach (array('spip_asso_comptes', 'spip_asso_destination_op', 'spip_transactions') as $table) {
	if (strpos($source, $table) !== false) {
		fwrite(STDERR, "Événements accède encore directement à {$table}.\n");
		exit(1);
	}
}
if (strpos($source, 'association_paiements_transaction_lire(') === false) {
	fwrite(STDERR, "La couche comptable Événements contourne la façade Paiements.\n");
	exit(1);
}
foreach (array(
	'association_compta_ecritures_objet_lister(',
	'association_compta_ecriture_creer(',
	'association_compta_ecriture_modifier(',
	'association_compta_ecriture_supprimer(',
) as $appel) {
	if (strpos($source, $appel) === false) {
		fwrite(STDERR, "API comptable absente de la couche Événements: {$appel}.\n");
		exit(1);
	}
}
echo "OK: la couche comptable Événements utilise uniquement l’API Comptabilité.\n";
