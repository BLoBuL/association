<?php

function association_test_run_diagnostic_formulaire_inscription_suite() {
	$echecs = 0;
	$verifier = function ($condition, $message) use (&$echecs) {
		if (!$condition) {
			$echecs++;
			echo "ECHEC diagnostic: $message\n";
		}
	};

	include_once ASSOCIATION_TEST_PLUGIN_ROOT . '/inc/diagnostic_formulaire_inscription.php';

	$sources = array(
		'ouverture_inscription' => array('inscription_ouverte' => 'oui', 'places_disponibles' => 'oui'),
		'eligibilite_inscription' => array('eligible_inscription' => 'oui', 'token_url' => 'secret-url'),
		'affichage_dans_activites' => array(
			'accompagnants' => 1,
			'places' => 1,
			'attentes' => 1,
			'validation' => '',
			'validation_sur_paiement' => '',
			'type_inscrits_evenement' => 'strict',
			'token' => 'secret-evenement',
			'payant' => '',
			'montant' => array(),
		),
		'gestions_places' => array('places_disponibles' => 10, 'places_limites' => 5),
		'configuration' => array(
			'meta_cfg_event_config_accompagnants' => 'tout',
			'meta_cfg_event_type_quota' => 'strict',
		),
		'regles_effectives' => array(
			'limite_configuree' => 5,
			'limite_effective' => 5,
			'places_disponibles' => 10,
			'places_attente_disponibles' => 5,
			'profil_session_applique' => false,
		),
	);
	$contexte = array(
		'interface' => 'bo',
		'parcours' => 'simple',
		'operation' => 'creation',
		'famille_active' => false,
		'champs_generes' => array('adherent' => array('saisie' => 'selection'), 'nb_inscrits' => array('saisie' => 'selection')),
		'conditions_affichage' => array('adherent' => '@select_type_inscrit@ == "membre"'),
	);
	$diagnostic = association_diagnostic_formulaire_inscription(380, $contexte, $sources);

	$verifier($diagnostic['code'] === 'IE1-BO-CR-OU1-EL1-FA0-ME0-PA0-AC1-TPS-QT-PL1-AT1-VA0-VP0-T00-LC05-LE05-PD010-AE005-PS0', 'code du scenario BO gratuit strict');
	$verifier($diagnostic['affichage_dans_activites']['token'] === '[masque]', 'token evenement masque');
	$verifier($diagnostic['eligibilite_inscription']['token_url'] === '[masque]', 'token URL masque');
	$verifier(isset($diagnostic['contexte']['champs_generes']['nb_inscrits']), 'structure des champs conservee');
	$verifier(strlen($diagnostic['reference']) === 8, 'reference courte sur huit caracteres');
	$verifier($diagnostic['scenario']['validation'] === false, 'booleen vide normalise a faux');

	$sources['affichage_dans_activites']['payant'] = true;
	$sources['affichage_dans_activites']['validation_sur_paiement'] = 'oui';
	$sources['affichage_dans_activites']['type_inscrits_evenement'] = 'public';
	$sources['affichage_dans_activites']['montant'] = array(array('id_categorie' => 1), array('id_categorie' => 2), array('id_categorie' => 3));
	$sources['configuration']['meta_cfg_event_config_accompagnants'] = 'membre_famille';
	$sources['configuration']['meta_cfg_event_type_quota'] = 'souple';
	$contexte['interface'] = 'fo';
	$contexte['parcours'] = 'multi';
	$contexte['operation'] = 'modification';
	$contexte['famille_active'] = true;
	$diagnostic_fo = association_diagnostic_formulaire_inscription(380, $contexte, $sources);

	$verifier($diagnostic_fo['code'] === 'IE1-FO-MO-OU1-EL1-FA1-ME1-PA1-AC1-TPP-QS-PL1-AT1-VA0-VP1-T03-LC05-LE05-PD010-AE005-PS0', 'code du scenario FO multi payant');
	$verifier($diagnostic_fo['reference'] !== $diagnostic['reference'], 'reference differente pour un autre scenario');
	$verifier(association_diagnostic_formulaire_inscription(380, $contexte, $sources)['reference'] === $diagnostic_fo['reference'], 'reference deterministe');

	$structure = association_diagnostic_inscription_analyser_saisies(array(
		array('saisie' => 'fieldset', 'options' => array('nom' => 'fieldset_infos_generales'), 'saisies' => array(
			array('saisie' => 'selection', 'options' => array('nom' => 'select_type_inscrit', 'obligatoire' => 'oui')),
			array('saisie' => 'selection', 'options' => array('nom' => 'membre', 'afficher_si' => '@select_type_inscrit@ == "membre"')),
		)),
	));
	$verifier(array_keys($structure['champs_generes']) === array('fieldset_infos_generales', 'membre', 'select_type_inscrit'), 'saisies imbriquees alignees sur les fieldsets multi');
	$verifier($structure['conditions_affichage']['membre'] === '@select_type_inscrit@ == "membre"', 'condition afficher_si conservee');
	$verifier($structure['champs_generes']['select_type_inscrit']['obligatoire'] === true, 'caractere obligatoire normalise');

	$regles_bo = association_inscription_regles_effectives('multi_prive', array('accompagnants' => true), array('places_limites' => 5), array('radio_type_adherent' => 'individuel'));
	$verifier($regles_bo['diagnostic']['limite_effective'] === 5 && $regles_bo['diagnostic']['profil_session_applique'] === false, 'le profil operateur BO reste sans effet');
	$regles_fo = association_inscription_regles_effectives('multi_public', array('accompagnants' => true), array('places_limites' => 5), array('radio_type_adherent' => 'individuel'));
	$verifier($regles_fo['diagnostic']['limite_effective'] === 1 && $regles_fo['diagnostic']['profil_session_applique'] === true, 'le profil visiteur FO est explicite');

	$gabarit_simple = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/formulaires/inscription_evenement.html');
	$gabarit_multi = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/formulaires/inscription_evenement_multi.html');
	$inclusion = 'fond=formulaires/inc-diagnostic-inscription';
	$verifier(strpos($gabarit_simple, '#SESSION{webmestre}') !== false && strpos($gabarit_simple, $inclusion) !== false, 'integration protegee dans le BO simple');
	$verifier(strpos($gabarit_multi, '#SESSION{webmestre}') !== false && strpos($gabarit_multi, $inclusion) !== false, 'integration protegee dans le BO multi');
	$verifier(strpos($gabarit_multi, '#ENV**|unserialize|foreach') === false, 'ancien dump brut retire du BO multi');
	$verifier(strpos(file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/formulaires/inc-diagnostic-inscription.html'), '#SESSION{webmestre}') !== false, 'protection maintenue dans le composant commun');

	if (!$echecs) {
		echo "OK diagnostic formulaire inscription\n";
	}
	return $echecs ? 1 : 0;
}
