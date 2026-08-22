<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault *
 *  Copyright (c) 2010 Emmanuel Saint-James *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL. *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne. *
\***************************************************************************/
if (!defined("_ECRIRE_INC_VERSION")) return;

// Le chargeur SPIP CLI et certains chemins historiques n'ajoutent que les
// sous-dossiers surchargeables du plugin. Enregistrer aussi sa racine garantit
// la découverte canonique des modules lang/association_*.php.
if (defined('_DIR_PLUGIN_ASSOCIATION')) {
    _chemin(array(rtrim(_DIR_PLUGIN_ASSOCIATION, '/\\') . '/'));
}

// pour executer les squelettes comportant la balise Meta

include_spip('balise/meta');
// charger les metas donnees
$inc_meta = charger_fonction('meta', 'inc'); // inc_version l'a deja chargee
// PHP 8 : initialiser avant l'appel pour éviter "Undefined global variable"
if (!isset($GLOBALS['association_metas'])) {
    $GLOBALS['association_metas'] = [];
}
$inc_meta('association_metas');

# ADHERENTS #
// Le premier element indique un ancien membre
$GLOBALS['association_liste_des_statuts'] =
    array('sorti','prospect','ok','echu','relance');
$GLOBALS['association_styles_des_statuts'] = array(
    "echu" => "echu",
    "ok" => "ok",
    "prospect" => "prospect",
    "relance" => "relance",
    "desactive" => "desactive"
);
# COTISATIONS #
$GLOBALS['association_cotisation_statuts'] =
    array('demande','attente','ok');
# ACTIVITES #
$GLOBALS['association_activites_statuts'] =
    array('','ok','preinscrit','liste_attente','desinscrit');

/*Fonctions liées aux inscriptions aux événements*/
include_spip('inc/fonctions/priviliges_adherent');
include_spip('inc/fonctions/affichage_dans_activites');
include_spip('inc/fonctions/eligibilite_inscription_evenement');
include_spip('inc/fonctions/eligibilite_desinscription_evenement');
include_spip('inc/fonctions/eligibilite_modification_evenement');
include_spip('inc/fonctions/alerte_inscription_evenement');
include_spip('inc/fonctions/gestion_places');
include_spip('inc/fonctions/liste_responsables_evenement');
include_spip('inc/fonctions/ouverture_inscription_evenement');
include_spip('inc/fonctions/validation_attente_automatique');
include_spip('formulaires/inc/inscription_evenement');
include_spip('inc/fonctions/facteur_envoyer_app');
include_spip('inc/fonctions/facteur_envoyer_mail_activites');
include_spip('inc/fonctions/association_job_notifier_echeance');
include_spip('inc/fonctions/facteur_envoyer_recu_adhesion');
include_spip('inc/fonctions/facteur_envoyer_recu_participation');

include_spip('inc/fonctions/comptes');

include_spip('inc/association_log');

function parser_emails_depuis_config($valeur) {
    include_spip('inc/filtres');

    if (is_array($valeur)) {
        $parts = $valeur;
    } else {
        $valeur = is_scalar($valeur) ? trim((string) $valeur) : '';
        if ($valeur === '') {
            return false;
        }
        $parts = preg_split('/[;,\s]+/', $valeur, -1, PREG_SPLIT_NO_EMPTY);
    }

    $emails = array();
    foreach ((array) $parts as $part) {
        $email = strtolower(trim((string) $part));
        if ($email === '') {
            continue;
        }
        if (email_valide($email)) {
            $emails[] = $email;
        }
    }

    $emails = array_values(array_unique($emails));
    return !empty($emails) ? $emails : false;
}

if (test_plugin_actif('gis')){
    include_spip('inc/fonctions/facteur_envoyer_notification_gis');
}

