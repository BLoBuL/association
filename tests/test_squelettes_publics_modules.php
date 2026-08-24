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

$page_evenement = file_get_contents($racine . '/plugins/association-evenements/squelettes/evenement.html');
foreach (array('album_photos_evenement', 'album_photos_evenement_locked') as $album) {
	if (!str_contains($page_evenement, 'fond=inclure/' . $album)) {
		fwrite(STDERR, "Le composant front Événements $album n'est pas intégré à la page publique.\n");
		exit(1);
	}
}
if (!str_contains($page_evenement, '#SESSION{statut_interne}|=={ok}|oui') || !str_contains($page_evenement, '#SESSION{statut_interne}|!={ok}|oui')) {
	fwrite(STDERR, "La page Événements ne réserve pas le portfolio complet aux adhérents à jour.\n");
	exit(1);
}

$actifs_blobul_interdits = array(
	'plugins/association-paiements/modeles' => array('blobul-BANK', 'zblobul'),
	'plugins/association-communication/emails' => array('blobul-CORE', 'zblobul_core', 'logo_blobul'),
	'plugins/association-evenements/squelettes' => array('blobul-ASSO_FO', 'zblobul_core', 'zblobul_asso'),
);
foreach ($actifs_blobul_interdits as $dossier => $marqueurs) {
	$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/' . $dossier));
	foreach ($iterateur as $fichier) {
		if (!$fichier->isFile() || !in_array($fichier->getExtension(), array('html', 'php', 'css'), true)) {
			continue;
		}
		$contenu = file_get_contents($fichier->getPathname());
		foreach ($marqueurs as $marqueur) {
			if (stripos($contenu, $marqueur) !== false) {
				fwrite(STDERR, "Dépendance Blobul résiduelle ($marqueur) dans {$fichier->getPathname()}.\n");
				exit(1);
			}
		}
	}
}

$modeles_paiement = array(
	'payer_acte.html',
	'payer_acte_adhesion.html',
	'payer_acte_formidable.html',
	'payer_acte_participation.html',
);
foreach ($modeles_paiement as $modele_paiement) {
	$chemin_modele = $racine . '/plugins/association-paiements/modeles/' . $modele_paiement;
	if (!is_file($chemin_modele)) {
		fwrite(STDERR, "Modèle Bank rapatrié absent : $modele_paiement.\n");
		exit(1);
	}
	$contenu_modele = file_get_contents($chemin_modele);
	if (!str_contains($contenu_modele, '#PAYER_ACTE') || !str_contains($contenu_modele, 'TRANSACTIONS')) {
		fwrite(STDERR, "Contrat Bank incomplet dans $modele_paiement.\n");
		exit(1);
	}
	if (str_contains($contenu_modele, '#META{/association/')) {
		fwrite(STDERR, "Lecture de méta historique dans $modele_paiement.\n");
		exit(1);
	}
}

$coque_email = file_get_contents($racine . '/plugins/association-communication/emails/texte.html');
foreach (array('inc-header', 'inc-title', 'inc-content', 'inc-footer') as $fragment_email) {
	if (!str_contains($coque_email, 'fond=emails/inc/' . $fragment_email)) {
		fwrite(STDERR, "Fragment autonome absent de la coque email : $fragment_email.\n");
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

$auteur_email = $racine . '/plugins/association-communication/emails/inc-email_auteur.html';
if (!file_exists($auteur_email)) {
	fwrite(STDERR, "Le composant email d'auteur requis par Événements est absent de Communication.\n");
	exit(1);
}
foreach (glob($racine . '/plugins/association-evenements/notifications/*.html') as $notification) {
	$contenu = file_get_contents($notification);
	if (str_contains($contenu, 'emails/inc-email_auteur') && !file_exists($auteur_email)) {
		fwrite(STDERR, "Dépendance email non satisfaite : $notification.\n");
		exit(1);
	}
}

$modele_ressources = file_get_contents($racine . '/plugins/association-prets/modeles/asso_ressources.html');
if (!str_contains($modele_ressources, 'match{^0000}') || !str_contains($modele_ressources, 'prets_retour_attente')) {
	fwrite(STDERR, "Le catalogue public expose encore la date SQL nulle d'un prêt non restitué.\n");
	exit(1);
}

echo "OK: les usages publics sont portés par leurs modules métier.\n";
