<?php

define('_ECRIRE_INC_VERSION', 1);
$racine = dirname(__DIR__);

function include_spip($chemin) { return true; }

require_once $racine . '/inc/association_migrations.php';
$fournisseurs = array(
	'association-evenements' => 'association_evenements',
	'association-ventes' => 'association_ventes',
	'association-adhesions' => 'association_adhesions',
	'association-prets' => 'association_prets',
	'association-communication' => 'association_communication',
	'association-dons' => 'association_dons',
	'association-compta' => 'association_compta',
);
foreach ($fournisseurs as $module => $prefixe) {
	require_once $racine . '/plugins/' . $module . '/inc/' . $prefixe . '_migrations_socle.php';
}

function pipeline($nom, $flux) {
	global $fournisseurs;
	if ($nom !== 'association_migrations_historiques') { return $flux; }
	foreach ($fournisseurs as $prefixe) {
		$fonction = $prefixe . '_association_migrations_historiques';
		$flux = $fonction($flux);
	}
	return $flux;
}

function verifier_migration($condition, $message) {
	if (!$condition) { fwrite(STDERR, "ECHEC: $message\n"); exit(1); }
	echo "OK: $message\n";
}

$maj = association_migrations_construire();
verifier_migration(count($maj) === 55, 'les 55 jalons historiques sont conservés');
verifier_migration(
	hash('sha256', serialize($maj)) === 'aeefc0bcd675e66c3ad52eeb6b033c031f59508cad4c8460a9e0641b91982670',
	'l ordre et le contenu des migrations sont strictement identiques à l historique'
);
verifier_migration($maj['1.2.7'] === array(), 'le jalon vide 1.2.7 est conservé');
verifier_migration($maj['1.3.3'][0][0] === 'association_evenements_migration_legacy', 'Événements précède Adhésions en 1.3.3');
verifier_migration($maj['1.6.1'][0][0] === 'association_completer_migration_cotisations', 'la finalisation des cotisations reste en 1.6.1');

$administration = file_get_contents($racine . '/association_administrations.php');
verifier_migration(strpos($administration, '_migration_legacy') === false, 'le socle ne nomme plus les migrations métier');
verifier_migration(strpos($administration, 'spip_asso_') === false, 'le socle ne nomme plus de table métier');

echo "Toutes les migrations historiques distribuées sont identiques.\n";
