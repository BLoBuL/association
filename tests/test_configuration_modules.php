<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/formulaires/inc/configurer_association.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/formulaires/inc/configurer_association_adhesions.php');
foreach (array('spip_asso_categories_adherents', 'spip_mailsubscribinglists') as $table) {
	if (strpos($socle, $table) !== false) {
		fwrite(STDERR, "La configuration du socle requête encore {$table}.\n");
		exit(1);
	}
}
$communication = file_get_contents($racine . '/plugins/association-communication/association_communication_pipelines.php');
if (
	strpos($adhesions, "pipeline('association_configuration_listes_diffusion'") === false
	|| strpos($communication, 'association_configuration_listes_diffusion') === false
) {
	fwrite(STDERR, "Contrat de listes de diffusion incomplet.\n");
	exit(1);
}
echo "OK: les données de configuration métier viennent de leurs modules.\n";