if (!defined('_DIR_PLUGIN_ASSOCIATION_ICONES')) {
    $plugin_dir = defined('_DIR_PLUGIN_ASSOCIATION') ? _DIR_PLUGIN_ASSOCIATION : (rtrim(dirname(__FILE__), '/\\') . '/');
    define('_DIR_PLUGIN_ASSOCIATION_ICONES', $plugin_dir . 'img_pack/');
}
// NOUVEAU BOUTON PUBLIC AVEC FA
function association_bouton_public_fa($texte, $fa_class, $script, $args='') {
    return '<a href="'
        . generer_url_public($script, $args)
        . '" title="'
        . $texte
        . '" class="lien_fa"
		><i class="'
        . $fa_class
        . '"  aria-hidden="true"></i></a>';
}
// NOUVEAU BOUTON PRIVE AVEC FA
function association_bouton_ecrire_fa($texte, $fa_class, $script, $args='') {
    $fa_class= !empty($fa_class) ? $fa_class : "fa-regular fa-circle-question";
    return '<a href="'
        . generer_url_ecrire($script, $args)
        . '" title="'
        . $texte
        . '" class="lien_fa"
		><i class="'
        . $fa_class
        . '"  aria-hidden="true"></i></a>';
}
// NOUVEAU LIEN PUBLIC AVEC FA
function association_lien_public_fa($texte, $fa_class, $script, $args='') {
    return '<a href="'
        . generer_url_public($script, $args)
        . '"'
        . ' class="lien_avec_fa"
		><span class="fa_icone"><i class="'
        . $fa_class
        . '"  aria-hidden="true"></i></span><span>'
        . $texte
        . '</span></a>';
}
// NOUVEAU LIEN PRIVE AVEC FA
function association_lien_ecrire_fa($texte, $fa_class, $script, $args='') {
    $fa_class= !empty($fa_class) ? $fa_class : "fa-regular fa-circle-question";

    return '<a href="'
        . generer_url_ecrire($script, $args)
        . '"'
        . ' class="lien_avec_fa"
		><span class="fa_icone"><i class="'
        . $fa_class
        . '"  aria-hidden="true"></i></span><span>'
        . $texte
        . '</span></a>';
}
function request_statut_interne_table_destinataire_mail_collectif() {
    $statut_interne = _request('statut_interne');
    if (in_array($statut_interne, $GLOBALS['association_liste_des_statuts'] ))
        return "statut_interne=" . sql_quote($statut_interne);
    elseif ($statut_interne == 'tous')
        return "statut_interne LIKE '%' ";
    else {
        $b = array('prospect','ok','echu','relance');
        return sql_in("statut_interne", $b);
    }
}

// affichage du nom des membres
function association_calculer_nom_membre($civilite, $prenom, $nom_famille) {
    $res = (!empty($civilite))?$civilite.' ':'';
    $res .= (!empty($prenom))?$prenom.' ':'';
    $res .= $nom_famille;
    return $res;
}
//Conversion de date
//UPDATE : $date_modif ajouté pour passer du format timedate à date. Limite au 10 premier caracteres
function association_datefr($date) {
    $date_modif=substr($date, 0, 10);
    $split = explode('-',$date_modif);
    $annee = $split[0];
    $mois = $split[1];
    $jour = $split[2];
    return $jour.'/'.$mois.'/'.$annee;
}
//Conversion de l'heure
//UPDATE : Extrait l'heure d'une date
function association_heurefr($heure) {
    $heure_modif=substr($heure, 10, 15);
    $split = explode(':',$heure_modif);
    $heure = $split[0];
    $minute = $split[1];
    return $heure.':'.$minute;
}
//UPDATE : modification des vérifications
/**
 * Vérifie si une date est au format jj/mm/aaaa et si elle est valide.
 *
 * Cette fonction prend une date sous forme de chaîne de caractères,
 * vérifie si elle correspond au format jj/mm/aaaa et si elle représente
 * une date valide. Si la date n'est pas valide, elle retourne un message
 * d'erreur approprié.
 *
 * @param string $date La date à vérifier.
 * @return string|null Retourne un message d'erreur si la date n'est pas valide,
 *                     sinon retourne null.
 */
