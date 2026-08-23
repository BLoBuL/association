<?php

$racine = dirname(__DIR__);
$page = file_get_contents($racine . '/prive/squelettes/contenu/prets.html');
$include = file_get_contents($racine . '/prive/squelettes/inclure/prets_ressource.html');
$edition = file_get_contents($racine . '/prive/squelettes/contenu/edit_pret.html');

if (!str_contains($page, 'BOUCLE_ressources_toutes(ASSO_RESSOURCES)')) {
	fwrite(STDERR, "La page Prêts sans identifiant ne liste pas toutes les ressources.\n");
	exit(1);
}
if (!str_contains($page, '#GET{id_ressource}|oui)<div class="error"')) {
	fwrite(STDERR, "L'erreur ressource introuvable n'est pas limitée à une ressource explicitement demandée.\n");
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

echo "OK: liste globale et édition des prêts conformes.\n";
