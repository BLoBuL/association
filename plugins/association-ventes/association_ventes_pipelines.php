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

function association_ventes_association_compta_objets_declarer($flux) {
	$data = array();
	$res = sql_select('id_vente,date_vente,article,acheteur', 'spip_asso_ventes', '', '', 'date_vente DESC');
	while ($vente = sql_fetch($res)) {
		$id = (int) $vente['id_vente'];
		$data[$id] = '#' . $id . ' - ' . $vente['date_vente'] . ' - ' . $vente['article'] . ' - ' . $vente['acheteur'];
	}
	$flux['data']['asso_vente'] = array(
		'label' => 'association_compta:choix_vente', 'objet' => 'asso_vente', 'champ' => 'id_vente',
		'label_selection' => 'association_compta:choix_vente', 'data' => $data,
		'imputation_recette' => $GLOBALS['association_metas']['pc_ventes'] ?? '',
	);
	return $flux;
}