function association_verifier_date($date) {
    if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
        return _T('association:erreur_format_date');
    }
    list($jour, $mois, $annee) = explode("/", $date);
    if (!checkdate($mois, $jour, $annee)) {
        return _T('association:erreur_date');
    }
    return null;
}


/**
 * Compare deux dates et heures en fonction d'un signe de comparaison spécifié.
 *
 * Cette fonction prend deux dates et heures au format chaîne et un signe de comparaison,
 * puis retourne le résultat de la comparaison des deux dates.
 *
 * @param string $datetime1 La première date et heure à comparer.
 * @param string $datetime2 La deuxième date et heure à comparer.
 * @param string $signe Le signe de comparaison à utiliser ('<', '>', '>=', '<=').
 * @return bool Le résultat de la comparaison des deux dates.
 */
function association_comparateur_date($datetime1, $datetime2, $signe) {
    $format = "%d-%d-%d %d:%d:%d";

    $mktime_1 = !empty($datetime1) ? strtotime($datetime1) : time();
    $mktime_2 = strtotime($datetime2);

    $comparisons = array(
        '<' => fn($a, $b) => $a < $b,
        '>' => fn($a, $b) => $a > $b,
        '>=' => fn($a, $b) => $a >= $b,
        '<=' => fn($a, $b) => $a <= $b,
    );

    return $comparisons[$signe]($mktime_1, $mktime_2);
}
function NbJours($debut, $fin, $absolu = true) {
    $timestamp_debut = strtotime($debut);
    $timestamp_fin = strtotime($fin);
    if ($timestamp_debut === false || $timestamp_fin === false) {
        association_log('association', 'NbJours: date invalide debut=' . $debut . ' fin=' . $fin, 'erreur');
        return null;
    }

    try {
        $date_debut = new DateTimeImmutable(date('Y-m-d', $timestamp_debut));
        $date_fin = new DateTimeImmutable(date('Y-m-d', $timestamp_fin));
    } catch (Exception $e) {
        association_log('association', 'NbJours: date invalide debut=' . $debut . ' fin=' . $fin, 'erreur');
        return null;
    }

    $jours = intval($date_debut->diff($date_fin)->format('%r%a'));
    return $absolu ? abs($jours) : $jours;
}
function association_nbrefr($montant) {
    $montant = number_format(floatval($montant), 2, ',', ' ');
    return $montant;
}
/* prend en parametre le nom de l'argument a chercher dans _request et retourne un float */
// TODO: rename into str_argument_to_float
function association_recupere_montant ($valeur) {
    if ($valeur != '') {
        $valeur = str_replace(" ", "", $valeur); /* suppprime les espaces separateurs de milliers */
        $valeur = str_replace(",", ".", $valeur); /* convertit les , en . */
        $valeur = floatval($valeur);
    } else $valeur = 0.0;
    return $valeur;
}
//Affichage du message indiquant la date
function association_date_du_jour($heure=false) {
    return '<p>'.($heure ? _T('association:date_du_jour_heure') : _T('association:date_du_jour')).'</p>';
}
function association_flottant($s) {
    return number_format(floatval($s), 2, ',', ' ');
}
function association_telfr($n) {
    $n = preg_replace('/\D/', '', $n);
    if (!intval($n)) return '';
    return preg_replace('/(\d\d)/', '\1&nbsp;', $n);
}
/*
 * Pourquoi supprimer toutes les table ???
 * A ne surtout pas réactiver..
 */
