<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/inc/fonctions/facteur_envoyer_recu_participation.php');
if (strpos($source, 'spip_transactions') !== false
	|| strpos($source, 'association_paiements_transaction_lire(') === false) {
	fwrite(STDERR, "Le reçu de participation contourne la façade Paiements.\n");
	exit(1);
}
echo "OK: le reçu de participation utilise la façade Paiements.\n";
