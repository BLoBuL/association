<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-compta/formulaires/inc/destinations.php');

if (preg_match('/\x60[^\x60]+\x60/', $source)) {
	fwrite(STDERR, "Une exécution shell PHP subsiste dans la gestion des destinations\n");
	exit(1);
}
if (strpos($source, '$GLOBALS[\'association_metas\'][$cle_destination] ?? \'\'') === false) {
	fwrite(STDERR, "La destination par défaut n'est pas lue de manière compatible PHP 8\n");
	exit(1);
}

echo "Gestion des destinations compatible PHP 8\n";
