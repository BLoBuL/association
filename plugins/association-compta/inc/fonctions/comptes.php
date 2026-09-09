<?php

/**
 * Fonctions utilitaires pour les comptes
 */

/**
 * Normalise un filtre d'imputation pour la vue comptes.
 *
 * Regles :
 * - vide => `%`
 * - motif avec wildcard SQL deja fourni => conserve tel quel
 * - code exact existant dans le plan => conserve tel quel
 * - sinon, si la valeur ressemble a un prefixe de classe/sous-classe, applique `LIKE <prefixe>%`
 *
 * @param string $imputation
 * @return string
 */
function association_comptes_normaliser_filtre_imputation($imputation) {
	$imputation = trim((string) $imputation);
	if ($imputation === '') {
		return '%';
	}

	if (strpos($imputation, '%') !== false || strpos($imputation, '_') !== false) {
		return $imputation;
	}

	$exact = sql_getfetsel(
		'code',
		'spip_asso_plan',
		'code=' . sql_quote($imputation),
		'',
		'',
		'0,1'
	);
	if ($exact !== null && $exact !== false && $exact !== '') {
		return $imputation;
	}

	if (preg_match('/^[0-9A-Za-z-]+$/', $imputation)) {
		return $imputation . '%';
	}

	return $imputation;
}
/**
 * Calcule les bornes (date début incluse, date fin exclue) d'un exercice comptable
 * en fonction d'une année de départ et de la configuration (jour/mois de début).
 * On utilise une intervalle semi-ouverte [debut, prochain_debut)
 * @param int $start_year Année de départ de l'exercice (ex: 2025 pour 2025-2026)
 * @return array ['debut' => 'YYYY-MM-DD', 'prochain_debut' => 'YYYY+1-MM-DD']
 */
function association_comptes_bornes_exercice($start_year) {
	// On ne dépend pas vraiment de la date de fin puisque l'intervalle s'arrête au prochain début
	$cfg_debut = $GLOBALS['association_metas']['exercice_comptable_debut'] ?? '01/01';
	// Sécurisation format JJ/MM
	if (!preg_match('#^(\d{2})/(\d{2})$#', $cfg_debut, $m)) {
		$m = [null, '01', '01'];
	}
	$jour_debut = max(1, min(31, intval($m[1])));
	$mois_debut = max(1, min(12, intval($m[2])));
	$debut = sprintf('%04d-%02d-%02d', $start_year, $mois_debut, $jour_debut);
	$prochain_debut = sprintf('%04d-%02d-%02d', $start_year + 1, $mois_debut, $jour_debut);
	return ['debut' => $debut, 'prochain_debut' => $prochain_debut, 'mois' => $mois_debut, 'jour' => $jour_debut];
}

/**
 * Retourne le libellé humain d'un exercice comptable.
 * Si l'exercice commence au 01/01, on affiche simplement l'année.
 * Sinon on conserve le format "YYYY-YYYY+1".
 *
 * @param int|string $start_year Année de départ de l'exercice
 * @return string
 */
function association_comptes_libelle_exercice($start_year) {
	$start_year = intval($start_year);
	if ($start_year <= 0) {
		return '';
	}

	$cfg_debut = $GLOBALS['association_metas']['exercice_comptable_debut'] ?? '01/01';
	if ($cfg_debut === '01/01') {
		return strval($start_year);
	}

	return $start_year . '-' . ($start_year + 1);
}

/**
 * Agrège les écritures par devise, sans additionner des monnaies différentes.
 * La devise de la transaction fait foi. Pour une cotisation historique sans
 * transaction, on reprend celle de la catégorie, puis la devise Intl du site.
 *
 * @param string $where Clause SQL qualifiée avec l'alias `c`
 * @return array<string,array{recettes:float,depenses:float,solde:float}>
 */
