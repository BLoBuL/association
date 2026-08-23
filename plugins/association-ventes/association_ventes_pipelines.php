<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_ventes_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_ventes_rgpd');
	$flux['data']['ventes'] = association_ventes_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}

function association_ventes_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['ventes_anonymisees'] = association_rgpd_updateq('spip_asso_ventes', association_rgpd_filtrer_champs('spip_asso_ventes', array(
		'acheteur' => 'Anonyme', 'commentaire' => '',
	)), 'id_acheteur=' . $id);
	return $flux;
}
