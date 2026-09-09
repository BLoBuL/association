<?php

include_spip('inc/destinations');

/**
 * Updates contexte for the form.
 * Some known destinations comptables from the UI have their IDs configured in dc_dons... in association_metas
 * @param $contexte : contexte du formulaire passed by reference
 * @param $default_destination : dons, ventes, cotisations
 */
function update_destination_contexte_from_compte(& $contexte, $id_compte, $default_destination) {
	if (!destinations_are_enabled()) {
		return;
	}
	// TODO: refactor..
	/* ces variables sont recuperees par la balise dynamique directement dans l'environnement */
	$contexte['destinations_on'] = true;
	$dest_id_montant = _get_map_of_destination_id_montant($id_compte);
	if (is_array($dest_id_montant)) {
		$contexte['id_dest'] = array_keys($dest_id_montant);
		$contexte['montant_dest'] = array_values($dest_id_montant);
	} else {
		$contexte['id_dest'] = '';
		$contexte['montant_dest'] = '';
	}
	$contexte['unique_dest'] = '';
	$cle_destination = 'dc_' . $default_destination;
	$contexte['defaut_dest'] = strlen($default_destination) > 0
		? ($GLOBALS['association_metas'][$cle_destination] ?? '')
		: '';
}

// recupere dans la table de comptes et celle des destinations la liste des destinations associees a une operation
// le parametre correspond a l'id_compte de l'operation dans spip_asso_compte (et spip_asso_destination)
function _get_map_of_destination_id_montant($id_compte) {
	if (!$id_compte) {
		return '';
	}

	if (
		$destination_query = sql_select(
			'spip_asso_destination_op.id_destination, spip_asso_destination_op.recette, spip_asso_destination_op.depense, spip_asso_destination.intitule',
			'spip_asso_destination_op RIGHT JOIN spip_asso_destination ON spip_asso_destination.id_destination=spip_asso_destination_op.id_destination',
			"id_compte=$id_compte",
			'',
			'spip_asso_destination.intitule'
		)
	) {
		$destination = [];
		while ($destination_op = sql_fetch($destination_query)) {
			/* soit recette soit depense est egal a 0, donc pour l'affichage du montant on se contente les additionner */
			$destination[$destination_op['id_destination']] = $destination_op['recette'] + $destination_op['depense'];
		}
		if (count($destination) > 0) {
			return $destination;
		}
	}
	return '';
}

function verifier_destination_comptable($montant, $montant_field, &$erreurs) {
	/* verifier si besoin que le montant des destinations correspond bien au montant de l'opération, sauf si on a deja une erreur de montant */
	if (destinations_are_enabled() && !array_key_exists($montant_field, $erreurs)) {

		if ($err_dest = _verifier_montant_destinations($montant)) {
			$erreurs['destinations'] = $err_dest;
		}
	}
}

/* fonction de verification des montants de destinations entres */
/* le parametre d'entree est le montant total attendu, les montants des destinations sont recuperes */
/* directement dans $_POST */
function _verifier_montant_destinations($montant_attendu) {
	$err = '';

	$toutesDestinations = array_values((array) _request('id_dest'));
	$toutesDestinationsMontants = array_values((array) _request('montant_dest'));
	if (!$toutesDestinations) {
		return _T('association_compta:erreur_pas_de_destination');
	}

	/* on verifie que le montant des destinations correspond au montant global et qu'il n'y a pas deux fois la meme destination (uniquement si on a plusieurs destinations) */
	$total_destination = 0;
	$id_inserted = [];

	if (count($toutesDestinations) > 1) {
		foreach ($toutesDestinations as $id => $id_destination) {
			/* on verifie qu'on n'a pas deja insere une destination avec cette id */
			if (!array_key_exists($id_destination, $id_inserted)) {
				$id_inserted[$id_destination] = 0;
			} else {
				$err = _T('association_compta:erreur_destination_dupliquee');
			}

			$total_destination += association_recupere_montant($toutesDestinationsMontants[$id] ?? 0); /* les montants sont dans un autre tableau aux meme cles */
		}

		/* on verifie que la somme des montants des destinations correspond au montant attendu */
		if ($montant_attendu != $total_destination) {
			$err .= _T('association_compta:erreur_montant_destination');
		}

	} else { /* une seule destination, le montant peut ne pas avoir ete precise, dans ce cas pas de verif, c'est le montant attendu qui sera entre dans la base */
		if (!empty($toutesDestinationsMontants[0])) {
			$montant = association_recupere_montant($toutesDestinationsMontants[0]);
			/* on verifie que le montant indique correspond au montant attendu */
			if ($montant_attendu != $montant) {
				$err = _T('association_compta:erreur_montant_destination');
			}
		}
	}
	return $err;
}

