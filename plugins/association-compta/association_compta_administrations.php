<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_compta_upgrade($nom_meta_base_version, $version_cible) {
	include_spip('base/upgrade');
	$maj = [
		'create' => [['maj_tables', [
			'spip_asso_comptes',
			'spip_asso_plan',
			'spip_asso_destination',
			'spip_asso_destination_op',
		]]],
	];
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

function association_compta_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}