function association_comptes_totaux_par_devise($where) {
	include_spip('intl_fonctions');
	$devise_defaut = strtoupper(trim((string) (function_exists('intl_devise_defaut') ? intl_devise_defaut() : lire_config('intl/devise_defaut'))));
	$totaux = [];
	$lignes = sql_allfetsel('c.id_compte,c.recette,c.depense,c.id_transaction', 'spip_asso_comptes c', $where);
	$devises = pipeline('association_compta_ecritures_devises', [
		'args' => ['ecritures' => $lignes],
		'data' => [],
	]);

	foreach ($lignes as $ligne) {
		$devise_source = $devises[(int) ($ligne['id_compte'] ?? 0)] ?? $devise_defaut;
		$devise = association_cotisation_resoudre_devise($devise_source);
		if (!isset($totaux[$devise])) {
			$totaux[$devise] = ['recettes' => 0.0, 'depenses' => 0.0, 'solde' => 0.0];
		}
		$totaux[$devise]['recettes'] += floatval($ligne['recette']);
		$totaux[$devise]['depenses'] += floatval($ligne['depense']);
		$totaux[$devise]['solde'] = $totaux[$devise]['recettes'] - $totaux[$devise]['depenses'];
	}

	if (!$totaux) {
		$totaux[$devise_defaut] = ['recettes' => 0.0, 'depenses' => 0.0, 'solde' => 0.0];
	}
	ksort($totaux);

	return $totaux;
}

/**
 * Détermine l'année de départ (start_year) de l'exercice pour une date donnée
 * @param string $date_sql (YYYY-MM-DD)
 * @return int
 */
function association_comptes_start_year_from_date($date_sql) {
	$cfg_debut = $GLOBALS['association_metas']['exercice_comptable_debut'] ?? '01/01';
	if (!preg_match('#^(\d{2})/(\d{2})$#', $cfg_debut, $m)) {
		$m = ['', '01', '01'];
	}
	$jour_debut = intval($m[1]);
	$mois_debut = intval($m[2]);
	@[$y, $m2, $d2] = explode('-', $date_sql);
	$y = intval($y);
	$m2 = intval($m2);
	$d2 = intval($d2);
	if ($m2 > $mois_debut || ($m2 == $mois_debut && $d2 >= $jour_debut)) {
		return $y; // date appartient à l'exercice débutant cette année
	}
	return $y - 1; // sinon exercice a commencé l'année précédente
}

/**
 * Calcule les totaux des comptes pour un exercice (année de départ) et une imputation données
 * @param string $imputation Code d'imputation (% pour tous)
 * @param int $exercice Année de départ de l'exercice (ex: 2025 pour 2025-2026)
 * @param string $vu Etat (optionnel)
 */
