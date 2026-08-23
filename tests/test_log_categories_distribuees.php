<?php
define('_ECRIRE_INC_VERSION', 1);
$racine = dirname(__DIR__);
function include_spip($chemin) { return true; }
function _T($cle) { return $cle; }
require_once $racine . '/inc/association_log.php';
$fichiers = array(
	'association-evenements/inc/association_evenements_log.php',
	'association-compta/inc/association_compta_log.php',
	'association-adhesions/inc/association_adhesions_log.php',
	'association-communication/inc/association_communication_log.php',
);
foreach ($fichiers as $fichier) { require_once $racine . '/plugins/' . $fichier; }
function pipeline($nom, $flux) {
	if ($nom !== 'association_log_categories') { return $flux; }
	foreach (array(
		'association_evenements_association_log_categories',
		'association_compta_association_log_categories',
		'association_adhesions_association_log_categories',
		'association_communication_association_log_categories',
	) as $fournisseur) { $flux = $fournisseur($flux); }
	return $flux;
}
function verifier_log($condition, $message) {
	if (!$condition) { fwrite(STDERR, "ECHEC: $message\n"); exit(1); }
	echo "OK: $message\n";
}
$attendues = array('autorisations','cotisations','notifications','inscriptions','comptabilite','adherents','cron','spam','email','gis','migration','sync');
$categories = association_log_categories_defaut();
verifier_log(array_keys($categories) === $attendues, 'les douze catégories conservent leur ordre historique');
verifier_log(count(array_unique(array_keys($categories))) === 12, 'aucune catégorie n est déclarée deux fois');
$socle = file_get_contents($racine . '/inc/association_log.php');
foreach (array('cotisations','notifications','inscriptions','comptabilite','adherents','spam','email','gis') as $categorie) {
	verifier_log(strpos($socle, "'$categorie' =>") === false, "$categorie n appartient plus au catalogue du socle");
}
echo "Toutes les catégories de logs distribuées sont identiques.\n";
