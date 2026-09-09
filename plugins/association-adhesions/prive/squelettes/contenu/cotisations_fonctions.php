<?php

/**
 * Fonctions spécifiques à la page cotisations
 *
 * @package SPIP\Association\Cotisations
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Prépare la liste des cotisations selon les critères actifs
 * (filtres GET + session)
 *
 * @param string $data Type de retour : 'array', 'criteres_sql', 'count'
 * @return mixed Array des cotisations, critères SQL ou count selon $data
 */
function preparer_liste_cotisations($data = 'array') {

	// Récupérer les filtres effectifs (requête > session > défauts)
	$filtres = filtre_filtres_effectifs_cotisations();

	// Construire les critères SQL
	$criteres = [];

	// 1. Période (bornes de dates)
	$periode = $filtres['periode'];
	$date_debut = periode_date_debut($periode);
	$date_fin = periode_date_fin($periode);

	if ($date_debut && $date_debut !== '0000-00-00') {
		$criteres[] = 'C.date_creation >= ' . sql_quote($date_debut);
	}
	if ($date_fin && $date_fin !== '9999-12-31') {
		$criteres[] = 'C.date_creation <= ' . sql_quote($date_fin);
	}

	// 2. Statut de la cotisation
	if (!empty($filtres['statut_cotisation'])) {
		$criteres[] = 'C.statut = ' . sql_quote($filtres['statut_cotisation']);
	}

	// 3. Type d'inscription (reinscription/inscription)
	if (!empty($filtres['reinscription'])) {
		$criteres[] = 'C.inscription = ' . sql_quote($filtres['reinscription']);
	}

	// 4. Catégorie de cotisation
	if (!empty($filtres['id_categorie'])) {
		$criteres[] = 'C.id_categorie = ' . intval($filtres['id_categorie']);
	}

	// 5. Type de cotisation (via catégories)
	if (!empty($filtres['type_cotisation'])) {
		// Récupérer les IDs des catégories correspondant à ce type
		$ids_categories = [];
		$result = sql_allfetsel(
			'id_categorie',
			'spip_asso_categories_adherents',
			'type_adherent = ' . sql_quote($filtres['type_cotisation']) . " AND statut='ok'"
		);
		if ($result) {
			foreach ($result as $row) {
				$ids_categories[] = intval($row['id_categorie']);
			}
		}
		if (count($ids_categories) > 0) {
			$criteres[] = 'C.id_categorie IN (' . implode(',', $ids_categories) . ')';
		} else {
			// Aucune catégorie ne correspond : forcer 0 résultat
			$criteres[] = 'C.id_categorie = 0';
		}
	}

	// Critère obligatoire : seulement les cotisations (id_categorie > 0)
	$criteres[] = 'C.id_categorie > 0';

	// Combiner les critères
	$where = count($criteres) > 0 ? implode(' AND ', $criteres) : '1=1';

	// Log de debug
	association_log('cotisations', '=== preparer_liste_cotisations ===', 'debug');
	association_log('cotisations', 'Filtres actifs: ' . var_export($filtres, true), 'debug');
	association_log('cotisations', 'WHERE: ' . $where, 'debug');

	// Retour selon le type demandé
	if ($data === 'criteres_sql') {
		return $where;
	}
	if ($data === 'count') {
		return sql_countsel('spip_asso_cotisations AS C', $where);
	} else {
		// Récupérer les cotisations
		$cotisations = sql_allfetsel(
			'C.id_cotisation,C.id_compte,C.id_auteur,C.date_creation AS date,C.inscription AS reinscription,C.statut AS statut_cotisation,C.id_categorie,C.id_transaction,C.montant AS recette',
			'spip_asso_cotisations AS C',
			$where,
			'',
			'C.date_creation DESC, C.id_cotisation DESC'
		);

		$nb = is_array($cotisations) ? count($cotisations) : 0;
		association_log('cotisations', 'Nombre de cotisations retournées: ' . $nb, 'debug');

		return is_array($cotisations) ? $cotisations : [];
	}
}
