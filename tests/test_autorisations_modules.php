<?php

$racine = dirname(__DIR__);
$sources = array(
	'comptes' => $racine . '/plugins/association-compta/association_compta_autoriser.php',
	'destinations' => $racine . '/plugins/association-compta/association_compta_autoriser.php',
	'dons' => $racine . '/plugins/association-dons/association_dons_autoriser.php',
	'ventes' => $racine . '/plugins/association-ventes/association_ventes_autoriser.php',
	'prets' => $racine . '/plugins/association-prets/association_prets_autoriser.php',
);

foreach ($sources as $module => $fichier) {
	$source = file_get_contents($fichier);
	if (strpos($source, "association_module_actif('$module')") === false) {
		fwrite(STDERR, "L'autorisation du module $module n'est pas reliée à sa configuration\n");
		exit(1);
	}
}

$source = file_get_contents($sources['prets']);
if (strpos($source, "association_module_actif('ressources')") !== false) {
	fwrite(STDERR, "Les ressources utilisent une option fantôme au lieu du module prêts\n");
	exit(1);
}

echo "Autorisations des modules métier cohérentes\n";
