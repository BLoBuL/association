<?php

$source = file_get_contents(dirname(__DIR__) . '/prive/squelettes/contenu/ventes.html');

if (!str_contains($source, '#SET{peut_supprimer,#AUTORISER{supprimer,vente,#ID_VENTE}}')
	|| !str_contains($source, '#GET{peut_supprimer}|oui')) {
	fwrite(STDERR, "L'autorisation de suppression des ventes n'est pas préparée séparément du balisage HTML.\n");
	exit(1);
}

echo "OK: la sélection des ventes ne fuit pas de syntaxe SPIP dans le HTML.\n";
