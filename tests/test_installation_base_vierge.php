<?php

$racine = dirname(__DIR__);
$administration = file_get_contents($racine . '/association_administrations.php');
$migrations_socle = file_get_contents($racine . '/inc/association_migrations.php');
$documentation = file_get_contents($racine . '/docs/installation-base-sans-tables-association.md');
$inventaire = file_get_contents($racine . '/inc/association_installation.php');
foreach (glob($racine . '/plugins/*/inc/*_installation.php') as $fichier_inventaire) {
	$inventaire .= file_get_contents($fichier_inventaire);
}
$erreurs = array();

$debut_historique = strpos($administration, '#V1.1.0');
$branche_create = $debut_historique === false ? $administration : substr($administration, 0, $debut_historique);
if (strpos($administration . $migrations_socle, "['maj_tables', ['spip_association_metas']]") === false) {
	$erreurs[] = 'la création du socle doit installer sa table de configuration';
}
if (preg_match('/spip_asso_(?!ciation_metas)/', $branche_create)) {
	$erreurs[] = 'la création du socle ne doit posséder aucune table métier';
}
$debut_desinstallation = strpos($administration, 'function association_vider_tables');
$branche_desinstallation = $debut_desinstallation === false ? '' : substr($administration, $debut_desinstallation);
if (!$branche_desinstallation || preg_match('/sql_drop_table\(["\']spip_asso_(?!ciation_metas)/', $branche_desinstallation)) {
	$erreurs[] = 'la désinstallation du socle ne doit supprimer aucune table métier';
}
if (strpos($branche_desinstallation, 'association_declarer_champs_extras') !== false) {
	$erreurs[] = 'la désinstallation du socle ne doit supprimer aucun Champ Extra métier';
}

$schemas = array(
	'association_base_version' => '1.6.1',
	'association_adhesions_base_version' => '1.4.0',
	'association_compta_base_version' => '1.0.0',
	'association_dons_base_version' => '1.0.0',
	'association_evenements_base_version' => '1.2.0',
	'association_prets_base_version' => '1.1.1',
	'association_ventes_base_version' => '1.1.0',
);
foreach ($schemas as $meta => $version) {
	if (strpos($inventaire, "'$meta' => '$version'") === false) {
		$erreurs[] = "schéma absent de l'inventaire distribué : $meta";
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
