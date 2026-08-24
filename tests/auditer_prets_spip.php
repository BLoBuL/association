<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}

$prets = sql_allfetsel('id_pret, id_ressource, id_emprunteur, date_retour', 'spip_asso_prets');
$ressources = sql_allfetsel('id_ressource, statut', 'spip_asso_ressources');
$ressources_par_id = array_column($ressources ?: array(), null, 'id_ressource');
$actifs = array();
$identifiants_invalides = 0;
$liens_orphelins = 0;

foreach ($prets ?: array() as $pret) {
	$id_ressource_brut = trim((string) ($pret['id_ressource'] ?? ''));
	$id_emprunteur_brut = trim((string) ($pret['id_emprunteur'] ?? ''));
	if (!ctype_digit($id_ressource_brut) || intval($id_ressource_brut) <= 0
		|| !ctype_digit($id_emprunteur_brut) || intval($id_emprunteur_brut) <= 0) {
		$identifiants_invalides++;
	}
	$id_ressource = intval($id_ressource_brut);
	if (!isset($ressources_par_id[$id_ressource])) {
		$liens_orphelins++;
	}
	$date_retour = trim((string) ($pret['date_retour'] ?? ''));
	if ($id_ressource > 0 && ($date_retour === '' || str_starts_with($date_retour, '0000-00-00'))) {
		$actifs[$id_ressource] = true;
	}
}

$statuts_incoherents = 0;
foreach ($ressources_par_id as $id_ressource => $ressource) {
	$attendu = isset($actifs[intval($id_ressource)]) ? 'reserve' : 'ok';
	if (in_array($ressource['statut'], array('ok', 'reserve'), true) && $ressource['statut'] !== $attendu) {
		$statuts_incoherents++;
	}
}

$trouver_table = charger_fonction('trouver_table', 'base');
$table = $trouver_table('spip_asso_prets');
$type_ressource = $table['field']['id_ressource'] ?? 'inconnu';
$type_emprunteur = $table['field']['id_emprunteur'] ?? 'inconnu';

echo 'OK: ' . count($ressources ?: array()) . ' ressources, '
	. count($prets ?: array()) . ' prêts, '
	. count($actifs) . ' ressources prêtées, '
	. $identifiants_invalides . ' identifiants invalides, '
	. $liens_orphelins . ' liens orphelins, '
	. $statuts_incoherents . ' statuts incohérents ; types '
	. $type_ressource . ' / ' . $type_emprunteur . ".\n";
