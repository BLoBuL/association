<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_mot_de_passe_traiter($id_auteur = null, $jeton = null) {
	// $res = array('message_ok'=>'');
	$res['redirect'] = generer_url_public('fiche_adherent', '');
	// refuser_traiter_formulaire_ajax(); // puisqu'on va loger l'auteur a la volee (c'est bonus)
	// compatibilite anciens appels du formulaire
	if ($jeton === null) {
		$jeton = _request('p');
	}
	$row = retrouve_auteur($id_auteur, $jeton);
	if ($row
	&& ($id_auteur = $row['id_auteur'])
	&& ($oubli = _request('oubli'))) {
		include_spip('action/editer_auteur');
		include_spip('action/inscrire_auteur');
		if ($err = auteur_modifier($id_auteur, ['pass' => $oubli])) {
			$res = ['message_erreur' => $err];
		} else {
			auteur_effacer_jeton($id_auteur);
			$login = $row['login'];
			// $res['message_ok'] = "<b>" . _T('pass_nouveau_enregistre') . "</b>".
			// "<br />" . _T('pass_rappel_login', array('login' => $login));
			include_spip('inc/auth');
			$row = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
			auth_loger($row);
			$res['redirect'] = generer_url_public('fiche_adherent', '');
		}
	}
	return $res;
}

function filtre_a_type_cotisation() {
	$desc = sql_showtable('spip_auteurs', true);
	if ($desc) {
		if (!empty($desc['field']['type_adherent']) || !empty($desc['field']['radio_type_adherent'])) {
			return false;
		}
	}
	return true;
}

function filtre_liste_type_cotisation() {
	if (!filtre_a_type_cotisation()) {
		return [];
	}

	$types = [];
	$result = sql_allfetsel(
		'DISTINCT type_adherent',
		'spip_asso_categories_adherents',
		"type_adherent IS NOT NULL AND type_adherent != '' AND statut='ok'"
	);

	if ($result) {
		foreach ($result as $row) {
			$type = $row['type_adherent'];
			$types[$type] = $type;
		}
	}

	return $types;
}

function filtre_ids_categories_par_type($type) {
	if (!$type) {
		return '0';
	}

	$result = sql_allfetsel(
		'id_categorie',
		'spip_asso_categories_adherents',
		'type_adherent = ' . sql_quote($type) . " AND statut='ok'"
	);

	if (!$result || count($result) == 0) {
		return '0'; // Aucune catégorie = forcer 0 résultat
	}

	$ids = [];
	foreach ($result as $row) {
		$ids[] = intval($row['id_categorie']);
	}

	return implode(',', $ids);
}

