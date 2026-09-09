<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_ventes_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	maj_plugin($meta, $cible, [
		'create' => [['maj_tables', ['spip_asso_ventes']]],
		'1.1.0' => [['maj_tables', ['spip_asso_ventes']]],
	]);
}
function association_ventes_vider_tables($meta) {
	effacer_meta($meta);
}
