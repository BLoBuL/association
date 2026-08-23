<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

foreach (array(
	'affichage_dans_activites', 'eligibilite_inscription_evenement',
	'eligibilite_desinscription_evenement', 'eligibilite_modification_evenement',
	'alerte_inscription_evenement', 'gestion_places', 'liste_responsables_evenement',
	'ouverture_inscription_evenement', 'validation_attente_automatique',
	'facteur_envoyer_mail_activites', 'facteur_envoyer_recu_participation',
) as $bibliotheque) {
	include_spip('inc/fonctions/' . $bibliotheque);
}
include_spip('formulaires/inc/inscription_evenement');

$GLOBALS['association_activites_statuts'] = ['', 'ok', 'preinscrit', 'liste_attente', 'desinscrit'];

$GLOBALS['table_des_tables']['asso_categories_activites'] = 'asso_categories_activites';
$GLOBALS['table_des_tables']['asso_activites'] = 'asso_activites';


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
