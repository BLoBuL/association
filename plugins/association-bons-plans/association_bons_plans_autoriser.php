<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_bons_plans_autoriser() {
}
function autoriser_bonsplans_menu_dist($faire, $type, $id, $qui, $opt) {
	return !empty($qui['id_auteur']);
}
function autoriser_bonplan_creer_dist($faire, $type, $id, $qui, $opt) {
	return !empty($qui['id_auteur']);
}
function autoriser_bonplan_voir_dist($faire, $type, $id, $qui, $opt) {
	if ($id && ($statut = sql_getfetsel('statut', 'spip_bons_plans', 'id_bon_plan=' . intval($id))) === 'publie') {
		return true;
	}
	return !empty($qui['statut']) && in_array($qui['statut'], ['0minirezo', '1comite'], true);
}
function autoriser_bonplan_modifier_dist($faire, $type, $id, $qui, $opt) {
	if (($qui['statut'] ?? '') === '0minirezo') {
		return true;
	}
	return ($qui['statut'] ?? '') === '1comite' && (!$id || (int) sql_countsel('spip_bons_plans_liens', ['id_bon_plan=' . intval($id), 'objet=' . sql_quote('auteur'), 'id_objet=' . intval($qui['id_auteur'])]));
}
function autoriser_bonplan_supprimer_dist($faire, $type, $id, $qui, $opt) {
	return ($qui['statut'] ?? '') === '0minirezo' && empty($qui['restreint']);
}
function autoriser_rubrique_creerbonplandans_dist($faire, $type, $id, $qui, $opt) {
	return $id && autoriser('voir', 'rubrique', $id, $qui) && autoriser('creer', 'bon_plan', 0, $qui);
}
function autoriser_associerbonsplans_dist($faire, $type, $id, $qui, $opt) {
	return ($qui['statut'] ?? '') === '0minirezo';
}
