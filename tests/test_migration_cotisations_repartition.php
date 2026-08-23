<?php

$racine = dirname(__DIR__);
$administration = file_get_contents($racine . '/association_administrations.php');
$stockage = file_get_contents($racine . '/inc/cotisations_stockage.php');
$paquet = file_get_contents($racine . '/paquet.xml');
$documentation = file_get_contents($racine . '/docs/migration-inscription4-et-cotisations.md');

$erreurs = array();

foreach (array(
	"\$maj['1.6.1']",
	"association_cotisation_devise_historique(\$compte)",
	"sql_getfetsel('devise', 'spip_transactions'",
	"sql_getfetsel('devise', 'spip_asso_categories_adherents'",
	"array('id_objet' => (int) \$id_cotisation)",
) as $attendu) {
	if (strpos($administration, $attendu) === false) {
		$erreurs[] = 'Migration incomplète : ' . $attendu;
	}
}

if (strpos($paquet, 'schema="1.6.1"') === false) {
	$erreurs[] = 'Le schéma du paquet ne déclenche pas la migration 1.6.1.';
}

if (strpos($stockage, 'association_cotisation_rattacher_compte($id_compte, $id_cotisation)') === false
	|| strpos($stockage, "array('objet' => 'cotisation', 'id_objet' => \$id_cotisation)") === false) {
	$erreurs[] = 'Les nouvelles cotisations ne maintiennent pas le lien comptable id_objet.';
}

if (strpos($documentation, '`date_fin_validite`') === false
	|| strpos($documentation, '`NULL` pour l\'historique') === false) {
	$erreurs[] = 'La règle de non-invention des validités historiques n’est pas documentée.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - répartition historique des cotisations\n";
