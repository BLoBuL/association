<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Rattache les justificatifs historiques au véritable objet cotisation.
 */
function association_adhesions_migrer_justificatifs_cotisations() {
	$migres = 0;
	$cotisations = sql_allfetsel(
		'id_cotisation,id_compte',
		'spip_asso_cotisations',
		'id_compte>0',
		'',
		'id_cotisation'
	) ?: [];
	foreach ($cotisations as $cotisation) {
		$id_cotisation = (int) $cotisation['id_cotisation'];
		$id_compte = (int) $cotisation['id_compte'];
		$liens = sql_allfetsel(
			'id_document,vu',
			'spip_documents_liens',
			"objet='compte' AND id_objet=" . $id_compte
		) ?: [];
		foreach ($liens as $lien) {
			$id_document = (int) $lien['id_document'];
			$canonique = "objet='cotisation' AND id_objet=" . $id_cotisation . ' AND id_document=' . $id_document;
			$historique = "objet='compte' AND id_objet=" . $id_compte . ' AND id_document=' . $id_document;
			if (sql_countsel('spip_documents_liens', $canonique)) {
				sql_delete('spip_documents_liens', $historique);
			} else {
				sql_updateq(
					'spip_documents_liens',
					['objet' => 'cotisation', 'id_objet' => $id_cotisation],
					$historique
				);
			}
			$migres++;
		}
	}
	return $migres;
}
