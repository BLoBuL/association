<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/inc/association_autorisations.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_autorisations.php');
foreach (array('association_est_responsable_evenement', 'association_peut_acceder_evenement') as $fonction) {
	if (strpos($socle, 'function ' . $fonction . '(') !== false
		|| strpos($evenements, 'function ' . $fonction . '(') === false) {
		fwrite(STDERR, "Autorisation événementielle mal répartie: {$fonction}.\n");
		exit(1);
	}
}
if (strpos($socle, 'spip_evenements') !== false) {
	fwrite(STDERR, "Le socle conserve une requête d'autorisation Événements.\n");
	exit(1);
}
echo "OK: les autorisations événementielles appartiennent à Événements.\n";
