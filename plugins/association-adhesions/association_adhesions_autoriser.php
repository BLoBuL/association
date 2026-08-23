<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Autoriser la migration des anciens comptes secondaires vers Familles.
 */
function autoriser_association_migrerfamilles_dist($faire, $type, $id, $qui, $opt) {
	include_spip('inc/association_autorisations');
	return association_est_admin_complet(association_normalize_qui($qui));
}

function autoriser_adherents_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}

function autoriser_cotisations_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}

function autoriser_adherents_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_voiradherent_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);
}


function autorite_autoriser_auteurs_menu($faire,$quoi,$id,$qui,$options){
	$qui = association_normalize_qui($qui);
	association_debug_log('autorite_autoriser_auteurs_menu entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');
	return ($qui['statut'] === '0minirezo');
}

function autorite_autoriser_auteur_voir($faire,$quoi,$id,$qui,$options){
	$qui = association_normalize_qui($qui);
	association_debug_log('autorite_autoriser_auteur_voir entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');
	return ($qui['statut'] === '0minirezo');
}