function filtre_liste_periodes_cotisations($limite = 0, $avec_stats = false, $type_contexte = null) {
	$periodes = [];

	// Déterminer le contexte : 'entreprise' ou null/adherent
	$context = $type_contexte ? $type_contexte : 'adherent';

	// Charger metas en sécurité
	$m = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : [];

	// Récupérer la configuration selon le contexte
	if ($context === 'entreprise') {
		// Pour les entreprises on peut avoir une date_fixee ou une logique annuelle
		$entreprise_mode = $m['entreprise_validation_mode'] ?? 'annuelle';
		$entreprise_date_fixee = $m['entreprise_validation_date'] ?? '';
		// Si date fixee on l'utilisera comme bornes (jj/mm)
	} else {
		// Conserver le comportement historique pour les adhérents
		$type_periode = $m['validite'] ?? 'annuelle';
		$date_scolaire_suivante = $m['date_scolaire_suivante'] ?? '01/06';
		$date_scolaire_nouvelle = $m['date_scolaire_nouvelle'] ?? '30/09';
	}

	// Parser les dates jj/mm depuis la config (réutilisable)
	$parse_jour_mois = function ($str, $fallback_j, $fallback_m) {
		if (!$str || strpos($str, '/') === false) {
			return [$fallback_j, $fallback_m];
		}
		$parts = explode('/', $str);
		if (count($parts) < 2) {
			return [$fallback_j, $fallback_m];
		}
		$j = intval($parts[0]);
		$m = intval($parts[1]);
		if ($j < 1 || $j > 31) {
			$j = $fallback_j;
		}
		if ($m < 1 || $m > 12) {
			$m = $fallback_m;
		}
		return [$j, $m];
	};

	// Trouver l'année de la première inscription (pour bornes inférieures)
	$query_auteurs_inscription = sql_allfetsel(
		'MIN(inscription) as date_min',
		'spip_auteurs',
		"inscription IS NOT NULL AND inscription != '' AND inscription != '0000-00-00'"
	);

	if ($query_auteurs_inscription && isset($query_auteurs_inscription[0]['date_min']) && $query_auteurs_inscription[0]['date_min'] != '0000-00-00') {
		$annee_min_inscription = intval(date('Y', strtotime($query_auteurs_inscription[0]['date_min'])));
	} else {
		$annee_min_inscription = intval(date('Y')) - 3;
	}

	$annee_courante = intval(date('Y'));

	// Générer les périodes selon le contexte
	for ($annee = $annee_min_inscription; $annee <= $annee_courante; $annee++) {
		if ($context === 'entreprise') {
			// Entreprise : si mode date_fixee -> période = date_fixee(annee) .. date_fixee(annee+1)
			if (!empty($entreprise_date_fixee) && preg_match('/^(\d{1,2})\/(\d{1,2})$/', $entreprise_date_fixee, $mdate)) {
				$j_fix = intval($mdate[1]);
				$m_fix = intval($mdate[2]);
				$date_debut = date('Y-m-d', mktime(0, 0, 0, $m_fix, $j_fix, $annee));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, $m_fix, $j_fix, $annee + 1));
				$libelle = $annee . '/' . ($annee + 1);
				$periode = [
					'annee_debut' => intval($annee),
					'annee_fin' => intval($annee + 1),
					'date_debut' => $date_debut,
					'date_fin' => $date_fin,
					'libelle' => $libelle,
				];
			} else {
				// Par défaut, période annuelle calendaire
				$date_debut = date('Y-m-d', mktime(0, 0, 0, 1, 1, $annee));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, 12, 31, $annee));
				$libelle = strval($annee);
				$periode = [
					'annee_debut' => intval($annee),
					'annee_fin' => intval($annee),
					'date_debut' => $date_debut,
					'date_fin' => $date_fin,
					'libelle' => $libelle,
				];
			}
		} else {
			// Comportement historique pour adhérents (scolaire ou annuel)
			if ($type_periode === 'scolaire') {
				[$j_suivante, $m_suivante] = $parse_jour_mois($date_scolaire_suivante, 1, 6);
				[$j_nouvelle, $m_nouvelle] = $parse_jour_mois($date_scolaire_nouvelle, 30, 9);
				$date_debut = date('Y-m-d', mktime(0, 0, 0, intval($m_suivante), intval($j_suivante), intval($annee)));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, intval($m_nouvelle), intval($j_nouvelle), intval($annee) + 1));
				$libelle = $annee . '/' . ($annee + 1);
				$periode = [
					'annee_debut' => intval($annee),
					'annee_fin' => intval($annee + 1),
					'date_debut' => $date_debut,
					'date_fin' => $date_fin,
					'libelle' => $libelle,
				];
			} else {
				// annuelle
				$date_debut = date('Y-m-d', mktime(0, 0, 0, 1, 1, $annee));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, 12, 31, $annee));
				$libelle = strval($annee);
				$periode = [
					'annee_debut' => intval($annee),
					'annee_fin' => intval($annee),
					'date_debut' => $date_debut,
					'date_fin' => $date_fin,
					'libelle' => $libelle,
				];
			}
		}

		// Marquer si période en cours
		$aujourdhui = date('Y-m-d');
		$periode['encours'] = ($aujourdhui >= $periode['date_debut'] && $aujourdhui <= $periode['date_fin']);

		// Compter les cotisations métier dans cette période.
		$nb_cotisations = sql_countsel(
			'spip_asso_cotisations',
			'date_creation >= ' . sql_quote($periode['date_debut']) . ' AND date_creation <= ' . sql_quote($periode['date_fin'])
		);
		$periode['nb_cotisations'] = intval($nb_cotisations);

		// N'ajouter que les périodes avec au moins une cotisation
		if ($nb_cotisations > 0) {
			$periodes[] = $periode;
		}
	}

	// Trier par date décroissante (plus récentes d'abord)
	usort($periodes, function ($a, $b) {
		return strcmp($b['date_debut'], $a['date_debut']);
	});

	// Limiter le nombre si demandé
	if ($limite > 0 && count($periodes) > $limite) {
		$periodes = array_slice($periodes, 0, $limite);
	}

	return $periodes;
}

function periode_defaut_libelle() {
	$periodes = filtre_liste_periodes_cotisations(0, false);
	if (is_array($periodes)) {
		foreach ($periodes as $p) {
			if (!empty($p['encours'])) {
				return $p['libelle'] ?? '';
			}
		}
	}
	return '';
}

