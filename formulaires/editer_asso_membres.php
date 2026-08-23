<?php

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
function formulaires_editer_asso_membres_charger_dist($id_auteur) {
	/* cet appel va charger dans $contexte tous les champs de la table spip_auteurs associes a l'id_auteur passe en param */
	$contexte = formulaires_editer_objet_charger('auteurs', $id_auteur, '', '',  generer_url_ecrire('adherents'), '');

	/* on a ajoute dans le contexte les metas de gestion optionnelle des champs Civilite, Prenom et Ref. Interne */
	$contexte['meta_civilite'] = $GLOBALS['association_metas']['civilite'];
	$contexte['meta_prenom'] = $GLOBALS['association_metas']['prenom'];
	$contexte['meta_id_asso'] = $GLOBALS['association_metas']['id_asso'];
	return $contexte;
}

function formulaires_editer_asso_membres_verifier_dist($id_auteur) {

	//UPDATE controle de la date en mode automatise
	
	foreach ($_POST as $champ => $mot) {
		if (!is_string($mot)) {
			continue;
		}

		if ($_POST[$champ] == '') {
		} else {
			$result = substr($champ, 0, 5);
			$testResult = 'date_';
			if ( $result == $testResult ){
				if ($erreur_validite = association_verifier_date(_request($champ))) {
					$erreurs[$champ] = _request($champ)."&nbsp;:&nbsp;".$erreur_validite;
				}
				if (count($erreurs)) {
				$erreurs['message_erreur'] = _T('association:erreur_titre');
				}
			}
		}
	}
	return $erreurs;
}

function formulaires_editer_asso_membres_traiter($id_auteur) {
	//UPDATE Modification de la date en sortie pour la générer au format SQL "timedate" "yyyy-mm-jj 00:00:00"
	
	$checkboxArray = array();
	
	foreach ($_POST as $champ => $mot) {
		if (!is_string($mot)) {
			continue;
		}

		//verification champs vide ou null
		if ($_POST[$champ] == ''){ 

			if ( substr($champ, 0, 8) == 'oui_non_' ){
				continue;
			}
			$resultatNull = null;
			$_POST[$champ] = $resultatNull;
		}else{

			//convertir les date au format timedate
			if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $mot)) {
	  		$otherDateMod = str_replace('/', '-', $mot);
	  		$dateFinale = date("Y-m-d H:i:s", strtotime($otherDateMod));
	  		$_POST[$champ] = $dateFinale;
		  	}

		  	//Conversion pour la checkbox
		  	$checkbox = substr($champ, 0, 8);
		  	$testCheckbox = 'checkbox';
		  	if ( $checkbox == $testCheckbox ){
				foreach ($mot as $checkboxResult) {
					$checkboxArray[] = $checkboxResult;
				}
			$checkboxFinal = join(",",$checkboxArray);
			$_POST[$champ] = $checkboxFinal;
			}
			
		}
	}
	return formulaires_editer_objet_traiter('auteurs', $id_auteur, '', '',  generer_url_ecrire('adherents'), '');
}
