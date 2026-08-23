<?php
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2015                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if (!defined('_ECRIRE_INC_VERSION')) return;
include_spip('inc/charsets');
include_spip('inc/filtres');
include_spip('inc/texte');

/**
 * Génère le formulaire d'export CSV des adhérents selon les critères donnés.
 *
 * @param string|false $criteres_sql Critères SQL optionnels pour filtrer les adhérents.
 * @return string HTML du formulaire généré.
 */
function generer_export_csv_adherents($criteres_sql = false){
    // Critères SQL minimums pour exclure certains utilisateurs
    $criteres_sql_minimum = "webmestre= 'non' AND statut != '5poubelle'";

    // Combine les critères SQL optionnels avec les critères minimums
    if(!empty($criteres_sql)){
        $criteres_sql_final = $criteres_sql . ' AND  ' . $criteres_sql_minimum ;
    } else {
        $criteres_sql_final = $criteres_sql_minimum;
    }
    // Requête pour récupérer les ID des auteurs correspondant aux critères
    $query = sql_select("id_auteur", 'spip_auteurs', $criteres_sql_final , '', "nom_famille ");
    $auteurs = '';
    $id_auteur_tboucle = '';
    $num_rows = mysqli_num_rows($query);
    $compteur = 0;

    // Boucle pour construire une liste d'ID d'auteurs séparés par des virgules
    while ($data = sql_fetch($query)) {
        $id_auteur = $data['id_auteur'];
        $compteur = $compteur +1;
        $id_auteur_tboucle .= ($num_rows == $compteur) ? $id_auteur : ''.$id_auteur. ',';
    }

    // Recherche des champs activés dans le plugin Inscription3
    $search = array ();
    $affichage_table = sql_select('*', 'spip_meta', "nom = 'inscription3'");
    while ($row = sql_fetch($affichage_table)) {
        $a = @unserialize($row['valeur']);
        foreach ($a as $key => $value) {
            if($value == 'on'){
                $_table = "_table";
                $fieldset = "fieldset_";
                $replace_string = array('_table', '_nocreation');
                if(preg_match('#'.$_table.'#', $key)){
                    $search [] = str_replace($replace_string, "", $key);
                }
            }
        }
    }

    // Recherche des correspondances dans les champs extras
    $match = false;
    $arraynom = array();
    $arraynom['id_auteur'] = '#ID';
    $arraynom['statut'] = 'Statut';
    $arraynom['email'] = 'Email';
    $correspondance = sql_select('*', 'spip_meta', "nom = 'champs_extras_spip_auteurs'");
    while ($row = sql_fetch($correspondance)) {
        $a = @unserialize($row['valeur']);
        foreach ($a as $value1) {
            if(preg_match('#'.$fieldset.'#', $value1['options']['nom']) AND !empty($value1['saisies'])){
                foreach ($value1['saisies'] as $value2) {
                    if(in_array($value2['options']['nom'], $search)){
                        $arraynom[$value2['options']['nom']] = $value2['options']['label'];
                    }
                }
            } elseif(in_array($value1['options']['nom'], $search)){
                $arraynom[$value1['options']['nom']] = $value1['options']['label'];
            }
        }
    }

    // Génération du formulaire HTML pour l'export CSV
    $res = '<div class="input_text"><label for="csv_name_id">' ._T('association_adhesions:nommer_selection_csv'). '</label><input type="text" id="CSV_name_id" name="csv_name" value=""></div><h4>' ._T('association_adhesions:information_a_inclure'). '</h4>';
    foreach ($arraynom as $k => $v) {
        $label = ($v) ? typo($v) : $k;
        $res .= "<div class='input_checkbox $k'><input type='checkbox' id='csv[$k]_id' name='csv[$k]' value='$k' /><label for='csv[$k]_id'>$label</label></div>";
    }
    $res .= "<div id='input_id_auteur_2' style='clear:both'></div>";
    $res .='<input class="id_auteur_boucle n2" name="id_auteur_boucle" type="hidden" value='.$id_auteur_tboucle.'>';

    // Retourne le formulaire généré
	include_spip('inc/securiser_action');
	$action = generer_action_auteur('exporter_adherents_csv', 'export', generer_url_ecrire('adherents'));
	return '<form method="post" action="' . attribut_html($action) . '"><div>'
		. $res
		. '<p class="boutons"><button type="submit" class="submit">' . _T('association_adhesions:bouton_generer_csv') . '</button></p>'
		. '</div></form>';
}
