<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/cotisations');
include_spip('inc/fonctions/generer_export_csv'); // fonctions du moteur de recherche
include_spip('inc/filtres');

/**
 * Lecture d'une configuration stockée (array/chaîne/sérialisée) pour la transformer en liste de champs
 */
function association_lire_config_liste($meta) {
	$cfg = $GLOBALS['association_metas'][$meta] ?? lire_config('association_metas/' . $meta);
	return association_normaliser_config_liste($cfg);
}

/**
 * Normalise la valeur d'une meta (sérialisée, tableau, chaîne CSV) en tableau de noms
 */
function association_normaliser_config_liste($cfg) {
	if (is_string($cfg) && $cfg !== '') {
		$trim = trim($cfg);
		// utiliser une classe de caractères plutôt qu'une alternation simple pour éviter l'avertissement
		if (preg_match('/^[asibOCdN]:/', $trim)) {
			$decoded = @unserialize($cfg);
			if ($decoded !== false || $cfg === 'b:0;') {
				$cfg = $decoded;
			}
		}
	}

	if (is_array($cfg)) {
		$fields = [];
		foreach ($cfg as $key => $value) {
			if (is_int($key)) {
				if ($value !== '' && $value !== null) {
					$fields[] = $value;
				}
				continue;
			}
			if ($value === '' || $value === null || $value === 'non') {
				continue;
			}
			$fields[] = $key;
		}
		return array_values(array_unique(array_filter($fields)));
	}

	if (is_string($cfg)) {
		$cfg = trim($cfg);
		if ($cfg === '') {
			return [];
		}
		if (strpos($cfg, ',') !== false) {
			return array_values(array_filter(array_map('trim', explode(',', $cfg))));
		}
		return [$cfg];
	}

	return [];
}

/**
 * Indexe les définitions des champs extras auteurs par leur nom
 */
function association_index_champs_extras() {
	static $index = null;
	if ($index !== null) {
		return $index;
	}

	$index = [];
	$champs_extras = lire_config('champs_extras_spip_auteurs');
	if (!is_array($champs_extras)) {
		return $index;
	}

	foreach ($champs_extras as $saisie) {
		if (isset($saisie['saisie']) && $saisie['saisie'] === 'fieldset' && !empty($saisie['saisies'])) {
			foreach ($saisie['saisies'] as $sub) {
				$nom = $sub['options']['nom'] ?? null;
				if ($nom) {
					$index[$nom] = $sub;
				}
			}
			continue;
		}

		$nom = $saisie['options']['nom'] ?? null;
		if ($nom) {
			$index[$nom] = $saisie;
		}
	}

	return $index;
}

/**
 * Prépare une définition exploitable (label + options) pour une liste de champs extras
 */
function association_definitions_champs_extras($fields) {
	$defs = [];
	if (!is_array($fields) || !count($fields)) {
		return $defs;
	}

	$index = association_index_champs_extras();
	foreach ($fields as $field) {
		if (!isset($index[$field])) {
			continue;
		}

		$def = $index[$field];
		$label = $def['options']['label'] ?? $field;
		$options = [];

		if (!empty($def['options']['data']) && is_array($def['options']['data'])) {
			$options = $def['options']['data'];
		} elseif (!empty($def['options']['datas']) && is_string($def['options']['datas'])) {
			$parsed = saisies_chaine2tableau($def['options']['datas']);
			if (is_array($parsed)) {
				$options = $parsed;
			}
		}

		// Aplatir systématiquement les datas/options pour éviter les optgroups
		if (!empty($options)) {
			$options = association_aplatir_datas_saisies($options);
		}

		$defs[$field] = [
			'nom' => $field,
			'label' => $label,
			'options' => $options,
		];
	}

	return $defs;
}

// Filtre par statut interne
function filtre_liste_statut_interne_adherents() {
	$statuts = [
		'ok' => _T('association_adhesions:statut_interne_ok'),
		'prospect' => _T('association_adhesions:statut_interne_prospect'),
		'echu' => _T('association_adhesions:statut_interne_echu'),
		'sorti' => _T('association_adhesions:statut_interne_sorti'),
	];
	return $statuts;
}

