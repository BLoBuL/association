<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_ventes_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_ventes_rgpd');
	$flux['data']['ventes'] = association_ventes_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}
