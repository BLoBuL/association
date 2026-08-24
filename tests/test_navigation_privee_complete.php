<?php

$racine = dirname(__DIR__);
$pages = array(
	'plugins/association-evenements' => array(
		'suivi_activites' => 'activites',
		'export_activites' => 'activites',
	),
	'plugins/association-compta' => array(
		'export_activites_compta' => 'comptes',
		'editer_asso_comptes' => 'comptes',
		'editer_asso_destinations' => 'destinations',
		'editer_asso_plan' => 'plan_comptable',
		'destination_comptable_import' => 'comptes',
		'plan_comptable_import' => 'comptes',
		'migration_donnees_comptables' => 'comptes',
	),
	'plugins/association-groupes' => array(
		'benevoles' => 'adherents',
	),
	'plugins/association-adhesions' => array(
		'recherche_avancee' => 'adherents',
		'cotisation_suppression' => 'cotisations',
	),
	'plugins/association-dons' => array(
		'editer_asso_dons' => 'dons',
	),
	'plugins/association-prets' => array(
		'editer_asso_ressources' => 'ressources',
	),
	'plugins/association-ventes' => array(
		'editer_asso_ventes' => 'ventes',
	),
	'plugins/association-paiements' => array(
		'transaction' => 'comptes',
		'transaction_abandon' => 'transaction',
		'transaction_remboursement' => 'transaction',
		'transaction_suppression' => 'transaction',
	),
	'plugins/association-communication' => array(
		'notifications' => 'configurer_association',
	),
);

$erreurs = array();
foreach ($pages as $plugin => $definitions) {
	foreach ($definitions as $page => $parent) {
		$hierarchie = "$racine/$plugin/prive/squelettes/hierarchie/$page.html";
		$navigation = "$racine/$plugin/prive/squelettes/navigation/$page.html";
		if (!is_file($hierarchie)) {
			$erreurs[] = "Hiérarchie absente : $page";
			continue;
		}
		if (!is_file($navigation)) {
			$erreurs[] = "Navigation absente : $page";
			continue;
		}
		$contenu_hierarchie = file_get_contents($hierarchie);
		$contenu_navigation = file_get_contents($navigation);
		if (!str_contains($contenu_hierarchie, '#URL_ECRIRE{accueil}')
			|| !str_contains($contenu_hierarchie, "#URL_ECRIRE{{$parent}}")
			|| !str_contains($contenu_hierarchie, '<strong class="on">')) {
			$erreurs[] = "Fil d’Ariane SPIP incomplet : $page";
		}
		if (!str_contains($contenu_navigation, '<INCLURE{fond=prive/squelettes/navigation/dist,env}>')
			|| !str_contains($contenu_navigation, '#BOITE_OUVRIR')
			|| !str_contains($contenu_navigation, "#URL_ECRIRE{{$parent}}")) {
			$erreurs[] = "Retour de navigation incomplet : $page";
		}
	}
}

$hierarchie_comptes = "$racine/plugins/association-compta/prive/squelettes/hierarchie/comptes.html";
if (!is_file($hierarchie_comptes)
	|| !str_contains(file_get_contents($hierarchie_comptes), '<strong class="on">')) {
	$erreurs[] = 'Hiérarchie absente : comptes';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: les parcours privés prioritaires possèdent hiérarchie et navigation SPIP.\n";
