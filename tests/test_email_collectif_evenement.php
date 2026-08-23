<?php

define('_ECRIRE_INC_VERSION', 1);

$GLOBALS['test_email_tables'] = array(
	'spip_auteurs' => array(
		array('id_auteur' => 1, 'email' => 'Membre@example.test'),
		array('id_auteur' => 2, 'email' => 'doublon@example.test'),
	),
	'spip_asso_activites' => array(
		array('id_activite' => 10, 'id_evenement' => 42, 'email_inscrit' => 'public@example.test', 'prenom_inscrit' => 'Test', 'nom_inscrit' => 'Public', 'nombre_inscrits' => 1, 'statut' => 'ok'),
		array('id_activite' => 11, 'id_evenement' => 42, 'email_inscrit' => 'DOUBLON@example.test', 'prenom_inscrit' => 'Test', 'nom_inscrit' => 'Doublon', 'nombre_inscrits' => 2, 'statut' => 'ok'),
		array('id_activite' => 12, 'id_evenement' => 99, 'email_inscrit' => 'hors-evenement@example.test', 'statut' => 'ok'),
		array('id_activite' => 13, 'id_evenement' => 42, 'email_inscrit' => 'desinscrit@example.test', 'statut' => 'desinscrit'),
	),
);

function sql_in($field, $values) {
	return $field . ' IN (' . implode(',', array_map('intval', $values)) . ')';
}

function sql_select($select, $table, $where = '') {
	$rows = $GLOBALS['test_email_tables'][$table] ?? array();
	$ids = array();
	if (preg_match('/(?:id_auteur|id_activite) IN \(([^)]*)\)/', $where, $matches)) {
		$ids = array_map('intval', explode(',', $matches[1]));
	}
	$filtered = array();
	foreach ($rows as $row) {
		$id = intval($row[$table === 'spip_auteurs' ? 'id_auteur' : 'id_activite'] ?? 0);
		if ($ids && !in_array($id, $ids, true)) {
			continue;
		}
		if ($table === 'spip_asso_activites') {
			if (str_contains($where, 'id_evenement=42') && intval($row['id_evenement']) !== 42) {
				continue;
			}
			if (str_contains($where, "statut!='desinscrit'") && $row['statut'] === 'desinscrit') {
				continue;
			}
		}
		$filtered[] = $row;
	}
	return array('rows' => $filtered, 'index' => 0);
}

function sql_fetsel($select, $table, $where = '') {
	if ($table !== 'spip_evenements' || !str_contains($where, 'id_evenement=42')) {
		return false;
	}
	return array(
		'id_evenement' => 42,
		'titre' => 'Sortie test',
		'date_debut' => '2026-08-01 18:30:00',
		'lieu' => 'Centre culturel',
		'adresse' => '',
	);
}

function extraire_multi($texte) { return $texte; }
function supprimer_tags($texte) { return strip_tags($texte); }
function affdate($date, $format = null) { return '01/08/2026 18:30'; }
function generer_url_public($page, $args = '') { return '/spip.php?page=' . $page . '&' . $args; }
function url_absolue($url) { return 'https://example.test' . $url; }
function include_spip($fichier) {}
function _request($cle) { return $GLOBALS['test_email_request'][$cle] ?? null; }
function set_request($cle, $valeur) { $GLOBALS['test_email_request'][$cle] = $valeur; }
function adherents_recherche_avancee_saisies() { return array(); }
function _T($cle, $variables = array()) {
	$textes = array(
		'association:email_collectif_evenement_rappel_sujet' => 'Rappel - @titre@',
		'association:email_collectif_evenement_rappel_texte' => 'Rappel de @titre@.',
		'association:email_collectif_evenement_annulation_sujet' => 'Annulation - @titre@',
		'association:email_collectif_evenement_annulation_texte' => 'Annulation de @titre@.',
		'association:email_collectif_evenement_report_sujet' => 'Report - @titre@',
		'association:email_collectif_evenement_report_texte' => 'Report de @titre@.',
		'association:email_collectif_evenement_modification_sujet' => 'Modification - @titre@',
		'association:email_collectif_evenement_modification_texte' => 'Modification de @titre@.',
		'association:email_collectif_rappel_evenement_lien' => 'Consulter evenement',
		'association:email_collectif_inscription_participants' => '@nombre@ participants',
	);
	$texte = $textes[$cle] ?? $cle;
	foreach ($variables as $nom => $valeur) {
		$texte = str_replace('@' . $nom . '@', $valeur, $texte);
	}
	return $texte;
}

function sql_fetch(&$result) {
	if ($result['index'] >= count($result['rows'])) {
		return false;
	}
	return $result['rows'][$result['index']++];
}

