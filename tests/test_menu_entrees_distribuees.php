<?php
define('_ECRIRE_INC_VERSION', 1);
$racine = dirname(__DIR__);
function include_spip($chemin) { return true; }
function _T($cle) { return $cle; }
require_once $racine . '/association_pipelines.php';
$modules = array(
	'association-prets' => 'association_prets', 'association-dons' => 'association_dons',
	'association-evenements' => 'association_evenements', 'association-groupes' => 'association_groupes',
	'association-compta' => 'association_compta', 'association-adhesions' => 'association_adhesions',
	'association-partenaires' => 'association_partenaires', 'association-commerce' => 'association_commerce',
	'association-ventes' => 'association_ventes', 'association-paiements' => 'association_paiements',
	'association-bannieres' => 'association_bannieres', 'association-communication' => 'association_communication',
	'association-bons-plans' => 'association_bons_plans',
);
foreach ($modules as $module => $prefixe) {
	require_once $racine . '/plugins/' . $module . '/inc/' . $prefixe . '_menu.php';
}
function pipeline($nom, $flux) {
	global $modules;
	if ($nom !== 'association_menu_entrees') { return $flux; }
	foreach ($modules as $prefixe) {
		$fonction = $prefixe . '_association_menu_entrees';
		$flux = $fonction($flux);
	}
	return $flux;
}
function verifier_menu($condition, $message) {
	if (!$condition) { fwrite(STDERR, "ECHEC: $message\n"); exit(1); }
	echo "OK: $message\n";
}
$entrees = pipeline('association_menu_entrees', array(
	'configurer_association' => array('ordre' => 99, 'label' => 'Paramètres', 'exec' => 'configurer_association', 'icone' => 'configurer_association'),
));
uasort($entrees, function ($a, $b) { return $a['ordre'] <=> $b['ordre']; });
$attendues = array('adherents','cotisations','activites','benevoles','partenaires','bons_plans','association_commerce','ventes','dons','comptes','transactions','prets','bannieres','notifications','configurer_association');
verifier_menu(array_keys($entrees) === $attendues, 'les entrées sont fournies et ordonnées par leurs modules');
foreach ($entrees as $cle => $definition) {
	$exec_attendu = $cle === 'association_commerce' ? 'commerce' : $cle;
	verifier_menu($definition['exec'] === $exec_attendu, "$cle conserve sa page privée");
}
$socle = file_get_contents($racine . '/association_pipelines.php');
foreach (array('adherents','cotisations','activites','benevoles','partenaires','bons_plans','association_commerce','ventes','dons','comptes','transactions','prets','bannieres','notifications') as $cle) {
	verifier_menu(strpos($socle, "'$cle' =>") === false, "$cle n est plus déclaré par le socle");
}
$autorisation_paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_autoriser.php');
verifier_menu(strpos($autorisation_paiements, "autoriser('menu', '_transactions'") === false, 'le menu Paiements ne reboucle pas sur l autorisation Bank');
echo "Toutes les entrées de menu distribuées sont identiques.\n";
