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

function association_dons_association_compta_objets_declarer($flux) {
	$data = array();
	$res = sql_select('id_don,date_don,bienfaiteur,valeur', 'spip_asso_dons', '', '', 'date_don DESC');
	while ($don = sql_fetch($res)) {
		$id = (int) $don['id_don'];
		$data[$id] = '#' . $id . ' - ' . $don['date_don'] . ' - ' . $don['bienfaiteur'] . ' - ' . $don['valeur'];
	}
	$flux['data']['asso_don'] = array(
		'label' => 'association_compta:choix_don', 'objet' => 'asso_don', 'champ' => 'id_don',
		'label_selection' => 'association_compta:choix_don', 'data' => $data,
		'imputation_recette' => $GLOBALS['association_metas']['pc_dons'] ?? '',
		'destination_defaut' => 'dons',
	);
	return $flux;
}
