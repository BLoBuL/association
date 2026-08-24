<?php

$racine = dirname(__DIR__);
$comptes = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');
$migration = file_get_contents($racine . '/plugins/association-compta/formulaires/migrer_asso_comptabilite.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_comptabilite.php');
$pipelines = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');

$erreurs = array();
foreach (array('inserer_compte_activite', 'inserer_compte_remboursement_activite', 'modifier_compte_activite') as $fonction) {
	if (str_contains($comptes, 'function ' . $fonction . '(')) {
		$erreurs[] = "L'ancien helper $fonction subsiste dans Comptabilité.";
	}
}
if (file_exists($racine . '/plugins/association-compta/action/synchroniser_comptabilite_evenement.php')) {
	$erreurs[] = "L'action événement subsiste dans Comptabilité.";
}
if (!file_exists($racine . '/plugins/association-evenements/action/synchroniser_comptabilite_evenement.php')) {
	$erreurs[] = "L'action événement n'appartient pas à Événements.";
}
if (file_exists($racine . '/plugins/association-compta/prive/objets/liste/table_comptabilite_activites.html')
	|| !file_exists($racine . '/plugins/association-evenements/prive/objets/liste/table_comptabilite_activites.html')) {
	$erreurs[] = "La vue comptable d'un événement n'appartient pas à Événements.";
}
if (str_contains($migration, 'spip_asso_activites') || str_contains($migration, 'synchroniser_comptabilite_evenement(')) {
	$erreurs[] = "La migration Comptabilité connaît encore l'implémentation Événements.";
}
foreach (array('association_evenements_compte_inscription_creer', 'association_evenements_compte_inscription_actualiser', 'association_evenements_compte_remboursement_creer') as $fonction) {
	if (!str_contains($evenements, 'function ' . $fonction . '(')) {
		$erreurs[] = "L'API Événements ne fournit pas $fonction.";
	}
}
if (!str_contains($pipelines, 'function association_evenements_association_compta_migration_metiers(')) {
	$erreurs[] = "Événements ne contribue pas à la migration comptable distribuée.";
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: Événements possède son cycle, sa synchronisation et sa vue comptables.\n";
