<?php

$racine = dirname(__DIR__);
$options = file_get_contents($racine . '/association_options.php');
$pipelines = file_get_contents($racine . '/association_pipelines.php');
$interdites = array(
	'inc/fonctions/eligibilite_inscription_evenement',
	'inc/fonctions/facteur_envoyer_recu_adhesion',
	'inc/fonctions/facteur_envoyer_app',
	'inc/fonctions/comptes',
	'inc/comptes',
	'inc/notifications_emails',
);
foreach ($interdites as $include) {
	if (strpos($options . $pipelines, "include_spip('{$include}'") !== false) {
		fwrite(STDERR, "Le bootstrap du socle charge encore {$include}.\n");
		exit(1);
	}
}
$attendus = array(
	'plugins/association-evenements/association_evenements_options.php' => 'eligibilite_inscription_evenement',
	'plugins/association-adhesions/association_adhesions_options.php' => 'facteur_envoyer_recu_adhesion',
	'plugins/association-paiements/association_paiements_options.php' => 'facteur_envoyer_app',
	'plugins/association-compta/association_compta_options.php' => 'inc/fonctions/comptes',
);
foreach ($attendus as $fichier => $marqueur) {
	if (strpos(file_get_contents($racine . '/' . $fichier), $marqueur) === false) {
		fwrite(STDERR, "Chargement métier absent de {$fichier}.\n");
		exit(1);
	}
}
if (!is_file($racine . '/plugins/association-compta/balise/editeur_destinations.php')
	|| is_file($racine . '/balise/editeur_destinations.php')) {
	fwrite(STDERR, "La balise Destinations n'appartient pas exclusivement à Comptabilité.\n");
	exit(1);
}
echo "OK: chaque bootstrap métier appartient à son plugin.\n";
