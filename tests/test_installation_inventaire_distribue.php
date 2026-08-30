<?php

define('_ECRIRE_INC_VERSION', 1);

$racine = dirname(__DIR__);
$modules = array(
	'association-adhesions', 'association-communication', 'association-compta',
	'association-dons', 'association-evenements', 'association-groupes',
	'association-paiements', 'association-prets', 'association-ventes',
	'association-commerce', 'association-partenaires', 'association-bannieres', 'association-bons-plans',
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
verifier($inventaire['plugins_requis'] === array('association'), 'la topologie exige uniquement le socle');
verifier(count($inventaire['plugins']) === 14, 'les quatorze plugins fournissent leur inventaire');
verifier(count($inventaire['tables']) === 18, 'les modules déclarent dix-huit tables');
verifier(count($inventaire['objets']) === 15, 'les modules déclarent quinze objets SQL');
verifier(count($inventaire['schemas']) === 10, 'les modules déclarent dix schémas');
verifier(empty($inventaire['erreurs']), 'aucune contribution ne se contredit');

$attendus = array(
	'association_adhesions_base_version' => '1.4.0',
	'association_compta_base_version' => '1.0.0',
	'association_evenements_base_version' => '1.2.0',
);
foreach ($attendus as $meta => $version) {
	verifier(($inventaire['schemas'][$meta] ?? '') === $version, "$meta appartient au module attendu");
}

// Le contrat distribué doit toujours suivre le schema de chaque paquet.xml.
// Cette vérification évite qu'une migration valide rende la commande de
// contrôle d'installation faussement rouge après une montée de schéma.
foreach (array_merge(array('association'), $modules) as $module) {
	$paquet = $module === 'association'
		? $racine . '/paquet.xml'
		: $racine . '/plugins/' . $module . '/paquet.xml';
	$xml = file_get_contents($paquet);
	if (!preg_match('/\bschema="([^"]+)"/', $xml, $match)) {
		continue;
	}
	$meta = str_replace('-', '_', $module) . '_base_version';
	verifier(
		($inventaire['schemas'][$meta] ?? '') === $match[1],
		"le schéma inventorié de $module correspond à paquet.xml"
	);
}

$commande = file_get_contents($racine . '/spip-cli/AssociationInstallationVerifier.php');
foreach (array('spip_asso_cotisations', 'spip_asso_comptes', 'spip_asso_activites', 'spip_asso_dons', 'spip_asso_prets', 'spip_asso_ventes') as $table) {
	verifier(strpos($commande, $table) === false, "le vérificateur central ne connaît plus $table");
}

echo "Tous les tests de l'inventaire d'installation distribué ont réussi.\n";
