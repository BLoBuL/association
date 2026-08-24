<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
function association_prets_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	maj_plugin($meta, $cible, array(
		'create' => array(array('maj_tables', array('spip_asso_ressources', 'spip_asso_prets'))),
		'1.1.0' => array(array('maj_tables', array('spip_asso_prets'))),
	));
}
function association_prets_vider_tables($meta) { effacer_meta($meta); }
