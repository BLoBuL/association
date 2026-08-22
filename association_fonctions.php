<?php
/**
 * Fonctions globales du plugin Association
 *
 * Ce fichier contient les fonctions utilitaires et helpers du plugin Association,
 * organisées par thématiques :
 * - Gestion des événements (agenda)
 * - Authentification et mot de passe
 * - Newsletters et listes de diffusion
 * - Rôles d'association
 * - Filtres de périodes et cotisations
 * - Helpers divers
 *
 * @package SPIP\Association\Fonctions
 * @author Blobul
 * @version 2025
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');

/**
 * Ramene un message Saisies/CVT a son texte affichable.
 *
 * Cette fonction est declaree dans le fichier de fonctions du plugin afin que
 * le filtre homonyme soit disponible des le calcul des squelettes SPIP.
 *
 * @param mixed $message
 * @return string
 */
function ie_message_erreur_texte($message) {
	$message = html_entity_decode((string) $message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	return trim(strip_tags($message));
}

/**
 * Filtre public pour exporter les donnees RGPD metier de l'association.
 *
 * @param int $id_auteur
 * @return array
 */
function association_export_donnees_auteur($id_auteur) {
	include_spip('inc/rgpd_export');
	return association_rgpd_export_donnees_auteur($id_auteur);
}

/**
 * Genere un nom de fichier explicite pour l'export RGPD association.
 *
 * @param mixed $date
 * @param int $id_auteur
 * @return string
 */
function association_nom_fichier_export_rgpd($date = null, $id_auteur = 0) {
	$adresse_site = $GLOBALS['meta']['adresse_site'] ?? '';
	$host = parse_url($adresse_site, PHP_URL_HOST);
	if (!$host) {
		$host = preg_replace(',^https?://,i', '', (string)$adresse_site);
		$host = explode('/', $host)[0] ?? '';
	}
	$host = strtolower(trim((string)$host));
	$host = preg_replace('/^www\./', '', $host);
	$host = preg_replace('/[^a-z0-9.-]+/', '-', $host);
	$host = trim(str_replace('.', '_', $host), '-_');
	if ($host === '') {
		$host = 'site';
	}

	$timestamp = $date ? strtotime((string)$date) : time();
	if (!$timestamp) {
		$timestamp = time();
	}

	$suffixe_auteur = intval($id_auteur) > 0 ? '-' . intval($id_auteur) : '';

	return 'export-association-' . $host . $suffixe_auteur . '-' . date('Y-m-d', $timestamp) . '.json';
}


// ============================================================================
// GESTION DES ÉVÉNEMENTS (AGENDA)
// ============================================================================


/**
 * Charge les valeurs du formulaire d'édition d'événement
 *
 * Prépare les données pour l'affichage du formulaire d'édition d'un événement,
 * en chargeant les valeurs par défaut depuis la configuration de l'association
 * et en gérant les spécificités (fuseaux horaires, répétitions, inscriptions).
 *
 * @param string|int $id_evenement ID de l'événement ou 'new' pour création
 * @param int $id_article ID de l'article parent de l'événement
 * @param string $retour URL de retour après édition
 * @param int $lier_trad Lier à une traduction (0 ou 1)
 * @param string $config_fonc Fonction de configuration personnalisée
 * @param array $row Données de ligne déjà chargées (optionnel)
 * @param string $hidden Champs cachés additionnels
 * @return array Tableau des valeurs du formulaire
 */
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

/**
 * Configuration par défaut du formulaire d'édition d'événement
 *
 * @return array Configuration vide (utilise les valeurs par défaut)
 */
function evenements_edit_config() {
	return array();
}

/**
 * Vérifie les données soumises du formulaire d'édition d'événement
 *
 * Valide les champs obligatoires (titre, dates), vérifie la cohérence des dates,
 * contrôle les autorisations et vérifie les montants pour les événements payants.
 *
 * @param string|int $id_evenement ID de l'événement ou 'new'
 * @param int $id_article ID de l'article parent
 * @param string $retour URL de retour
 * @param int $lier_trad Lier à une traduction
 * @param string $config_fonc Fonction de configuration
 * @param array $row Données de ligne
 * @param string $hidden Champs cachés
 * @return array Tableau des erreurs (vide si pas d'erreur)
 */
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
        $erreurs[$key] = '<div style="color:red">' ._T('association:evenement_montant_erreur_categorie'). '</div>';
    }
}
if($au_moins_une_categorie_valide != true){
    $erreurs['categorie_prix'] = '<div style="color:red">' ._T('association:evenement_montant_erreur'). '</div>';
}
}
#if (!count($erreurs))
#	$erreurs['message_erreur'] = 'ok?';
return $erreurs;
}


