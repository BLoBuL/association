<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_bons_plans_upgrade($meta, $cible) {
	include_spip('inc/plugin');
	if (function_exists('test_plugin_actif') && test_plugin_actif('spip_bon_plan')) {
		throw new RuntimeException('Désactiver le plugin historique spip_bon_plan avant Association - Bons plans.');
	}
	include_spip('base/upgrade');
	$creer = [['maj_tables', ['spip_bons_plans', 'spip_bons_plans_liens']]];
	maj_plugin($meta, $cible, ['create' => $creer, '1.0.0' => $creer]);
}

function association_bons_plans_vider_tables($meta) {
	// Les tables historiques sont conservées pour permettre une désactivation sans perte de données.
	effacer_meta($meta);
}
