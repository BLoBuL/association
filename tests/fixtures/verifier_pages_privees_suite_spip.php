<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Compile et rend les pages privées de la suite avec une session synthétique.
 * Ce contrôle ne modifie ni auteur, ni droit, ni donnée métier.
 */
$GLOBALS['visiteur_session'] = array(
	'id_auteur' => 2,
	'nom' => 'Recette technique',
	'statut' => '0minirezo',
	'webmestre' => 'oui',
	'lang' => 'fr',
);
$GLOBALS['auteur_session'] = $GLOBALS['visiteur_session'];

$pages = array(
	'configurer_association', 'adherents', 'cotisations', 'activites', 'comptes',
	'bilan', 'plan_comptable', 'destinations', 'transactions', 'notifications',
	'benevoles', 'ressources', 'prets', 'dons', 'ventes', 'commerce',
	'partenaires', 'bannieres', 'bons_plans',
);
$resultats = array();

foreach ($pages as $page) {
	$html = recuperer_fond('prive/squelettes/contenu/' . $page, array('exec' => $page));
	$resultats[$page] = array(
		'octets' => strlen($html),
		'erreur' => (bool) preg_match('/Erreur d.exécution|Fatal error|Call to undefined|filtre .* non défini/ui', $html),
		'titre' => (bool) preg_match('/<h1\b/ui', $html),
	);
}

echo json_encode($resultats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
