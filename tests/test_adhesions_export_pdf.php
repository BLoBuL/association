<?php

define('_ECRIRE_INC_VERSION', 1);
function include_spip($fichier) {}
function _T($chaine) { return $chaine; }
function _request($nom) { return $GLOBALS['requete'][$nom] ?? null; }
function charger_fonction($nom, $type) {
	return $nom === 'trouver_table'
		? static fn($table) => ['field' => array_fill_keys(['id_auteur', 'nom', 'email', 'statut', 'prenom', 'pass'], 'text')]
		: static fn() => 'export';
}
function autoriser($faire, $type) { return $GLOBALS['droit'] && $faire === 'associer' && $type === 'adherents'; }
function lire_config($nom, $defaut) {
	return $nom === 'inscription3' ? ['prenom_table' => 'on', 'pass_table' => 'on', 'absent_table' => 'on'] : [];
}
function saisies_lister_par_nom($saisies, $conteneurs) {
	return ['prenom' => ['options' => ['label' => 'Prénom']], 'pass' => ['options' => ['label' => 'Secret']], 'absent' => ['options' => ['label' => 'Absent']]];
}
function sql_in($champ, $ids) {
	if ($champ !== 'id_auteur' || $ids !== [12, 42]) { throw new RuntimeException('Identifiants non bornés'); }
	return 'id_auteur IN (12,42)';
}
function sql_allfetsel($champs, $table, $where, $group, $order) {
	if ($champs !== ['prenom', 'email'] || $table !== 'spip_auteurs' || count($where) !== 3) { throw new RuntimeException('Colonnes ou périmètre altérés'); }
	$GLOBALS['lectures']++;
	return $GLOBALS['vide'] ? [] : [['prenom' => 'Élodie <test>', 'email' => 'recette@example.test']];
}
function association_pdf_envoyer($fond, $contexte, $nom) { $GLOBALS['exports'][] = compact('fond', 'contexte', 'nom'); }
require dirname(__DIR__) . '/plugins/association-adhesions/inc/association_adhesions_export.php';
require dirname(__DIR__) . '/plugins/association-adhesions/action/exporter_adherents_pdf.php';
$GLOBALS['requete'] = ['csv' => ['prenom' => 'prenom', 'email' => 'email'], 'id_auteur_boucle' => '12,42', 'csv_name' => 'Sélection'];
$GLOBALS['droit'] = false;
$GLOBALS['lectures'] = 0;
$GLOBALS['exports'] = [];
$GLOBALS['vide'] = false;
if (action_exporter_adherents_pdf_dist() !== false || $GLOBALS['lectures']) { throw new RuntimeException('Lecture sans autorisation'); }
$GLOBALS['droit'] = true;
foreach (['pass', 'absent', 'email FROM spip_auteurs', 'alea_actuel'] as $champ) {
	if (association_adhesions_export_colonnes([$champ => 'on']) !== []) { throw new RuntimeException('Champ interdit accepté'); }
}
foreach (['', '0', '12,-42', '12,42 OR 1=1', ['12']] as $ids) {
	$GLOBALS['requete']['id_auteur_boucle'] = $ids;
	if (action_exporter_adherents_pdf_dist() !== false || $GLOBALS['lectures']) { throw new RuntimeException('Identifiant invalide accepté'); }
}
$GLOBALS['requete']['id_auteur_boucle'] = '12,42';
if (!action_exporter_adherents_pdf_dist()) { throw new RuntimeException('Export valide refusé'); }
$export = $GLOBALS['exports'][0];
if ($export['contexte']['lignes'] !== [['Élodie <test>', 'recette@example.test']] || $export['contexte']['colonnes'][0] !== 'Prénom') { throw new RuntimeException('Ordre ou UTF8 altéré'); }
$GLOBALS['vide'] = true;
action_exporter_adherents_pdf_dist();
if ($GLOBALS['exports'][1]['contexte']['lignes'] !== []) { throw new RuntimeException('Liste vide incorrecte'); }
echo "OK : PDF adhérents, droits, sélection, liste blanche, UTF8 et liste vide.\n";
