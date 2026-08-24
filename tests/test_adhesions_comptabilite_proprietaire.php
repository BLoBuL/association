<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_comptabilite.php');
$cotisations = file_get_contents($racine . '/plugins/association-adhesions/inc/api_cotisations.php');
$comptes = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');

if (!str_contains($api, 'function association_adhesions_compte_cotisation_creer(')
	|| !str_contains($api, 'function association_adhesions_compte_cotisation_modifier(')
	|| !str_contains($cotisations, 'association_adhesions_compte_cotisation_creer(')
	|| !str_contains($cotisations, 'association_adhesions_compte_cotisation_modifier(')
	|| str_contains($comptes, 'function compte_cotisation(')
	|| str_contains($comptes, 'function modifier_compte_cotisation(')
) {
	fwrite(STDERR, "La comptabilité des cotisations n'appartient pas entièrement à Adhésions.\n");
	exit(1);
}

echo "OK: les adaptateurs comptables des cotisations appartiennent à Adhésions.\n";
