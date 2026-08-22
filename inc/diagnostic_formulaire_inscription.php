<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Construit le diagnostic commun des formulaires d'inscription evenement.
 *
 * Le diagnostic est volontairement independant du rendu FO ou BO. Les wrappers
 * peuvent completer le contexte avec les champs generes et leurs conditions
 * d'affichage sans dupliquer les donnees metier communes.
 *
 * @param int $id_evenement
 * @param array $contexte
 * @param array|null $sources Sources injectables pour les tests ou un appelant
 *   ayant deja calcule les donnees metier.
 * @return array
 */
function association_diagnostic_formulaire_inscription($id_evenement, $contexte = array(), $sources = null) {
	$id_evenement = intval($id_evenement);
	$contexte = association_diagnostic_inscription_normaliser_contexte($contexte);

	if ($sources === null) {
		$sources = association_diagnostic_inscription_collecter_sources($id_evenement);
	}

	$sources += array(
		'ouverture_inscription' => array(),
		'eligibilite_inscription' => array(),
		'affichage_dans_activites' => array(),
		'gestions_places' => array(),
		'configuration' => array(),
		'regles_effectives' => array(),
	);

	$diagnostic = array(
		'format' => 'IE1',
		'id_evenement' => $id_evenement,
		'contexte' => $contexte,
		'scenario' => association_diagnostic_inscription_scenario($contexte, $sources),
		'ouverture_inscription' => $sources['ouverture_inscription'],
		'eligibilite_inscription' => $sources['eligibilite_inscription'],
		'affichage_dans_activites' => $sources['affichage_dans_activites'],
		'gestions_places' => $sources['gestions_places'],
		'configuration' => $sources['configuration'],
		'regles_effectives' => $sources['regles_effectives'],
	);

	$diagnostic = association_diagnostic_inscription_nettoyer($diagnostic);
	$diagnostic['code'] = association_diagnostic_inscription_code($diagnostic['scenario']);
	$diagnostic['reference'] = strtoupper(substr(hash('sha256', association_diagnostic_inscription_json_canonique($diagnostic)), 0, 8));

	return $diagnostic;
}

/**
 * Calcule les regles effectivement utilisees par la validation.
 *
 * Le resultat conserve les valeurs de l'evenement et leurs valeurs effectives
 * afin de rendre visible toute surcharge liee au profil de session.
 */
function association_inscription_regles_effectives($mode, $affichage, $gestions_places, $session = array()) {
	$affichage = is_array($affichage) ? $affichage : array();
	$gestions_places = is_array($gestions_places) ? $gestions_places : array();
	$session = is_array($session) ? $session : array();
	$profil = strtolower((string) ($session['radio_type_adherent'] ?? ''));
	$est_mode_public = (strpos((string) $mode, 'public') !== false);
	$accompagnants_configures = association_diagnostic_inscription_booleen($affichage['accompagnants'] ?? null);
	$limite_configuree = max(0, intval($gestions_places['places_limites'] ?? 0));
	$profil_session_applique = false;

	if ($est_mode_public && $profil === 'individuel') {
		$affichage['accompagnants'] = false;
		$gestions_places['places_limites'] = 1;
		$profil_session_applique = true;
	} elseif ($est_mode_public && $profil === 'couple') {
		$gestions_places['places_limites'] = 2;
		$profil_session_applique = true;
	}

	return array(
		'affichage' => $affichage,
		'gestions_places' => $gestions_places,
		'diagnostic' => array(
			'profil_session' => $profil !== '' ? $profil : 'inconnu',
			'profil_session_applique' => $profil_session_applique,
			'accompagnants_configures' => $accompagnants_configures,
			'accompagnants_effectifs' => association_diagnostic_inscription_booleen($affichage['accompagnants'] ?? null),
			'limite_configuree' => $limite_configuree,
			'limite_effective' => max(0, intval($gestions_places['places_limites'] ?? 0)),
			'places_disponibles' => max(0, intval($gestions_places['places_disponibles'] ?? 0)),
			'places_attente_disponibles' => max(0, intval($gestions_places['places_en_attentes_disponible'] ?? 0)),
		),
	);
}

/**
 * Prepare le contexte diagnostic a partir du mode et des saisies CVT reels.
 */
