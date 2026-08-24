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

include_spip('inc/exporter_csv');
include_spip('inc/association_autorisations');

// TODO: where is this used?
function action_exporter_activites_csv_dist()
{
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_evenement = (int) $securiser_action();
	if (!autoriser('voir_activites', 'evenement', $id_evenement)) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}

	## ID EVENEMENT
	$config_evenement = affichage_dans_activites($id_evenement);

	## DETROMPEUR

	// spip_asso_activites
	$trouver_table = charger_fonction('trouver_table', 'base');
	$desc = $trouver_table('spip_asso_activites');

	$key_array = array(
		'date',
		'id_activite',
		'statut',
		'nombre_inscrits',
		'nom_participants',
		'commentaire',
	);

	foreach ($key_array as $result)
		if(array_key_exists($result, $desc['field'])){
				if($result == 'statut')
					$preas[1] = 'a.statut AS activite_statut';
				elseif($result == 'date')
					$preas[0] = 'a.date';
				elseif($result == 'id_activite')
					$preas[2] = 'a.id_activite';
				elseif($result == 'nombre_inscrits')
					$preas[11] = 'a.nombre_inscrits';
				elseif($result == 'nom_participants')
					$preas[12] = 'a.nom_participants';
				elseif($result == 'commentaire')
					$preas[13] = 'a.commentaire';
			}

	// spip_auteurs
	$trouver_table = charger_fonction('trouver_table', 'base');
	$desc = $trouver_table('spip_auteurs');

	$key_array = array(
		'id_auteur',
		'sexe',
		'prenom',
		'nom_famille',
		'telephone',
		'mobile',
		'email',
		'nationalite',
	);

	foreach ($key_array as $result)
		if(array_key_exists($result, $desc['field'])){
			if($result == 'id_auteur')
				$preas[3] = 'b.id_auteur';
			elseif($result == 'sexe')
				$preas[4] = 'b.sexe';
			elseif($result == 'prenom')
				$preas[5] = 'b.prenom';
			elseif($result == 'nom_famille')
				$preas[6] = 'b.nom_famille';
			elseif($result == 'telephone')
				$preas[7] = 'b.telephone';
			elseif($result == 'mobile')
				$preas[8] = 'b.mobile';
			elseif($result == 'email')
				$preas[9] = 'b.email';
			elseif($result == 'nationalite')
				$preas[10] = 'b.nationalite';
		}

	$colonnes_paiement = array();
	if ($config_evenement['payant']) {
		$preas[14] = 'a.id_transaction';
		$colonnes_paiement = array('montant', 'paiement_statut');
	}

	// Mise en forme
	ksort($preas);
	$preas = array_values($preas);

	$liste = implode(',', $preas);
        
	$query_activites = sql_select(
		$liste,
		'spip_asso_activites AS a INNER JOIN spip_auteurs AS b ON b.id_auteur=a.id_auteur',
		'a.id_evenement=' . $id_evenement,
		'',
		'(a.statut="ok") DESC, (a.statut="preinscrit") DESC, a.statut, a.id_activite'
	);
	$preas_clean = array();

	$remplacement  = array( "a.", "b.", "c.", "statut AS ", "statut AS " );
	foreach($preas as $key){
		$key = str_replace($remplacement,'', $key);
		$preas_clean[] = $key;
	}
	$preas_clean = array_merge($preas_clean, $colonnes_paiement);

	if(sql_count($query_activites)){
		$resultat_final = array();
		$lignes = array();
		$ids_transactions = array();

		while ($adherent_array = sql_fetch($query_activites)) {
			$lignes[] = $adherent_array;
			if (!empty($adherent_array['id_transaction'])) {
				$ids_transactions[] = (int) $adherent_array['id_transaction'];
			}
		}
		include_spip('inc/association_paiements_transactions');
		$transactions = association_paiements_transactions_lire($ids_transactions);
		foreach ($lignes as $adherent_array) {
			$result = array();
			foreach (array_diff($preas_clean, $colonnes_paiement) as $key) {
				$result[] = $adherent_array[$key] ?? '';
			}
			if ($config_evenement['payant']) {
				$transaction = $transactions[(int) ($adherent_array['id_transaction'] ?? 0)] ?? array();
				$result[] = $transaction['montant'] ?? '';
				$result[] = $transaction['statut'] ?? '';
			}
			$resultat_final[] = $result;
		}

		$titre = _T('association_evenements:titre_csv_activites', array('id_evenement' => $id_evenement))."-".$GLOBALS['meta']['nom_site']."-".date('Y-m-d');
		$exporter_csv = charger_fonction('exporter_csv', 'inc/');
		$exporter_csv($titre, $resultat_final, ",", $preas_clean);

	}
}

