<?php

define('_ECRIRE_INC_VERSION', 1);

$racine = dirname(__DIR__);
$modules = array(
	'association-adhesions', 'association-communication', 'association-compta',
	'association-dons', 'association-evenements', 'association-groupes',
	'association-paiements', 'association-prets', 'association-ventes',
);

require_once $racine . '/inc/association_installation.php';
foreach ($modules as $module) {
	$prefixe = str_replace('-', '_', $module);
	require_once $racine . '/plugins/' . $module . '/inc/' . $prefixe . '_installation.php';
}

function pipeline($nom, $flux) {
	global $modules;
	if ($nom !== 'association_installation_inventaire') {
		return $flux;
	}
	foreach ($modules as $module) {
		$fonction = str_replace('-', '_', $module) . '_association_installation_inventaire';
		$flux = $fonction($flux);
	}
	return $flux;
}

function verifier($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "ECHEC: $message\n");
		exit(1);
	}
	echo "OK: $message\n";
}

$inventaire = association_installation_inventaire();
verifier(count($inventaire['plugins_requis']) === 10, 'la topologie exige dix plugins');
verifier(count($inventaire['plugins']) === 10, 'les dix plugins fournissent leur inventaire');
verifier(count($inventaire['tables']) === 14, 'les modules déclarent quatorze tables');
verifier(count($inventaire['objets']) === 12, 'les modules déclarent douze objets SQL');
verifier(count($inventaire['schemas']) === 7, 'les modules déclarent sept schémas');
verifier(empty($inventaire['erreurs']), 'aucune contribution ne se contredit');

$attendus = array(
	'association_adhesions_base_version' => '1.2.0',
	'association_compta_base_version' => '1.0.0',
	'association_evenements_base_version' => '1.1.0',
);
foreach ($attendus as $meta => $version) {
	verifier(($inventaire['schemas'][$meta] ?? '') === $version, "$meta appartient au module attendu");
}

$commande = file_get_contents($racine . '/spip-cli/AssociationInstallationVerifier.php');
foreach (array('spip_asso_cotisations', 'spip_asso_comptes', 'spip_asso_activites', 'spip_asso_dons', 'spip_asso_prets', 'spip_asso_ventes') as $table) {
	verifier(strpos($commande, $table) === false, "le vérificateur central ne connaît plus $table");
}

echo "Tous les tests de l'inventaire d'installation distribué ont réussi.\n";
