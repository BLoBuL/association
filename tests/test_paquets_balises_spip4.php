<?php

$racine = dirname(__DIR__);
$fichiers = array_merge(array($racine . '/paquet.xml'), glob($racine . '/plugins/*/paquet.xml'));
$balises = array('paquet', 'nom', 'auteur', 'credit', 'licence', 'necessite', 'utilise', 'procure', 'pipeline', 'style', 'script', 'lib');
$erreurs = array();

foreach ($fichiers as $fichier) {
	$xml = file_get_contents($fichier);
	preg_match_all('/<\/?([a-z_]+)/', $xml, $matches);
	foreach (array_unique($matches[1]) as $balise) {
		if (!in_array($balise, $balises, true)) {
			$erreurs[] = basename(dirname($fichier)) . '/paquet.xml : balise SPIP 4 inconnue ' . $balise;
		}
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK balises paquet.xml compatibles SPIP 4\n";
