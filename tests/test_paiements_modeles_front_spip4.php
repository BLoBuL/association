<?php

$racine = dirname(__DIR__);
$plugin = $racine . '/plugins/association-paiements';
$erreurs = array();
$modeles = array(
	'payer_acte.html',
	'payer_acte_adhesion.html',
	'payer_acte_formidable.html',
	'payer_acte_participation.html',
);

foreach ($modeles as $modele) {
	$source = file_get_contents($plugin . '/modeles/' . $modele);
	if (stripos($source, '<style') !== false) {
		$erreurs[] = $modele . ' contient encore une feuille de style inline.';
	}
	if (strpos($source, 'fond=modeles/confirmer_payer_acte') === false
		|| strpos($source, 'order_resume=#EVAL{$_SESSION}') === false) {
		$erreurs[] = $modele . ' ne respecte plus le contrat de confirmation Bank.';
	}
}

$paquet = file_get_contents($plugin . '/paquet.xml');
$pipelines = file_get_contents($plugin . '/association_paiements_pipelines.php');
$css = file_get_contents($plugin . '/css/association_paiements.css');
if (strpos($paquet, 'nom="insert_head_css"') === false) {
	$erreurs[] = 'Le pipeline public insert_head_css n est pas déclaré.';
}
if (strpos($pipelines, 'function association_paiements_insert_head_css(') === false
	|| strpos($pipelines, "find_in_path('css/association_paiements.css')") === false) {
	$erreurs[] = 'La feuille autonome n est pas chargée par le pipeline SPIP.';
}
foreach (array('.payer_mode .boutons form', '.prefer-logo .payer_mode .submit .logo') as $selecteur) {
	if (strpos($css, $selecteur) === false) {
		$erreurs[] = 'Sélecteur de paiement manquant : ' . $selecteur;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - modèles de paiement autonomes et styles publics SPIP 4\n";
