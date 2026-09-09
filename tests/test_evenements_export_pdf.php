<?php

define('_ECRIRE_INC_VERSION', 1);
function include_spip($fichier) {}
function charger_fonction($nom, $type) { return static function () { return $GLOBALS['id_test']; }; }
function autoriser($faire, $type, $id) { return $GLOBALS['droit_test'] && $faire === 'gererinscriptions' && $type === 'evenement' && $id === 42; }
function sql_fetsel($champs, $table, $where) { return $GLOBALS['existe_test'] ? ['titre' => 'Événement test'] : false; }
function sql_allfetsel($champs, $table, $where, $group, $order) {
	if ($where !== 'id_evenement=42') { throw new RuntimeException('Export hors événement'); }
	return [['id_activite' => 1, 'prenom_inscrit' => 'Élodie', 'nom_inscrit' => 'Test', 'email_inscrit' => 'test@example.test', 'nombre_inscrits' => 2, 'statut' => 'ok']];
}
function association_pdf_envoyer($fond, $contexte, $nom) { $GLOBALS['exports_test'][] = compact('fond', 'contexte', 'nom'); }
require dirname(__DIR__) . '/plugins/association-evenements/action/exporter_activite_pdf.php';
$GLOBALS['exports_test'] = [];
$GLOBALS['id_test'] = 42;
$GLOBALS['droit_test'] = false;
$GLOBALS['existe_test'] = true;
if (action_exporter_activite_pdf_dist() !== false || $GLOBALS['exports_test']) { throw new RuntimeException('Export sans autorisation'); }
$GLOBALS['droit_test'] = true;
$GLOBALS['existe_test'] = false;
if (action_exporter_activite_pdf_dist() !== false || $GLOBALS['exports_test']) { throw new RuntimeException('Export inexistant'); }
$GLOBALS['existe_test'] = true;
if (action_exporter_activite_pdf_dist() !== true || count($GLOBALS['exports_test']) !== 1) { throw new RuntimeException('Export manquant'); }
$export = $GLOBALS['exports_test'][0];
if ($export['fond'] !== 'prive/pdf/association_evenements' || $export['contexte']['lignes'][0]['prenom_inscrit'] !== 'Élodie') { throw new RuntimeException('Contexte altéré'); }
echo "OK : export PDF événement autorisé et borné, caractères UTF-8 conservés.\n";
