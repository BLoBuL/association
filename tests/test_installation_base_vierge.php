<?php

$racine = dirname(__DIR__);
$administration = file_get_contents($racine . '/association_administrations.php');
$documentation = file_get_contents($racine . '/docs/installation-base-sans-tables-association.md');
$verificateur = file_get_contents($racine . '/spip-cli/AssociationInstallationVerifier.php');
$erreurs = array();

$debut_historique = strpos($administration, '#V1.1.0');
$branche_create = $debut_historique === false ? $administration : substr($administration, 0, $debut_historique);
if (strpos($branche_create, "array('maj_tables', array('spip_association_metas'))") === false) {
	$erreurs[] = 'la création du socle doit installer sa table de configuration';
}
if (preg_match('/spip_asso_(?!ciation_metas)/', $branche_create)) {
	$erreurs[] = 'la création du socle ne doit posséder aucune table métier';
}

$schemas = array(
	'association_base_version' => '1.6.1',
	'association_adhesions_base_version' => '1.1.0',
	'association_compta_base_version' => '1.0.0',
	'association_dons_base_version' => '1.0.0',
	'association_evenements_base_version' => '1.1.0',
	'association_prets_base_version' => '1.0.0',
	'association_ventes_base_version' => '1.0.0',
);
foreach ($schemas as $meta => $version) {
	if (strpos($verificateur, "'$meta' => '$version'") === false) {
		$erreurs[] = "schéma absent du vérificateur : $meta";
	}
	if (strpos($documentation, "$meta=$version") === false) {
		$erreurs[] = "schéma absent de la procédure : $meta";
	}
}

if (strpos($documentation, 'Ne jamais appliquer ce nettoyage lorsqu\'une table') === false) {
	$erreurs[] = 'la procédure doit interdire le nettoyage des métas sur une base métier existante';
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "Installation sur base vierge et métas de schéma documentées.\n";
