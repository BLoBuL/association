<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Maintenance des abonnements et redirections, propriété de Communication.
 */

if (!function_exists('asso_table_col_for_type')) {
	function asso_table_col_for_type($type) {
		if (!preg_match('/^[a-z0-9_]+$/i', (string) $type)) {
			return [];
		}
		include_spip('base/objets');
		$table = table_objet_sql($type);
		$colonne = id_table_objet($type);
		$description = $table ? sql_showtable($table, true) : [];
		if (!$table || !$colonne || empty($description['field'][$colonne])) {
			return [];
		}
		return [$table, $colonne];
	}
}

function asso_supprimer_mailsubscribers_pour_auteurs(array $ids_auteurs, $dry_run = true) {
	$out = ['mailsubscribers_supprimes' => 0, 'mailsubscriptions_supprimees' => 0, 'mailshots_destinataires_supprimees' => 0, 'ignore' => false];

	// Supposer la présence des tables mailsubscribers / mailsubscriptions
	$has_ms = true;
	$has_msubs = true;
	$has_mailshots_dest = true; // on suppose la table et la colonne présentes
	if (!$has_ms && !$has_msubs) {
		$out['ignore'] = true;
		return $out;
	}

	// Récupération des emails des auteurs
	$emails = [];
	$res = sql_select('email', 'spip_auteurs', sql_in('id_auteur', $ids_auteurs) . ' AND email<>""');
	while ($row = sql_fetch($res)) {
		$emails[] = $row['email'];
	}
	$emails = array_values(array_unique(array_filter($emails)));
	if (!$emails) {
		return $out;
	}
	$emails_md5 = array_map('md5', $emails);

	if ($has_ms) {
		// Trouver les ids des abonnés à supprimer
		$ids_ms = [];
		$where_ms = '(' . sql_in('email', $emails) . ' OR ' . sql_in('email', $emails_md5) . ')';
		$res2 = sql_select('id_mailsubscriber', 'spip_mailsubscribers', $where_ms);
		while ($r = sql_fetch($res2)) {
			$ids_ms[] = intval($r['id_mailsubscriber']);
		}
		if ($ids_ms) {
			$out['mailsubscriptions_supprimees'] = $dry_run
				? sql_countsel('spip_mailsubscriptions', sql_in('id_mailsubscriber', $ids_ms))
				: sql_delete('spip_mailsubscriptions', sql_in('id_mailsubscriber', $ids_ms));
			$out['mailshots_destinataires_supprimees'] = $dry_run
				? sql_countsel('spip_mailshots_destinataires', sql_in('email', $emails))
				: sql_delete('spip_mailshots_destinataires', sql_in('email', $emails));
			$out['mailsubscribers_supprimes'] = $dry_run
				? sql_countsel('spip_mailsubscribers', sql_in('id_mailsubscriber', $ids_ms))
				: sql_delete('spip_mailsubscribers', sql_in('id_mailsubscriber', $ids_ms));
		}
	} elseif ($has_msubs) {
		// Si seulement spip_mailsubscriptions et si une colonne email existe
		// la colonne email existe
		$where_msubs = '(' . sql_in('email', $emails) . ' OR ' . sql_in('email', $emails_md5) . ')';
		$out['mailsubscriptions_supprimees'] = $dry_run
			? sql_countsel('spip_mailsubscriptions', $where_msubs)
			: sql_delete('spip_mailsubscriptions', $where_msubs);
	}
	return $out;
}

/**
 * Anonymiser les auteurs et leurs donnees metier via l'API RGPD du plugin.
 *
 * @param bool $dry_run
 * @return array
 */

function asso_supprimer_urls_obsoletes($dry_run = true, $lot = 1000) {
	$urls = [];
	$ignores = [];
	$types = [];
	$res_types = sql_select('DISTINCT type', 'spip_urls');
	while ($row = sql_fetch($res_types)) {
		$types[] = $row['type'];
	}
	if (!$types) {
		return ['supprimees' => 0, 'ignore' => true];
	}

	foreach ($types as $type) {
		// Si le type est malformé (ex: contient un point -> base.qualifiee), on nettoie ces urls directement
		if (preg_match('/\./', $type) || !preg_match('/^[a-z0-9_]+$/i', $type)) {
			$res_bad = sql_select('url', 'spip_urls', 'type=' . sql_quote($type), '', '', intval($lot));
			while ($r = sql_fetch($res_bad)) {
				$urls[] = $r['url'];
			}
			continue;
		}

		$cible = asso_table_col_for_type($type);
		if (!$cible) {
			$ignores[] = $type;
			continue;
		}
		[$table, $col] = $cible;

		// Tentative protégée : effectuer la requête LEFT JOIN ; si échec SQL (table/colonne absente), on retombe sur la solution de repli
		$join_query = 'u.url';
		$join_from = "spip_urls AS u LEFT JOIN $table AS o ON o.$col=u.id_objet AND u.type=" . sql_quote($type);
		$join_where = 'u.type=' . sql_quote($type) . " AND o.$col IS NULL";

		$res = @sql_select($join_query, $join_from, $join_where, '', '', intval($lot));
		if ($res === false) {
			$ignores[] = $type;
			association_log('cron', 'Nettoyage URLs ignoré pour type=' . $type . ' : table ou colonne cible introuvable.', 'erreur');
			continue;
		}

		while ($row = sql_fetch($res)) {
			$urls[] = $row['url'];
		}
	}

	if (!$urls) {
		return ['supprimees' => 0, 'types_ignores' => $ignores];
	}
	$urls = array_values(array_unique($urls));
	if (count($urls) > $lot) {
		$urls = array_slice($urls, 0, $lot);
	}
	$in = sql_in('url', $urls);
	$nb = $dry_run ? sql_countsel('spip_urls', $in) : sql_delete('spip_urls', $in);
	return ['supprimees' => intval($nb), 'urls' => $urls, 'types_ignores' => $ignores];
}

