<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/action/synchroniser_comptabilite_evenement.php');
foreach (array('spip_asso_comptes', 'spip_asso_destination_op', 'spip_transactions') as $table) {
	if (strpos($source, $table) !== false) {
		fwrite(STDERR, "La synchronisation Événements accède encore directement à {$table}.\n");
		exit(1);
	}
}
foreach (array('association_compta_ecritures_objet_lister(', 'association_compta_ecriture_modifier(', 'association_compta_ecriture_supprimer(', 'association_evenements_transactions_lire(') as $appel) {
	if (strpos($source, $appel) === false) {
		fwrite(STDERR, "Contrat absent de la synchronisation Événements: {$appel}.\n");
		exit(1);
	}
}
echo "OK: la synchronisation Événements utilise les API Comptabilité et Paiements.\n";
