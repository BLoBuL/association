<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/inc/rgpd_export.php');
$paquet = file_get_contents($racine . '/paquet.xml');
$erreurs = array();

if (strpos($paquet, '<pipeline nom="association_rgpd_export_auteur" action="" />') === false) {
	$erreurs[] = 'le socle ne déclare pas le contrat public RGPD';
}
foreach (array(
	'spip_asso_cotisations', 'spip_asso_comptes', 'spip_asso_activites',
	'spip_asso_dons', 'spip_asso_ventes', 'spip_asso_prets', 'spip_asso_ressources',
) as $table_metier) {
	if (strpos($socle, $table_metier) !== false) {
		$erreurs[] = 'le socle RGPD lit encore une table métier : ' . $table_metier;
	}
}

$modules = array(
	'association-adhesions' => array('spip_asso_cotisations', 'cotisations'),
	'association-evenements' => array('spip_asso_activites', 'inscriptions_evenements'),
	'association-compta' => array('spip_asso_comptes', 'operations_comptables'),
	'association-dons' => array('spip_asso_dons', 'dons'),
	'association-ventes' => array('spip_asso_ventes', 'ventes'),
	'association-prets' => array('spip_asso_prets', 'prets'),
);
foreach ($modules as $module => [$table, $cle]) {
	$dir = $racine . '/plugins/' . $module;
	$manifest = file_get_contents($dir . '/paquet.xml');
	$fichiers = glob($dir . '/inc/*_rgpd.php') ?: array();
	$code = $fichiers ? file_get_contents(reset($fichiers)) : '';
	$pipelines = glob($dir . '/*_pipelines.php') ?: array();
	$code_pipeline = $pipelines ? file_get_contents(reset($pipelines)) : '';
	if (strpos($manifest, 'nom="association_rgpd_export_auteur"') === false) {
		$erreurs[] = $module . ' ne déclare pas le pipeline RGPD';
	}
	if (strpos($code, $table) === false || strpos($code_pipeline, "['data']['{$cle}']") === false) {
		$erreurs[] = $module . ' ne fournit pas sa donnée RGPD propriétaire';
	}
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "OK: export RGPD distribué entre les plugins propriétaires.\n";
