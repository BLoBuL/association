<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function autoriser_ressources_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	return association_module_actif('prets') && association_est_admin_complet($qui);
}

function autoriser_prets_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_ressource_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_ressource_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_pret_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_prets_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_pret_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_prets_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_ressources_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}
