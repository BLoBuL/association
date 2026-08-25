<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$racine = dirname(__DIR__);

function fichier_suite(string $chemin): string {
	global $racine;
	$candidat_monorepo = $racine . '/' . $chemin;
	if (is_file($candidat_monorepo)) {
		return $candidat_monorepo;
	}
	if (preg_match('#^plugins/([^/]+)/(.*)$#', $chemin, $match)) {
		return dirname($racine) . '/' . $match[1] . '/' . $match[2];
	}
	return $candidat_monorepo;
}

function verifier_ui(bool $condition, string $message): void {
	if (!$condition) {
		fwrite(STDERR, "ECHEC: {$message}\n");
		exit(1);
	}
	echo "OK: {$message}\n";
}

$css = file_get_contents($racine . '/prive/themes/spip/css/asso.css');
verifier_ui(str_contains($css, 'INTEGRATION AU PRIVE SPIP 4'), 'la couche d integration SPIP 4 est presente');
verifier_ui(str_contains($css, '.tableau_asso'), 'les tableaux historiques ont une presentation commune');
verifier_ui(str_contains($css, 'input[type="checkbox"]'), 'les cases a cocher natives sont restaurees');
verifier_ui(str_contains($css, ':focus-visible'), 'les actions au clavier conservent un focus visible');
verifier_ui(str_contains($css, 'overflow-x: auto'), 'les grands tableaux defilent dans leur propre zone');

$notifications = file_get_contents(fichier_suite('plugins/association-communication/prive/squelettes/contenu/notifications.html'));
verifier_ui(str_contains($notifications, 'tableau-association-scroll'), 'le tableau des notifications ne fait pas deborder la page privee');

$formulaires = array(
	'plugins/association-dons/formulaires/editer_asso_dons.html',
	'plugins/association-ventes/formulaires/editer_asso_ventes.html',
	'plugins/association-compta/formulaires/editer_asso_plan.html',
	'plugins/association-compta/formulaires/editer_asso_destinations.html',
	'plugins/association-prets/formulaires/editer_asso_ressources.html',
);

foreach ($formulaires as $fichier) {
	$contenu = file_get_contents(fichier_suite($fichier));
	verifier_ui(str_contains($contenu, 'formulaire_asso'), $fichier . ' utilise la classe formulaire commune');
	verifier_ui(str_contains($contenu, '<fieldset'), $fichier . ' groupe ses champs avec fieldset');
	verifier_ui(str_contains($contenu, '<legend'), $fichier . ' fournit une legende');
	verifier_ui(!preg_match('/<ul(?:\s|>)/i', $contenu), $fichier . ' ne repose plus sur une liste de mise en page');
}

$actions = array(
	'plugins/association-paiements/prive/objets/liste/transactions.html',
	'plugins/association-evenements/prive/objets/liste/item_categorie_participation.html',
	'plugins/association-evenements/prive/objets/liste/item_inscription_adherent.html',
	'plugins/association-adhesions/prive/objets/liste/item_categorie_cotisation.html',
	'plugins/association-adhesions/prive/objets/liste/item_cotisation_adherent.html',
);

foreach ($actions as $fichier) {
	$contenu = file_get_contents(fichier_suite($fichier));
	verifier_ui(str_contains($contenu, 'aria-hidden="true"'), $fichier . ' masque les pictogrammes decoratifs');
	verifier_ui(
		str_contains($contenu, 'aria-label=') || str_contains($contenu, 'visually-hidden'),
		$fichier . ' nomme les actions affichees uniquement par pictogramme'
	);
}

echo "Controle statique de l interface privee SPIP 4 termine.\n";
