<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_rgpd_export_evenement($id_evenement) {
	$id_evenement = intval($id_evenement);
	if ($id_evenement <= 0) {
		return array();
	}
	$row = sql_fetsel('id_evenement,titre,date_debut,date_fin,lieu,adresse', 'spip_evenements', 'id_evenement=' . $id_evenement);
	if (!$row) {
		return array();
	}
	return array(
		'id_evenement' => intval($row['id_evenement'] ?? 0),
		'titre' => (string)($row['titre'] ?? ''),
		'date_debut' => association_rgpd_export_date($row['date_debut'] ?? ''),
		'date_fin' => association_rgpd_export_date($row['date_fin'] ?? ''),
		'lieu' => (string)($row['lieu'] ?? ''),
		'adresse' => (string)($row['adresse'] ?? ''),
	);
}

function association_evenements_rgpd_export_inscriptions($id_auteur, $email = '') {
	$where = array('id_auteur=' . intval($id_auteur));
	if ($email !== '') {
		$where[] = 'email_inscrit=' . sql_quote($email);
	}
	$rows = sql_allfetsel('*', 'spip_asso_activites', '(' . implode(' OR ', $where) . ')', '', 'date DESC, id_activite DESC');
	$export = array();
	foreach ($rows as $row) {
		$id_evenement = intval($row['id_evenement'] ?? 0);
		$id_transaction = intval($row['id_transaction'] ?? 0);
		$export[] = array(
			'id_activite' => intval($row['id_activite'] ?? 0),
			'id_evenement' => $id_evenement,
			'evenement' => association_evenements_rgpd_export_evenement($id_evenement),
			'id_auteur' => intval($row['id_auteur'] ?? 0),
			'date' => association_rgpd_export_date($row['date'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
			'statut' => (string)($row['statut'] ?? ''),
			'en_attente' => intval($row['en_attente'] ?? 0),
			'valider' => intval($row['valider'] ?? 0),
			'nombre_inscrits' => intval($row['nombre_inscrits'] ?? 0),
			'nb_invite' => intval($row['nb_invite'] ?? 0),
			'prenom_inscrit' => (string)($row['prenom_inscrit'] ?? ''),
			'nom_inscrit' => (string)($row['nom_inscrit'] ?? ''),
			'email_inscrit' => (string)($row['email_inscrit'] ?? ''),
			'tel_inscrit' => (string)($row['tel_inscrit'] ?? ''),
			'ip_inscrit' => (string)($row['ip_inscrit'] ?? ''),
			'nom_participants' => association_rgpd_decoder_structure($row['nom_participants'] ?? ''),
			'commentaire' => (string)($row['commentaire'] ?? ''),
			'log' => (string)($row['log'] ?? ''),
			'id_transaction' => $id_transaction,
			'transaction' => function_exists('association_paiements_rgpd_export_transaction')
				? association_paiements_rgpd_export_transaction($id_transaction)
				: array(),
			'transaction_enregistree' => association_rgpd_decoder_structure($row['transaction'] ?? ''),
		);
	}
	return $export;
}