// retourne dans un <div> le code HTML/javascript correspondant au selecteur de destinations dynamique
// le premier parametre permet de donner un tableau de destinations deja selectionnees(ou '' si on ajoute une operation)
// le second parametre (optionnel) permet de specifier si on veut associer une destination unique, par default on peut ventiler sur
// plusieurs destinations
// le troisieme parametre permet de regler une destination par defaut[contient l'id de la destination] - quand $destination est vide
function association_editeur_destinations($destination, $unique = '', $defaut = '') {
	// recupere la liste de toutes les destination dans un code HTML <option value="destination_id">destination</option>
	$liste_destination = _get_destination_id_intitule_options();

	$res = '';
	if (strlen($liste_destination) == 0) {
		return $res;
	}

	$res .= '<label for="destination"><strong>'
		. _T('association_compta:destination')
		. '&nbsp;:</strong></label>'
		. '<div id="divTxtDestination" class="formulaire_edition_destinations">';

	$idIndex = 0;
	if ($destination != '') { /* si on a une liste de destinations (on edite une operation) */
		foreach ($destination as $destId => $destMontant) {
			$liste_destination_selected = preg_replace('/(value=\'' . $destId . '\')/', '$1 selected="selected"', $liste_destination);
			$res .= '<div class="formo" id="row' . $idIndex . '">'
				. '<ul>';
			$res .= '<li class="editer_id_dest[' . $idIndex . ']">'
				. '<select name="id_dest[' . $idIndex . ']" id="id_dest[' . $idIndex . ']" >'
				. $liste_destination_selected
				. '</select></li>';
			if ($unique == false) {
				$res .= '<li class="editer_montant_dest[' . $idIndex . ']"><input name="montant_dest[' . $idIndex . ']" value="'
					. association_nbrefr(association_recupere_montant($destMontant))
					. '" type="text" id="montant_dest[' . $idIndex . ']" /></li>'
					. "<button class='destButton association-compta-destination-ajouter' type='button'>+</button>";
			}
			$res .= '</ul></div>';
			if ($idIndex > 0) {
				$res .= "<button class='destButton association-compta-destination-retirer' type='button' data-destination-row='row" . $idIndex . "'>-</button>";
			}
			$idIndex++;
		}
	} else {/* pas de destination deja definies pour cette operation */
		if ($defaut != '') {
			$liste_destination = preg_replace('/(value=\'' . $defaut . '\')/', '$1 selected="selected"', $liste_destination);
		}
		$res .= '<div id="row1" class="formo">'
			. '<ul>'
			. '<li title="' . attribut_html(_T('association_compta:destination')) . '" class="editer_id_dest[1]">'
			. '<select name="id_dest[1]" id="id_dest[1]">'
			. $liste_destination
			. '</select>'
			. '</li>';
		if (!$unique) {
			$res .= '<li title="' . attribut_html(_T('association_compta:montant')) . '" class="editer_montant_dest[1]">'
				. '<input name="montant_dest[1]" value="0" type="text" id="montant_dest[1]"/>'
				. '</li>'
				. "<button class='destButton association-compta-destination-ajouter' type='button'>+</button>";
		}
		// UPDATE: desactivation de la fermeture de la div qui mettait du boxon dans l'ajout de cotisation..
		$res .= '</ul></div>';
	}

	if ($unique == false) {
		$res .= '<input type="hidden" id="idNextDestination" value="' . ($idIndex + 1) . '">';
	}
	$res .= '</div>';
	return $res;
}

// retourne une liste d'option HTML de l'ensemble des destinations de la base, ordonee par intitule
function _get_destination_id_intitule_options() {
	$liste_destination = '';
	$sql = sql_select('id_destination,intitule', 'spip_asso_destination', '', '', 'intitule');
	while ($destination_info = sql_fetch($sql)) {
		$id_destination = $destination_info['id_destination'];
		$liste_destination .= "<option value='$id_destination'>" . $destination_info['intitule'] . '</option>';
	}
	return $liste_destination;
}
