<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_ventes_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_ventes_rgpd');
	$flux['data']['ventes'] = association_ventes_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}

function association_ventes_association_capacites($capacites) {
	$capacites['ventes'] = ['plugin' => 'association_ventes'];

	return $capacites;
}

function association_ventes_association_enregistrer_vente($vente) {
	include_spip('inc/association_ventes_commandes');

	return association_ventes_enregistrer($vente);
}

function association_ventes_post_edition($flux) {
	if (($flux['args']['table'] ?? '') !== 'spip_commandes') {
		return $flux;
	}

	$id_commande = (int) ($flux['args']['id_objet'] ?? 0);
	$statut = (string) ($flux['data']['statut'] ?? '');
	if (!$id_commande || ($statut && !in_array($statut, ['attente', 'partiel', 'attente_echeance', 'paye', 'envoye'], true))) {
		return $flux;
	}

	include_spip('inc/association_ventes_commandes');
	association_ventes_commande_synchroniser($id_commande);

	return $flux;
}

function association_ventes_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['ventes_anonymisees'] = association_rgpd_updateq('spip_asso_ventes', association_rgpd_filtrer_champs('spip_asso_ventes', [
		'acheteur' => 'Anonyme', 'commentaire' => '',
	]), 'id_acheteur=' . $id);
	return $flux;
}

function association_ventes_association_compta_objets_declarer($flux) {
	$data = [];
	$res = sql_select('id_vente,date_vente,article,acheteur', 'spip_asso_ventes', '', '', 'date_vente DESC');
	while ($vente = sql_fetch($res)) {
		$id = (int) $vente['id_vente'];
		$data[$id] = '#' . $id . ' - ' . $vente['date_vente'] . ' - ' . $vente['article'] . ' - ' . $vente['acheteur'];
	}
	$flux['data']['asso_vente'] = [
		'label' => 'association_compta:choix_vente', 'objet' => 'asso_vente', 'champ' => 'id_vente',
		'label_selection' => 'association_compta:choix_vente', 'data' => $data,
		'imputation_recette' => $GLOBALS['association_metas']['pc_ventes'] ?? '',
		'destination_defaut' => 'ventes',
	];
	return $flux;
}
