<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_prets_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_prets_rgpd');
	$flux['data']['prets'] = association_prets_rgpd_export(intval($flux['args']['id_auteur'] ?? 0));
	return $flux;
}

function association_prets_association_capacites($capacites) {
	$capacites['prets'] = ['plugin' => 'association_prets'];
	$capacites['ressources'] = ['plugin' => 'association_prets'];

	return $capacites;
}

function association_prets_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['prets_anonymises'] = association_rgpd_updateq('spip_asso_prets', association_rgpd_filtrer_champs('spip_asso_prets', [
		'commentaire_sortie' => '', 'commentaire_retour' => '',
	]), 'id_emprunteur=' . sql_quote((string) $id));
	return $flux;
}

function association_prets_association_compta_objets_declarer($flux) {
	$data = [];
	$res = sql_select('p.id_pret,p.date_sortie,r.intitule', 'spip_asso_prets AS p LEFT JOIN spip_asso_ressources AS r ON r.id_ressource=p.id_ressource', '', '', 'p.date_sortie DESC');
	while ($pret = sql_fetch($res)) {
		$id = (int) $pret['id_pret'];
		$data[$id] = '#' . $id . ' - ' . $pret['date_sortie'] . ' - ' . $pret['intitule'];
	}
	$flux['data']['pret'] = [
		'label' => 'association_compta:choix_pret', 'objet' => 'pret', 'champ' => 'id_pret',
		'label_selection' => 'association_compta:choix_pret', 'data' => $data,
		'imputation_recette' => $GLOBALS['association_metas']['pc_prets'] ?? '',
	];
	return $flux;
}
