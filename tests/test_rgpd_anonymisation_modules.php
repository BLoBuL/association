<?php

define('_ECRIRE_INC_VERSION', true);
function include_spip($fichier) {}
function _T($cle) { return $cle; }
function pipeline($nom, $flux) {
	if ($nom !== 'association_rgpd_anonymiser_auteur' || intval($flux['args']['id_auteur'] ?? 0) !== 7) {
		return array();
	}
	return array(
		'activites_anonymisees' => 1,
		'dons_anonymises' => 1,
		'ventes_anonymisees' => 1,
		'prets_anonymises' => 1,
		'comptes_anonymises' => 1,
		'transactions_anonymisees' => 1,
	);
}

$racine = dirname(__DIR__);
require $racine . '/inc/rgpd_anonymisation.php';
$resultat = association_rgpd_anonymiser_auteur(7, array('email' => 'test@example.invalid'));
$attendues = array('activites_anonymisees', 'dons_anonymises', 'ventes_anonymisees', 'prets_anonymises', 'comptes_anonymises', 'transactions_anonymisees');
if (empty($resultat['ok']) || array_diff($attendues, array_keys($resultat['resume'] ?? array()))) {
	fwrite(STDERR, "L'orchestrateur RGPD ne conserve pas le résumé des modules.\n");
	exit(1);
}

$socle = file_get_contents($racine . '/inc/rgpd_anonymisation.php');
if (preg_match('/spip_(?:asso_|transactions)/', $socle)) {
	fwrite(STDERR, "L'anonymisation du socle référence encore une table métier.\n");
	exit(1);
}
foreach (array('evenements', 'dons', 'ventes', 'prets', 'compta', 'paiements') as $module) {
	$fichier = $racine . '/plugins/association-' . $module . '/association_' . $module . '_pipelines.php';
	$source = file_get_contents($fichier);
	if (strpos($source, 'association_rgpd_anonymiser_auteur') === false) {
		fwrite(STDERR, "Handler RGPD absent du module {$module}.\n");
		exit(1);
	}
}

echo "OK: l'anonymisation RGPD est distribuée entre les modules propriétaires.\n";
