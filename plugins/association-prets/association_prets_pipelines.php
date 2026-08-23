<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_prets_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_prets_rgpd');
	$flux['data']['prets'] = association_prets_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}
