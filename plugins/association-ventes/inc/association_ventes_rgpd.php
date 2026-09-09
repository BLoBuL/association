<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_ventes_rgpd_export($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_ventes', 'id_acheteur=' . intval($id_auteur), '', 'date_vente DESC, id_vente DESC');
	$export = [];
	foreach ($rows as $row) {
		$export[] = [
			'id_vente' => intval($row['id_vente'] ?? 0),
			'article' => (string) ($row['article'] ?? ''),
			'code' => (string) ($row['code'] ?? ''),
			'acheteur' => (string) ($row['acheteur'] ?? ''),
			'id_acheteur' => intval($row['id_acheteur'] ?? 0),
			'quantite' => (string) ($row['quantite'] ?? ''),
			'date_vente' => association_rgpd_export_date($row['date_vente'] ?? ''),
			'date_envoi' => association_rgpd_export_date($row['date_envoi'] ?? ''),
			'prix_vente' => (string) ($row['prix_vente'] ?? ''),
			'frais_envoi' => (float) ($row['frais_envoi'] ?? 0),
			'commentaire' => (string) ($row['commentaire'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		];
	}
	return $export;
}