// ============================================================================
// AUTHENTIFICATION ET MOT DE PASSE
// ============================================================================

/**
 * Traite la réinitialisation du mot de passe d'un adhérent
 *
 * Permet à un adhérent de définir un nouveau mot de passe via un lien
 * de réinitialisation avec jeton. Après modification, l'adhérent est
 * automatiquement connecté et redirigé vers sa fiche.
 *
 * @param int|null $id_auteur ID de l'auteur/adhérent
 * @param string|null $jeton Jeton de réinitialisation
 * @return array Résultat avec 'redirect' ou 'message_erreur'
 */
function formulaires_mot_de_passe_traiter($id_auteur=null, $jeton=null){
//$res = array('message_ok'=>'');
$res['redirect'] = generer_url_public('fiche_adherent',"");
//refuser_traiter_formulaire_ajax(); // puisqu'on va loger l'auteur a la volee (c'est bonus)
// compatibilite anciens appels du formulaire
if (is_null($jeton)) $jeton = _request('p');
$row = retrouve_auteur($id_auteur,$jeton);
if ($row
&& ($id_auteur = $row['id_auteur'])
&& ($oubli = _request('oubli'))) {
include_spip('action/editer_auteur');
include_spip('action/inscrire_auteur');
if ($err = auteur_modifier($id_auteur, array('pass'=>$oubli))){
    $res = array('message_erreur'=>$err);
}
else {
    auteur_effacer_jeton($id_auteur);
    $login = $row['login'];
    //$res['message_ok'] = "<b>" . _T('pass_nouveau_enregistre') . "</b>".
    //"<br />" . _T('pass_rappel_login', array('login' => $login));
    include_spip('inc/auth');
    $row = sql_fetsel("*","spip_auteurs","id_auteur=".intval($id_auteur));
    auth_loger($row);
    $res['redirect'] = generer_url_public('fiche_adherent',"");
}
}
return $res;
}


// ============================================================================
// NEWSLETTERS ET LISTES DE DIFFUSION
// ============================================================================

/**
 * Construit le critere des listes automatiques selon le statut d'adhesion.
 *
 * Le role webmaster est un droit technique et ne doit pas retirer un adherent
 * d'une liste metier lorsque son statut interne correspond.
 *
 * @param string $statut_interne Statut interne attendu.
 * @return string Critere SQL borne aux comptes actifs.
 */
function association_condition_auteurs_newsletter($statut_interne) {
    return "statut IN ('6forum','1comite','0minirezo') AND statut_interne=" . sql_quote($statut_interne);
}

/**
 * Prépare une liste d'auteurs pour une newsletter selon leur statut interne
 *
 * @param string $statut_interne Statut interne des adhérents (ok, prospect, echu, relance, sorti)
 * @return array Tableau d'auteurs avec nom et email
 */
function preparer_liste_auteurs_newsletter($statut_interne){
$where = association_condition_auteurs_newsletter($statut_interne);
$auteurs = sql_allfetsel("CONCAT(nom_famille,' ',prenom) AS nom,email", "spip_auteurs", $where );

return $auteurs;
}

/**
 * Liste des adhérents "à jour" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */
function mailsubscribers_synchro_list_newsletter_statut_interne_ok() {
	$auteurs = preparer_liste_auteurs_newsletter('ok');
	return $auteurs;
}

/**
 * Liste des adhérents "prospects" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */
function mailsubscribers_synchro_list_newsletter_statut_interne_prospect() {
	$auteurs = preparer_liste_auteurs_newsletter('prospect');
	return $auteurs;
}

/**
 * Liste des adhérents "échus" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */
function mailsubscribers_synchro_list_newsletter_statut_interne_echu() {
	$auteurs = preparer_liste_auteurs_newsletter('echu');
	return $auteurs;
}