function filtre_calcul_fonction_comptes_calculer_totaux($imputation, $exercice, $vu) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	// Normaliser l'exercice : si la valeur fournie est invalide ou vide,
	// on utilise l'exercice courant déterminé par la date et la configuration.
	$ex_int = intval($exercice);
	if ($ex_int <= 0) {
		// Déterminer l'exercice courant (année de départ) à partir d'aujourd'hui
		$ex_int = association_comptes_start_year_from_date(date('Y-m-d'));
	}
	$bornes = association_comptes_bornes_exercice($ex_int);
	$debut_ex = $bornes['debut'];
	$prochain_debut_ex = $bornes['prochain_debut'];
	// Build vu clause: support numeric (vu = N) or special string '>=0' meaning vu >= 0
	$vu_clause = '';
	if (is_numeric($vu)) {
		$vu_clause = ' AND vu=' . intval($vu);
	} elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
		$vu_clause = ' AND vu >= 0';
	}
	$where = 'imputation LIKE ' . sql_quote($imputation) . $vu_clause;
	$where .= ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	$data = sql_fetsel('SUM(recette) AS somme_recettes, SUM(depense) AS somme_depenses', 'spip_asso_comptes', $where);
	$where_par_devise = 'c.imputation LIKE ' . sql_quote($imputation) . $vu_clause;
	$where_par_devise .= ' AND c.date >= ' . sql_quote($debut_ex) . ' AND c.date < ' . sql_quote($prochain_debut_ex);
	$totaux_par_devise = association_comptes_totaux_par_devise($where_par_devise);

	// Coalescer les valeurs à 0.0 si la requête retourne NULL (aucune ligne)
	$somme_recettes = 0.0;
	$somme_depenses = 0.0;
	if ($data) {
		if (isset($data['somme_recettes']) && $data['somme_recettes'] !== null) {
			$somme_recettes = floatval($data['somme_recettes']);
		}
		if (isset($data['somme_depenses']) && $data['somme_depenses'] !== null) {
			$somme_depenses = floatval($data['somme_depenses']);
		}
	}

	// Calculer aussi les totaux pour les opérations non validées (vu=0)
	$where_unval = 'imputation LIKE ' . sql_quote($imputation) . ' AND vu=0';
	$where_unval .= ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	$data_unval = sql_fetsel('SUM(recette) AS somme_recettes_unval, SUM(depense) AS somme_depenses_unval', 'spip_asso_comptes', $where_unval);
	$somme_recettes_unval = 0.0;
	$somme_depenses_unval = 0.0;
	if ($data_unval) {
		if (isset($data_unval['somme_recettes_unval']) && $data_unval['somme_recettes_unval'] !== null) {
			$somme_recettes_unval = floatval($data_unval['somme_recettes_unval']);
		}
		if (isset($data_unval['somme_depenses_unval']) && $data_unval['somme_depenses_unval'] !== null) {
			$somme_depenses_unval = floatval($data_unval['somme_depenses_unval']);
		}
	}

	// Comptages : opérations totales / recettes / depenses pour vu=1 et vu>=0
	$where_base = 'imputation LIKE ' . sql_quote($imputation) . ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	$count_operations_vu1 = sql_countsel('spip_asso_comptes', $where_base . ' AND vu=1');
	$count_operations_all = sql_countsel('spip_asso_comptes', $where_base . ' AND vu >= 0');
	$count_recettes_vu1 = sql_countsel('spip_asso_comptes', $where_base . ' AND vu=1 AND recette>0');
	$count_recettes_all = sql_countsel('spip_asso_comptes', $where_base . ' AND vu >= 0 AND recette>0');
	$count_depenses_vu1 = sql_countsel('spip_asso_comptes', $where_base . ' AND vu=1 AND depense>0');
	$count_depenses_all = sql_countsel('spip_asso_comptes', $where_base . ' AND vu >= 0 AND depense>0');

	return [
		'recettes' => $somme_recettes,
		'depenses' => $somme_depenses,
		'solde' => $somme_recettes - $somme_depenses,
		// non validées (vu=0)
		'recettes_unval' => $somme_recettes_unval,
		'depenses_unval' => $somme_depenses_unval,
		'solde_unval' => $somme_recettes_unval - $somme_depenses_unval,
		// utiles : totaux incluant non validées
		'recettes_incluant_unval' => $somme_recettes + $somme_recettes_unval,
		'depenses_incluant_unval' => $somme_depenses + $somme_depenses_unval,
		'solde_incluant_unval' => ($somme_recettes + $somme_recettes_unval) - ($somme_depenses + $somme_depenses_unval),
		'par_devise' => $totaux_par_devise,
		// comptes d'opérations (pour l'affichage de compteurs)
		'count_operations_vu1' => intval($count_operations_vu1),
		'count_operations_all' => intval($count_operations_all),
		'count_recettes_vu1' => intval($count_recettes_vu1),
		'count_recettes_all' => intval($count_recettes_all),
		'count_depenses_vu1' => intval($count_depenses_vu1),
		'count_depenses_all' => intval($count_depenses_all),
	];
}

/**
 * Liste les exercices disponibles (années de départ) triés décroissants
 * @param string $imputation
 * @param string $vu
 * @return array start_years
 */
function filtre_calcul_fonction_comptes_lister_annees($imputation, $vu) {
	// Pour compat : cette fonction renvoie maintenant les années de départ d'exercice
	return filtre_calcul_fonction_comptes_lister_exercices($imputation, $vu);
}

function filtre_calcul_fonction_comptes_lister_exercices($imputation, $vu) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	$where = 'imputation LIKE ' . sql_quote($imputation) . (!is_numeric($vu) ? '' : (' AND vu=' . intval($vu)));
	$mm = sql_fetsel('MIN(date) AS mind, MAX(date) AS maxd', 'spip_asso_comptes', $where);
	if (!$mm || !$mm['mind']) {
		// Aucun enregistrement : proposer l'exercice courant
		$annee = date('Y');
		return [$annee];
	}
	$min_start = association_comptes_start_year_from_date($mm['mind']);
	$max_start = association_comptes_start_year_from_date($mm['maxd']);
	$out = [];
	for ($y = $max_start; $y >= $min_start; $y--) {
		$out[] = $y;
	}
	return $out;
}

/**
 * Liste les opérations comptables pour un exercice (année de départ)
 * @param string $imputation
 * @param int $exercice Année de départ
 */
function filtre_calcul_fonction_comptes_lister_operations($imputation, $exercice, $vu, $debut, $max_par_page) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	$bornes = association_comptes_bornes_exercice(intval($exercice));
	$debut_ex = $bornes['debut'];
	$prochain_debut_ex = $bornes['prochain_debut'];
	$where = 'imputation LIKE ' . sql_quote($imputation) . (!is_numeric($vu) ? '' : (' AND vu=' . intval($vu)));
	$where .= ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	$comptes = sql_allfetsel('id_compte', 'spip_asso_comptes', $where, '', 'date DESC, id_compte DESC', intval($debut) . ',' . intval($max_par_page));
	return array_column($comptes, 'id_compte');
}

