<?php

$paquet = file_get_contents(dirname(__DIR__) . '/paquet.xml');
foreach (['nom="bank"', 'nom="association_compta"'] as $attendu) {
	if (strpos($paquet, $attendu) === false) {
		fwrite(STDERR, "Dépendance absente: {$attendu}\n");
		exit(1);
	}
}
foreach (['nom="association_adhesions"', 'nom="association_evenements"'] as $interdit) {
	if (strpos($paquet, $interdit) !== false) {
		fwrite(STDERR, "Dépendance métier inverse interdite: {$interdit}\n");
		exit(1);
	}
}
echo "OK: structure Association Paiements.\n";