/**
 * Liste des adhérents "en relance" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */
function mailsubscribers_synchro_list_newsletter_statut_interne_relance() {
	$auteurs = preparer_liste_auteurs_newsletter('relance');
	return $auteurs;
}

/**
 * Liste des conjoints d'adhérents pour synchronisation avec Mailsubscribers
 * @return array Liste de conjoints avec nom et email
 */
function mailsubscribers_synchro_list_newsletter_liste_conjoints() {
$where = "statut IN ('6forum','1comite','0minirezo') AND email_conjoint != '' AND statut_interne=" . sql_quote("ok");
$auteurs = sql_allfetsel("CONCAT(nom_conjoint,' ',prenom_conjoint) AS nom,email_conjoint AS email", "spip_auteurs", $where);

return $auteurs;
}


// ============================================================================
// HELPERS DIVERS
// ============================================================================

/**
 * Filtre pour obtenir l'identifiant de configuration bancaire
 *
 * Wrapper autour de bank_config_id() pour utilisation dans les squelettes SPIP.
 *
 * @param array $config Configuration bancaire
 * @return string Identifiant de configuration bancaire
 */
function filtre_bank_config_id($config){
	include_spip('inc/bank');
	return bank_config_id($config);
}
/**
 * Traite le formulaire d'édition de newsletter
 *
 * Gère la soumission du formulaire d'édition de newsletter, en tenant compte
 * des différents statuts (prepa, prop, prog) et des modifications possibles.
 * Corrige le bug d'incompatibilité avec CFG/Cextras dans la programmation.
 *
 * @param string $id_newsletter ID de la newsletter ou 'new' pour création
 * @param string $retour URL de retour après traitement
 * @param int $lier_trad Lier à une traduction (0 ou 1)
 * @param string $config_fonc Fonction de configuration personnalisée
 * @param array $row Données de ligne
 * @param string $hidden Champs cachés additionnels
 * @return array Résultat du traitement du formulaire
 */
function formulaires_editer_newsletter_traiter($id_newsletter='new', $retour='', $lier_trad=0, $config_fonc='newsletter_edit_config', $row=array(), $hidden=''){
$baked = 1;
$statut = (intval($id_newsletter)?sql_getfetsel('statut','spip_newsletters','id_newsletter='.intval($id_newsletter)):'prepa');
if (in_array($statut,array('prepa','prop','prog')))
$baked = _request('baked');
if ($baked){
// pas de modif des contenu editoriaux si on est cuit
set_request('chapo');
set_request('texte');
set_request('patron');
}
else {
// pas de modif des contenues bruts si on est en preparation
set_request('html_email');
set_request('html_page');
set_request('texte_email');
}

// AJOUT BLOBUL 15/18/2018
// CORRIGE LE BUG d"INCOMPATIBILITE DE CEXTRA DANS LA PROGRAMMATION DES NEWSLETTER
if (_request('formulaire_action') =='programmer_newsletter'){
unset ($_POST['date_premier_jour_calendrier']);
unset ($_POST['date_dernier_jour_calendrier']);
unset ($_POST['cextra_date_premier_jour_calendrier']);
unset ($_POST['cextra_date_dernier_jour_calendrier']);
}
// FIN

$res = formulaires_editer_objet_traiter('newsletter',$id_newsletter,'',$lier_trad,$retour,$config_fonc,$row,$hidden);
if (!$baked AND $res['id_newsletter']) {
// mettre a jour les liens vers les articles selectionnes
$possibles = array('article'=>'*','rubrique'=>'*');
$liens = array();
if ($selection = _request('selection_edito')){
    foreach ($selection as $s){
        $s = explode("|",$s);
        list($objet,$id_objet) = $s;
        if (isset($possibles[$objet])){
            $liens[$objet][] = $id_objet;
        }
    }
}
include_spip("action/editer_liens");
objet_dissocier(array("newsletter"=>$res['id_newsletter']),$possibles);
if (count($liens))
    objet_associer(array("newsletter"=>$res['id_newsletter']),$liens);
// regenerer le html et texte...
// sauf si c'est une nl prog (statut=prog)
if(!_request('statut')=='prog'){
    $generer_newsletter = charger_fonction("generer_newsletter","action");
    $generer_newsletter($res['id_newsletter']);
}
}
return $res;
}
/**
 * Désérialise récursivement les valeurs d'un tableau
 *
 * Parcourt un tableau et désérialise automatiquement les valeurs sérialisées.
 * Utile pour traiter les données des champs extras ou de configuration.
 *
 * @param array $array Tableau contenant potentiellement des valeurs sérialisées
 * @return array Tableau avec valeurs désérialisées
 */
