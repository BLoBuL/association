<?php
define('_ECRIRE_INC_VERSION', 1);
function include_spip($fichier) {}
function _T($texte, $args = []) { return $texte; }
function minipres($texte) { return $texte; }
function charger_fonction($nom, $type) { return static fn() => $GLOBALS['arg']; }
function autoriser($faire, $type) { return $GLOBALS['droit'] && $faire === 'associer' && $type === 'dons'; }
function association_dons_recu_fiscal_emetteur() { return ['complet' => $GLOBALS['complet'], 'emetteur' => ['nom' => 'RECETTE organisme']]; }
function sql_fetsel($champs, $table, $where) {
	if ($champs !== 'id_auteur,nom' || $where !== 'id_auteur=42') { throw new RuntimeException('Lecture non bornée'); }
	$GLOBALS['lectures']++;
	return ['id_auteur' => 42, 'nom' => 'RECETTE Élodie'];
}
function association_dons_montant_fiscal($id, $annee) {
	if ($id !== 42 || $annee !== 2025) { throw new RuntimeException('Période non bornée'); }
	return $GLOBALS['montant'];
}
function association_pdf_envoyer($fond, $contexte, $nom) { $GLOBALS['pdf'][] = compact('fond', 'contexte', 'nom'); }
require dirname(__DIR__) . '/plugins/association-dons/action/exporter_recu_fiscal_pdf.php';
$GLOBALS['arg'] = 'specimen-42-2025';
$GLOBALS['droit'] = false;
$GLOBALS['complet'] = true;
$GLOBALS['lectures'] = 0;
$GLOBALS['montant'] = 150.25;
$GLOBALS['pdf'] = [];
if (action_exporter_recu_fiscal_pdf_dist() !== false || $GLOBALS['lectures']) { throw new RuntimeException('Accès sans droit'); }
$GLOBALS['droit'] = true;
foreach (['42-2025', 'specimen-0-2025', 'specimen-42-2025-injection'] as $arg) {
	$GLOBALS['arg'] = $arg;
	ob_start();
	$resultat = action_exporter_recu_fiscal_pdf_dist();
	ob_end_clean();
	if ($resultat !== false || $GLOBALS['lectures']) { throw new RuntimeException('Émission définitive ou identifiant invalide accepté'); }
}
$GLOBALS['arg'] = 'specimen-42-2025';
$GLOBALS['complet'] = false;
ob_start();
$resultat = action_exporter_recu_fiscal_pdf_dist();
ob_end_clean();
if ($resultat !== false || $GLOBALS['lectures']) { throw new RuntimeException('Émetteur incomplet accepté'); }
$GLOBALS['complet'] = true;
if (!action_exporter_recu_fiscal_pdf_dist() || $GLOBALS['pdf'][0]['contexte']['montant'] !== '150,25') { throw new RuntimeException('Aperçu incorrect'); }
$GLOBALS['montant'] = 0;
ob_start();
$resultat = action_exporter_recu_fiscal_pdf_dist();
ob_end_clean();
if ($resultat !== false || count($GLOBALS['pdf']) !== 1) { throw new RuntimeException('Don nul exporté'); }
echo "OK : aperçu Dons, droits propres, organisme complet et émission définitive fermée.\n";
