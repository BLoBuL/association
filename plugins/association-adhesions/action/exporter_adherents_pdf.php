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

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('pdf/extends');

function action_exporter_adherents_pdf_dist()
{
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	if (!autoriser('associer', 'adherents')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

	//Etape 1 = recherche des occurence dans inscription 3

	$search = array ();
	$affichage_table = sql_select('*', 'spip_meta', "nom = 'inscription3'");
	while ($row = sql_fetch($affichage_table)) {
		foreach ($row as $k => $v) {
			if ($k == 'valeur'){
				$a = @unserialize($v);
				foreach ($a as $key => $value) {
					if($value == 'on'){						
						$_table = "_table";
						$_nocreation = "_nocreation";
						if(preg_match('#'.$_table.'#', $key)){
							//echo $key;
							$newstring = str_replace($_table, "", $key);
							$search [] = str_replace($_nocreation, "", $newstring);
						}
					}
				}
			}
		}
	}


	// Etape 2  = recherche des correspondances dans champs extras
	$match = false;

	$arraynom = array();

	$correspondance = sql_select('*', 'spip_meta', "nom = 'champs_extras_spip_auteurs'");
	
	while ($row = sql_fetch($correspondance)) {
		foreach ($row as $k => $v) {
			if ($k == 'valeur'){
				$a = @unserialize($v);
				foreach ($a as $key => $value) {
					foreach ($value as $key1 => $value1) {
						if ($key1 == 'options'){
							foreach ($search as $ksearch => $vsearch) {
								foreach ($value1 as $koption => $voption) {
									if($koption == 'nom'){
										if($vsearch == $voption){
											$match = true;
											$ar1 = $voption;
											//echo $voption;
										}										
									}
									if ($koption == "label") {
										if ($match == true){
											$arraynom[$ar1] = $voption;
											$match = false;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	// Etape 3  = Création du PDF
	include_spip('pdf/extends');
	$pdf=new PDF();

	//UPDATE Choix de son titre, avec compatibilité des caractères
	$selection_info = $_REQUEST['pdf_name'];
	$pdf->titre = utf8_decode($selection_info);
	
	$pdf->Open();
	$pdf->AddPage();
	
	//UPDATE compatibilité des caractères
	//On définit les colonnes (champs,largeur,intitulé,alignement)
	$champs = sql_select('*', 'spip_auteurs', '', '', '', '1');

	while ($row = sql_fetch($champs)) {
		$test = $row;
	}

	$sent = _request('champs');
	foreach ($test as $k => $v) {

	  if ($sent[$k]=='on') {
	  	//$date = substr($k, 0, 10);
	    $p = ($type===false) ? 'R' : (($type==0) ? 'L' : 'C');
	    $n = ($type===false) ? 15 : (($type==0) ? 30 : '12%');

	    foreach ($arraynom as $knom => $vnom) {
			if ($k == $knom){
				$trad = $vnom;
			}
		}

		// Ajout de la colone et de son nom 
	    $pdf->AddCol($k, $n, $trad, $p);
	  }
	}

	//Permet un filtre selectif des auteurs selon la recherche
	$filtreAuteur = $_REQUEST['id_auteur_boucle'];
	$prop=array(
		'HeaderColor'=>array(255,150,100),
		'color1'=>array(224,235,255),
		'color2'=>array(255,255,255),
		'padding'=>1,
		'align'=>'C'
	);

	$order = 'id_auteur';
	if ($sent['nom_famille']=='on')
	  $order = 'nom_famille' . ",$order";
	$pdf->Query(sql_select('*','spip_auteurs', 'id_auteur IN ('.mysql_real_escape_string($filtreAuteur).')', '', $order), $prop);
	$pdf->Output();
	}
}