function sql_insertq() {
	throw new RuntimeException('Le test ne doit effectuer aucune ecriture.');
}

require dirname(__DIR__) . '/plugins/association-communication/inc/email_collectif.php';
require dirname(__DIR__) . '/plugins/association-communication/formulaires/inc/email_collectif.php';
require dirname(__DIR__) . '/plugins/association-adhesions/formulaires/email_collectif_adherent.php';
require dirname(__DIR__) . '/plugins/association-adhesions/formulaires/email_collectif_adherent/verifier_etape_post_saisies.php';
require dirname(__DIR__) . '/plugins/association-evenements/formulaires/email_collectif_evenement.php';
require dirname(__DIR__) . '/plugins/association-evenements/formulaires/email_collectif_evenement/verifier_etape_post_saisies.php';

$emails = association_email_collectif_resoudre_destinataires(array(1, 2), array(10, 11, 12, 13), 42);
$attendus = array('membre@example.test', 'doublon@example.test', 'public@example.test');
sort($emails);
sort($attendus);

if ($emails !== $attendus) {
	fwrite(STDERR, 'Destinataires inattendus: ' . json_encode($emails) . PHP_EOL);
	exit(1);
}

$inscriptions = association_email_collectif_inscriptions_evenement(42);
if (array_keys($inscriptions) !== array(10, 11)) {
	fwrite(STDERR, 'Inscriptions inattendues: ' . json_encode(array_keys($inscriptions)) . PHP_EOL);
	exit(1);
}
if (($inscriptions[10]['libelle'] ?? '') !== 'Test Public'
	|| ($inscriptions[11]['libelle'] ?? '') !== 'Test Doublon — 2 participants') {
	fwrite(STDERR, 'Libelles inscriptions inattendus: ' . json_encode($inscriptions) . PHP_EOL);
	exit(1);
}

$selection_initiale = association_email_collectif_selection_activites_initiale($inscriptions, array(), false);
$selection_videe = association_email_collectif_selection_activites_initiale($inscriptions, array(), true);
if ($selection_initiale !== array(10, 11) || $selection_videe !== array()) {
	fwrite(STDERR, 'Preselection inattendue: ' . json_encode(array($selection_initiale, $selection_videe)) . PHP_EOL);
	exit(1);
}

$GLOBALS['meta']['adresse_site'] = 'https://example.test';
$GLOBALS['test_email_request'] = array(
	'_etape' => 5,
	'id_evenement' => 42,
	'selecteur_adherent' => array(),
	'selecteur_activite_evenement' => array(),
);
$erreurs_avant_selection = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(3, 42);
$erreurs_sans_destinataire = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(4, 42);
if ($erreurs_avant_selection || !isset($erreurs_sans_destinataire['selecteur_adherent'])) {
	fwrite(STDERR, 'Le formulaire doit refuser un envoi sans destinataire.' . PHP_EOL);
	exit(1);
}

$GLOBALS['test_email_request']['selecteur_activite_evenement'] = array(10);
$erreurs_avec_destinataire = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(4, 42);
if ($erreurs_avec_destinataire) {
	fwrite(STDERR, 'Le formulaire refuse une inscription valide.' . PHP_EOL);
	exit(1);
}

$GLOBALS['test_email_request']['type_destinataires_evenement'] = 'adherents_association';
$GLOBALS['test_email_request']['selecteur_adherent'] = array();
$erreurs_source_association_sans_adherent = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(5, 42);
$GLOBALS['test_email_request']['selecteur_adherent'] = array(1);
$erreurs_source_association = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(5, 42);
if (!isset($erreurs_source_association_sans_adherent['selecteur_adherent']) || $erreurs_source_association) {
	fwrite(STDERR, 'La source association doit ignorer les inscriptions evenement.' . PHP_EOL);
	exit(1);
}

$GLOBALS['test_email_request']['type_destinataires_evenement'] = 'inscrits_evenement';
$GLOBALS['test_email_request']['selecteur_activite_evenement'] = array();
$erreurs_source_evenement_sans_inscription = formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist(4, 42);
if (!isset($erreurs_source_evenement_sans_inscription['selecteur_adherent'])) {
	fwrite(STDERR, 'La source evenement doit ignorer les adherents de la recherche.' . PHP_EOL);
	exit(1);
}

$GLOBALS['test_email_request']['selecteur_adherent'] = array();
$erreurs_adherent = formulaires_email_collectif_adherent_verifier_etape_post_saisies_dist(5);
if (!isset($erreurs_adherent['selecteur_adherent'])) {
	fwrite(STDERR, 'Le parcours adherent ne doit pas accepter une inscription evenement.' . PHP_EOL);
	exit(1);
}

