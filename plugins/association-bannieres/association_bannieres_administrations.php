<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bannieres_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	maj_plugin($meta, $cible, array('create' => array(array('maj_tables', array('spip_asso_bannieres')))));
}
function association_bannieres_vider_tables($meta) { effacer_meta($meta); }