/**
 * Compte le nombre d'opérations pour un exercice
 */
function filtre_calcul_fonction_comptes_compter_operations($imputation, $exercice, $vu) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	$bornes = association_comptes_bornes_exercice(intval($exercice));
	$debut_ex = $bornes['debut'];
	$prochain_debut_ex = $bornes['prochain_debut'];
	// Build vu clause: numeric vu => vu = N, string '>=0' => vu >= 0, empty => no vu filter
	$vu_clause = '';
	if (is_numeric($vu)) {
		$vu_clause = ' AND vu=' . intval($vu);
	} elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
		$vu_clause = ' AND vu >= 0';
	}
	$where = 'imputation LIKE ' . sql_quote($imputation) . $vu_clause;
	$where .= ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	return sql_countsel('spip_asso_comptes', $where);
}

/**
 * Compte le nombre d'opérations de recettes (>0) pour un exercice donné
 * @param string $imputation
 * @param int $exercice
 * @param string|int $vu
 * @return int
 */
function filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, $vu) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	$bornes = association_comptes_bornes_exercice(intval($exercice));
	$debut_ex = $bornes['debut'];
	$prochain_debut_ex = $bornes['prochain_debut'];
	$vu_clause = '';
	if (is_numeric($vu)) {
		$vu_clause = ' AND vu=' . intval($vu);
	} elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
		$vu_clause = ' AND vu >= 0';
	}
	$where = 'imputation LIKE ' . sql_quote($imputation) . $vu_clause;
	$where .= ' AND date >= ' . sql_quote($debut_ex) . ' AND date < ' . sql_quote($prochain_debut_ex);
	$where .= ' AND recette > 0';
	return sql_countsel('spip_asso_comptes', $where);
}

/**
 * Compte le nombre d'opérations de dépenses (>0) pour un exercice donné
 * @param string $imputation
 * @param int $exercice
 * @param string|int $vu
 * @return int
 */
function filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, $vu) {
	$imputation = association_comptes_normaliser_filtre_imputation($imputation);
	$bornes = association_comptes_bornes_exercice(intval($exercice));
	$vu_clause = '';
	if (is_numeric($vu)) {
		$vu_clause = ' AND vu=' . intval($vu);
	} elseif (is_string($vu) && preg_match('#^>=\s*0$#', $vu)) {
		$vu_clause = ' AND vu >= 0';
	}
	$where = 'imputation LIKE ' . sql_quote($imputation) . $vu_clause;
	$where .= ' AND date >= ' . sql_quote($bornes['debut']) . ' AND date < ' . sql_quote($bornes['prochain_debut']);
	$where .= ' AND depense > 0';
	return sql_countsel('spip_asso_comptes', $where);
}

/**
 * Compte le nombre d'opérations de recettes en tenant compte d'un toggle "inclure_non_validees".
 * Si $inclure_non_validees est truthy, on compte sans filtrer sur vu (toutes opérations),
 * sinon on applique le filtre $vu fourni.
 */
function filtre_calcul_fonction_comptes_compter_recettes_selon_inclusion($imputation, $exercice, $vu, $inclure_non_validees) {
	if (intval($inclure_non_validees) === 1) {
		// compter toutes les recettes de l'exercice (vu non filtré)
		return filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, '');
	}
	return filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, $vu);
}

/**
 * Compte le nombre d'opérations de dépenses en tenant compte d'un toggle "inclure_non_validees".
 */
function filtre_calcul_fonction_comptes_compter_depenses_selon_inclusion($imputation, $exercice, $vu, $inclure_non_validees) {
	if (intval($inclure_non_validees) === 1) {
		return filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, '');
	}
	return filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, $vu);
}

/**
 * Différence entre le nombre d'opérations non validées (vu=0) et validées pour les recettes
 */
function filtre_calcul_fonction_comptes_diff_recettes($imputation, $exercice, $vu) {
	$valide = intval(filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, $vu));
	$total_unval = intval(filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, 0));
	$diff = $total_unval - $valide;
	return ($diff > 0) ? $diff : 0;
}

