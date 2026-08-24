<?php

$racine = dirname(__DIR__);
$paquet = file_get_contents($racine . '/paquet.xml');
$base = file_get_contents($racine . '/base/association_evenements.php');
$admin = file_get_contents($racine . '/association_evenements_administrations.php');
$champs_extras = file_get_contents($racine . '/base/association_champs_extras.php');
$fonctions = file_get_contents($racine . '/association_evenements_fonctions.php');
$erreurs = array();
foreach (array('prefix="association_evenements"', 'schema="1.2.0"', 'nom="agenda"', 'nom="saisies"', 'nom="verifier"') as $attendu) {
	if (strpos($paquet, $attendu) === false) $erreurs[] = 'déclaration absente: ' . $attendu;
}
foreach (array('spip_asso_categories_activites', 'spip_asso_activites', 'spip_asso_categories_activites_liens') as $table) {
	if (strpos($base, $table) === false) $erreurs[] = 'table absente: ' . $table;
}
foreach (array('type_inscrit', 'association', 'participants_json', 'visible_in_list_members', 'notify_the_members', 'journal', 'annotation', 'condition_inscription') as $champ) {
	if (strpos($base, "'" . $champ . "'") === false) $erreurs[] = 'champ métier absent: ' . $champ;
}
if (strpos($admin, 'sql_drop_table') !== false) $erreurs[] = 'désinstallation destructive';
if (!str_contains($champs_extras, 'function association_champs_extras_meta')
	|| str_contains($champs_extras, "include_spip('association_options')")
	|| str_contains($champs_extras, '_DIR_PLUGIN_ASSOCIATION')) {
	$erreurs[] = 'les champs extras doivent lire leur configuration sans charger le socle historique';
}
foreach (array('inc/actions', 'inc/editer', 'inc/autoriser') as $api) {
	if (!str_contains($fonctions, "include_spip('$api')")) {
		$erreurs[] = "la surcharge Agenda doit charger l’API SPIP $api";
	}
}
if (!is_file($racine . '/squelettes/evenement.html')) $erreurs[] = 'page publique événement absente';
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK: structure Association Événements.\n";
