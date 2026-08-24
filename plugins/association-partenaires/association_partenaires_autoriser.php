<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_partenaires_autoriser() {
}

function autoriser_partenaires_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}

function autoriser_partenaire_creer_dist($faire, $type, $id, $qui, $opt) { return autoriser('partenaires_menu', '', 0, $qui); }
function autoriser_partenaire_modifier_dist($faire, $type, $id, $qui, $opt) { return autoriser('partenaires_menu', '', 0, $qui); }
function autoriser_partenaire_supprimer_dist($faire, $type, $id, $qui, $opt) { return autoriser('partenaires_menu', '', 0, $qui); }
function autoriser_partenaire_voir_dist($faire, $type, $id, $qui, $opt) { return true; }
