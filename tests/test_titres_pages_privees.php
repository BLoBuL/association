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

$formulaire_compte = file_get_contents(dirname(__DIR__) . '/formulaires/editer_asso_comptes.php');
$contenu_compte = file_get_contents($racine . 'editer_asso_comptes.html');
if (str_contains($formulaire_compte, "generer_url_ecrire('asso_comptes')")
	|| !str_contains($formulaire_compte, "generer_url_ecrire('comptes')")
	|| str_contains($contenu_compte, '#URL_ECRIRE{asso_comptes}')
	|| !str_contains($contenu_compte, '#URL_ECRIRE{comptes}')) {
	fwrite(STDERR, "L'édition comptable redirige encore vers la page privée legacy asso_comptes.\n");
	exit(1);
}

$edit_cotisation = file_get_contents($racine . 'edit_cotisation.html');
$editer_cotisation = file_get_contents($racine . 'editer_asso_cotisation.html');
if (!str_contains($edit_cotisation, '<h1 class="grostitre"><:association:ajout_de_cotisation:></h1>')
	|| !str_contains($edit_cotisation, 'titre=non')
	|| !str_contains($editer_cotisation, '#ENV{titre,oui}|=={oui}|oui)<h1 class="grostitre">')) {
	fwrite(STDERR, "Les routes d'édition de cotisation n'ont pas de titre privé SPIP sans doublon.\n");
	exit(1);
}

$voir_activites = file_get_contents($racine . 'voir_activites.html');
if (!str_contains($voir_activites, '<h1 class="grostitre">#TITRE</h1>')) {
	fwrite(STDERR, "Le tableau de bord d'un événement n'utilise pas son titre comme H1 privé.\n");
	exit(1);
}

echo "Titres des pages privées conformes\n";
