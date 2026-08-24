<?php

$racine_prets = dirname(__DIR__) . '/plugins/association-prets';
$formulaire = file_get_contents($racine_prets . '/formulaires/editer_asso_pret.php');
$action = file_get_contents($racine_prets . '/action/supprimer_prets.php');
$helper = file_get_contents($racine_prets . '/inc/prets.php');

foreach (array('association_prets_compte_lire(', 'association_prets_compte_enregistrer(') as $attendu) {
	if (strpos($formulaire, $attendu) === false) {
		fwrite(STDERR, "Lien comptable canonique absent: $attendu\n");
		exit(1);
	}
}
if (strpos($formulaire, 'pret_imputation_obligatoire') === false) {
	fwrite(STDERR, "Une réservation payante reste possible sans imputation\n");
	exit(1);
}
if (strpos($helper, "association_compta_ecritures_objet_lister('pret'") === false
	|| strpos($helper, "'legacy_id_journal' => true") === false
	|| strpos($helper, "'legacy_justification_prefix'") === false) {
	fwrite(STDERR, "L adaptateur comptable ne couvre pas le format canonique et le legacy\n");
	exit(1);
}
if (strpos($helper . $formulaire . $action, 'spip_asso_comptes') !== false
	|| strpos($helper . $formulaire . $action, 'spip_asso_destination_op') !== false) {
	fwrite(STDERR, "Prêts accède encore directement aux tables comptables\n");
	exit(1);
}
if (strpos($formulaire, "'id_auteur' => (int) \$pret['id_emprunteur']") === false) {
	fwrite(STDERR, "L écriture de prêt ne conserve pas l emprunteur\n");
	exit(1);
}
if (strpos($action, 'sql_query($ok ? \'COMMIT\' : \'ROLLBACK\')') === false) {
	fwrite(STDERR, "La suppression d'un prêt n'est pas transactionnelle\n");
	exit(1);
}

echo "Comptabilité des prêts cohérente\n";
