<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function autoriser_dons_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	return association_module_actif('dons') && association_est_admin_complet($qui);
}

function autoriser_don_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_don_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_dons_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}