function association_diagnostic_inscription_contexte_formulaire($mode, $id_activite, $saisies, $famille_active = null) {
	$mode = (string) $mode;
	$analyse_saisies = association_diagnostic_inscription_analyser_saisies($saisies);
	return array(
		'interface' => in_array($mode, array('prive', 'multi_prive'), true) ? 'bo' : 'fo',
		'parcours' => (strpos($mode, 'multi') !== false) ? 'multi' : 'simple',
		'operation' => intval($id_activite) > 0 ? 'modification' : 'creation',
		'famille_active' => association_diagnostic_inscription_booleen($famille_active),
		'champs_generes' => $analyse_saisies['champs_generes'],
		'conditions_affichage' => $analyse_saisies['conditions_affichage'],
	);
}

/**
 * Extrait uniquement la structure des saisies, jamais leurs valeurs ou defauts.
 */
function association_diagnostic_inscription_analyser_saisies($saisies) {
	$resultat = array('champs_generes' => array(), 'conditions_affichage' => array());
	if (!is_array($saisies)) {
		return $resultat;
	}
	foreach ($saisies as $saisie) {
		if (!is_array($saisie)) {
			continue;
		}
		$options = (isset($saisie['options']) && is_array($saisie['options'])) ? $saisie['options'] : array();
		$nom = isset($options['nom']) ? (string) $options['nom'] : '';
		if ($nom !== '') {
			$resultat['champs_generes'][$nom] = array(
				'saisie' => (string) ($saisie['saisie'] ?? ''),
				'obligatoire' => association_diagnostic_inscription_booleen($options['obligatoire'] ?? false),
			);
			if (!empty($options['afficher_si'])) {
				$resultat['conditions_affichage'][$nom] = (string) $options['afficher_si'];
			}
		}
		if (!empty($saisie['saisies'])) {
			$enfants = association_diagnostic_inscription_analyser_saisies($saisie['saisies']);
			$resultat['champs_generes'] = array_merge($resultat['champs_generes'], $enfants['champs_generes']);
			$resultat['conditions_affichage'] = array_merge($resultat['conditions_affichage'], $enfants['conditions_affichage']);
		}
	}
	ksort($resultat['champs_generes'], SORT_STRING);
	ksort($resultat['conditions_affichage'], SORT_STRING);
	return $resultat;
}

function association_diagnostic_inscription_webmestre() {
	foreach (array('visiteur_session', 'auteur_session') as $globale) {
		if (($GLOBALS[$globale]['webmestre'] ?? '') === 'oui') {
			return true;
		}
	}
	return false;
}

/**
 * Collecte les quatre sources metier communes aux parcours FO et BO.
 */