/*function association_delete_tables($flux){
	spip_unlink(cache_meta('association_metas'));
}*/
// Pour ne pas avoir a ecrire le prefixe "spip_" dans les squelettes etc
// (cf trouver_table)
global $table_des_tables;
$table_des_tables['asso_dons'] = 'asso_dons';
$table_des_tables['asso_ventes'] = 'asso_ventes';
$table_des_tables['asso_comptes'] = 'asso_comptes';
$table_des_tables['comptes'] = 'asso_comptes';
$table_des_tables['asso_categories_adherents'] = 'asso_categories_adherents';
$table_des_tables['asso_categories_activites'] = 'asso_categories_activites';
$table_des_tables['asso_plan'] = 'asso_plan';
$table_des_tables['asso_ressources'] = 'asso_ressources';
$table_des_tables['asso_prets'] = 'asso_prets';
$table_des_tables['asso_activites'] = 'asso_activites';
$table_des_tables['auteurs'] = 'auteurs';
//pour cette table on passe par spip_auteurs (base auteurs de spip)
$table_des_tables['association_metas'] = 'association_metas';
$table_des_tables['asso_destination'] = 'asso_destination';
$table_des_tables['asso_destination_op'] = 'asso_destination_op';
// Pour que les raccourcis ci-dessous heritent d'une zone de clic pertinente
global $table_titre;
$table_titre['auteurs']= "nom_famille AS titre, '' AS lang";
$table_titre['asso_dons']= "CONCAT('don ', id_don) AS titre, '' AS lang";
// Toujours charger la description des tables (a ameliorer)
//include _DIR_PLUGIN_ASSOCIATION . 'base/association.php';
// Raccourcis
// Les tables ayant 2 prefixes ("spip_asso_")
// le raccourci "don" implique de declarer le raccourci "asso_don" etc.
function generer_url_asso_don($id, $param='', $ancre='') {
    return  generer_url_ecrire('edit_don', "id=" . intval($id));
}
function generer_url_don($id, $param='', $ancre='') {
    return  array('asso_don', $id);
}
function generer_url_asso_membre($id, $param='', $ancre='') {
    return  generer_url_ecrire('voir_adherent', "id_auteur=" . intval($id));
}
function generer_url_membre($id, $param='', $ancre='') {
    return  array('auteurs', $id);
}
function generer_url_asso_vente($id, $param='', $ancre='') {
    return  generer_url_ecrire('edit_vente', "id=" . intval($id));
}
function generer_url_vente($id, $param='', $ancre='') {
    return  array('asso_vente', $id);
}

function adherent_correction_statut(){
    # Recherche et correction des status des adhérents (évite de se retrouver avec une liste vide lors de l'installation)
    $auteurs_query = sql_select('statut_interne, id_auteur', 'spip_auteurs', "statut_interne=''");
    if($auteurs_query){
        while ($data = sql_fetch($auteurs_query)) {
            $id_auteur = $data['id_auteur'];
            //echo $id_auteur;
            //print_r($data);
            sql_updateq('spip_auteurs',array(
                "statut_interne" => 'prospect'),
                        "id_auteur=$id_auteur");
        }
    }
};
/**
 * Détermine les droits d'un auteur sur les événements.
 *
 * Cette fonction analyse les droits d'un auteur en fonction de son statut
 * et des événements auxquels il est associé. Elle retourne un tableau contenant
 * les événements accessibles, le type d'accès, et des informations supplémentaires.
 *
 * @param int $id_auteur L'identifiant de l'auteur.
 * @param int|string $id_evenement (Optionnel) L'identifiant de l'événement. Si vide, tous les événements sont considérés.
 * @return array Un tableau contenant :
 *               - array $activites_array : Liste des identifiants des événements accessibles.
 *               - string $type_auteur : Type d'accès de l'auteur ('complet', 'restreint', 'restreint_wrong', 'public').
 *               - array $id_result : Informations supplémentaires sur les droits :
 *                   - bool $id_result['affichage'] : Indique si l'auteur peut voir les événements.
 *                   - string $id_result['condition'] : Condition SQL pour filtrer les événements.
 *                   - array $id_result['array'] : Liste des identifiants des événements accessibles.
 */
