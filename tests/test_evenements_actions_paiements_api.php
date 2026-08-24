<?php

$racine = dirname(__DIR__);
$source = file_get_contents($racine . '/plugins/association-evenements/action/gerer_activites.php');
$api = file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_transactions.php');
if (strpos($source, 'spip_transactions') !== false
	|| strpos($source, 'association_evenements_transaction_lire(') === false
	|| strpos($source, 'association_evenements_transaction_modifier(') === false
	|| strpos($source, 'association_evenements_transaction_supprimer_non_encaissee(') === false) {
	fwrite(STDERR, "Les actions BO Événements contournent encore la façade Paiements.\n");
	exit(1);
}
if (strpos($api, "=== 'ok'") === false) {
	fwrite(STDERR, "La suppression Paiements ne protège pas les transactions encaissées.\n");
	exit(1);
}
echo "OK: les actions BO utilisent Paiements et protègent les encaissements.\n";
