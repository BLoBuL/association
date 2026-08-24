<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_ecritures.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_comptabilite.php');

foreach (array('association_compta_ecriture_creer', 'association_compta_ecriture_modifier') as $fonction) {
	if (!str_contains($api, 'function ' . $fonction . '(')) {
		fwrite(STDERR, "API comptable absente: $fonction.\n");
		exit(1);
	}
}
foreach (array('reinscription', 'id_categorie', 'statut_cotisation') as $champ_metier) {
	$debut = strpos($api, 'function association_compta_ecriture_champs_autorises(');
	$fin = strpos($api, 'function association_compta_ecriture_normaliser(', $debut);
	if (str_contains(substr($api, $debut, $fin - $debut), "'$champ_metier'")) {
		fwrite(STDERR, "Le champ métier $champ_metier fuit dans l’API comptable.\n");
		exit(1);
	}
}
if (str_contains($adhesions, 'inserer_compte(') || str_contains($adhesions, 'modifier_compte(')
	|| !str_contains($adhesions, 'association_compta_ecriture_creer(')
	|| !str_contains($adhesions, 'association_compta_ecriture_modifier(')) {
	fwrite(STDERR, "Adhésions n’utilise pas exclusivement l’API comptable structurée.\n");
	exit(1);
}
echo "OK: l’API d’écriture comptable est structurée et sans champ métier de cotisation.\n";
