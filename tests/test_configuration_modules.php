<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/formulaires/inc/configurer_association.php');
foreach (array('spip_asso_categories_adherents', 'spip_mailsubscribinglists') as $table) {
	if (strpos($socle, $table) !== false) {
		fwrite(STDERR, "La configuration du socle requête encore {$table}.\n");
		exit(1);
	}
}
$contrats = array(
	'association_configuration_categorie_entreprise' => $racine . '/plugins/association-adhesions/association_adhesions_pipelines.php',
	'association_configuration_listes_diffusion' => $racine . '/plugins/association-communication/association_communication_pipelines.php',
);
foreach ($contrats as $pipeline => $fichier) {
	if (strpos($socle, "pipeline('{$pipeline}'") === false || strpos(file_get_contents($fichier), $pipeline) === false) {
		fwrite(STDERR, "Contrat de configuration incomplet: {$pipeline}.\n");
		exit(1);
	}
}
echo "OK: les données de configuration métier viennent de leurs modules.\n";