function deserialize_values($array) {
	foreach ($array as $key => $value) {
		if (is_string($value) && association_is_serialized($value)) {
			$array[$key] = unserialize($value);
		}
	}
	return $array;
}

/**
 * Vérifie si une valeur est sérialisée
 *
 * Détecte si une valeur est une chaîne sérialisée PHP.
 * Préfixée pour éviter les collisions avec d'autres plugins (SPIP 4.2+, WordPress, etc.)
 *
 * @param mixed $value Valeur à vérifier
 * @return bool True si la valeur est sérialisée, false sinon
 */
function association_is_serialized($value) {
	return (is_string($value) && ($value === 'b:0;' || @unserialize($value) !== false));
}


/**
 * Filtre pour obtenir les rôles d'association nettoyés
 *
 * Récupère les rôles bénévoles/fonctions de l'association et les nettoie
 * en supprimant les groupes vides et en filtrant optionnellement par
 * liste de fonctions autorisées.
 *
 * @param array $fonctions_benevole Liste des fonctions à conserver (vide = toutes)
 * @return array Tableau des rôles avec groupes nettoyés
 */
function filtre_roles_association($fonctions_benevole = []) {
	include_spip('inc/fonctions/roles_association');
	$roles = roles_association();

	foreach ($roles as $nom_champ => $role) {
		// Filtrer par fonctions autorisées si liste fournie
		if (!empty($fonctions_benevole) AND !in_array($nom_champ, $fonctions_benevole)) {
			unset($roles[$nom_champ]);
			continue;
		}

		// Supprimer les groupes vides
		if (isset($role['groupes']) and is_array($role['groupes'])) {
			$roles[$nom_champ]['groupes'] = array_filter($role['groupes']);
		}
	}

	return $roles;
}


// ============================================================================
// FILTRES DE COTISATIONS ET PÉRIODES
// ============================================================================

/**
 * Vérifie si le filtre "type de cotisation" doit être affiché
 *
 * Le filtre type_cotisation (basé sur les catégories) est affiché uniquement
 * si la colonne type_adherent n'existe PAS dans spip_auteurs (ancien système).
 *
 * @return bool True si le filtre doit être affiché, false sinon
 */
function filtre_a_type_cotisation(){
    $desc = sql_showtable('spip_auteurs', true);
    if ($desc) {
        if (!empty($desc['field']['type_adherent']) || !empty($desc['field']['radio_type_adherent'])) {
            return false;
        }
    }
	return true;
}

/**
 * Liste les types de cotisation depuis les catégories d'adhésion
 *
 * Récupère les valeurs distinctes du champ type_adherent depuis les catégories
 * actives, utilisé pour filtrer les cotisations par type.
 *
 * @return array Tableau associatif [type => type] des types disponibles
 */
