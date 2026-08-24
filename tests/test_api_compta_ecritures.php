<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_ecritures.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_comptabilite.php');
$domaines = array(
	'Adhésions' => $adhesions,
	'Dons' => file_get_contents($racine . '/plugins/association-dons/inc/association_dons_comptabilite.php'),
	'Ventes' => file_get_contents($racine . '/plugins/association-ventes/inc/association_ventes_comptabilite.php'),
	'Événements' => file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_comptabilite.php'),
);

foreach (array('association_compta_ecriture_creer', 'association_compta_ecriture_modifier') as $fonction) {
	if (!str_contains($api, 'function ' . $fonction . '(')) {
		fwrite(STDERR, "API comptable absente: $fonction.\n");
		exit(1);
	}
}
$ancien = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');
if (str_contains($ancien, 'function inserer_compte(') || str_contains($ancien, 'function modifier_compte(')) {
	fwrite(STDERR, "Les anciennes primitives positionnelles subsistent.\n");
	exit(1);
}
foreach (array('reinscription', 'id_categorie', 'statut_cotisation') as $champ_metier) {
	$debut = strpos($api, 'function association_compta_ecriture_champs_autorises(');
	$fin = strpos($api, 'function association_compta_ecriture_normaliser(', $debut);
	if (str_contains(substr($api, $debut, $fin - $debut), "'$champ_metier'")) {
		fwrite(STDERR, "Le champ métier $champ_metier fuit dans l’API comptable.\n");
		exit(1);
	}
}
foreach ($domaines as $domaine => $source) {
	if (str_contains($source, 'inserer_compte(') || str_contains($source, 'modifier_compte(')
		|| !str_contains($source, 'association_compta_ecriture_creer(')) {
		fwrite(STDERR, "$domaine n’utilise pas exclusivement l’API comptable structurée.\n");
		exit(1);
	}
}
echo "OK: l’API d’écriture comptable est structurée et sans champ métier de cotisation.\n";
