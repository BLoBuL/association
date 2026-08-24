<?php

$racine = dirname(__DIR__);
$paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_pipelines.php');
$paquet = file_get_contents($racine . '/plugins/association-paiements/paquet.xml');
$evenements = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_pipelines.php');
$compta = file_get_contents($racine . '/plugins/association-compta/association_compta_pipelines.php');
$debut_callback = strpos($paiements, 'function association_trig_bank_notifier_reglement(');
$fin_callback = strpos($paiements, 'function is_successful_reglement(', $debut_callback);
$callback = substr($paiements, $debut_callback, $fin_callback - $debut_callback);

if (!str_contains($paquet, 'nom="association_paiements_reglement_traiter"')
	|| !str_contains($paiements, "pipeline('association_paiements_reglement_traiter'")
	|| preg_match('/spip_asso_(?:activites|cotisations)/', $callback)
	|| str_contains($paiements, 'function mise_a_jour_participation(')
	|| !str_contains($evenements, 'function association_evenements_association_paiements_reglement_traiter(')
	|| !str_contains($adhesions, 'function association_adhesions_association_paiements_reglement_traiter(')
	|| !str_contains($compta, 'function association_compta_association_paiements_reglement_traiter(')
) {
	fwrite(STDERR, "Le callback Bank n'est pas distribué entre ses propriétaires métier.\n");
	exit(1);
}

echo "OK: Paiements distribue les règlements sans lire les tables métier.\n";