function filtre_liste_type_cotisation(){
	if (!filtre_a_type_cotisation()) return array();

	$types = array();
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


/**
 * Convertit un type_adherent en liste d'IDs de catégories
 *
 * Retourne une chaîne d'IDs compatible avec le critère SPIP {id_categorie IN ...}.
 * Utilisé pour filtrer les cotisations par type via les catégories associées.
 *
 * @param string $type Type d'adhérent recherché (ex: "entreprise", "individuel")
 * @return string Liste d'IDs séparés par virgule (ex: "1,3,5") ou "0" si aucun
 */
function filtre_ids_categories_par_type($type){
	if (!$type) return '0';

	$result = sql_allfetsel(
		'id_categorie',
		'spip_asso_categories_adherents',
		"type_adherent = " . sql_quote($type) . " AND statut='ok'"
	);

	if (!$result || count($result) == 0) {
		return '0'; // Aucune catégorie = forcer 0 résultat
	}

	$ids = array();
	foreach ($result as $row) {
		$ids[] = intval($row['id_categorie']);
	}

	return implode(',', $ids);
}


/**
 * Liste les périodes de cotisations contenant au moins une cotisation
 *
 * Génère les périodes de cotisations (annuelles ou scolaires selon config)
 * depuis la première inscription jusqu'à aujourd'hui, et ne retourne que
 * les périodes où au moins une cotisation existe en base de données.
 *
 * Chaque période contient :
 * - libelle : "2024/2025" (scolaire) ou "2024" (annuelle)
 * - date_debut, date_fin : Bornes de la période
 * - encours : true si période actuelle
 * - nb_cotisations : Nombre de cotisations dans la période
 *
 * @param int $limite Nombre maximum de périodes à retourner (0 = illimité)
 * @param bool $avec_stats [Obsolète] Stats toujours incluses
 * @return array Tableau de périodes triées par date décroissante
 */
function filtre_liste_periodes_cotisations($limite = 0, $avec_stats = false, $type_contexte = null){
	$periodes = array();

	// Déterminer le contexte : 'entreprise' ou null/adherent
	$context = $type_contexte ? $type_contexte : 'adherent';

	// Charger metas en sécurité
	$m = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();

	// Récupérer la configuration selon le contexte
	if ($context === 'entreprise') {
		// Pour les entreprises on peut avoir une date_fixee ou une logique annuelle
		$entreprise_mode = isset($m['entreprise_validation_mode']) ? $m['entreprise_validation_mode'] : 'annuelle';
		$entreprise_date_fixee = isset($m['entreprise_validation_date']) ? $m['entreprise_validation_date'] : '';
		// Si date fixee on l'utilisera comme bornes (jj/mm)
	} else {
		// Conserver le comportement historique pour les adhérents
		$type_periode = isset($m['validite']) ? $m['validite'] : 'annuelle';
		$date_scolaire_suivante = isset($m['date_scolaire_suivante']) ? $m['date_scolaire_suivante'] : '01/06';
		$date_scolaire_nouvelle = isset($m['date_scolaire_nouvelle']) ? $m['date_scolaire_nouvelle'] : '30/09';
	}

	// Parser les dates jj/mm depuis la config (réutilisable)
	$parse_jour_mois = function($str, $fallback_j, $fallback_m) {
		if (!$str || strpos($str, '/') === false) return array($fallback_j, $fallback_m);
		$parts = explode('/', $str);
		if (count($parts) < 2) return array($fallback_j, $fallback_m);
		$j = intval($parts[0]); $m = intval($parts[1]);
		if ($j < 1 || $j > 31) $j = $fallback_j;
		if ($m < 1 || $m > 12) $m = $fallback_m;
		return array($j, $m);
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
				$date_debut = date('Y-m-d', mktime(0,0,0, $m_fix, $j_fix, $annee));
				$date_fin = date('Y-m-d', mktime(0,0,0, $m_fix, $j_fix, $annee + 1));
				$libelle = $annee . '/' . ($annee + 1);
				$periode = array(
				'annee_debut' => intval($annee),
				'annee_fin' => intval($annee + 1),
				'date_debut' => $date_debut,
				'date_fin' => $date_fin,
				'libelle' => $libelle
				);
			} else {
				// Par défaut, période annuelle calendaire
				$date_debut = date('Y-m-d', mktime(0,0,0, 1, 1, $annee));
				$date_fin = date('Y-m-d', mktime(0,0,0, 12, 31, $annee));
				$libelle = strval($annee);
				$periode = array(
				'annee_debut' => intval($annee),
				'annee_fin' => intval($annee),
				'date_debut' => $date_debut,
				'date_fin' => $date_fin,
				'libelle' => $libelle
				);
			}
		} else {
			// Comportement historique pour adhérents (scolaire ou annuel)
			if ($type_periode === 'scolaire') {
				list($j_suivante, $m_suivante) = $parse_jour_mois($date_scolaire_suivante, 1, 6);
				list($j_nouvelle, $m_nouvelle) = $parse_jour_mois($date_scolaire_nouvelle, 30, 9);
				$date_debut = date('Y-m-d', mktime(0, 0, 0, intval($m_suivante), intval($j_suivante), intval($annee)));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, intval($m_nouvelle), intval($j_nouvelle), intval($annee) + 1));
				$libelle = $annee . '/' . ($annee + 1);
				$periode = array(
				'annee_debut' => intval($annee),
				'annee_fin' => intval($annee + 1),
				'date_debut' => $date_debut,
				'date_fin' => $date_fin,
				'libelle' => $libelle
				);
			} else {
				// annuelle
				$date_debut = date('Y-m-d', mktime(0, 0, 0, 1, 1, $annee));
				$date_fin = date('Y-m-d', mktime(0, 0, 0, 12, 31, $annee));
				$libelle = strval($annee);
				$periode = array(
				'annee_debut' => intval($annee),
				'annee_fin' => intval($annee),
				'date_debut' => $date_debut,
				'date_fin' => $date_fin,
				'libelle' => $libelle
				);
			}
		}

		// Marquer si période en cours
		$aujourdhui = date('Y-m-d');
		$periode['encours'] = ($aujourdhui >= $periode['date_debut'] && $aujourdhui <= $periode['date_fin']);

		// Compter les cotisations dans cette période (id_categorie > 0 = cotisations uniquement)
		$nb_cotisations = sql_countsel(
			'spip_asso_comptes',
			"id_categorie > 0 AND date >= " . sql_quote($periode['date_debut']) . " AND date <= " . sql_quote($periode['date_fin'])
		);
		$periode['nb_cotisations'] = intval($nb_cotisations);

		// N'ajouter que les périodes avec au moins une cotisation
		if ($nb_cotisations > 0) {
			$periodes[] = $periode;
		}
	}

	// Trier par date décroissante (plus récentes d'abord)
	usort($periodes, function($a, $b) {
		return strcmp($b['date_debut'], $a['date_debut']);
	});

	// Limiter le nombre si demandé
	if ($limite > 0 && count($periodes) > $limite) {
		$periodes = array_slice($periodes, 0, $limite);
	}

	return $periodes;
}