function droit_auteur_evenements($id_auteur, $id_evenement=''){
    // Création d'un tableau pour stocker les événements accessibles
    $activites_array = array();

    // Récupération des informations sur l'auteur
    $id_type_query = $query = sql_fetsel('*', 'spip_auteurs', "id_auteur=$id_auteur");
    $statut = $id_type_query['statut'];

    // Gestion des droits en fonction du statut de l'auteur
    if ($statut == '0minirezo'){
        // Administrateur complet (non-restreint)
        include_spip('inc/autoriser');
        $rubriques_restreintes = liste_rubriques_auteur($id_auteur);

        if (!empty($rubriques_restreintes)) {
            // Admin restreint SPIP : récupère les événements de ses rubriques ET des articles dont il est auteur
            $type_auteur = 'restreint';
            $id_result = array();

            // Récupérer les articles liés aux rubriques restreintes
            $ids_rubriques = is_array($rubriques_restreintes) ? implode(',', array_keys($rubriques_restreintes)) : $rubriques_restreintes;
            $query_articles = sql_select(
                 "DISTINCT a.id_evenement",
                 "spip_evenements AS a INNER JOIN spip_articles AS b ON (a.id_article = b.id_article) LEFT JOIN spip_rubriques AS r ON (b.id_rubrique = r.id_rubrique)",
                 "a.inscription=1 AND (b.id_rubrique IN ($ids_rubriques) OR r.id_parent IN ($ids_rubriques))"
             );

            if (sql_count($query_articles) > 0) {
                while ($data = sql_fetch($query_articles)) {
                    $activites_array[] = $data["id_evenement"];
                }
            }

            // Ajouter aussi les événements des articles dont l'admin est auteur
            $query_auteur = sql_select(
                 "DISTINCT a.id_evenement",
                 "spip_evenements AS a INNER JOIN spip_articles AS b ON (a.id_article = b.id_article) INNER JOIN spip_auteurs_liens AS l ON (b.id_article = l.id_objet AND l.objet = 'article')",
                 "a.inscription=1 AND l.id_auteur = $id_auteur"
             );

            if (sql_count($query_auteur) > 0) {
                while ($data = sql_fetch($query_auteur)) {
                    $activites_array[] = $data["id_evenement"];
                }
            }

            $activites_array = array_unique($activites_array);
            if (!empty($activites_array)) {
                $id_result['affichage'] = true;
                $id_result['condition'] = 'AND id_evenement IN (' . implode(',', $activites_array) . ')';
                $id_result['array'] = $activites_array;
            } else {
                $id_result['affichage'] = false;
                $id_result['condition'] = '';
                $id_result['array'] = array();
            }
        } else {
            // Admin complet (non-restreint)
            $type_auteur = 'complet';
            $id_result['affichage'] = true;
            $id_result['condition'] = '';
        }
    } elseif ($statut == '1comite'){
        // Rédacteur avec accès restreint
        $argument_id = (!empty($id_evenement)) ? 'AND a.id_evenement=' . $id_evenement : '';
        $id_result = array();

        // Récupération des événements liés à l'auteur
        $query_articles_liens = sql_select("*", "spip_auteurs_liens as b INNER JOIN spip_evenements AS a ON (a.id_article = b.id_objet AND inscription=1)", " b.id_auteur=$id_auteur AND b.objet='article'");
        if(sql_count($query_articles_liens)){
            while ($data = sql_fetch($query_articles_liens)) {
                $activites_array[] = $data["id_evenement"];
            }
            $id_result['affichage'] = true;
            $id_result['condition'] = 'AND id_evenement IN (' .implode(',', $activites_array). ')';
            $id_result['array'] = $activites_array;
        } else {
            $id_result['affichage'] = false;
            $id_result['condition'] = '';
            $id_result['array'] = array();
        }

        // Détermination du type d'accès
        $type_auteur = (!empty($activites_array)) ? 'restreint' : 'restreint_wrong';
    } elseif ($statut == '6forum'){
        // Utilisateur public
        $type_auteur = 'public';
    };

    // Retourne les droits sous forme de tableau
    return array($activites_array, $type_auteur, $id_result);
}