foreach (array('libre', 'rappel', 'annulation', 'report', 'modification') as $type) {
	$gabarit = association_email_collectif_gabarit_evenement(42, $type);
	if (($gabarit['gabarit_evenement'] ?? '') !== $type
		|| ($type === 'libre' && array_filter(array(
			$gabarit['sujet'] ?? '',
			$gabarit['titre'] ?? '',
			$gabarit['chapeau'] ?? '',
			$gabarit['texte'] ?? '',
		)))
		|| ($type !== 'libre' && (
			!str_contains($gabarit['sujet'] ?? '', 'Sortie test')
			|| ($gabarit['texte'] ?? '') !== ''
		))) {
		fwrite(STDERR, 'Gabarit evenement inattendu: ' . json_encode($gabarit) . PHP_EOL);
		exit(1);
	}
}

function noms_saisies(array $saisies) {
	$noms = array();
	foreach ($saisies as $saisie) {
		if (isset($saisie['options']['nom'])) {
			$noms[] = $saisie['options']['nom'];
		}
		if (!empty($saisie['saisies']) && is_array($saisie['saisies'])) {
			$noms = array_merge($noms, noms_saisies($saisie['saisies']));
		}
	}
	return $noms;
}

$GLOBALS['meta']['adresse_site'] = 'https://example.test';
$noms_adherent = noms_saisies(association_formulaire_email_collectif_saisies('adherent'));
$GLOBALS['test_email_request']['type_destinataires_evenement'] = 'inscrits_evenement';
$saisies_evenement = association_formulaire_email_collectif_saisies('evenement');
$nombre_etapes_evenement = count($saisies_evenement);
$GLOBALS['test_email_request']['type_destinataires_evenement'] = 'adherents_association';
$saisies_association = association_formulaire_email_collectif_saisies('evenement');
$nombre_etapes_association = count($saisies_association);
$noms_evenement = noms_saisies($saisies_association);
if (!in_array('ajouter_information_paiement', $noms_adherent, true)
	|| in_array('type_destinataires_evenement', $noms_adherent, true)
	|| in_array('inclure_contenu_evenement', $noms_adherent, true)
	|| in_array('ajouter_information_paiement', $noms_evenement, true)
	|| !in_array('type_destinataires_evenement', $noms_evenement, true)
	|| !in_array('inclure_contenu_evenement', $noms_evenement, true)) {
	fwrite(STDERR, 'Les options propres aux deux parcours sont melangees.' . PHP_EOL);
	exit(1);
}
if ($nombre_etapes_evenement !== 5 || $nombre_etapes_association !== 6) {
	fwrite(STDERR, 'Le branchement apres apercu doit produire 5 ou 6 etapes.' . PHP_EOL);
	exit(1);
}

$racine = dirname(__DIR__);
$contenu_prive = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/edit_email_collectif_activite.html');
$liste_inscriptions = file_get_contents($racine . '/plugins/association-evenements/formulaires/inc-email-collectif-inscriptions-evenement.html');
if (!str_contains($liste_inscriptions, 'tableau_inscriptions_activite')
	|| !str_contains($liste_inscriptions, 'selecteur_activite_evenement[]')
	|| !str_contains($liste_inscriptions, 'activite_entete_statut_inscription')
	|| !str_contains($liste_inscriptions, 'onglets_simple email_collectif_filtre_statut')
	|| !str_contains($liste_inscriptions, 'aria-current="page"')
	|| !str_contains($liste_inscriptions, "event.preventDefault()")
	|| str_contains($liste_inscriptions, 'class="ajax"')
	|| str_contains($liste_inscriptions, '<ul class="liste-items">')) {
	fwrite(STDERR, 'La liste historique detaillee des destinataires evenement doit etre conservee.' . PHP_EOL);
	exit(1);
}
$raccourci = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/inc-voir_activites/bloc_raccourcis.html');
$notification = file_get_contents($racine . '/plugins/association-communication/notifications/email_collectif.html');
if (file_exists($racine . '/exec/edit_email_collectif_activite.php')
	|| !str_contains($contenu_prive, '#FORMULAIRE_EMAIL_COLLECTIF_EVENEMENT')
	|| !str_contains($contenu_prive, 'email_collectif_evenement_gabarit_libre')
	|| !str_contains($notification, 'inclure_contenu_evenement')
	|| !str_contains($raccourci, 'edit_email_collectif_activite')
	|| str_contains($raccourci, 'edit_email_collectif_adherent')) {
	fwrite(STDERR, 'Le parcours evenement SPIP 4.4 est mal route.' . PHP_EOL);
	exit(1);
}

echo "OK: parcours separes, option evenement, gabarit libre, moteur mutualise et routage SPIP 4.4\n";
