<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_prets_rgpd_export_ressource($id_ressource) {
	$id_ressource = intval($id_ressource);
	if ($id_ressource <= 0) {
		return [];
	}
	$row = sql_fetsel('id_ressource,code,intitule,statut', 'spip_asso_ressources', 'id_ressource=' . $id_ressource);
	if (!$row) {
		return [];
	}
	return [
		'id_ressource' => intval($row['id_ressource'] ?? 0),
		'code' => (string) ($row['code'] ?? ''),
		'intitule' => (string) ($row['intitule'] ?? ''),
		'statut' => (string) ($row['statut'] ?? ''),
	];
}

function association_prets_rgpd_export($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_prets', 'id_emprunteur=' . sql_quote((string) intval($id_auteur)), '', 'date_sortie DESC, id_pret DESC');
	$export = [];
	foreach ($rows as $row) {
		$export[] = [
			'id_pret' => intval($row['id_pret'] ?? 0),
			'id_ressource' => (string) ($row['id_ressource'] ?? ''),
			'ressource' => association_prets_rgpd_export_ressource($row['id_ressource'] ?? ''),
			'date_sortie' => association_rgpd_export_date($row['date_sortie'] ?? ''),
			'duree' => intval($row['duree'] ?? 0),
			'date_retour' => association_rgpd_export_date($row['date_retour'] ?? ''),
			'id_emprunteur' => (string) ($row['id_emprunteur'] ?? ''),
			'statut' => (string) ($row['statut'] ?? ''),
			'commentaire_sortie' => (string) ($row['commentaire_sortie'] ?? ''),
			'commentaire_retour' => (string) ($row['commentaire_retour'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		];
	}
	return $export;
}
