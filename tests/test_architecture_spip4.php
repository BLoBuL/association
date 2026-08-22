<?php

$racine = dirname(__DIR__);
$erreurs = array();
$verifier = function ($condition, $message) use (&$erreurs) {
	if (!$condition) $erreurs[] = $message;
};

$paquet = file_get_contents($racine . '/paquet.xml');
$schema = file_get_contents($racine . '/base/association.php');
$verifier(strpos($paquet, '<necessite nom="inscription4"') !== false, 'Inscription 4 doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_adhesions"') !== false, 'Le module Adhesions doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_evenements"') !== false, 'Le module Evenements doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_compta"') !== false, 'Le module Comptabilite doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_dons"') !== false, 'Le module Dons doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_ventes"') !== false, 'Le module Ventes doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_prets"') !== false, 'Le module Prets doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_paiements"') !== false, 'Le module Paiements doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_groupes"') !== false, 'Le module Groupes doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_communication"') !== false, 'Le module Communication doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="bank"') === false, 'Bank doit etre porte par le module Paiements.');
$verifier(strpos($paquet, '<necessite nom="mailsubscribers"') === false, 'Mailsubscribers doit etre porte par le module Communication.');
$verifier(strpos($paquet, '<necessite nom="inscription3"') === false, 'Inscription 3 ne doit plus etre une dependance.');
$verifier(strpos($schema, "spip_asso_cotisations") === false, 'Le socle ne doit plus posseder la table metier des cotisations.');
$verifier(strpos($schema, "spip_asso_categories_adherents") === false, 'Le socle ne doit plus posseder les categories d adhesion.');
$verifier(strpos($schema, "spip_asso_activites") === false, 'Le socle ne doit plus posseder la table des inscriptions aux evenements.');
$verifier(strpos($schema, "spip_asso_categories_activites") === false, 'Le socle ne doit plus posseder les tarifs des evenements.');
$verifier(strpos($schema, "spip_asso_comptes") === false, 'Le socle ne doit plus posseder le journal comptable.');
$verifier(strpos($schema, "spip_asso_plan") === false, 'Le socle ne doit plus posseder le plan comptable.');
$verifier(strpos($schema, "spip_asso_dons") === false, 'Le socle ne doit plus posseder les dons.');
$verifier(strpos($schema, "spip_asso_ventes") === false, 'Le socle ne doit plus posseder les ventes.');
$verifier(strpos($schema, "spip_asso_prets") === false, 'Le socle ne doit plus posseder les prets.');
$verifier(!preg_match('/\"(?:reinscription|statut_cotisation)\"\s*=>/', $schema), 'Le schema des comptes ne doit plus declarer de champs metier de cotisation.');
$verifier(
	strpos($schema, "include_once __DIR__ . '/association_champs_extras.php'") !== false,
	'La declaration des champs extras doit rester chargeable pendant l installation du plugin.'
);
$verifier(!is_dir($racine . '/exec') || count(glob($racine . '/exec/*')) === 0, 'Les pages privees ne doivent plus utiliser exec/.');
$verifier(!is_file($racine . '/inc/page.php'), 'L ancien moteur de rendu PHP inc/page.php doit etre supprime.');
$verifier(!is_file($racine . '/inc/navigation_modules.php'), 'L ancienne navigation PHP doit etre supprimee.');
$verifier(!is_file($racine . '/balise/autoriser_page.php'), 'La balise d autorisation des anciens exec doit etre supprimee.');
$verifier(!is_file($racine . '/squelettes/profil.html'), 'La page profil doit appartenir au module Adhesions.');
$verifier(!is_file($racine . '/squelettes/evenement.html'), 'La page evenement doit appartenir au module Evenements.');

$pages_migrees = array(
	'action_activites', 'activites', 'adherents', 'bilan', 'comptes',
	'configurer_association', 'cotisations', 'destinations', 'dons',
	'edit_compte', 'edit_cotisation', 'edit_destination', 'edit_don',
	'edit_email_collectif_adherent', 'edit_plan', 'edit_pret',
	'edit_ressource', 'edit_vente', 'plan_comptable', 'prets',
	'ressources', 'ventes', 'voir_activites', 'voir_adherent',
);
foreach ($pages_migrees as $page) {
	$verifier(
		is_file($racine . '/prive/squelettes/contenu/' . $page . '.html'),
		'Le squelette prive contenu/' . $page . '.html est manquant.'
	);
}

$iterateur_prive = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/prive'));
foreach ($iterateur_prive as $fichier) {
	if (!$fichier->isFile() || $fichier->getExtension() !== 'html') continue;
	$contenu = file_get_contents($fichier->getPathname());
	$chemin = str_replace($racine . DIRECTORY_SEPARATOR, '', $fichier->getPathname());
	$verifier(strpos($contenu, '<?php') === false, $chemin . ' contient encore du PHP embarque.');
	$verifier(
		!preg_match('/fond=(?:right_col|content)\//', $contenu),
		$chemin . ' depend encore d un squelette de theme ou de FO.'
	);
	$verifier(strpos($contenu, '#EVAL') === false, 'Usage de #EVAL dans ' . $fichier->getFilename() . '.');
}

$sources = array_merge(
	glob($racine . '/*.php'),
	glob($racine . '/{action,base,formulaires,genie,inc,notifications,prive,squelettes}/**/*.{php,html}', GLOB_BRACE)
);
foreach ($sources as $fichier) {
	$contenu = is_file($fichier) ? file_get_contents($fichier) : '';
	$chemin = str_replace($racine . DIRECTORY_SEPARATOR, '', $fichier);
	if (preg_match('/zblobul|blobul_core/i', $contenu)) {
		$erreurs[] = 'Dependance Blobul implicite dans ' . $chemin;
	}
	$verifier(strpos($contenu, '/ecrire/?exec=') === false, 'URL privee codee en dur dans ' . $chemin . '.');
}

if ($erreurs) {
	foreach ($erreurs as $erreur) echo "ECHEC: $erreur\n";
	exit(1);
}

echo "OK: architecture SPIP 4, Inscription 4 et frontieres metier.\n";
