<?php

$racine = dirname(__DIR__);
$plugin = $racine . '/plugins/association-compta';
$erreurs = array();
$paquet = file_get_contents($plugin . '/paquet.xml');
$liste = file_get_contents($plugin . '/prive/objets/liste/table_comptes.html');
$item = file_get_contents($plugin . '/prive/objets/liste/item_compte.html');
$page = file_get_contents($plugin . '/prive/squelettes/contenu/comptes.html');
$js = file_get_contents($plugin . '/javascript/association_compta.js');

if (strpos($paquet, 'source="javascript/association_compta.js" type="prive"') === false) {
	$erreurs[] = 'Le script Comptabilité n est pas déclaré comme ressource privée.';
}
foreach (array($liste, $item, $page) as $source) {
	if (stripos($source, 'onclick=') !== false || stripos($source, '<script') !== false) {
		$erreurs[] = 'Un gestionnaire ou script inline subsiste dans la liste des comptes.';
	}
}
foreach (array(
	"matches('#form_comptes #check_all')",
	"matches('#form_comptes')",
	"closest('.association-compta-confirmer')",
	'formulaire.dataset.confirmSuppression',
) as $contrat) {
	if (strpos($js, $contrat) === false) {
		$erreurs[] = 'Comportement JavaScript manquant : ' . $contrat;
	}
}
if (strpos($liste . $page . $js, 'action_masse') !== false) {
	$erreurs[] = 'La référence au champ inexistant action_masse doit rester supprimée.';
}
if (strpos($page, 'fetch(') !== false || strpos($page, 'XMLHttpRequest') !== false) {
	$erreurs[] = 'Le commutateur de session SPIP ne doit plus être doublé par une requête JavaScript.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - interactions Comptabilité sans scripts inline et compatibles AJAX SPIP\n";
