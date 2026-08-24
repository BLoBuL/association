<?php
require_once __DIR__ . '/inc/association_evenements_statistiques_compta.php';
require_once __DIR__ . '/inc/association_evenements_responsables.php';

if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_evenements_integration_active($prefixe) {
	if (function_exists('association_plugin_actif')) {
		return association_plugin_actif($prefixe);
	}
	if (function_exists('test_plugin_actif')) {
		$scenario = $GLOBALS['association_test_scenario']['plugins'] ?? null;
		if (is_array($scenario) && array_key_exists($prefixe, $scenario)) {
			return test_plugin_actif($prefixe);
		}
	}

	return true;
}

function association_evenements_profil_participant(array $participant) {
	if (function_exists('association_profil_participant')) {
		return association_profil_participant($participant);
	}

	$id_auteur = (int) ($participant['id_auteur'] ?? 0);
	$auteur = $id_auteur ? (sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . $id_auteur) ?: array()) : array();
	$est_membre = ($auteur['statut_interne'] ?? '') === 'ok';

	return $participant + array(
		'profil' => $est_membre ? 'membre' : 'public',
		'est_membre' => $est_membre,
		'famille' => array(),
	);
}

function association_evenements_afficher_montant($montant, $devise = 'EUR', $type = 'symbol') {
	$devise = strtoupper(trim((string) $devise)) ?: 'EUR';
	if (association_evenements_integration_active('association_paiements') && function_exists('bank_affiche_montant')) {
		return bank_affiche_montant((float) $montant, $devise, $type);
	}
	if (class_exists('NumberFormatter')) {
		$format = new NumberFormatter($GLOBALS['spip_lang'] ?? 'fr_FR', NumberFormatter::CURRENCY);
		$valeur = $format->formatCurrency((float) $montant, $devise);
		if ($valeur !== false) {
			return $valeur;
		}
	}

	return number_format((float) $montant, 2, ',', ' ') . ' ' . $devise;
}

function association_evenements_paiements_configs($contexte = 'acte') {
	if (!association_evenements_integration_active('association_paiements') || !function_exists('bank_lister_configs')) {
		return array();
	}
	return bank_lister_configs($contexte);
}

function association_evenements_paiement_id($config) {
	return function_exists('bank_config_id') ? bank_config_id($config) : '';
}

function association_evenements_paiement_titre($prestation) {
	return function_exists('bank_titre_type_paiement') ? bank_titre_type_paiement($prestation) : '';
}

// Ces surcharges du formulaire Agenda sont chargées depuis les squelettes,
// avant que le fichier CVT d'origine ait nécessairement inclus ses API.
if (function_exists('include_spip')) {
	include_spip('inc/actions');
	include_spip('inc/editer');
	include_spip('inc/autoriser');
}


