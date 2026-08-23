<?php

$racine_prets = dirname(__DIR__) . '/plugins/association-prets';
$formulaire = file_get_contents($racine_prets . '/formulaires/editer_asso_pret.php');
$action = file_get_contents($racine_prets . '/action/supprimer_prets.php');
$helper = file_get_contents($racine_prets . '/inc/prets.php');

foreach (array('$compte[\'objet\'] = \'pret\'', '$compte[\'id_objet\'] = $id_pret') as $attendu) {
	if (strpos($formulaire, $attendu) === false) {
		fwrite(STDERR, "Lien comptable canonique absent: $attendu\n");
		exit(1);
	}
}
if (strpos($formulaire, 'pret_imputation_obligatoire') === false) {
	fwrite(STDERR, "Une réservation payante reste possible sans imputation\n");
	exit(1);
}
if (strpos($helper, "objet='pret' AND id_objet=") === false || strpos($helper, 'justification LIKE') === false) {
	fwrite(STDERR, "Le critère comptable ne couvre pas le format canonique et le legacy\n");
	exit(1);
}
if (strpos($action, 'sql_query($ok ? \'COMMIT\' : \'ROLLBACK\')') === false) {
	fwrite(STDERR, "La suppression d'un prêt n'est pas transactionnelle\n");
	exit(1);
}

echo "Comptabilité des prêts cohérente\n";
