<?php

$racine = dirname(__DIR__);
$pages = array(
	'plugins/association-adhesions/prive/squelettes/contenu/editer_asso_categorie_cotisation.html' => '#AUTORISER{cotisations_menu}',
	'plugins/association-communication/prive/squelettes/contenu/notifications.html' => '#AUTORISER{notifications_menu}',
	'plugins/association-compta/prive/squelettes/contenu/migration_donnees_comptables.html' => '#AUTORISER{comptes_menu}',
	'plugins/association-evenements/prive/squelettes/contenu/analyse_compta_activites.html' => '#AUTORISER{administrer,activites}',
	'plugins/association-evenements/prive/squelettes/contenu/editer_asso_categorie_activite.html' => '#AUTORISER{administrer,activites}',
	'plugins/association-evenements/prive/squelettes/contenu/export_activites_compta.html' => '#AUTORISER{administrer,activites}',
	'plugins/association-evenements/prive/squelettes/contenu/export_activites.html' => '#AUTORISER{administrer,activites}',
	'plugins/association-evenements/prive/squelettes/contenu/suivi_activites.html' => '#AUTORISER{administrer,activites}',
);
$erreurs = array();
foreach ($pages as $fichier => $garde) {
	$source = file_get_contents($racine . '/' . $fichier);
	if (strpos($source, $garde) === false || strpos($source, 'sinon_interdire_acces') === false) {
		$erreurs[] = $fichier . ' ne porte pas sa garde ' . $garde . '.';
	}
}
$evenements = file_get_contents($racine . '/plugins/association-evenements/association_evenements_autoriser.php');
if (strpos($evenements, "droit_auteur_evenements((int) \$qui['id_auteur'])") === false
	|| strpos($evenements, 'function autoriser_activites_administrer_dist') === false) {
	$erreurs[] = 'Les droits Evenements ne distinguent pas acces et administration.';
}
$communication = file_get_contents($racine . '/plugins/association-communication/association_communication_autoriser.php');
if (strpos($communication, 'function autoriser_notifications_menu_dist') === false) {
	$erreurs[] = 'Communication ne protege pas sa page Notifications.';
}
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK - gardes des pages privees metier\n";