// Filtre par type d'adhérent (si la colonne existe dans spip_auteurs) - plus robuste
function filtre_liste_type_adherent() {
	// 1) Tenter de récupérer la définition depuis la configuration des champs extras
	$liste_champs_extra = lire_config('champs_extras_spip_auteurs');
	if (is_array($liste_champs_extra)) {
		foreach ($liste_champs_extra as $champs_extra) {
			if (isset($champs_extra['options']['nom']) && in_array($champs_extra['options']['nom'], ['radio_type_adherent', 'type_adherent'])) {
				$datas = $champs_extra['options']['datas'] ?? '';
				$opts = saisies_chaine2tableau($datas);
				if (is_array($opts) && count($opts) > 0) {
					return $opts;
				}
			}
		}
	}

	// 2) Sinon, si la colonne existe en base, récupérer les valeurs distinctes (supporter les deux noms possibles)
	$desc = sql_showtable('spip_auteurs', true);
	if ($desc) {
		$cols = [];
		if (!empty($desc['field']['type_adherent'])) {
			$cols[] = 'type_adherent';
		}
		if (!empty($desc['field']['radio_type_adherent'])) {
			$cols[] = 'radio_type_adherent';
		}

		foreach ($cols as $col) {
			$vals = [];
			$res = sql_allfetsel('DISTINCT ' . $col . ' AS val', 'spip_auteurs', "$col IS NOT NULL AND $col != ''");
			if ($res) {
				foreach ($res as $row) {
					$v = $row['val'];
					$vals[$v] = $v;
				}
			}
			if (count($vals) > 0) {
				return $vals;
			}
		}
	}

	// 3) Fallback : si une fonction helper existe (plugins), utiliser radio_type_adherent()
	if (function_exists('radio_type_adherent')) {
		$r = radio_type_adherent();
		if (is_array($r) && count($r) > 0) {
			return $r;
		}
	}

	// Par défaut, aucune option
	return [];
}

/**
 * Helper pour vérifier si la gestion des comptes secondaires est activée.
 * @return bool
 */
function est_actif_gestion_comptes_secondaires() {
	include_spip('inc/config');

	$config_nouvelle = $GLOBALS['association_metas']['config_compte_secondaire']
		?? lire_config('association_metas/config_compte_secondaire', 'non');

	return $config_nouvelle === 'oui';
}

/**
 * Vérifie si le filtre type_compte doit être affiché
 * @return bool
 */
function filtre_a_type_compte() {
	return est_actif_gestion_comptes_secondaires();
}

/**
 * Filtre SPIP pour vérifier si la gestion des comptes secondaires est active
 * Utilisable dans les templates comme : [(#VAL|gestion_comptes_secondaires_active|oui) ...]
 * @return bool
 */
function gestion_comptes_secondaires_active() {
	return est_actif_gestion_comptes_secondaires();
}

/**
 * Récupère les options du filtre type_compte
 * @return array
 */
function filtre_liste_type_compte() {
	if (est_actif_gestion_comptes_secondaires()) {
		return [
			'defaut' => _T('association_adhesions:filtre_type_compte_defaut'),
			'compte_principal' => _T('association_adhesions:filtre_type_compte_principal'),
			'compte_secondaire' => _T('association_adhesions:filtre_type_compte_secondaire'),
		];
	}
	return [];
}

// filtre_a_type_cotisation et filtre_liste_type_cotisation sont dans association_fonctions.php

// Indicateur pour le template : existe-t-il un filtre type_adherent à afficher ?
function filtre_has_type_adherent() {
	// Priorité : config champs_extras
	$liste_champs_extra = lire_config('champs_extras_spip_auteurs');
	if (is_array($liste_champs_extra)) {
		foreach ($liste_champs_extra as $champs_extra) {
			if (isset($champs_extra['options']['nom']) && in_array($champs_extra['options']['nom'], ['radio_type_adherent', 'type_adherent'])) {
				$datas = $champs_extra['options']['datas'] ?? '';
				$opts = saisies_chaine2tableau($datas);
				if (is_array($opts) && count($opts) > 0) {
					// Indiquer que les options viennent de la config
					return ['cfg'];
				}
			}
		}
	}

	// Vérifier la présence de colonnes en base
	$desc = sql_showtable('spip_auteurs', true);
	if ($desc) {
		if (!empty($desc['field']['type_adherent'])) {
			return ['type_adherent'];
		}
		if (!empty($desc['field']['radio_type_adherent'])) {
			return ['radio_type_adherent'];
		}
	}

	return [];
}