/**
 * Supprime les URLs d'un type spécifique dans la table `spip_urls`.
 *
 * Cette fonction permet de supprimer les URLs associées à un type donné.
 * Elle effectue une suppression par lot pour éviter de traiter un trop grand nombre d'entrées à la fois.
 *
 * Critères:
 * - Les URLs à supprimer sont filtrées par leur type (`type`).
 *
 * Paramètres:
 * @param string $type Le type des URLs à supprimer (ex: 'mailsubscriber').
 * @param bool $dry_run Si `true`, aucune suppression n'est effectuée, seulement un comptage.
 * @param int $lot Le nombre maximum d'entrées à traiter dans un lot.
 *
 * Retour:
 * @return array Un tableau contenant:
 * - `supprimees` (int): Le nombre d'URLs supprimées ou comptées.
 * - `ids` (array): Les identifiants des URLs à supprimer.
 */
function asso_supprimer_urls_par_type($type, $dry_run = true, $lot = 1000) {
	$ids = [];
	$ignores = [];
	// Si type invalide -> supprimer toutes les urls de ce type
	if (preg_match('/\./', $type) || !preg_match('/^[a-z0-9_]+$/i', $type)) {
		$res_all = sql_select('url', 'spip_urls', 'type=' . sql_quote($type), '', '', intval($lot));
		while ($r = sql_fetch($res_all)) {
			$ids[] = $r['url'];
		}
	} else {
		$cible = asso_table_col_for_type($type);
		if (!$cible) {
			return ['supprimees' => 0, 'types_ignores' => [$type]];
		}
		[$table, $col] = $cible;
		// Tentative protégée : JOIN ; si échec SQL (table/colonne absente), on récupère toutes les urls de ce type
		$join_query = 'u.url';
		$join_from = "spip_urls AS u LEFT JOIN $table AS o ON o.$col=u.id_objet AND u.type=" . sql_quote($type);
		$join_where = 'u.type=' . sql_quote($type) . " AND o.$col IS NULL";

		$res = @sql_select($join_query, $join_from, $join_where, '', '', intval($lot));
		if ($res === false) {
			$ignores[] = $type;
			association_log('cron', 'Nettoyage URLs ignoré pour type=' . $type . ' : table ou colonne cible introuvable.', 'erreur');
		} else {
			while ($row = sql_fetch($res)) {
				$ids[] = $row['url'];
			}
		}
	}

	if (!$ids) {
		return ['supprimees' => 0, 'types_ignores' => $ignores];
	}
	$in = sql_in('url', $ids);
	$nb = $dry_run ? sql_countsel('spip_urls', $in) : sql_delete('spip_urls', $in);
	return ['supprimees' => intval($nb), 'ids' => $ids, 'types_ignores' => $ignores];
}

/**
 * Supprimer les mailsubscribers orphelins (pas d'auteur associé via email).
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_mailsubscribers_orphelines($dry_run = true, $lot = 1000) {
	// on suppose la table spip_mailsubscribers présente

	$ids = [];
	$emails = [];
	// LEFT JOIN sur auteurs via email ; si aucun auteur correspondant alors orphelin
	$res = sql_select(
		'ms.id_mailsubscriber,ms.email',
		'spip_mailsubscribers AS ms LEFT JOIN spip_auteurs AS a ON a.email = ms.email',
		'a.id_auteur IS NULL',
		'',
		'',
		intval($lot)
	);
	while ($row = sql_fetch($res)) {
		$ids[] = intval($row['id_mailsubscriber']);
		if (!empty($row['email'])) {
			$emails[] = $row['email'];
		}
	}
	if (!$ids) {
		return ['supprimees' => 0, 'ids' => []];
	}

	$in = sql_in('id_mailsubscriber', $ids);
	$nb = $dry_run ? sql_countsel('spip_mailsubscribers', $in) : sql_delete('spip_mailsubscribers', $in);

	$where_destinataires = $emails ? sql_in('email', array_values(array_unique($emails))) : '0=1';
	$dest = $dry_run
		? sql_countsel('spip_mailshots_destinataires', $where_destinataires)
		: sql_delete('spip_mailshots_destinataires', $where_destinataires);

	return ['supprimees' => intval($nb), 'ids' => $ids, 'mailshots_destinataires_supprimees' => intval($dest)];
}
