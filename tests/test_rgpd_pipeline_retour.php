<?php

define('_ECRIRE_INC_VERSION', true);

function include_spip($fichier) {}
function sql_fetsel($select, $table, $where) {
	return $table === 'spip_auteurs' ? array('id_auteur' => 7, 'email' => 'test@example.invalid') : array();
}
function pipeline($nom, $flux) {
	if ($nom !== 'association_rgpd_export_auteur' || intval($flux['args']['id_auteur'] ?? 0) !== 7) {
		return array();
	}
	// SPIP retourne directement `data`, pas l'enveloppe args/data reçue par
	// les handlers du pipeline.
	return array(
		'cotisations' => array(array('id_cotisation' => 1)),
		'inscriptions_evenements' => array(),
		'operations_comptables' => array(),
		'dons' => array(),
		'ventes' => array(),
		'prets' => array(),
	);
}

require dirname(__DIR__) . '/inc/rgpd_export.php';
$export = association_rgpd_export_donnees_auteur(7);
$attendues = array('date_export_association', 'cotisations', 'inscriptions_evenements', 'operations_comptables', 'dons', 'ventes', 'prets');
$manquantes = array_diff($attendues, array_keys($export));
if ($manquantes || intval($export['cotisations'][0]['id_cotisation'] ?? 0) !== 1) {
	fwrite(STDERR, 'Le socle ne consomme pas correctement le retour natif du pipeline SPIP.' . PHP_EOL);
	exit(1);
}

echo "OK: retour natif du pipeline RGPD SPIP conservé.\n";
