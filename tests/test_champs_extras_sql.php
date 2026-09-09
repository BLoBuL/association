<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/base/association_champs_extras.php');
$erreurs = array();

if (preg_match('/["\']sql["\'][^\r\n]*\bDEFAUT\b/', $source)) {
	$erreurs[] = 'une declaration SQL Champs Extras contient encore DEFAUT au lieu de DEFAULT';
}

foreach (array(
	'\'sql\' => "int(11) DEFAULT \'0\' NOT NULL"',
	'\'sql\' => "varchar(3) DEFAULT \'non\' NULL"',
) as $declaration) {
	if (strpos($source, $declaration) === false) {
		$erreurs[] = 'declaration SQL normalisee absente: ' . $declaration;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: syntaxe SQL des Champs Extras Association.\n";
