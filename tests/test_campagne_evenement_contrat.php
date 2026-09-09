<?php

define('_ECRIRE_INC_VERSION', 1);
function include_spip($path) {}
function charger_fonction($nom, $type) { return static function () { return 'export'; }; }
function _request($nom) { return $GLOBALS['requete'][$nom] ?? null; }
function autoriser($faire, $type, $id) { return $id === 42; }
function test_plugin_actif($prefixe) { return !empty($GLOBALS['communication']); }
function association_evenements_responsables_ids($id) { return []; }
function sql_in($champ, $ids) { return $champ . ' IN (' . implode(',', $ids) . ')'; }
function sql_allfetsel($champs, $table, $where) {
	if ($where[0] !== 'id_evenement=42') { throw new RuntimeException('Lecture hors périmètre'); }
	return str_contains($where[1], '12') ? [['email_inscrit' => 'test@example.test']] : [];
}
function pipeline($nom, $flux) {
	if ($nom === 'association_capacites') {
		return $GLOBALS['communication'] ? ['campagnes_email' => ['plugin' => 'association_communication']] : [];
	}
	if ($nom === 'association_programmer_campagne' && $GLOBALS['communication']) {
		$GLOBALS['campagnes'][] = $flux;
		$flux['id_mailshot'] = 7;
	}
	return $flux;
}
require dirname(__DIR__) . '/inc/association_capacites.php';
require dirname(__DIR__) . '/plugins/association-evenements/action/envoyer_email_collectif_activite.php';
$GLOBALS['requete'] = ['id_evenement' => 42, 'array_activites' => [12], 'sujet' => 'Test', 'html' => '<p>Test</p>'];
$GLOBALS['communication'] = false;
$GLOBALS['campagnes'] = [];
if (association_programmer_campagne([])['id_mailshot'] !== 0 || action_envoyer_email_collectif_activite_dist() !== false) {
	throw new RuntimeException('Absence du fournisseur non gérée');
}
$GLOBALS['communication'] = true;
if (action_envoyer_email_collectif_activite_dist() !== 7 || count($GLOBALS['campagnes']) !== 1) {
	throw new RuntimeException('Campagne non transmise au fournisseur');
}
$GLOBALS['requete']['id_evenement'] = 99;
if (action_envoyer_email_collectif_activite_dist() !== false || count($GLOBALS['campagnes']) !== 1) {
	throw new RuntimeException('Événement non autorisé envoyé');
}
$GLOBALS['requete']['id_evenement'] = 42;
$GLOBALS['requete']['array_activites'] = [999];
if (action_envoyer_email_collectif_activite_dist() !== false || count($GLOBALS['campagnes']) !== 1) {
	throw new RuntimeException('Sélection étrangère envoyée');
}
echo "OK : campagne facultative, autorisation et sélection bornées à l'événement.\n";