/**
 * Différence entre le nombre d'opérations non validées (vu=0) et validées pour les dépenses
 */
function filtre_calcul_fonction_comptes_diff_depenses($imputation, $exercice, $vu) {
	$valide = intval(filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, $vu));
	$total_unval = intval(filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, 0));
	$diff = $total_unval - $valide;
	return ($diff > 0) ? $diff : 0;
}

/**
 * Récupère l'année de départ d'un exercice à partir d'une date
 * @param string $date_sql (YYYY-MM-DD)
 * @return int
 */
function association_comptes_start_year($date_sql) {
	return association_comptes_start_year_from_date($date_sql);
}

/**
 * Filtre SPIP : retourne l'année de départ d'exercice d'une date
 */
function filtre_association_comptes_start_year($date_sql) {
	return association_comptes_start_year_from_date($date_sql);
}

/**
 * Filtre SPIP : retourne le libellé humain d'un exercice comptable.
 */
function filtre_association_comptes_libelle_exercice($start_year) {
	return association_comptes_libelle_exercice($start_year);
}

/**
 * Calcule les statistiques comptables des activités pour un exercice donné.
 * Exercice = année de départ (ex: 2025 pour 2025-2026 selon config exercice_comptable_debut).
 *
 * @param int|string $exercice Année de départ
 * @return array Tableau de stats:
 *   - total_operations
 *   - total_recettes
 *   - total_depenses
 *   - solde
 *   - montant_moyen
 *   - par_statut[statut] = ['count'=>n,'montant'=>m]
 *   - par_mode[mode] = ['count'=>n,'montant'=>m]
 */
function stats_compta_activites_exercice($exercice) {
	$stats = [
		'total_operations' => 0,
		'total_recettes' => 0.0,
		'total_depenses' => 0.0,
		// 'solde' will be computed after aggregation
		'montant_moyen' => 0.0,
		'par_statut' => [],
		'par_mode' => [],
	];
	$stats['solde'] = 0.0;
	$distribuees = pipeline('association_evenements_stats_compta', [
		'args' => ['exercice' => (int) $exercice],
		'data' => [],
	]);
	return is_array($distribuees) && $distribuees ? array_replace($stats, $distribuees) : $stats;
}
/**
 * Filtre SPIP pour accéder aux stats comptables des activités d'un exercice
 */
function filtre_stats_compta_activites_exercice($exercice) {
	return stats_compta_activites_exercice($exercice);
}

/**
 * Filtre SPIP pour obtenir les bornes d'un exercice au format JSON (utile pour debug)
 * Usage en squelette: [(#ENV{exercice}|association_comptes_bornes_exercice_json)]
 */
function filtre_association_comptes_bornes_exercice_json($exercice) {
	$ex_int = intval($exercice);
	if ($ex_int <= 0) {
		$ex_int = association_comptes_start_year_from_date(date('Y-m-d'));
	}
	$bornes = association_comptes_bornes_exercice($ex_int);
	return json_encode($bornes);
}

/**
 * Filtre SPIP pour obtenir/mettre en session la préférence inclure_non_validees.
 * Usage en squelette : [(#ENV{inclure_non_validees,''}|association_get_inclure_non_validees)]
 * - si la valeur ENV est fournie non vide, elle est stockée en session et retournée
 * - sinon on retourne la valeur en session (ou 0 par défaut)
 */
function filtre_association_get_inclure_non_validees($env_val = '') {
	include_spip('inc/session');

	if ($env_val !== null && $env_val !== '') {
		$v = intval($env_val) ? 1 : 0;
		session_set('association_compta_inclure_non_validees', $v);
		return $v;
	}

	return intval(session_get('association_compta_inclure_non_validees')) ? 1 : 0;
}

/**
 * Filtre SPIP pour retourner le vu effectif à utiliser dans les requêtes
 * en fonction du toggle inclure_non_validees.
 * Usage: [(#ENV{vu}|association_vu_selon_inclusion{#ENV{inclure_non_validees}})]
 */
function filtre_association_vu_selon_inclusion($vu, $inclure_non_validees) {
	if (intval($inclure_non_validees) === 1) {
		// explicit marker meaning "vu >= 0" (inclure validées et non-validées)
		return '>=0';
	}
	// si un vu numérique est fourni, le conserver (ex: 0 ou 1)
	if (is_numeric($vu) && $vu !== '') {
		return intval($vu);
	}
	// par défaut, compter les opérations validées
	return '1';
}
