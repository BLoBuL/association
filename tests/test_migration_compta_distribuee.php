<?php

$racine = dirname(__DIR__);
$formulaire = file_get_contents($racine . '/plugins/association-compta/formulaires/migrer_asso_comptabilite.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_migration_compta.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_migration_compta.php');
$paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_pipelines.php');

$erreurs = array();
if (!str_contains($formulaire, "pipeline('association_compta_migration_metiers'")) {
	$erreurs[] = 'Le formulaire ne distribue pas les migrations métier.';
}
foreach (array('appliquer_migration_manuelle', 'appliquer_migration_auto', 'get_config_plan_comptable_migration') as $ancienne) {
	if (str_contains($formulaire, 'function ' . $ancienne . '(')) {
		$erreurs[] = "L'ancienne fonction $ancienne subsiste dans Comptabilité.";
	}
}
if (!str_contains($adhesions, "objet='cotisation'") || str_contains($evenements, "objet='cotisation'")) {
	$erreurs[] = 'La migration des cotisations n’est pas isolée dans Adhésions.';
}
if (!str_contains($evenements, "c.objet='evenement'") || str_contains($adhesions, "c.objet='evenement'")) {
	$erreurs[] = 'La migration des événements n’est pas isolée dans Événements.';
}
if (!str_contains($paiements, 'function association_paiements_association_compta_migration_metiers(')) {
	$erreurs[] = 'Paiements ne prend pas en charge ses transactions orphelines pendant la migration.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK: la migration comptable délègue cotisations, événements et paiements à leurs plugins.\n";