// filtre_liste_periodes_cotisations est dans association_fonctions.php

/**
 * Liste toutes les périodes pour la page adhérents (sans filtrage par cotisations)
 *
 * Contrairement à filtre_liste_periodes_cotisations() qui ne retourne que les périodes
 * avec des cotisations, cette fonction retourne TOUTES les périodes depuis la première
 * inscription, même sans cotisation. Utilisé pour filtrer les adhérents par période
 * de validité (un adhérent peut être "à jour" même sans nouvelle cotisation).
 *
 * @param int $limite Nombre maximum de périodes à retourner (0 = illimité)
 * @return array Tableau de périodes triées par date décroissante
 */
function filtre_liste_periodes_adherents($limite = 6) {
	$periodes = [];

	// Détecter contexte optionnel depuis la requête (toggle Adhérents/Entreprises)
	if (isset($_REQUEST['periode_contexte'])) {
		$contexte = association_adhesions_valeur_scalaire($_REQUEST['periode_contexte'], null);
	} else {
		$contexte = null;
	}

	// Charger metas en sécurité
	$m = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : [];

	// Si contexte entreprise, on utilise la meta entreprise_validation_date pour construire les périodes (jj/mm)
	if ($contexte === 'entreprise') {
		$entreprise_date_fixee = $m['entreprise_validation_date'] ?? '';
	}

	// Récupérer les dates scolaires/config adhérents (fallbacks)
	$type_periode = $m['validite'] ?? 'annuelle';
	$date_scolaire_suivante = $m['date_scolaire_suivante'] ?? '01/06';
	$date_scolaire_nouvelle = $m['date_scolaire_nouvelle'] ?? '30/09';

	// Parser helper
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

	// Trouver l'année de la première inscription
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
	$aujourdhui = date('Y-m-d');

	for ($annee = $annee_min_inscription; $annee <= $annee_courante; $annee++) {
		if ($contexte === 'entreprise') {
			// Entreprise : si date_fixee jj/mm définie -> période de cette date année -> année+1, sinon période calendaire
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
				// annuelle calendaire par défaut
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
			// Comportement historique adhérent (scolaire ou annuelle)
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
		$periode['encours'] = ($aujourdhui >= $periode['date_debut'] && $aujourdhui <= $periode['date_fin']);

		// Ne pas inclure une période qui commence strictement dans le futur
		if ($periode['date_debut'] > $aujourdhui) {
			continue;
		}

		$periodes[] = $periode;
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

/**
 * Prépare la liste des auteurs selon les critères passés en paramètre.
 *
 * Version refactorisée utilisant AdherentsSearchContext pour combiner
 * filtres GET (période, statut, type) et recherches POST (rapide/avancée)
 *
 * @param string $data Type de données à retourner : 'array_auteur', 'criteres_sql', 'context'
 * @return mixed
 */
function preparer_liste_adherents($data = '') {

	include_spip('inc/adherents_search_context');
	include_spip('formulaires/inc/adherents_recherche_avancee');

	// Créer le contexte unifié depuis GET/POST
	$context = AdherentsSearchContext::fromRequest();

	// Liste des périodes (toutes les périodes, pas seulement celles avec cotisations)
	$liste_periodes_adherents = filtre_liste_periodes_adherents(0);

	// Générer les critères SQL via le context (combine GET + POST)
	$criteres_sql = $context->toSQL($liste_periodes_adherents);

	// Log de debug
	association_log('adherents', '=== DEBUT preparer_liste_adherents (context) ===', 'debug');
	association_log('adherents', 'Filtres actifs: ' . var_export($context->hasActiveFilters(), true), 'debug');
	if ($context->periode) {
		association_log('adherents', 'Période: ' . $context->periode, 'debug');
	}
	if ($context->statut_interne) {
		association_log('adherents', 'Statut: ' . $context->statut_interne, 'debug');
	}
	if ($context->recherche_type) {
		association_log('adherents', 'Type recherche: ' . $context->recherche_type, 'debug');
	}
	association_log('adherents', 'Critères SQL combinés: ' . $criteres_sql, 'debug');

	// Récupérer les adhérents via generer_array_adherents
	$id_auteurs = generer_array_adherents($criteres_sql, $context->periode_data);

	// Log final
	$nb_adherents = is_array($id_auteurs) ? count($id_auteurs) : 0;
	association_log('adherents', '=== FIN preparer_liste_adherents ===', 'debug');
	association_log('adherents', 'Nombre adhérents retournés: ' . $nb_adherents, 'debug');
	if ($nb_adherents > 0 && is_array($id_auteurs)) {
		association_log('adherents', 'Premiers IDs: ' . implode(', ', array_slice(array_keys($id_auteurs), 0, 10)), 'debug');
	}

	// Retour en fonction du paramètre $data
	if ($data === 'array_auteur') {
		return is_array($id_auteurs) ? $id_auteurs : [];
	}
	if ($data === 'criteres_sql') {
		return $criteres_sql;
	}
	if ($data === 'context') {
		return $context; // Nouveau: permet de récupérer le contexte complet
	}

	return null;
}

/**
 * Récupère un résumé de la recherche active (session ou POST)
 * pour affichage dans le breadcrumb
 *
 * @return array Tableau avec 'type' et 'resume'
 */
function get_recherche_active_resume() {
	$resume = [];
	include_spip('inc/session');
	$recherche_rapide = session_get('adherents_recherche_rapide');
	$recherche_avancee = session_get('adherents_recherche_avancee');

	// Vérifier recherche rapide
	if (is_array($recherche_rapide) && !empty($recherche_rapide)) {
		$criteres = $recherche_rapide;
		$parts = [];

		if (!empty($criteres['_input_nom_famille'])) {
			$parts[] = _T('association_adhesions:filtres_actifs_nom') . ' ' . $criteres['_input_nom_famille'];
		}
		if (!empty($criteres['_input_prenom'])) {
			$parts[] = _T('association_adhesions:filtres_actifs_prenom') . ' ' . $criteres['_input_prenom'];
		}
		if (!empty($criteres['_input_email'])) {
			$parts[] = _T('association_adhesions:filtres_actifs_email') . ' ' . $criteres['_input_email'];
		}
		if (!empty($criteres['_input_mobile'])) {
			$parts[] = _T('association_adhesions:filtres_actifs_mobile') . ' ' . $criteres['_input_mobile'];
		}

		if (!empty($parts)) {
			$resume = [
				'type' => 'rapide',
				'resume' => implode(', ', $parts),
			];
		}
	}
	// Vérifier recherche avancée
	elseif (is_array($recherche_avancee) && !empty($recherche_avancee)) {
		$criteres = $recherche_avancee;
		$nb_criteres = count($criteres) - 2; // Enlever 'type_recherche' et 'operateur_recherche'

		$resume = [
			'type' => 'avancee',
			'resume' => _T('association_adhesions:recherche_active_avancee_nb', ['nb' => $nb_criteres]),
		];
	}

	return $resume;
}

// periode_defaut_libelle est dans association_fonctions.php

/**
 * Calcule les filtres effectifs (requête > session > défauts)
 * @return array
 */
function filtre_filtres_effectifs() {
	include_spip('inc/session');
	$filtres_session = session_get('adherents_filtres');
	$filtres_session = is_array($filtres_session) ? $filtres_session : [];

	// Si la requête demande l'effacement de la recherche rapide, supprimer en session
	if (!empty($_REQUEST['clear_search'])) {
		session_set('adherents_recherche_rapide', null);
		session_set('adherents_recherche_avancee', null);
	}

	// Si la requête demande l'effacement de filtres spécifiques, les supprimer en session
	if (!empty($_REQUEST['clear_filters'])) {
		$to_clear = is_array($_REQUEST['clear_filters']) ? $_REQUEST['clear_filters'] : explode(',', (string) $_REQUEST['clear_filters']);
		foreach ($to_clear as $k) {
			$k = trim($k);
			if ($k) {
				unset($filtres_session[$k]);
			}
		}
	}

	$eff = [];

	// Période
	if (isset($_REQUEST['periode'])) {
		$eff['periode'] = $_REQUEST['periode'] === 'tout' ? '' : $_REQUEST['periode'];
	} elseif (!empty($filtres_session['periode'])) {
		$eff['periode'] = $filtres_session['periode'] === 'tout' ? '' : $filtres_session['periode'];
	} else {
		$eff['periode'] = periode_defaut_libelle();
	}

	// Statut interne
	if (isset($_REQUEST['statut_interne'])) {
		$eff['statut_interne'] = $_REQUEST['statut_interne'] === 'tout' ? '' : $_REQUEST['statut_interne'];
	} elseif (!empty($filtres_session['statut_interne'])) {
		$eff['statut_interne'] = $filtres_session['statut_interne'] === 'tout' ? '' : $filtres_session['statut_interne'];
	} else {
		$eff['statut_interne'] = 'ok';
	}

	// Type adhérent (pas de défaut)
	if (isset($_REQUEST['type_adherent'])) {
		$eff['type_adherent'] = $_REQUEST['type_adherent'] === 'tout' ? '' : $_REQUEST['type_adherent'];
	} elseif (!empty($filtres_session['type_adherent'])) {
		$eff['type_adherent'] = $filtres_session['type_adherent'] === 'tout' ? '' : $filtres_session['type_adherent'];
	} else {
		$eff['type_adherent'] = '';
	}

	// Type de compte (si gestion active)
	$def_compte = est_actif_gestion_comptes_secondaires() ? 'compte_principal' : '';
	if (isset($_REQUEST['type_compte'])) {
		$eff['type_compte'] = $_REQUEST['type_compte']; // peut être 'defaut'
	} elseif (!empty($filtres_session['type_compte'])) {
		$eff['type_compte'] = $filtres_session['type_compte'];
	} else {
		$eff['type_compte'] = $def_compte;
	}

	// Type cotisation (basé sur les catégories, pas de défaut)
	if (isset($_REQUEST['type_cotisation'])) {
		$eff['type_cotisation'] = $_REQUEST['type_cotisation'] === 'tout' ? '' : $_REQUEST['type_cotisation'];
	} elseif (!empty($filtres_session['type_cotisation'])) {
		$eff['type_cotisation'] = $filtres_session['type_cotisation'] === 'tout' ? '' : $filtres_session['type_cotisation'];
	} else {
		$eff['type_cotisation'] = '';
	}

	// Filtres dynamiques issus de la configuration
	if ($dyn = liste_filtres_dynamiques_adherents()) {
		foreach ($dyn as $nom_filtre => $def) {
			$valeur = _request($nom_filtre);
			if ($valeur !== null) {
				$eff[$nom_filtre] = ($valeur === 'tout') ? '' : $valeur;
				$filtres_session[$nom_filtre] = $eff[$nom_filtre];
			} elseif (!empty($filtres_session[$nom_filtre])) {
				$eff[$nom_filtre] = $filtres_session[$nom_filtre];
			} else {
				$eff[$nom_filtre] = '';
			}
		}
	}

	session_set('adherents_filtres', $filtres_session ?: null);

	return $eff;
}

/**
 * Retourne la liste des champs extras sélectionnés en configuration
 * (clé = nom du champ)
 * @return array
 */
function filtre_liste_champs_filtres_configures() {
	return association_lire_config_liste('config_champs_filtres_adherents');
}

/**
 * Liste des champs extras sélectionnés pour affichage en colonnes
 */
function filtre_liste_champs_colonnes_configures() {
	return association_lire_config_liste('config_champs_colonnes_adherents');
}

/**
 * Construit la liste des filtres dynamiques (champs extras sélectionnés)
 * Chaque entrée fournit le nom du champ, son label et les options disponibles.
 */
function liste_filtres_dynamiques_adherents() {
	static $filtres = null;

	if ($filtres !== null) {
		return $filtres;
	}

	$fields = filtre_liste_champs_filtres_configures();
	$definitions = association_definitions_champs_extras($fields);
	$filtres = [];

	foreach ($definitions as $nom => $def) {
		if (empty($def['options']) || !is_array($def['options'])) {
			continue;
		}
		$filtres[$nom] = $def;
	}

	return $filtres;
}

/**
 * Colonnes dynamiques configurées pour le tableau adhérents
 */
function liste_colonnes_dynamiques_adherents() {
	static $colonnes = null;
	if ($colonnes !== null) {
		return $colonnes;
	}

	$fields = filtre_liste_champs_colonnes_configures();
	$colonnes = association_definitions_champs_extras($fields);
	return $colonnes;
}

/**
 * Cache les valeurs des colonnes dynamiques pour chaque adhérent
 */
function association_donnees_colonnes_adherent($id_auteur) {
	static $cache = [];
	if (isset($cache[$id_auteur])) {
		return $cache[$id_auteur];
	}

	$fields = filtre_liste_champs_colonnes_configures();
	if (!count($fields)) {
		$cache[$id_auteur] = [];
		return $cache[$id_auteur];
	}

	$select = array_unique(array_filter($fields));
	if (!in_array('id_auteur', $select, true)) {
		$select[] = 'id_auteur';
	}

	$select_sql = implode(', ', array_map('trim', $select));
	$row = sql_fetsel($select_sql, 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
	$cache[$id_auteur] = is_array($row) ? $row : [];

	return $cache[$id_auteur];
}

function association_hydrater_colonnes_dynamiques(array &$adherents) {
	$colonnes = filtre_liste_champs_colonnes_configures();
	if (!count($colonnes) || !count($adherents)) {
		return;
	}

	foreach ($adherents as $id => $row) {
		$id_auteur = $row['id_auteur'] ?? $id;
		$valeurs = association_donnees_colonnes_adherent($id_auteur);
		foreach ($colonnes as $champ) {
			$cle = 'col_' . $champ;
			if (!array_key_exists($cle, $adherents[$id])) {
				$adherents[$id][$cle] = $valeurs[$champ] ?? '';
			}
		}
	}
}

function valeur_colonne_dynamique_adherent($id_auteur, $champ) {
	if (!$champ) {
		return '';
	}
	$row = association_donnees_colonnes_adherent($id_auteur);
	return $row[$champ] ?? '';
}

function formatter_valeur_colonne_dynamique($valeur, $options = []) {
	if ($valeur === null || $valeur === '') {
		return '';
	}
	if (is_array($options) && isset($options[$valeur])) {
		return $options[$valeur];
	}
	return $valeur;
}

function association_champs_triables_fixes() {
	return [
		'id_auteur' => 'id_auteur',
		'tri_nom' => 'tri_nom',
		'inscription' => 'inscription',
		'validite' => 'validite',
		'statut_interne' => 'statut_interne',
	];
}

function association_defaut_tri_adherents() {
	$tri = [
		'nom_famille' => 1,
		'statut_interne' => -1,
		'validite' => -1,
		'inscription' => -1,
		'id_auteur' => 1,
	];

	foreach (liste_colonnes_dynamiques_adherents() as $nom => $def) {
		$tri['col_' . $nom] = 1;
	}

	return $tri;
}

function filtre_liste_noms_filtres_dynamiques_dist($base = '') {
	$noms = [];

	if (is_string($base) && $base !== '') {
		$noms = array_filter(array_map('trim', explode(',', $base)));
	}

	foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {
		$noms[] = $nom;
	}

	$noms = array_unique(array_filter($noms));

	return implode(',', $noms);
}

function filtre_noms_filtres_dynamiques_dist($base = '') {
	return filtre_liste_noms_filtres_dynamiques_dist($base);
}

function filtre_filtres_dynamiques_actifs_dist($filtres_effectifs = []) {
	if (!is_array($filtres_effectifs) || !count($filtres_effectifs)) {
		return false;
	}

	foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {
		if (!empty($filtres_effectifs[$nom])) {
			return true;
		}
	}

	return false;
}

function filtre_url_supprimer_filtres_dynamiques_dist($url) {
	$dyn = liste_filtres_dynamiques_adherents();
	if (!count($dyn)) {
		return $url;
	}

	foreach (array_keys($dyn) as $nom) {
		$url = parametre_url($url, $nom, '');
	}

	return $url;
}

function filtre_liste_filtres_reset_adherents_dist() {
	$filtres = ['periode', 'statut_interne', 'type_adherent', 'type_compte', 'type_cotisation'];
	foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {
		$filtres[] = $nom;
	}
	return implode(',', array_unique($filtres));
}

function filtre_association_defaut_tri_adherents_dist($val = null) {
	return association_defaut_tri_adherents();
}

function filtre_trier_colonne_dynamique_dist($adherents) {
	if (!is_array($adherents) || !count($adherents)) {
		return $adherents;
	}

	association_hydrater_colonnes_dynamiques($adherents);

	$tri = preg_replace('/[^a-z0-9_]/i', '', (string) _request('tri_dyn'));
	$sens = strtolower((string) _request('tri_dyn_sens')) === 'desc' ? -1 : 1;

	$colonnes_triables = association_champs_triables_fixes();
	foreach (filtre_liste_champs_colonnes_configures() as $champ) {
		$colonnes_triables['col_' . $champ] = 'col_' . $champ;
	}

	$cle_tri = ($tri !== '' && isset($colonnes_triables[$tri])) ? $colonnes_triables[$tri] : '';

	if ($cle_tri) {
		uasort($adherents, function ($a, $b) use ($cle_tri, $sens) {
			$va = $a[$cle_tri] ?? '';
			$vb = $b[$cle_tri] ?? '';
			if ($va === $vb) {
				return ($a['id_auteur'] ?? 0) <=> ($b['id_auteur'] ?? 0);
			}
			$cmp = strnatcasecmp((string) $va, (string) $vb);
			return $sens * $cmp;
		});
	}

	$i = 0;
	foreach ($adherents as $id => $row) {
		$adherents[$id]['ordre_tri'] = $cle_tri ? $i++ : 0;
	}

	return $adherents;
}

function association_liste_champs_recherche_avancee_actifs() {
	include_spip('inc/session');
	$recherche_avancee = session_get('adherents_recherche_avancee');
	$params = [];
	if (is_array($recherche_avancee) && !empty($recherche_avancee)) {
		foreach ($recherche_avancee as $champ => $valeur) {
			if (is_array($valeur)) {
				continue;
			}
			if (in_array($champ, ['recherche', 'action', 'formulaire_action', 'formulaire_action_args', 'formulaire_action_sign'])) {
				continue;
			}
			$params[] = $champ;
		}
	}
	return array_values(array_unique($params));
}

function association_recherche_avancee_reset_demandee() {
	$champs = association_liste_champs_recherche_avancee_actifs();
	if (!count($champs)) {
		return false;
	}
	$present = false;
	foreach ($champs as $champ) {
		$valeur = _request($champ);
		if ($valeur !== null) {
			$present = true;
			if ($valeur !== '') {
				return false;
			}
		}
	}
	return $present;
}

function filtre_url_reinit_recherche_avancee_dist($url) {
	foreach (association_liste_champs_recherche_avancee_actifs() as $champ) {
		$url = parametre_url($url, $champ, '');
	}
	return $url;
}

function filtre_get_recherche_rapide_active_dist() {
	$champs = ['_input_nom_famille', '_input_prenom', '_input_email', '_input_mobile'];
	$actifs = [];

	foreach ($champs as $champ) {
		$val = _request($champ);
		if ($val !== null && $val !== '') {
			$actifs[$champ] = $val;
		}
	}

	if (!count($actifs)) {
		include_spip('inc/session');
		$recherche_rapide = session_get('adherents_recherche_rapide');
		if (is_array($recherche_rapide) && !empty($recherche_rapide)) {
			foreach ($champs as $champ) {
				if (!empty($recherche_rapide[$champ])) {
					$actifs[$champ] = $recherche_rapide[$champ];
				}
			}
		}
	}

	return $actifs;
}

/**
 * Aplatit un tableau de 'datas' issu des Saisies en une liste plate clé=>label.
 * Utilise la fonction du plugin Saisies `saisies_aplatir_tableau` ; si elle n'est pas
 * définie, charge explicitement le fichier du plugin `inc/saisies_data`.
 * Aucun fallback local n'est effectué (conforme à la demande).
 *
 * @param array|string $datas
 * @return array
 */
function association_aplatir_datas_saisies($datas) {
	include_spip('inc/saisies_data');
	return saisies_aplatir_tableau($datas, true);
}
