<?php

/**
 * Vérification fonctionnelle de la migration des cotisations sur une base SPIP.
 *
 * À exécuter depuis la racine du site :
 *   spip php:run --include=plugins/association-adhesions/tests/verifier_migration_cotisations_spip.php
 *
 * La sortie ne contient que des agrégats et des empreintes, jamais de donnée
 * personnelle ni de détail comptable.
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être exécuté dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/association_adhesions_migration');

$erreurs = array();
$table_cotisations = sql_showtable('spip_asso_cotisations', true);
$table_comptes = sql_showtable('spip_asso_comptes', true);

if (!$table_cotisations || !$table_comptes) {
	fwrite(STDERR, "Tables de cotisations ou de comptabilité absentes.\n");
	exit(1);
}

$where_historique = isset($table_comptes['field']['objet'])
	? "objet='cotisation' OR reinscription<>'' OR statut_cotisation<>''"
	: "reinscription<>'' OR statut_cotisation<>''";

$canonique = function () use ($where_historique) {
	$historique = array();
	foreach (sql_allfetsel('*', 'spip_asso_comptes', $where_historique, '', 'id_compte') as $ligne) {
		$historique[] = array(
			(int) $ligne['id_compte'],
			(int) ($ligne['id_auteur'] ?? 0),
			(int) ($ligne['id_categorie'] ?? 0),
			(int) ($ligne['id_transaction'] ?? 0),
			(string) ($ligne['reinscription'] ?? ''),
			(string) ($ligne['statut_cotisation'] ?? ''),
			(string) ($ligne['date'] ?? ''),
			number_format((float) ($ligne['recette'] ?? 0), 2, '.', ''),
			association_cotisation_devise_historique($ligne),
		);
	}

	$cotisations = array();
	foreach (sql_allfetsel('*', 'spip_asso_cotisations', '', '', 'id_compte') as $ligne) {
		$cotisations[] = array(
			(int) $ligne['id_compte'],
			(int) $ligne['id_auteur'],
			(int) $ligne['id_categorie'],
			(int) $ligne['id_transaction'],
			(string) $ligne['inscription'],
			(string) $ligne['statut'],
			(string) $ligne['date_creation'],
			number_format((float) $ligne['montant'], 2, '.', ''),
			(string) $ligne['devise'],
		);
	}

	return array(
		'historique' => $historique,
		'cotisations' => $cotisations,
		'hash_historique' => hash('sha256', json_encode($historique)),
		'hash_cotisations' => hash('sha256', json_encode($cotisations)),
	);
};

$avant = $canonique();
association_completer_migration_cotisations();
association_completer_migration_cotisations();
$apres = $canonique();

if (count($apres['historique']) !== count($apres['cotisations'])) {
	$erreurs[] = 'Le nombre de cotisations diffère du nombre de lignes historiques.';
}
if ($apres['hash_historique'] !== $apres['hash_cotisations']) {
	$erreurs[] = 'La répartition métier diffère de la source historique.';
}
if ($avant['hash_cotisations'] !== $apres['hash_cotisations']) {
	$erreurs[] = 'La migration répétée n’est pas idempotente.';
}
if (sql_countsel('spip_asso_cotisations AS c LEFT JOIN spip_asso_comptes AS a ON a.id_compte=c.id_compte', 'a.id_compte IS NULL')) {
	$erreurs[] = 'Au moins une cotisation ne possède pas son écriture comptable.';
}
if (sql_countsel('spip_asso_cotisations', "date_debut_validite IS NOT NULL OR date_fin_validite IS NOT NULL")) {
	$erreurs[] = 'Une validité absente du schéma historique a été inventée.';
}
if (sql_countsel('spip_asso_cotisations', "devise='' OR devise IS NULL")) {
	$erreurs[] = 'Au moins une cotisation ne possède pas de devise.';
}
if (isset($table_comptes['field']['objet']) && sql_countsel(
	'spip_asso_comptes AS a LEFT JOIN spip_asso_cotisations AS c ON c.id_cotisation=a.id_objet',
	"a.objet='cotisation' AND c.id_cotisation IS NULL"
)) {
	$erreurs[] = 'Au moins une écriture comptable pointe vers une cotisation absente.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo json_encode(array(
	'ok' => true,
	'historiques' => count($apres['historique']),
	'cotisations' => count($apres['cotisations']),
	'empreinte' => $apres['hash_cotisations'],
	'idempotente' => true,
	'sans_transaction' => (int) sql_countsel('spip_asso_cotisations', 'id_transaction=0'),
	'gratuites' => (int) sql_countsel('spip_asso_cotisations', 'montant=0'),
), JSON_UNESCAPED_SLASHES) . "\n";
