<?php

$racine = dirname(__DIR__);
$administration = file_get_contents($racine . '/association_administrations.php');
$migration_adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_migration.php');
$migrations_socle_adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_migrations_socle.php');
$stockage = file_get_contents($racine . '/plugins/association-adhesions/inc/cotisations_stockage.php');
$paquet = file_get_contents($racine . '/paquet.xml');
$documentation = file_get_contents($racine . '/docs/migration-inscription4-et-cotisations.md');

$erreurs = array();

if (strpos($administration, 'association_migrations_construire()') === false
	|| strpos($migrations_socle_adhesions, "['1.6.1', ['association_completer_migration_cotisations']") === false
	|| strpos($migrations_socle_adhesions, "include_spip('inc/association_adhesions_migration')") === false) {
	$erreurs[] = 'Le pont de compatibilité 1.6.1 du socle est incomplet.';
}

foreach (array(
	"association_cotisation_devise_historique(\$compte)",
	"association_adhesions_transaction_lire(\$id_transaction)",
	"sql_getfetsel('devise', 'spip_asso_categories_adherents'",
	"['id_objet' => (int) \$id_cotisation]",
) as $attendu) {
	if (strpos($migration_adhesions, $attendu) === false) {
		$erreurs[] = 'Migration incomplète : ' . $attendu;
	}
}

if (strpos($paquet, 'schema="1.6.1"') === false) {
	$erreurs[] = 'Le schéma du paquet ne déclenche pas la migration 1.6.1.';
}

if (strpos($stockage, 'association_cotisation_rattacher_compte($id_compte, $id_cotisation)') === false
	|| strpos($stockage, "['objet' => 'cotisation', 'id_objet' => \$id_cotisation]") === false) {
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
