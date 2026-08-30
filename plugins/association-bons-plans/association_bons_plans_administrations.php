<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_bons_plans_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	$creer = array(array('maj_tables', array('spip_bons_plans', 'spip_bons_plans_liens')));
	maj_plugin($meta, $cible, array('create' => $creer, '1.0.0' => $creer));
}

function association_bons_plans_vider_tables($meta) {
	// Les tables historiques sont conservées pour permettre une désactivation sans perte de données.
	effacer_meta($meta);
}
