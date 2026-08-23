<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_dons_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_dons_rgpd');
	$flux['data']['dons'] = association_dons_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}

function association_dons_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['dons_anonymises'] = association_rgpd_updateq('spip_asso_dons', association_rgpd_filtrer_champs('spip_asso_dons', array(
		'bienfaiteur' => 'Anonyme', 'colis' => '', 'contrepartie' => '', 'commentaire' => '',
	)), 'id_adherent=' . $id);
	return $flux;
}
