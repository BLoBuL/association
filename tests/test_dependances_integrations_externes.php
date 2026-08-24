<?php

$racine_monorepo = dirname(__DIR__);
if (is_dir($racine_monorepo . '/plugins/association-adhesions')) {
	$racine = $racine_monorepo . '/plugins';
} else {
	$racine = dirname($racine_monorepo);
}
$attendus = array(
	'association-adhesions/paquet.xml' => array('<necessite nom="medias"'),
	'association-compta/paquet.xml' => array('<utilise nom="commandes"'),
	'association-paiements/paquet.xml' => array('<utilise nom="commandes"', '<utilise nom="formidable"'),
);
$erreurs = array();
$paquets = array_merge(
	array($racine_monorepo . '/paquet.xml'),
	glob($racine . '/association-*/paquet.xml') ?: array()
);
foreach ($paquets as $fichier_paquet) {
	$contenu_paquet = file_get_contents($fichier_paquet);
	if (!str_contains($contenu_paquet, 'compatibilite="[4.0.0;4.*]"')) {
		$erreurs[] = "Compatibilité SPIP 4 absente de {$fichier_paquet}";
	}
	if (preg_match('/<(?:necessite|utilise)\s+nom="[^"]*(?:blobul|asso_bo|asso_fo)/i', $contenu_paquet)) {
		$erreurs[] = "Dépendance Blobul historique interdite dans {$fichier_paquet}";
	}
}
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
