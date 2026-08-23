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

function action_exporter_activite_pdf_dist()
{
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_evenement = (int) $securiser_action();
	if (!autoriser('associer', 'activites')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

	//UPDATE Passage � l'encodage UTF8 (� modifier selon la langue ou trouver un meilleur moyen)
	$query = sql_select("*", "spip_evenements", "id_evenement=$id_evenement") ;
	while ($data = sql_fetch($query)) {
		$nom_evenement = utf8_decode( $data['titre'] );
	}

	$pdf=new PDF();	
	$pdf->titre = _T('association_evenements:activite_titre_inscriptions_activites');
	$pdf->titre =  $nom_evenement;
	$pdf->Open();
	$pdf->AddPage();
	//On d�finit les colonnes (champs,largeur,intitul�,alignement)
	$pdf->AddCol('id_activite',10,'ID','R');
	$pdf->AddCol('nom',50,_T('association_evenements:activite_libelle_nomcomplet'),'L');
	$pdf->AddCol('id_adherent',20,'N� membre','R');
	$pdf->AddCol('membres',50,'Membres','L');
	$pdf->AddCol('non_membres',50,'Non membres','L');
	$pdf->AddCol('inscrits',10,'Nbre','R');
	$pdf->AddCol('montant',10,'�','R');
	$pdf->AddCol('statut',10,'Statut','L');
	$prop=array(
		'HeaderColor'=>array(255,150,100),
          'color1'=>array(224,235,255),
          'color2'=>array(255,255,255),
          'padding'=>2);
	$pdf->Table("SELECT * FROM spip_asso_activites WHERE id_evenement=$id_evenement ORDER BY nom",$prop);
	$pdf->Output();
	}
}

