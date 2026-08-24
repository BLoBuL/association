<?php

$racine = dirname(__DIR__);
$interdits = array();
$iterateur = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($racine, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterateur as $fichier) {
	$chemin = str_replace('\\', '/', $fichier->getPathname());
	if ($fichier->getExtension() !== 'php'
		|| str_contains($chemin, '/tests/')
		|| str_contains($chemin, '/vendor/')
	) {
		continue;
	}
	$source = file_get_contents($fichier->getPathname());
	if (str_contains($source, 'spip_asso_membres')
		|| str_contains($source, 'update_spip_asso_membre')
		|| preg_match('/function (?:generer_url_(?:asso_)?membre|adherent_correction_statut)\s*\(/', $source)
	) {
		$interdits[] = substr($chemin, strlen(str_replace('\\', '/', $racine)) + 1);
	}
}

if ($interdits) {
	fwrite(STDERR, "Références à l'ancienne table des membres : " . implode(', ', $interdits) . "\n");
	exit(1);
}

echo "OK: les adhérents utilisent exclusivement l'objet auteur SPIP, sans alias membre.\n";
