<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/fonctions/priviliges_adherent');
include_spip('inc/fonctions/association_job_notifier_echeance');
include_spip('inc/fonctions/facteur_envoyer_recu_adhesion');

if (test_plugin_actif('gis')) {
	include_spip('inc/fonctions/facteur_envoyer_notification_gis');
}

$GLOBALS['association_cotisation_statuts'] = ['demande', 'attente', 'ok'];
$GLOBALS['association_styles_des_statuts'] = [
	'echu' => 'echu',
	'ok' => 'ok',
	'prospect' => 'prospect',
	'relance' => 'relance',
	'desactive' => 'desactive',
];
$GLOBALS['table_titre']['auteurs'] = "nom_famille AS titre, '' AS lang";

$GLOBALS['table_des_tables']['asso_categories_adherents'] = 'asso_categories_adherents';

function generer_url_asso_membre($id, $param = '', $ancre = '') {
	return generer_url_ecrire('voir_adherent', 'id_auteur=' . intval($id));
}

function generer_url_membre($id, $param = '', $ancre = '') {
	return array('auteurs', $id);
}


function association_calculer_nom_membre($civilite, $prenom, $nom_famille) {
    $res = (!empty($civilite))?$civilite.' ':'';
    $res .= (!empty($prenom))?$prenom.' ':'';
    $res .= $nom_famille;
    return $res;
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
}
