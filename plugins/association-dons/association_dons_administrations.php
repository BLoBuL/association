<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_dons_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	maj_plugin($meta, $cible, [
		'create' => [['maj_tables', ['spip_asso_dons']]],
	]);
}
function association_dons_vider_tables($meta) {
	effacer_meta($meta);
}
