<?php

$racine = dirname(__DIR__);
$fichiers = array(
	'formulaires/editer_asso_dons.php',
	'formulaires/editer_asso_ventes.php',
	'formulaires/editer_asso_ressources.php',
	'formulaires/editer_asso_plan.php',
	'formulaires/editer_asso_membres.php',
);

foreach ($fichiers as $fichier) {
	$source = file_get_contents($racine . '/' . $fichier);
	$boucles = substr_count($source, 'foreach ($_POST as $champ => $mot)');
	$protections = substr_count($source, 'if (!is_string($mot))');
	if ($boucles !== $protections) {
		fwrite(STDERR, "$fichier applique encore un traitement de chaîne à une valeur POST tableau.\n");
		exit(1);
	}
}

echo "OK: les normalisations de dates ignorent les valeurs POST non textuelles sous PHP 8.\n";
