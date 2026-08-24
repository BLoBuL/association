<?php

$racine = dirname(__DIR__) . '/plugins/association-compta';
$erreurs = array();
foreach (array('valider_compte', 'invalider_compte', 'supprimer_compte', 'traiter_comptes') as $action) {
	$source = file_get_contents($racine . '/action/' . $action . '.php');
	if (strpos($source, 'spip_asso_comptes') !== false) {
		$erreurs[] = 'L action ' . $action . ' contourne encore l API comptable.';
	}
	if (strpos($source, 'association_compta_ecriture_') === false) {
		$erreurs[] = 'L action ' . $action . ' n appelle pas l API comptable.';
	}
}
$maintenance = file_get_contents($racine . '/inc/association_compta_maintenance.php');
if (strpos($maintenance, "sql_delete('spip_asso_comptes'") !== false
	|| strpos($maintenance, 'association_compta_ecriture_supprimer(') === false) {
	$erreurs[] = 'La maintenance comptable ne nettoie pas les ventilations via l API.';
}
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK: actions et maintenance passent par l API Comptabilite.\n";