/**
 * Retourne le libellé de la période par défaut (période en cours)
 *
 * @return string Libellé de la période encours ou chaîne vide
 */
function periode_defaut_libelle(){
	$periodes = filtre_liste_periodes_cotisations(0, false);
	if (is_array($periodes)){
		foreach ($periodes as $p){
			if (!empty($p['encours'])) {
				return isset($p['libelle']) ? $p['libelle'] : '';
			}
		}
	}
	return '';
}

/**
 * Trouve une période par son libellé
 *
 * @param string $libelle Libellé de la période (ex: "2024/2025") ou "tout"
 * @return array|null Array de la période trouvée ou null
 */
function trouver_periode_par_libelle($libelle){
	$periodes = filtre_liste_periodes_cotisations(0, false);
	if (!is_array($periodes)) return null;

	// Si "tout" ou vide, retourner la période encours ou la première
	if (!$libelle || $libelle === 'tout'){
		foreach($periodes as $p){
			if (!empty($p['encours'])) return $p;
		}
		return reset($periodes);
	}

	// Chercher par libellé exact
	foreach($periodes as $p){
		if (isset($p['libelle']) && (string)$p['libelle'] === (string)$libelle) {
			return $p;
		}
	}

	return null;
}

/**
 * Retourne la date de début d'une période
 *
 * @param string $libelle Libellé de la période ou "tout"
 * @return string Date au format Y-m-d ou '0000-00-00' si "tout"
 */
function periode_date_debut($libelle){
	if (!$libelle || $libelle === 'tout') return '0000-00-00';
	$p = trouver_periode_par_libelle($libelle);
	return $p && !empty($p['date_debut']) ? $p['date_debut'] : '0000-00-00';
}

/**
 * Retourne la date de fin d'une période
 *
 * @param string $libelle Libellé de la période ou "tout"
 * @return string Date au format Y-m-d ou '9999-12-31' si "tout"
 */
