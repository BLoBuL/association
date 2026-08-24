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
if (strpos($paquet, '<pipeline nom="association_notification_exemple" action="" />') === false
	|| strpos($fonctions, "pipeline('association_notification_exemple'") === false
	|| preg_match('/spip_asso_comptes|spip_asso_activites/', $fonctions)) {
	fwrite(STDERR, "Les exemples de Notifications ne sont pas delegues aux plugins metier\n");
	exit(1);
}
$fournisseur_adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_notifications.php');
$fournisseur_evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_notifications.php');
if (strpos($fournisseur_adhesions, "'spip_asso_cotisations'") === false
	|| strpos($fournisseur_adhesions, 'association_cotisation_lire_par_compte') === false
	|| strpos($fournisseur_evenements, "'spip_asso_activites'") === false) {
	fwrite(STDERR, "Les plugins proprietaires ne fournissent pas leurs exemples de notification\n");
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