@$GLOBALS['spip_pipeline']['jqueryui_plugins'] .= "|insert_jqueryui";
function insert_jqueryui($flux) {
	$flux[] = 'jquery.ui.autocomplete';
	return $flux;
}

/**
* Récupère les responsables d'un événement ou d'un article.
*
* Cette fonction permet de récupérer les identifiants des responsables associés
* à un événement ou à un article, en fonction des paramètres fournis.
*
* @param string $id_evenement L'identifiant de l'événement. Si 'new', la fonction
*                              traite un nouvel événement.
* @param string $id_article   L'identifiant de l'article. Utilisé si l'événement
*                              est nouveau.
* @return array|false Retourne un tableau contenant les informations des responsables
*                     ou `false` si aucun responsable n'est trouvé.
*/
function responsables_evenement($id_evenement='new',$id_article=''){
    $id_result = array();
    $id_result['auteur_array_defaut'] = '';
    $id_evenement = !empty($id_evenement) ? $id_evenement : _request('id_evenement');
    /* Si un événement existant est spécifié */
    if(!empty($id_evenement) AND ($id_evenement != 'new')) {

        /* Recherche des auteurs liés à l'article de l'événement */
        $query_liste_responsables_evenement_article = sql_select("*", "(spip_auteurs_liens as lien RIGHT JOIN spip_evenements AS a ON (a.id_article = lien.id_objet AND a.id_evenement='$id_evenement')) RIGHT JOIN spip_auteurs as c ON (c.id_auteur=lien.id_auteur AND c.statut_interne='ok')", "lien.objet='article'");

        /* Recherche des responsables dans la base de données pour l'événement */
        $query_evenement = sql_fetsel('responsables','spip_evenements',"id_evenement=$id_evenement");

        /* Formatage des résultats pour les fonctions dépendantes */
        if (sql_count($query_liste_responsables_evenement_article) > 0){
            $auteur_array = array();
            while ($data = sql_fetch($query_liste_responsables_evenement_article)) {
                $auteur_array[] = $data["id_auteur"];
            }
            $id_result['auteur_array'] = $auteur_array;
            $id_result['auteur_array_defaut'] = $query_evenement['responsables'];
            $implode =  'id_auteur IN (' . implode(',', $auteur_array) . ')';
            $id_result['condition'] = sql_select( '*', 'spip_auteurs', "$implode");
            return $id_result;
        } else{
            return false;
        }

    /* Si un nouvel événement est spécifié avec un article */
    }elseif($id_evenement == 'new' AND !empty($id_article)) {

        /* Recherche des auteurs liés à l'article */
        $query_liste_responsables_evenement_article = sql_select("*", "spip_auteurs AS auteurs, spip_auteurs_liens AS lien","lien.objet= 'article' AND lien.id_objet=". $id_article ." AND auteurs.id_auteur=lien.id_auteur  AND  auteurs.statut_interne='ok' ");

        if (sql_count($query_liste_responsables_evenement_article) > 0){
            $auteur_array = array();
            while ($data = sql_fetch($query_liste_responsables_evenement_article)) {
                $auteur_array[] = $data["id_auteur"];
            }
            $id_result['auteur_array'] = $auteur_array;
            $id_result['auteur_array_defaut'] = $auteur_array;
            $implode =  'id_auteur IN (' . implode(',', $auteur_array) . ')';
            $id_result['condition'] = sql_select( '*', 'spip_auteurs', "$implode");
            return $id_result;
        }else{
            return false;
        }
    }
    return false;
}

