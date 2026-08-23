<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_prets_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_prets_rgpd');
	$flux['data']['prets'] = association_prets_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}

function association_prets_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['prets_anonymises'] = association_rgpd_updateq('spip_asso_prets', association_rgpd_filtrer_champs('spip_asso_prets', array(
		'commentaire_sortie' => '', 'commentaire_retour' => '',
	)), 'id_emprunteur=' . sql_quote((string) $id));
	return $flux;
}
