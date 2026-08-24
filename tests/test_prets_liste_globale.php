<?php

$racine = dirname(__DIR__) . '/plugins/association-prets';
$page = file_get_contents($racine . '/prive/squelettes/contenu/prets.html');
$include = file_get_contents($racine . '/prive/squelettes/inclure/prets_ressource.html');
$edition = file_get_contents($racine . '/prive/squelettes/contenu/edit_pret.html');
$formulaire_ressource = file_get_contents($racine . '/formulaires/editer_asso_ressources.html');
$charger_ressource = file_get_contents($racine . '/formulaires/editer_asso_ressources.php');
$formulaire_pret = file_get_contents($racine . '/formulaires/editer_asso_pret.html');
$charger_pret = file_get_contents($racine . '/formulaires/editer_asso_pret.php');
$navigation = file_get_contents($racine . '/prive/squelettes/navigation/prets.html');

if (!str_contains($page, 'BOUCLE_ressources_toutes(ASSO_RESSOURCES)')) {
	fwrite(STDERR, "La page Prêts sans identifiant ne liste pas toutes les ressources.\n");
	exit(1);
}
if (!str_contains($page, '#GET{id_ressource}|oui)<div class="error"')) {
	fwrite(STDERR, "L'erreur ressource introuvable n'est pas limitée à une ressource explicitement demandée.\n");
	exit(1);
}
if (!str_contains($page, '#GET{id_ressource}|non)<div class="notice"')) {
	fwrite(STDERR, "Le message de liste vide apparaît encore sur une ressource explicitement demandée.\n");
	exit(1);
}
if (!str_contains($include, 'BOUCLE_prets(ASSO_PRETS){id_ressource}')) {
	fwrite(STDERR, "L'inclusion par ressource ne liste pas les prêts.\n");
	exit(1);
}
if (!str_contains($edition, '<h1 class="grostitre">')) {
	fwrite(STDERR, "La page d'édition d'un prêt ne possède pas de titre SPIP privé.\n");
	exit(1);
}
if (!str_contains($formulaire_ressource, 'devise=#ENV{devise}')
	|| str_contains($formulaire_ressource, '#META{/association/symbole}')
	|| !str_contains($charger_ressource, 'intl_devise_defaut()')) {
	fwrite(STDERR, "Le prix de location ne reprend pas la devise Intl du site.\n");
	exit(1);
}
if (!str_contains($charger_pret, "'editable' => false")
	|| !str_contains($charger_pret, "_T('association_prets:ressource_introuvable')")
	|| !str_contains($formulaire_pret, '[(#ENV{editable}|oui)')) {
	fwrite(STDERR, "Le formulaire de prêt sans ressource ne rend pas son erreur explicite.\n");
	exit(1);
}
if (!str_contains($navigation, 'BOUCLE_ressource_selectionnee(ASSO_RESSOURCES)')
	|| !str_contains($navigation, 'id_objet=#ID_RESSOURCE')) {
	fwrite(STDERR, "Le raccourci de création d'un prêt n'est pas borné à une ressource existante.\n");
	exit(1);
}

echo "OK: liste globale et édition des prêts conformes.\n";
