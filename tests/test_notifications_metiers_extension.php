<?php

$racine = dirname(__DIR__);
$racine_communication = $racine . '/plugins/association-communication';
$paquet = file_get_contents($racine_communication . '/paquet.xml');
$fonctions = file_get_contents($racine_communication . '/prive/squelettes/contenu/notifications_fonctions.php');
$page = file_get_contents($racine_communication . '/prive/squelettes/contenu/notifications.html');
$tableau = file_get_contents($racine_communication . '/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_metiers.html');

if (strpos($fonctions, "pipeline('association_notifications_metiers'") === false) {
	fwrite(STDERR, "La page Notifications n'expose pas le pipeline métier\n");
	exit(1);
}
if (strpos($paquet, '<pipeline nom="association_notifications_metiers" action="" />') === false) {
	fwrite(STDERR, "Le pipeline métier n'est pas déclaré par le paquet\n");
	exit(1);
}
if (strpos($page, 'tab,metiers') === false || strpos($page, 'inc-tableau_notif_metiers') === false) {
	fwrite(STDERR, "L'onglet des notifications métier est absent\n");
	exit(1);
}
foreach (['nom', 'label', 'description', 'sujet', 'url'] as $champ) {
	if (strpos($tableau, 'table_valeur{' . $champ . '}') === false) {
		fwrite(STDERR, "Le tableau métier n'affiche pas le champ $champ\n");
		exit(1);
	}
}

echo "Extension des notifications métier réussie\n";
