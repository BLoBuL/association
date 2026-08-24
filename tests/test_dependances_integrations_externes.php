<?php

$racine = dirname(__DIR__) . '/plugins';
$attendus = array(
	'association-adhesions/paquet.xml' => array('<necessite nom="medias"'),
	'association-compta/paquet.xml' => array('<utilise nom="commandes"'),
	'association-paiements/paquet.xml' => array('<utilise nom="commandes"', '<utilise nom="formidable"'),
);
$erreurs = array();
foreach ($attendus as $fichier => $marqueurs) {
	$contenu = file_get_contents($racine . '/' . $fichier);
	foreach ($marqueurs as $marqueur) {
		if (strpos($contenu, $marqueur) === false) {
			$erreurs[] = "Dependance absente dans {$fichier}: {$marqueur}";
		}
	}
}

$maintenance = file_get_contents($racine . '/association-paiements/inc/association_paiements_maintenance.php');
foreach (array('commandes', 'formidable') as $prefixe) {
	if (strpos($maintenance, "test_plugin_actif('{$prefixe}')") === false) {
		$erreurs[] = "La lecture optionnelle {$prefixe} n est pas gardee.";
	}
}

$comptes = file_get_contents($racine . '/association-compta/inc/comptes.php');
if (strpos($comptes, "sql_showtable('spip_commandes'") === false
	|| strpos($comptes, "sql_showtable('spip_commandes_details'") === false) {
	$erreurs[] = 'La compatibilite Commandes ne verifie pas la presence des tables historiques.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: dependances obligatoires et integrations optionnelles explicites.\n";