function ie_message_erreur_texte($message) {
	$message = html_entity_decode((string) $message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	return trim(strip_tags($message));
}

function formulaires_editer_evenement_charger($id_evenement = 'new', $id_article = 0, $retour = '', $lier_trad = 0, $config_fonc = 'evenements_edit_config', $row = array(), $hidden = '') {
$valeurs = formulaires_editer_objet_charger('evenement', $id_evenement, $id_article, 0, $retour, $config_fonc, $row, $hidden);
$valeurs['_saisie_timezone'] = lire_config('agenda/fuseaux_horaires', 0);
if ($valeurs['_saisie_timezone']) {
include_spip('inc/agenda_timezone');
}
if(!$valeurs['id_article']) {
$valeurs['id_article'] = $id_article;
}
if (!$valeurs['titre']) {
$valeurs['titre'] = sql_getfetsel('titre', 'spip_articles', 'id_article='.intval($valeurs['id_article']));
}
$valeurs['id_parent'] = $valeurs['id_article'];
unset($valeurs['id_article']);
// pour le selecteur d'article(s) optionnel
$valeurs['parents_id'] = array('article|'.$valeurs['id_parent']);
// fixer la date par defaut en cas de creation d'evenement
if (!intval($id_evenement)) {
$t=time();
$valeurs['date_debut'] = date('Y-m-d H:i:00', $t);
$valeurs['date_fin'] = date('Y-m-d H:i:00', $t+3600);
$valeurs['horaire'] = 'oui';
}
$now = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
if ($valeurs['_saisie_timezone']) {
$valeurs['date_debut'] = agenda_tz_date_local_to_tz($valeurs['date_debut'], $valeurs['timezone_affiche']);
$valeurs['date_fin'] = agenda_tz_date_local_to_tz($valeurs['date_fin'], $valeurs['timezone_affiche']);
$now = agenda_tz_date_local_to_tz($now, $valeurs['timezone_affiche']);
}
// les repetitions
$valeurs['repetitions'] = array();
if (intval($id_evenement)) {
$repetitons = sql_allfetsel('date_debut', 'spip_evenements', 'id_evenement_source='.intval($id_evenement), '', 'date_debut');
foreach ($repetitons as $d) {
    if ($valeurs['_saisie_timezone']) {
        $d['date_debut'] = agenda_tz_date_local_to_tz($d['date_debut'], $valeurs['timezone_affiche']);
    }
    $valeurs['repetitions'][] = date('d/m/Y', strtotime($d['date_debut']));
}
}
$valeurs['repetitions'] = implode(',', $valeurs['repetitions']);
// dispatcher date et heure
list($valeurs['date_debut'], $valeurs['heure_debut']) = explode(' ', date('d/m/Y H:i', strtotime($valeurs['date_debut'])));
list($valeurs['date_fin'], $valeurs['heure_fin']) = explode(' ', date('d/m/Y H:i', strtotime($valeurs['date_fin'])));
// si ce sont des evenements a la journee, on mets une heure par defaut calee sur l'heure actuelle (dans la timezone cible),
// si jamais l'utilisateur veut passer l'evenement en mode horaire
if ($valeurs['horaire'] === 'non') {
$valeurs['heure_debut'] = date('H:00', strtotime($now) + 1800);
$valeurs['heure_fin'] = date('H:00', strtotime($now) +1800 + 3600);
}
// traiter specifiquement l'horaire qui est une checkbox
if (_request('date_debut') and !_request('horaire')) {
$valeurs['horaire'] = 'oui';
}
if (!$valeurs['type_inscrits_evenement']) {
// Les valeurs doivent être strict ou prive
$valeurs['type_inscrits_evenement'] =   ($GLOBALS['association_metas']['meta_cfg_event_type_inscrits_evenement'] == 'only_strict' ) ? 'strict' : $GLOBALS['association_metas']['meta_cfg_event_type_inscrits_evenement'];
}
/*if (!$valeurs['afficher_liste_inscrits']) {
// Les valeurs doivent être 1 ou 0
 if($GLOBALS['association_metas']['meta_cfg_event_afficher_liste_inscrits'] == 'toujours'){
    $valeurs['afficher_liste_inscrits'] '1';
}elseif($GLOBALS['association_metas']['meta_cfg_event_afficher_liste_inscrits'] == 'jamais'){
    $valeurs['afficher_liste_inscrits'] = '0';
}else{
    $valeurs['afficher_liste_inscrits'] = $GLOBALS['association_metas']['meta_cfg_event_afficher_liste_inscrits'];
}
}*/
if (!isset($valeurs['ouverture_differe'])) {
$valeurs['ouverture_differe'] = $GLOBALS['association_metas']['meta_cfg_event_ouverture_differe'] ;
}
if (!$valeurs['fermeture_inscription']) {
// Les valeurs doivent être midnight,last_minute,24h,48h,72h,96h,7j,14j,30,unlimited,now,cancel
$valeurs['fermeture_inscription'] = $GLOBALS['association_metas']['meta_cfg_event_inscription_deadline'] ;
}
// Pouvoir interdire l'affichage de l'inscription (puisque ce n'est pas traite' par le plugin)
$valeurs['_affiche_inscription'] = isset($GLOBALS['agenda_affiche_inscription']) ? $GLOBALS['agenda_affiche_inscription'] : false;
$valeurs['places'] = intval($valeurs['places']);
// est-ce qu'on utilise jQueryUI ou le picker SPIP 4.0 ?
$valeurs['_picker'] = 'jqueryui';
if (_SPIP_VERSION_ID > 30300 and !test_plugin_actif('jqueryui')) {
$valeurs['_picker'] = 'spip40';
}
include_spip('inc/evenement_defauts');
$valeurs = association_evenement_appliquer_defauts($valeurs, $id_evenement);
return $valeurs;
}

function evenements_edit_config() {
	return array();
}

function formulaires_editer_evenement_verifier($id_evenement = 'new', $id_article = 0, $retour = '', $lier_trad = 0, $config_fonc = 'evenements_edit_config', $row = array(), $hidden = '') {
$erreurs = formulaires_editer_objet_verifier('evenement', $id_evenement, array('titre', 'date_debut', 'date_fin'));
include_spip('inc/date_gestion');
include_spip('inc/saisies');
// on charge les saisies
$saisies = _request('_saisies');
$horaire = _request('horaire') == 'non' ? false : true;
if (empty($erreurs['date_debut'])) {
$date_debut = verifier_corriger_date_saisie('debut', $horaire, $erreurs);
}
if (empty($erreurs['date_fin'])) {
$date_fin = verifier_corriger_date_saisie('fin', $horaire, $erreurs);
}
if ($date_debut and $date_fin and $date_fin < $date_debut) {
$erreurs['date_fin'] = _T('agenda:erreur_date_avant_apres');
}
include_spip('formulaires/selecteur/selecteur_fonctions');
if (count($id = picker_selected(_request('parents_id'), 'article'))
and $id = reset($id)
and $id = sql_getfetsel('id_article', 'spip_articles', 'id_article='.intval($id))) {
// reinjecter dans id_parent
set_request('id_parent', $id);
}
if (!$id_parent = intval(_request('id_parent'))) {
$erreurs['id_parent'] = _T('agenda:erreur_article_manquant');
} else {
if (!autoriser('creerevenementdans', 'article', $id_parent)) {
    $erreurs['id_parent'] = _T('agenda:erreur_article_interdit');
}
}
/*AJOUT BLOBUL*/
/*On vérifie qu'un montant correct soit associé à au moins une catégorie pour les événements payant*/
/*Si événement payant*/
$payant = _request('payant');
$categorie_result = _request('categorie_prix');
if($payant == '1'){
$au_moins_une_categorie_valide = false; // initialisation explicite avant la boucle
foreach($_REQUEST as $key => $val){
    if(stristr($key,'categorie_prix') && is_numeric($val) && !empty($val) ){
            $au_moins_une_categorie_valide = true;
    }elseif(stristr($key,'categorie_prix') && !is_numeric($val) && !empty($val)){
        $erreurs[$key] = '<div style="color:red">' ._T('association_evenements:evenement_montant_erreur_categorie'). '</div>';
    }
}
if($au_moins_une_categorie_valide != true){
    $erreurs['categorie_prix'] = '<div style="color:red">' ._T('association_evenements:evenement_montant_erreur'). '</div>';
}
}
#if (!count($erreurs))
#	$erreurs['message_erreur'] = 'ok?';
return $erreurs;
}
