<?php

$racine = dirname(__DIR__) . '/prive/squelettes/contenu/';
$pages = array(
	'destinations' => 'association:destination_comptable',
	'dons' => 'association:tous_les_dons',
	'ventes' => 'association:toutes_les_ventes',
	'ressources' => 'association:ressources_titre_liste_ressources',
	'prets' => 'association:prets_titre_liste_reservations',
	'plan_comptable' => 'association:plan_comptable',
	'bilan' => 'association:bilans_comptables',
);

foreach ($pages as $page => $titre) {
	$source = file_get_contents($racine . $page . '.html');
	if (strpos($source, '<h1 class="grostitre"><:' . $titre . ':></h1>') === false) {
		fwrite(STDERR, "Titre SPIP privé absent de la page $page\n");
		exit(1);
	}
}

echo "Titres des pages privées conformes\n";
