<?php

define('_ECRIRE_INC_VERSION', 1);

$racine = dirname(__DIR__);
$modules = array(
	'association_adhesions' => 'association-adhesions',
	'association_compta' => 'association-compta',
	'association_evenements' => 'association-evenements',
	'association_dons' => 'association-dons',
	'association_prets' => 'association-prets',
	'association_ventes' => 'association-ventes',
);

$objets = array();
foreach ($modules as $prefixe => $repertoire) {
	require_once $racine . '/plugins/' . $repertoire . '/base/' . $prefixe . '.php';
	$fonction = $prefixe . '_declarer_tables_objets_sql';
	if (!function_exists($fonction)) {
		fwrite(STDERR, "Pipeline objets SQL absent pour $prefixe.\n");
		exit(1);
	}
	$objets = $fonction($objets);

	$paquet = file_get_contents($racine . '/plugins/' . $repertoire . '/paquet.xml');
	if (!str_contains($paquet, 'nom="declarer_tables_objets_sql"')) {
		fwrite(STDERR, "Pipeline declarer_tables_objets_sql non déclaré pour $prefixe.\n");
		exit(1);
	}
}

$attendues = array(
	'spip_asso_categories_adherents',
	'spip_asso_cotisations',
	'spip_asso_comptes',
	'spip_asso_plan',
	'spip_asso_destination',
	'spip_asso_destination_op',
	'spip_asso_categories_activites',
	'spip_asso_activites',
	'spip_asso_dons',
	'spip_asso_ressources',
	'spip_asso_prets',
	'spip_asso_ventes',
);

sort($attendues);
$declarees = array_keys($objets);
sort($declarees);
if ($declarees !== $attendues) {
	fwrite(STDERR, "Inventaire des objets SQL métier incorrect.\n");
	exit(1);
}

foreach ($objets as $table => $declaration) {
	if (($declaration['principale'] ?? '') !== 'oui') {
		fwrite(STDERR, "$table n'est pas déclarée comme objet principal SPIP.\n");
		exit(1);
	}
	if (empty($declaration['titre']) || empty($declaration['field']) || empty($declaration['key']['PRIMARY KEY'])) {
		fwrite(STDERR, "Déclaration objet SPIP incomplète pour $table.\n");
		exit(1);
	}
}

echo "OK: les douze tables métier principales sont des objets SQL SPIP 4.\n";
