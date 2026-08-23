<?php
/***************************************************************************
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
/*
A faire :
- Mettre les villes en MAJ
- Mettre les noms de famille en MAJ
- Forcer les codes postaux en format text "=""12345"""
*/
if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/charsets');
include_spip('inc/filtres');
include_spip('inc/texte');
function action_exporter_adherents_csv_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	if (!autoriser('associer', 'adherents')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		// Preparation
		$preas = $entete = $case = $valeur_case = array();
		$titre_perso = $input = '';
		// Array auteurs concernés
		$ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) _request('id_auteur_boucle'))))));
		$filtreAuteur = implode(',', $ids);
		//Recupération des champs
		$titre_perso = _request('csv_name');
		$input = _request('csv');
		if (is_array($input) && $ids) {
			foreach ($input as $key => $value) {
				array_push($entete, $value);
				array_push($preas, $key);
			}
			$colonne_select = implode(', ', $preas);
			$base = sql_select($colonne_select, 'spip_auteurs', "id_auteur IN ($filtreAuteur)");
			$iextras = unserialize($GLOBALS['meta']['champs_extras_spip_auteurs']);
			// 1 / Auteur par auteur
			while ($row = sql_fetch($base)) {
				// 2 / Keys demandées
				foreach($preas as $key => $value){
                    if(($value == 'code_postal') AND  substr($row[$value], 0, 1) == "0"){
						$value_value = '="' . $row[$value] . '"';
                    }elseif($value == 'nom_famille' OR $value == 'nom_conjoint'){
						$value_value = strtoupper($row[$value]);
                    }else{
                        $value_value = $row[$value];
                    } 
      
					// 3 / Vérifications si il s'agit d'une selection dans une liste
					foreach ($iextras as $viextras){
						if($viextras['options']['nom'] == $value AND $viextras['saisie'] == 'selection'){                            
							$result = saisies_chaine2tableau($viextras['options']['datas']);
							/*$value_propre = mb_convert_encoding(array_column($result, $value_value)[0], "UTF-16LE", "UTF-8");*/
                            $value_propre = array_column($result, $value_value)[0];
						}
					}                     
					// Sinon on récupere la value
					$value_propre = (empty($value_propre))? $value_value : $value_propre;
					// Puis on l'enregistre
					$valeur_case[] = $value_propre;
					$value_propre = '';
				}
				// Enfin on enregistre un array de value pour un auteur, à la suite des autres auteurs
				array_push($case, $valeur_case);
				$valeur_case 	= array();
			}
			// Titre perso
			if($titre_perso)
				$titre = $titre_perso."-".$GLOBALS['meta']['nom_site']."-".date('Y-m-d');
			else
				$titre = _T('association:titre_csv')."-".$GLOBALS['meta']['nom_site']."-".date('Y-m-d');
			// chargement de la fonction
			$exporter_csv = charger_fonction('exporter_csv', 'inc/');
			// Creation du CSV et export
			$exporter_csv($titre, $case, ",", $entete);
		}
	}
}
