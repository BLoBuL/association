<?php

$racine = dirname(__DIR__);
$vue = file_get_contents($racine . '/plugins/association-evenements/prive/objets/liste/table_comptabilite_activites_fonctions.php');
$api = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_ecritures.php');
if (strpos($vue, 'spip_asso_comptes') !== false
	|| strpos($vue, 'association_compta_ecritures_lister(') === false
	|| strpos($api, 'function association_compta_ecritures_lister(') === false) {
	fwrite(STDERR, "La vue comptable Événements ne passe pas exclusivement par l’API Comptabilité.\n");
	exit(1);
}
echo "OK: la vue comptable Événements utilise l’API de lecture Comptabilité.\n";
