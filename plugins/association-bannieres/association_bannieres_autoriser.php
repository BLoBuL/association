<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_bannieres_autoriser() {
}
function autoriser_bannieres_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}
function autoriser_banniere_creer_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_banniere_modifier_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_banniere_supprimer_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_banniere_voir_dist($faire, $type, $id, $qui, $opt) {
	return true;
}
function autoriser_assobanniere_creer_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_assobanniere_modifier_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_assobanniere_supprimer_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('bannieres_menu', '', 0, $qui);
}
function autoriser_assobanniere_voir_dist($faire, $type, $id, $qui, $opt) {
	return true;
}
