<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_dons_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_dons_rgpd');
	$flux['data']['dons'] = association_dons_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}
