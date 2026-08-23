<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS['table_des_tables']['asso_dons'] = 'asso_dons';
$GLOBALS['table_titre']['asso_dons'] = "CONCAT('don ', id_don) AS titre, '' AS lang";

function generer_url_asso_don($id, $param = '', $ancre = '') {
	return generer_url_ecrire('edit_don', 'id=' . intval($id));
}

function generer_url_don($id, $param = '', $ancre = '') {
	return array('asso_don', $id);
}