function association_diagnostic_inscription_collecter_sources($id_evenement) {
	include_spip('inc/fonctions/affichage_dans_activites');
	include_spip('inc/fonctions/gestion_places');
	include_spip('inc/fonctions/ouverture_inscription_evenement');
	include_spip('inc/fonctions/eligibilite_inscription_evenement');

	$configuration = array();
	$cles_configuration = array(
		'meta_cfg_event_config_accompagnants',
		'meta_cfg_event_form_info_supp',
		'meta_cfg_event_accompagnants',
		'meta_cfg_event_limite_nb_accompagnants',
		'meta_cfg_event_type_inscrits_evenement',
		'meta_cfg_event_type_quota',
		'meta_cfg_event_invites',
		'meta_cfg_event_quota_inscription_adherent',
		'nb_inscription_quota_adherent',
		'nb_jour_quota_adherent',
	);
	$metas = (isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']))
		? $GLOBALS['association_metas']
		: array();
	foreach ($cles_configuration as $cle) {
		if (array_key_exists($cle, $metas)) {
			$configuration[$cle] = $metas[$cle];
		}
	}

	$affichage = affichage_dans_activites($id_evenement);
	$gestions_places = gestions_places($id_evenement);
	$mode = (string) (_request('ie_mode') ?? 'public');
	$regles = association_inscription_regles_effectives($mode, $affichage, $gestions_places, $GLOBALS['visiteur_session'] ?? array());

	return array(
		'ouverture_inscription' => ouverture_inscription_evenement($id_evenement),
		'eligibilite_inscription' => eligibilite_inscription_evenement($id_evenement),
		'affichage_dans_activites' => $affichage,
		'gestions_places' => $gestions_places,
		'configuration' => $configuration,
		'regles_effectives' => $regles['diagnostic'],
	);
}

function association_diagnostic_inscription_normaliser_contexte($contexte) {
	$contexte = is_array($contexte) ? $contexte : array();
	$interface = strtolower((string) ($contexte['interface'] ?? 'fo'));
	$parcours = strtolower((string) ($contexte['parcours'] ?? 'simple'));
	$operation = strtolower((string) ($contexte['operation'] ?? 'creation'));

	return array(
		'interface' => in_array($interface, array('fo', 'bo'), true) ? $interface : 'inconnu',
		'parcours' => in_array($parcours, array('simple', 'multi'), true) ? $parcours : 'inconnu',
		'operation' => in_array($operation, array('creation', 'modification'), true) ? $operation : 'inconnue',
		'famille_active' => association_diagnostic_inscription_booleen($contexte['famille_active'] ?? null),
		'champs_generes' => is_array($contexte['champs_generes'] ?? null) ? $contexte['champs_generes'] : array(),
		'conditions_affichage' => is_array($contexte['conditions_affichage'] ?? null) ? $contexte['conditions_affichage'] : array(),
	);
}

function association_diagnostic_inscription_scenario($contexte, $sources) {
	$affichage = is_array($sources['affichage_dans_activites']) ? $sources['affichage_dans_activites'] : array();
	$ouverture = is_array($sources['ouverture_inscription']) ? $sources['ouverture_inscription'] : array();
	$eligibilite = is_array($sources['eligibilite_inscription']) ? $sources['eligibilite_inscription'] : array();
	$configuration = is_array($sources['configuration']) ? $sources['configuration'] : array();
	$regles = is_array($sources['regles_effectives']) ? $sources['regles_effectives'] : array();
	$tarifs = (isset($affichage['montant']) && is_array($affichage['montant'])) ? $affichage['montant'] : array();
	// La configuration historique applique le quota souple quand la meta manque.
	$type_quota = strtolower((string) ($configuration['meta_cfg_event_type_quota'] ?? 'souple'));

	return array(
		'interface' => $contexte['interface'],
		'operation' => $contexte['operation'],
		'inscription_ouverte' => association_diagnostic_inscription_booleen($ouverture['inscription_ouverte'] ?? null),
		'eligible' => association_diagnostic_inscription_booleen($eligibilite['eligible_inscription'] ?? null),
		'famille' => $contexte['famille_active'],
		'multi_etape' => ($contexte['parcours'] === 'multi'),
		'payant' => association_diagnostic_inscription_booleen($affichage['payant'] ?? null),
		'accompagnants' => association_diagnostic_inscription_booleen($affichage['accompagnants'] ?? null),
		'type_inscrits' => strtolower((string) ($affichage['type_inscrits_evenement'] ?? '')),
		'quota' => in_array($type_quota, array('strict', 'souple'), true) ? $type_quota : 'inconnu',
		'places' => association_diagnostic_inscription_booleen($affichage['places'] ?? null),
		'attentes' => association_diagnostic_inscription_booleen($affichage['attentes'] ?? null),
		'validation' => association_diagnostic_inscription_booleen($affichage['validation'] ?? null),
		'validation_sur_paiement' => association_diagnostic_inscription_booleen($affichage['validation_sur_paiement'] ?? null),
		'nombre_tarifs' => count($tarifs),
		'limite_configuree' => max(0, intval($regles['limite_configuree'] ?? 0)),
		'limite_effective' => max(0, intval($regles['limite_effective'] ?? 0)),
		'places_disponibles' => max(0, intval($regles['places_disponibles'] ?? 0)),
		'places_attente_disponibles' => max(0, intval($regles['places_attente_disponibles'] ?? 0)),
		'profil_session_applique' => association_diagnostic_inscription_booleen($regles['profil_session_applique'] ?? null),
	);
}

function association_diagnostic_inscription_code($scenario) {
	$type_inscrits = array('public' => 'P', 'prive' => 'R', 'strict' => 'S');
	$quota = array('souple' => 'S', 'strict' => 'T');
	$interface = strtoupper($scenario['interface'] ?? 'X');
	$operation = (($scenario['operation'] ?? '') === 'modification') ? 'MO' : ((($scenario['operation'] ?? '') === 'creation') ? 'CR' : 'OX');

	return implode('-', array(
		'IE1',
		$interface,
		$operation,
		'OU' . association_diagnostic_inscription_bit($scenario['inscription_ouverte'] ?? null),
		'EL' . association_diagnostic_inscription_bit($scenario['eligible'] ?? null),
		'FA' . association_diagnostic_inscription_bit($scenario['famille'] ?? null),
		'ME' . association_diagnostic_inscription_bit($scenario['multi_etape'] ?? null),
		'PA' . association_diagnostic_inscription_bit($scenario['payant'] ?? null),
		'AC' . association_diagnostic_inscription_bit($scenario['accompagnants'] ?? null),
		'TP' . ($type_inscrits[$scenario['type_inscrits'] ?? ''] ?? 'X'),
		'Q' . ($quota[$scenario['quota'] ?? ''] ?? 'X'),
		'PL' . association_diagnostic_inscription_bit($scenario['places'] ?? null),
		'AT' . association_diagnostic_inscription_bit($scenario['attentes'] ?? null),
		'VA' . association_diagnostic_inscription_bit($scenario['validation'] ?? null),
		'VP' . association_diagnostic_inscription_bit($scenario['validation_sur_paiement'] ?? null),
		'T' . str_pad((string) min(99, max(0, intval($scenario['nombre_tarifs'] ?? 0))), 2, '0', STR_PAD_LEFT),
		'LC' . association_diagnostic_inscription_nombre_code($scenario['limite_configuree'] ?? 0, 2),
		'LE' . association_diagnostic_inscription_nombre_code($scenario['limite_effective'] ?? 0, 2),
		'PD' . association_diagnostic_inscription_nombre_code($scenario['places_disponibles'] ?? 0, 3),
		'AE' . association_diagnostic_inscription_nombre_code($scenario['places_attente_disponibles'] ?? 0, 3),
		'PS' . association_diagnostic_inscription_bit($scenario['profil_session_applique'] ?? null),
	));
}

function association_diagnostic_inscription_nombre_code($valeur, $longueur) {
	$maximum = (10 ** intval($longueur)) - 1;
	return str_pad((string) min($maximum, max(0, intval($valeur))), intval($longueur), '0', STR_PAD_LEFT);
}

function association_diagnostic_inscription_booleen($valeur) {
	if ($valeur === true || $valeur === 1 || $valeur === '1' || $valeur === 'oui') {
		return true;
	}
	if ($valeur === false || $valeur === 0 || $valeur === '0' || $valeur === '' || $valeur === 'non') {
		return false;
	}
	return null;
}

function association_diagnostic_inscription_bit($valeur) {
	return $valeur === true ? '1' : ($valeur === false ? '0' : 'X');
}

function association_diagnostic_inscription_liste($valeur) {
	if (!is_array($valeur)) {
		return array();
	}
	$valeur = array_values(array_unique(array_filter(array_map('strval', $valeur), 'strlen')));
	sort($valeur, SORT_STRING);
	return $valeur;
}

/**
 * Retire les secrets et normalise recursivement les valeurs exportees.
 */
function association_diagnostic_inscription_nettoyer($valeur, $cle = '') {
	if (preg_match('/(?:token|secret|password|mot_de_passe|api[_-]?key|authorization)/i', (string) $cle)) {
		return '[masque]';
	}
	if (!is_array($valeur)) {
		return is_object($valeur) ? '[objet]' : $valeur;
	}
	$resultat = array();
	foreach ($valeur as $sous_cle => $sous_valeur) {
		$resultat[$sous_cle] = association_diagnostic_inscription_nettoyer($sous_valeur, $sous_cle);
	}
	return $resultat;
}

function association_diagnostic_inscription_json_canonique($valeur) {
	$valeur = association_diagnostic_inscription_trier($valeur);
	return json_encode($valeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Formate le diagnostic pour son affichage dans le bloc reserve au webmestre.
 */
function association_diagnostic_inscription_json($diagnostic) {
	if (!is_array($diagnostic)) {
		return '';
	}
	$diagnostic = association_diagnostic_inscription_nettoyer($diagnostic);
	$json = json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	return is_string($json) ? $json : '';
}

function association_diagnostic_inscription_trier($valeur) {
	if (!is_array($valeur)) {
		return $valeur;
	}
	if (array_keys($valeur) !== range(0, count($valeur) - 1)) {
		ksort($valeur, SORT_STRING);
	}
	foreach ($valeur as $cle => $sous_valeur) {
		$valeur[$cle] = association_diagnostic_inscription_trier($sous_valeur);
	}
	return $valeur;
}
