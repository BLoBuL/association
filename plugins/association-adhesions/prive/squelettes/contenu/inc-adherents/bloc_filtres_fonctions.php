<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

function filtre_compteur_adherents(){
    // Charger la fonction helper
    include_spip('prive/squelettes/contenu/adherents_fonctions');
    $config_compte_secondaire = est_actif_gestion_comptes_secondaires() ? 'oui' : 'non';

    $compteur_adherents = array();
    $criteres_sql = array(
                    "webmestre= 'non'",
                    "statut != '5poubelle'",
                    "auteur_compte_principal = false"
                );
    $query_auteurs = sql_select('statut,statut_interne,auteur_compte_principal', 'spip_auteurs',$criteres_sql);

    $compteur_adherents['ok'] = $compteur_adherents['ok_secondaire'] = $compteur_adherents['ok_titulaire'] =
    $compteur_adherents['prospect'] = $compteur_adherents['prospect_titulaire'] = $compteur_adherents['prospect_secondaire'] = 
    $compteur_adherents['echu'] = $compteur_adherents['echu_titulaire'] = $compteur_adherents['echu_secondaire'] = 
    $compteur_adherents['relance'] = $compteur_adherents['relance_titulaire'] = $compteur_adherents['relance_secondaire'] = 
    $compteur_adherents['8aconfirmer'] = $compteur_adherents['5poubelle'] = $compteur_adherents['total'] = 0;
    while ($res = sql_fetch($query_auteurs)) {
         $compteur_adherents['total'] = $compteur_adherents['total'] +1;
        if( !in_array($res['statut'],array('5poubelle,8aconfirmer')) AND $res['statut_interne'] == 'ok' ){
        $compteur_adherents['ok']= $compteur_adherents['ok'] +1;
            if($res['auteur_compte_principal'] >= 1 AND $config_compte_secondaire == 'oui') {
                $compteur_adherents['ok_secondaire'] = $compteur_adherents['ok_secondaire'] +1;
            }elseif(empty($res['auteur_compte_principal']) AND $config_compte_secondaire == 'oui'){$compteur_adherents['ok_titulaire'] = $compteur_adherents['ok_titulaire']+1;
            }                                        
        }elseif( !in_array($res['statut'],array('5poubelle,8aconfirmer')) AND $res['statut_interne'] == 'prospect' ){
        $compteur_adherents['prospect']= $compteur_adherents['prospect'] +1;
            if($res['auteur_compte_principal'] >= 1 AND $config_compte_secondaire == 'oui') {
                $compteur_adherents['prospect_secondaire'] = $compteur_adherents['prospect_secondaire'] +1;
            }elseif(empty($res['auteur_compte_principal']) AND $config_compte_secondaire == 'oui'){$compteur_adherents['prospect_titulaire'] = $compteur_adherents['prospect_titulaire']+1;
            }
        }elseif( !in_array($res['statut'],array('5poubelle,8aconfirmer')) AND $res['statut_interne'] == 'echu' ){
        $compteur_adherents['echu']= $compteur_adherents['echu'] +1;
            if($res['auteur_compte_principal'] >= 1 AND $config_compte_secondaire == 'oui') {
                $compteur_adherents['echu_secondaire'] = $compteur_adherents['echu_secondaire'] +1;
            }elseif(empty($res['auteur_compte_principal']) AND $config_compte_secondaire == 'oui'){$compteur_adherents['echu_titulaire'] = $compteur_adherents['echu_titulaire']+1;
            }
        }elseif( !in_array($res['statut'],array('5poubelle,8aconfirmer')) AND $res['statut_interne'] == 'relance' ){
        $compteur_adherents['relance']= $compteur_adherents['relance'] +1;
            if($res['auteur_compte_principal'] >= 1 AND $config_compte_secondaire == 'oui') {
                $compteur_adherents['relance_secondaire'] = $compteur_adherents['relance_secondaire'] +1;
            }elseif(empty($res['auteur_compte_principal']) AND $config_compte_secondaire == 'oui'){$compteur_adherents['relance_titulaire'] = $compteur_adherents['relance_titulaire']+1;
            }
        }elseif( !in_array($res['statut'],array('5poubelle,8aconfirmer')) AND $res['statut_interne'] == 'sorti' ){
            $compteur_adherents['sorti']= $compteur_adherents['sorti'] +1;                        
        }elseif($res['statut'] == '8aconfirmer'){
            $compteur_adherents['8aconfirmer']= $compteur_adherents['8aconfirmer'] +1;
        }elseif($res['statut'] == '5poubelle'){
            $compteur_adherents['5poubelle']= $compteur_adherents['5poubelle'] +1;
        }                                
    }
    return array_filter($compteur_adherents);
}


function filtre_compte_titulaire($statut_interne){

    $res = sql_select('statut', 'spip_auteurs', "statut_interne= '$statut_interne' AND auteur_compte_principal is NULL");

    $test = sql_count($res);
    return $test;
}
function filtre_compte_secondaire($statut_interne){

    $res = sql_select('statut', 'spip_auteurs', "statut_interne= '$statut_interne' AND auteur_compte_principal > 1");

    $test = sql_count($res);
    return $test;
}