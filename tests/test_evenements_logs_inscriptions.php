<?php

$racine = dirname(__DIR__) . '/plugins/association-evenements/';
$fichiers = array(
	'formulaires/inc/inscription_evenement.php',
	'formulaires/inc/inscription_evenement_backend.php',
	'formulaires/inscription_evenement_multi.php',
);
$contenu = '';
foreach ($fichiers as $fichier) {
	$contenu .= file_get_contents($racine . $fichier);
}
$multi_public = file_get_contents($racine . 'formulaires/inscription_evenement_multi_public.php');

$erreurs = array();
if (!str_contains($contenu, "association_log('inscriptions'")) {
	$erreurs[] = 'les diagnostics ne passent pas par la catégorie métier inscriptions';
}
if (preg_match('/spip_log\([\s\S]{0,600}_LOG_DEBUG\)/', $contenu)) {
	$erreurs[] = 'un diagnostic direct contourne encore la configuration des logs';
}
foreach (array(
	'var_export($s',
	'erreurs_complet=',
	"'categorie_brut' =>",
	"'transaction_data_form' =>",
	"'transaction_calc_tr' =>",
	"spam prenom et nom identique :",
	'IP: $ip_client',
) as $motif_interdit) {
	if (str_contains($contenu, $motif_interdit)) {
		$erreurs[] = "diagnostic sensible encore présent : $motif_interdit";
	}
}
if (str_contains($multi_public, 'ie_multi_public_saisies_legacy')) {
	$erreurs[] = 'le générateur public conserve encore son ancien contrat legacy';
}
if (!str_contains($multi_public, 'function ie_multi_public_saisies(')) {
	$erreurs[] = 'l’adaptateur canonique des saisies FO multi est absent';
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "Diagnostics d'inscription configurables et sans contenu personnel.\n";
