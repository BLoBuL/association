<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_compta_ecriture_champs_autorises() {
	return array(
		'date', 'recette', 'depense', 'justification', 'imputation', 'journal',
		'id_auteur', 'id_objet', 'objet', 'id_transaction', 'vu',
	);
}

function association_compta_ecriture_normaliser(array $donnees) {
	$donnees = array_intersect_key($donnees, array_flip(association_compta_ecriture_champs_autorises()));
	foreach (array('recette', 'depense') as $champ) {
		if (array_key_exists($champ, $donnees)) {
			$donnees[$champ] = (float) $donnees[$champ];
		}
	}
	foreach (array('id_auteur', 'id_objet', 'id_transaction', 'vu') as $champ) {
		if (array_key_exists($champ, $donnees)) {
			$donnees[$champ] = (int) $donnees[$champ];
		}
	}
	return $donnees;
}

function association_compta_ecriture_creer(array $donnees) {
	$donnees += array(
		'date' => date('Y-m-d H:i:s'),
		'recette' => 0,
		'depense' => 0,
		'justification' => '',
		'imputation' => '',
		'journal' => '',
		'id_auteur' => 0,
		'id_objet' => 0,
		'objet' => '',
		'id_transaction' => 0,
		'vu' => 0,
	);
	return (int) sql_insertq('spip_asso_comptes', association_compta_ecriture_normaliser($donnees));
}

function association_compta_ecriture_modifier($id_compte, array $donnees) {
	$id_compte = (int) $id_compte;
	if ($id_compte <= 0) {
		return 0;
	}
	$donnees = association_compta_ecriture_normaliser($donnees);
	if ($donnees) {
		sql_updateq('spip_asso_comptes', $donnees, 'id_compte=' . $id_compte);
	}
	return $id_compte;
}
