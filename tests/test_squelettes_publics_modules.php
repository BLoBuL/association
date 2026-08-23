<?php

$racine = dirname(__DIR__);
$attendus = array(
	'plugins/association-adhesions/squelettes/inscription.html' => '#FORMULAIRE_INSCRIPTION',
	'plugins/association-adhesions/squelettes/profil.html' => 'content/fiche_adherent',
	'plugins/association-evenements/squelettes/evenement.html' => '#FORMULAIRE_INSCRIPTION_EVENEMENT_PUBLIC',
	'plugins/association-prets/squelettes/ressources.html' => '#MODELE{asso_ressources',
	'plugins/association-communication/squelettes/newsletter.html' => '#FORMULAIRE_NEWSLETTER_SUBSCRIBE',
);

foreach ($attendus as $fichier => $usage) {
	$contenu = file_get_contents($racine . '/' . $fichier);
	if (!str_contains($contenu, '<main class="main" role="main">') || !str_contains($contenu, '<h1>')) {
		fwrite(STDERR, "Structure publique SPIP incomplète : $fichier.\n");
		exit(1);
	}
	if (!str_contains($contenu, $usage)) {
		fwrite(STDERR, "Usage front office absent de $fichier : $usage.\n");
		exit(1);
	}
}

if (file_exists($racine . '/modeles/asso_ressources.html')) {
	fwrite(STDERR, "Le modèle Ressources appartient encore au socle au lieu du module Prêts.\n");
	exit(1);
}
if (!file_exists($racine . '/plugins/association-prets/modeles/asso_ressources.html')) {
	fwrite(STDERR, "Le modèle Ressources est absent du module Prêts.\n");
	exit(1);
}

$modele_ressources = file_get_contents($racine . '/plugins/association-prets/modeles/asso_ressources.html');
if (!str_contains($modele_ressources, 'match{^0000}') || !str_contains($modele_ressources, 'prets_retour_attente')) {
	fwrite(STDERR, "Le catalogue public expose encore la date SQL nulle d'un prêt non restitué.\n");
	exit(1);
}

echo "OK: les usages publics sont portés par leurs modules métier.\n";
