<?php

$source = file_get_contents(dirname(__DIR__) . '/association_autoriser.php');

foreach (array('comptes', 'destinations', 'dons', 'ventes', 'prets') as $module) {
	if (strpos($source, "association_module_actif('$module')") === false) {
		fwrite(STDERR, "L'autorisation du module $module n'est pas reliée à sa configuration\n");
		exit(1);
	}
}

if (strpos($source, "association_module_actif('ressources')") !== false) {
	fwrite(STDERR, "Les ressources utilisent une option fantôme au lieu du module prêts\n");
	exit(1);
}

echo "Autorisations des modules métier cohérentes\n";
