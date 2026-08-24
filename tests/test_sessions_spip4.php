<?php

$racine = dirname(__DIR__);
$plugins = $racine . '/plugins';
$erreurs = array();
$autorisations_bank = array(
	'association-paiements/modeles/payer_acte.html',
	'association-paiements/modeles/payer_acte_adhesion.html',
	'association-paiements/modeles/payer_acte_formidable.html',
	'association-paiements/modeles/payer_acte_participation.html',
);
$trouves_bank = array();

$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plugins));
foreach ($iterateur as $fichier) {
	if (!$fichier->isFile() || !preg_match('/\.(?:php|html)$/', $fichier->getFilename())) {
		continue;
	}
	$source = file_get_contents($fichier->getPathname());
	if (strpos($source, 'session_start(') === false && strpos($source, '$_SESSION') === false) {
		continue;
	}
	$relatif = str_replace('\\', '/', substr($fichier->getPathname(), strlen($plugins) + 1));
	if (!in_array($relatif, $autorisations_bank, true)) {
		$erreurs[] = 'Session PHP native hors contrat Bank : ' . $relatif;
		continue;
	}
	$trouves_bank[] = $relatif;
	if (strpos($source, 'fond=modeles/confirmer_payer_acte') === false
		|| strpos($source, 'order_resume=#EVAL{$_SESSION}') === false) {
		$erreurs[] = 'Le modèle autorisé ne correspond plus au contrat de confirmation Bank : ' . $relatif;
	}
}

sort($autorisations_bank);
sort($trouves_bank);
if ($trouves_bank !== $autorisations_bank) {
	$erreurs[] = 'La liste des exceptions Bank attendues ne correspond pas aux modèles présents.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - sessions natives limitees au contrat des quatre modeles Bank\n";
