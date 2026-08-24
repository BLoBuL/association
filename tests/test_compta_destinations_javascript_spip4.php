<?php

$racine = dirname(__DIR__);
$plugin = $racine . '/plugins/association-compta';
$erreurs = array();
$paquet = file_get_contents($plugin . '/paquet.xml');
$php = file_get_contents($plugin . '/formulaires/inc/destinations.php');
$js = file_get_contents($plugin . '/javascript/jquery.destinations_form.js');
$plan = file_get_contents($plugin . '/prive/squelettes/contenu/plan_comptable.html');
$imports = file_get_contents($plugin . '/formulaires/importer_plan_comptable.html')
	. file_get_contents($plugin . '/formulaires/importer_destination_comptable.html');

if (strpos($paquet, 'source="javascript/jquery.destinations_form.js" type="prive"') === false) {
	$erreurs[] = 'Le composant Destinations n est pas déclaré comme script privé.';
}
foreach (array('<script', 'onClick=', 'onclick=', 'addFormField(', 'removeFormField(') as $reliquat) {
	if (stripos($php, $reliquat) !== false) {
		$erreurs[] = 'Reliquat JavaScript inline dans l éditeur de destinations : ' . $reliquat;
	}
}
if (strpos($php, 'title="Destination comptable"') !== false || strpos($php, 'title="Montant"') !== false) {
	$erreurs[] = 'Les titres de l éditeur doivent utiliser le domaine de langue Comptabilité.';
}
foreach (array(
	"closest('.association-compta-destination-ajouter')",
	"closest('.association-compta-destination-retirer')",
	"querySelector('select[name^=\"id_dest[\"]')",
	'ligne.remove()',
) as $contrat) {
	if (strpos($js, $contrat) === false) {
		$erreurs[] = 'Comportement Destinations manquant : ' . $contrat;
	}
}
if (strpos($js, '$(') !== false || strpos($js, 'function addFormField') !== false) {
	$erreurs[] = 'Le composant Destinations ne doit plus dépendre de jQuery ni exposer de fonction globale.';
}
if (strpos($plan, 'onchange=') !== false
	|| strpos($plan, 'association-compta-submit-on-change') === false) {
	$erreurs[] = 'Le filtre du plan comptable doit réutiliser le composant privé délégué.';
}
if (stripos($imports, '<script') !== false) {
	$erreurs[] = 'Les formulaires d import ne doivent plus conserver de script mort commenté.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - éditeur de destinations autonome sans jQuery ni scripts inline\n";
