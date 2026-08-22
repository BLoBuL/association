<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
function association_ventes_upgrade($meta, $cible) { include_spip('base/upgrade'); maj_plugin($meta, $cible, array('create' => array(array('maj_tables', array('spip_asso_ventes'))))); }
function association_ventes_vider_tables($meta) { effacer_meta($meta); }