function trouver_periode_par_libelle($libelle) {
	$periodes = filtre_liste_periodes_cotisations(0, false);
	if (!is_array($periodes)) {
		return null;
	}

	// Si "tout" ou vide, retourner la période encours ou la première
	if (!$libelle || $libelle === 'tout') {
		foreach ($periodes as $p) {
			if (!empty($p['encours'])) {
				return $p;
			}
		}
		return reset($periodes);
	}

	// Chercher par libellé exact
	foreach ($periodes as $p) {
		if (isset($p['libelle']) && (string) $p['libelle'] === (string) $libelle) {
			return $p;
		}
	}

	return null;
}

function periode_date_debut($libelle) {
	if (!$libelle || $libelle === 'tout') {
		return '0000-00-00';
	}
	$p = trouver_periode_par_libelle($libelle);
	return $p && !empty($p['date_debut']) ? $p['date_debut'] : '0000-00-00';
}

function periode_date_fin($libelle) {
	if (!$libelle || $libelle === 'tout') {
		return '9999-12-31';
	}
	$p = trouver_periode_par_libelle($libelle);
	return $p && !empty($p['date_fin']) ? $p['date_fin'] : '9999-12-31';
}

function filtre_filtres_effectifs_cotisations() {
	include_spip('inc/session');
	$filtres_session = session_get('cotisations_filtres');
	$filtres_session = is_array($filtres_session) ? $filtres_session : [];

	$eff = [];

	// Helper de persistance : URL > Session > null
	$persist = function ($key) use (&$filtres_session) {
		if (array_key_exists($key, $_REQUEST)) {
			$filtres_session[$key] = $_REQUEST[$key];
			return $_REQUEST[$key];
		}
		if (!empty($filtres_session[$key])) {
			return $filtres_session[$key];
		}
		return null;
	};

	// Période : défaut = encours, 'tout' = vide
	$periode = $persist('periode');
	if ($periode === 'tout') {
		$periode = '';
	} elseif (!$periode) {
		$periode = periode_defaut_libelle();
	}
	$eff['periode'] = $periode;

	// Statut cotisation : vide par défaut
	$statut = $persist('statut_cotisation');
	$eff['statut_cotisation'] = ($statut === 'tout' || !$statut) ? '' : $statut;

	// Reinscription/inscription : vide par défaut
	$reins = $persist('reinscription');
	$eff['reinscription'] = ($reins === 'tout' || !$reins) ? '' : $reins;

	// Catégorie : vide par défaut
	$cat = $persist('id_categorie');
	$eff['id_categorie'] = ($cat === 'tout' || !$cat) ? '' : $cat;

	// Type cotisation : vide par défaut
	$type_cot = $persist('type_cotisation');
	$eff['type_cotisation'] = ($type_cot === 'tout' || !$type_cot) ? '' : $type_cot;

	session_set('cotisations_filtres', $filtres_session);

	return $eff;
}

function association_adhesions_valeur_scalaire($valeur, $defaut = '') {
	if (is_array($valeur)) {
		foreach ($valeur as $element) {
			if ($element === null || is_array($element)) {
				continue;
			}
			$scalaire = trim((string) $element);
			if ($scalaire !== '') {
				return $scalaire;
			}
		}
		$valeurs = [];
		array_walk_recursive($valeur, static function ($element) use (&$valeurs) {
			$valeurs[] = (string) $element;
		});
		return $valeurs ? implode(',', $valeurs) : $defaut;
	}
	return ($valeur === null || $valeur === '') ? $defaut : (string) $valeur;
}

function liste_periodes_cotisations($val = null) {
	// Priorité : paramètre explicite 'periode_contexte' > paramètre 'type_cotisation' > défaut 'adherent'
	$contexte = null;
	if (isset($_REQUEST['periode_contexte']) && $_REQUEST['periode_contexte']) {
		// Normaliser la valeur (éviter tableau)
		$contexte = association_adhesions_valeur_scalaire($_REQUEST['periode_contexte'], null);
	} elseif (isset($_REQUEST['type_cotisation']) && $_REQUEST['type_cotisation']) {
		$type_cot = null;
		$type_cot = association_adhesions_valeur_scalaire($_REQUEST['type_cotisation'], null);
		// Si type_cotisation est littéralement 'entreprise' on bascule
		if ($type_cot === 'entreprise') {
			$contexte = 'entreprise';
		}
	}

	return filtre_liste_periodes_cotisations(0, false, $contexte);
}

function filtre_association_contexte_adhesion($id_auteur, $date_reference = '') {
	include_spip('inc/cotisations');
	return association_contexte_adhesion(intval($id_auteur), $date_reference ?: null);
}
