<?php

$racine = dirname(__DIR__);
$formulaire = file_get_contents($racine . '/plugins/association-compta/formulaires/editer_asso_comptes.php');
$paquet_compta = file_get_contents($racine . '/plugins/association-compta/paquet.xml');
$erreurs = array();

foreach (array('spip_evenements', 'spip_asso_cotisations', 'spip_asso_dons', 'spip_asso_ventes', 'spip_asso_prets') as $table) {
	if (str_contains($formulaire, $table)) {
		$erreurs[] = "Le formulaire générique connaît encore la table métier $table.";
	}
}
foreach (array("case 'evenement'", "case 'cotisation'", "case 'activite'", "case 'don'", 'preparer_liste_evenements') as $logique) {
	if (str_contains($formulaire, $logique)) {
		$erreurs[] = "Le formulaire générique contient encore la logique métier $logique.";
	}
}
foreach (array('association_compta_objets_declarer', 'association_compta_redirection_ecriture') as $pipeline) {
	if (!str_contains($paquet_compta, 'pipeline nom="' . $pipeline . '"')) {
		$erreurs[] = "Le contrat Comptabilité $pipeline est absent.";
	}
}

$modules = array(
	'association-adhesions' => array('cotisation', 'spip_asso_cotisations'),
	'association-evenements' => array('evenement', 'spip_evenements'),
	'association-dons' => array('asso_don', 'spip_asso_dons'),
	'association-ventes' => array('asso_vente', 'spip_asso_ventes'),
	'association-prets' => array('pret', 'spip_asso_prets'),
);
foreach ($modules as $module => [$objet, $table]) {
	$pipelines = file_get_contents($racine . '/plugins/' . $module . '/' . str_replace('-', '_', $module) . '_pipelines.php');
	$paquet = file_get_contents($racine . '/plugins/' . $module . '/paquet.xml');
	if (!str_contains($paquet, 'pipeline nom="association_compta_objets_declarer"')
		|| !str_contains($pipelines, 'association_compta_objets_declarer(')
		|| !str_contains($pipelines, "'objet' => '$objet'")
		|| !str_contains($pipelines, $table)) {
		$erreurs[] = "$module ne déclare pas complètement son objet comptable $objet.";
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: les objets du formulaire comptable sont déclarés par leurs plugins métier.\n";
