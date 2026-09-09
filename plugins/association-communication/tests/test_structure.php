<?php

$p = file_get_contents(dirname(__DIR__) . '/paquet.xml');
foreach (['nom="mailsubscribers"', 'nom="notifications"'] as $a) {
	if (strpos($p, $a) === false) {
		fwrite(STDERR, "Dépendance absente: $a\n");
		exit(1);
	}
}echo "OK: structure Association Communication.\n";
