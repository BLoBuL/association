<?php

$racine = dirname(__DIR__);
$paquet = file_get_contents($racine . '/plugins/association-evenements/paquet.xml');
$backend = file_get_contents($racine . '/formulaires/inc/inscription_evenement_backend.php');
$formater = file_get_contents($racine . '/formulaires/inc/inscription_evenement.php');

$erreurs = array();
foreach (array(
	'association_inscription_evenement_charger',
	'association_inscription_evenement_verifier',
	'association_inscription_evenement_traiter',
) as $pipeline) {
	if (strpos($paquet, '<pipeline nom="' . $pipeline . '" action="" />') === false) {
		$erreurs[] = 'pipeline métier non déclaré : ' . $pipeline;
	}
}

if (strpos($backend, "'association' . _LOG_CRITIQUE") !== false) {
	$erreurs[] = 'les traces de diagnostic du backend ne doivent pas être critiques';
}

$critiques_formater = substr_count($formater, "'association' . _LOG_CRITIQUE");
if ($critiques_formater !== 1 || strpos($formater, 'creation impossible pour') === false) {
	$erreurs[] = 'seule une impossibilité métier réelle doit rester critique dans le formateur';
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "Pipelines et niveaux de journalisation événementiels conformes.\n";

