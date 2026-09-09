<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_dons_rgpd_export($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_dons', 'id_adherent=' . intval($id_auteur), '', 'date_don DESC, id_don DESC');
	$export = [];
	foreach ($rows as $row) {
		$export[] = [
			'id_don' => intval($row['id_don'] ?? 0),
			'date_don' => association_rgpd_export_date($row['date_don'] ?? ''),
			'bienfaiteur' => (string) ($row['bienfaiteur'] ?? ''),
			'id_adherent' => intval($row['id_adherent'] ?? 0),
			'argent' => (string) ($row['argent'] ?? ''),
			'colis' => (string) ($row['colis'] ?? ''),
			'valeur' => (string) ($row['valeur'] ?? ''),
			'contrepartie' => (string) ($row['contrepartie'] ?? ''),
			'commentaire' => (string) ($row['commentaire'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		];
	}
	return $export;
}
