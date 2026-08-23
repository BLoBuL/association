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

include_spip('inc/association_log');



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


// affichage du nom des membres

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
$table_des_tables['auteurs'] = 'auteurs';
//pour cette table on passe par spip_auteurs (base auteurs de spip)
$table_des_tables['association_metas'] = 'association_metas';
// Pour que les raccourcis ci-dessous heritent d'une zone de clic pertinente
global $table_titre;
$table_titre['auteurs']= "nom_famille AS titre, '' AS lang";
// Toujours charger la description des tables (a ameliorer)
//include _DIR_PLUGIN_ASSOCIATION . 'base/association.php';
// Raccourcis
// Les tables ayant 2 prefixes ("spip_asso_")
// le raccourci "don" implique de declarer le raccourci "asso_don" etc.

;
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
