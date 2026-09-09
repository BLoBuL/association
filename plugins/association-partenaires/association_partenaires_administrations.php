<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_partenaires_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	$creer = [['maj_tables', ['spip_asso_partenaires']]];
	maj_plugin($meta, $cible, ['create' => $creer, '1.0.0' => $creer]);
}

function association_partenaires_vider_tables($meta) {
	effacer_meta($meta);
}
