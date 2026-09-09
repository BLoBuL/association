<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_adhesions_association_log_categories($flux) {
	return association_log_categories_ajouter($flux, [
		'cotisations' => [
			'ordre' => 20,
			'label' => _T('association_adhesions:log_cat_cotisations'),
		],
		'adherents' => [
			'ordre' => 60,
			'label' => _T('association_adhesions:log_cat_adherents'),
		],
		'gis' => [
			'ordre' => 100,
			'label' => _T('association_adhesions:log_cat_gis'),
		],
	]);
}
