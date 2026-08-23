<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS['table_des_tables']['asso_ventes'] = 'asso_ventes';

function generer_url_asso_vente($id, $param = '', $ancre = '') {
	return generer_url_ecrire('edit_vente', 'id=' . intval($id));
}

function generer_url_vente($id, $param = '', $ancre = '') {
	return array('asso_vente', $id);
}