function periode_date_fin($libelle){
	if (!$libelle || $libelle === 'tout') return '9999-12-31';
	$p = trouver_periode_par_libelle($libelle);
	return $p && !empty($p['date_fin']) ? $p['date_fin'] : '9999-12-31';
}
/**
 * Récupère les filtres effectifs pour la page cotisations
 *
 * Gère la persistance des filtres en session et combine avec les paramètres URL.
 * La priorité est : URL > Session > Défaut.
 *
 * Filtres gérés :
 * - periode : Libellé de période (défaut = encours, 'tout' = vide)
 * - statut_cotisation : Statut de la cotisation (ok, attente, etc.)
 * - reinscription : Type (reinscription/inscription)
 * - id_categorie : ID de catégorie de cotisation
 * - type_cotisation : Type via catégories (entreprise, individuel, etc.)
 *
 * @return array Tableau des filtres effectifs
 */
function filtre_filtres_effectifs_cotisations(){
	if (!isset($_SESSION)) session_start();
	if (!isset($_SESSION['cotisations_filtres'])) {
		$_SESSION['cotisations_filtres'] = array();
	}

	$eff = array();

	// Helper de persistance : URL > Session > null
	$persist = function($key){
		if (array_key_exists($key, $_REQUEST)){
			$_SESSION['cotisations_filtres'][$key] = $_REQUEST[$key];
			return $_REQUEST[$key];
		}
		if (!empty($_SESSION['cotisations_filtres'][$key])) {
			return $_SESSION['cotisations_filtres'][$key];
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

	return $eff;
}

/**
 * Wrapper utilisé par les squelettes : #VAL|liste_periodes_cotisations
 * Détermine le contexte (entreprise / adherent) depuis la requête
 * et retourne la liste des périodes appropriée.
 */
function liste_periodes_cotisations($val = null) {
    // Priorité : paramètre explicite 'periode_contexte' > paramètre 'type_cotisation' > défaut 'adherent'
    $contexte = null;
    if (isset($_REQUEST['periode_contexte']) && $_REQUEST['periode_contexte']) {
        // Normaliser la valeur (éviter tableau)
        if (function_exists('filtre_scalar_val')) {
            $contexte = filtre_scalar_val($_REQUEST['periode_contexte'], null);
        } else {
            $contexte = is_array($_REQUEST['periode_contexte']) ? (string)reset($_REQUEST['periode_contexte']) : (string)$_REQUEST['periode_contexte'];
        }
    } elseif (isset($_REQUEST['type_cotisation']) && $_REQUEST['type_cotisation']) {
        $type_cot = null;
        if (function_exists('filtre_scalar_val')) {
            $type_cot = filtre_scalar_val($_REQUEST['type_cotisation'], null);
        } else {
            $type_cot = is_array($_REQUEST['type_cotisation']) ? (string)reset($_REQUEST['type_cotisation']) : (string)$_REQUEST['type_cotisation'];
        }
        // Si type_cotisation est littéralement 'entreprise' on bascule
        if ($type_cot === 'entreprise') {
            $contexte = 'entreprise';
        }
    }

    return filtre_liste_periodes_cotisations(0, false, $contexte);
}

/**
 * Filtre SPIP: scalar_val
 * Assure que la valeur fournie est une chaîne simple :
 * - si null ou vide -> retourne le défaut
 * - si tableau -> retourne le premier élément non vide (string) ou implode(',', ...) selon cas
 * - sinon retourne la valeur telle quelle
 * Utilisation dans les squelettes : (#GET{periode_contexte}|scalar_val{defaut})
 */
function filtre_scalar_val($val, $defaut = ''){
    if (is_array($val)){
        // Chercher le premier élément scalar non vide
        foreach ($val as $v) {
            if ($v === null) continue;
            if (is_array($v)) continue;
            $s = trim((string)$v);
            if ($s !== '') return $s;
        }
        // Si aucun élément scalar, tenter d'imploder
        $flat = array();
        array_walk_recursive($val, function($v) use (&$flat){ if (!is_array($v)) $flat[] = (string)$v; });
        if (count($flat)) return implode(',', $flat);
        return $defaut;
    }
    if ($val === null || $val === '') return $defaut;
    return (string)$val;
}

function filtre_association_contexte_adhesion($id_auteur, $date_reference = '') {
    include_spip('inc/cotisations');
    return association_contexte_adhesion(intval($id_auteur), $date_reference ?: null);
}
