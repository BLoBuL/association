<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Fournit à Communication les données d'un événement sans lui exposer les
 * tables appartenant au module Événements.
 */
function association_evenements_association_communication_email_collectif_evenement($flux) {
	$operation = (string) ($flux['args']['operation'] ?? '');
	$id_evenement = (int) ($flux['args']['id_evenement'] ?? 0);
	if (!$id_evenement) {
		return $flux;
	}

	if ($operation === 'inscriptions') {
		$inscriptions = [];
		$res = sql_select(
			'id_activite,id_auteur,nom_inscrit,prenom_inscrit,statut,nombre_inscrits',
			'spip_asso_activites',
			'id_evenement=' . $id_evenement . " AND statut!='desinscrit'"
		);
		while ($row = sql_fetch($res)) {
			$id_activite = (int) ($row['id_activite'] ?? 0);
			if ($id_activite) {
				$inscriptions[$id_activite] = $row;
			}
		}
		$flux['data'] = $inscriptions;
	} elseif ($operation === 'emails') {
		$ids = array_values(array_filter(array_unique(array_map('intval', (array) ($flux['args']['id_activites'] ?? [])))));
		$emails = [];
		if ($ids) {
			$where = sql_in('id_activite', $ids)
				. ' AND id_evenement=' . $id_evenement
				. " AND statut!='desinscrit'";
			$res = sql_select('email_inscrit', 'spip_asso_activites', $where);
			while ($row = sql_fetch($res)) {
				$emails[] = $row['email_inscrit'] ?? '';
			}
		}
		$flux['data'] = $emails;
	} elseif ($operation === 'evenement') {
		$flux['data'] = sql_fetsel(
			'id_evenement,titre,date_debut,lieu,adresse',
			'spip_evenements',
			'id_evenement=' . $id_evenement
		) ?: [];
	}

	return $flux;
}
