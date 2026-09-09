<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Récupère les rôles d'association et les auteurs associés à chaque rôle.
 *
 * Cette fonction analyse les champs de type rôle définis dans les champs extras
 * et identifie les auteurs assignés à chaque rôle. Elle gère également les cas
 * où un auteur a plusieurs rôles séparés par des virgules.
 *
 * @return array Un tableau associatif contenant les rôles et leurs informations
 */
function roles_association() {
	// Liste des champs considérés comme des rôles
	$champs_roles = ['fonction', 'fonction_secondaire', 'hotesse_quartier', 'marraine_quartier', 'responsable_quartier', 'responsable_activite', 'responsable_evenement'];
	$roles = [];

	include_spip('inc/cextras');
	include_spip('inc/saisies');

	if (function_exists('champs_extras_objet') && function_exists('saisies_lister_par_nom')) {
		// Récupération des champs extras définis pour les auteurs
		$champs_extras = champs_extras_objet('spip_auteurs');
		$champs_plats = saisies_lister_par_nom($champs_extras);

		foreach ($champs_roles as $nom_champ) {
			if (!isset($champs_plats[$nom_champ])) {
				continue;
			}

			$champ = $champs_plats[$nom_champ];
			if (!in_array($champ['saisie'], ['selection', 'selection_multiple', 'radio']) || !isset($champ['options']['datas'])) {
				continue;
			}

			$datas = $champ['options']['datas'];
			// Récupération des valeurs et préservation de leur ordre original
			$valeurs_plates = is_string($datas) ? saisies_chaine2tableau($datas) : [];
			$groupes = is_string($datas) ? saisies_extraire_groupes_chaine($datas) : [];

			$roles[$nom_champ] = [
				'label' => isset($champ['options']['label']) ? _T($champ['options']['label']) : $nom_champ,
				'valeurs' => [], // Sera rempli en préservant l'ordre original
				'groupes' => $groupes,
			];

			// Récupérer tous les auteurs actifs une seule fois pour optimiser
			$tous_auteurs = sql_allfetsel('id_auteur, ' . $nom_champ, 'spip_auteurs', 'statut_interne = ' . sql_quote('ok') . ' AND ' . $nom_champ . ' != ""');

			// Préparer un tableau associatif des auteurs par rôle
			$auteurs_par_role = [];
			foreach ($tous_auteurs as $auteur) {
				// Diviser les rôles multiples si nécessaire
				$roles_auteur = explode(',', $auteur[$nom_champ]);
				foreach ($roles_auteur as $role) {
					$role = trim($role);
					if (!empty($role)) {
						if (!isset($auteurs_par_role[$role])) {
							$auteurs_par_role[$role] = [];
						}
						$auteurs_par_role[$role][] = $auteur['id_auteur'];
					}
				}
			}

			// Préserve l'ordre des valeurs comme défini dans le champ de saisie
			foreach ($groupes as $cle_groupe => $valeur_groupe) {

				foreach ($valeur_groupe as $cle => $valeur) {
					if (isset($auteurs_par_role[$cle]) && !empty($auteurs_par_role[$cle])) {
						$roles[$nom_champ]['valeurs'][$cle] = [
							'libelle' => $valeur,
							'id_auteurs' => $auteurs_par_role[$cle],
						];
					} else {
						unset($roles[$nom_champ]['groupes'][$cle_groupe][$cle]);
					}
				}
			}

			// Nettoyage des groupes vides
			$roles[$nom_champ]['groupes'] = array_filter($roles[$nom_champ]['groupes']);
		}
	}
	return $roles;
}

/**
 * Extrait les valeurs plates à partir d'une chaîne formatée.
 *
 * @param string $chaine La chaîne contenant les valeurs à extraire
 * @return array Un tableau associatif des valeurs extraites
 */
function extraire_valeurs_chaine($chaine) {
	$valeurs = [];
	$lignes = explode("\n", $chaine);

	foreach ($lignes as $ligne) {
		$ligne = trim($ligne);
		if (empty($ligne) || substr($ligne, 0, 1) == '*') {
			continue;
		}

		if (strpos($ligne, '|') !== false) {
			[$cle, $valeur] = explode('|', $ligne, 2);
			$valeurs[trim($cle)] = trim($valeur);
		} else {
			$valeurs[trim($ligne)] = trim($ligne);
		}
	}

	return $valeurs;
}

/**
 * Extrait les groupes et leurs valeurs à partir d'une chaîne formatée.
 *
 * @param string $chaine La chaîne contenant les groupes à extraire
 * @return array Un tableau associatif des groupes et leurs valeurs
 */
function extraire_groupes_chaine($chaine) {
	$groupes = [];
	$groupe_courant = '';
	$lignes = explode("\n", $chaine);

	foreach ($lignes as $ligne) {
		$ligne = trim($ligne);
		if (empty($ligne)) {
			continue;
		}

		if (substr($ligne, 0, 1) == '*') {
			$groupe_courant = trim(substr($ligne, 1));
			$groupes[$groupe_courant] = [];
		} elseif (!empty($groupe_courant)) {
			if (strpos($ligne, '|') !== false) {
				[$cle, $valeur] = explode('|', $ligne, 2);
				$groupes[$groupe_courant][trim($cle)] = trim($valeur);
			} else {
				$groupes[$groupe_courant][trim($ligne)] = trim($ligne);
			}
		}
	}

	return $groupes;
}

/**
 * Fonction qui vérifie si la fonction saisies_extraire_groupes existe
 * dans le plugin saisies et l'utilise, sinon utilise la fonction locale.
 *
 * @param string $chaine La chaîne contenant les groupes à extraire
 * @return array Un tableau associatif des groupes et leurs valeurs
 */
function saisies_extraire_groupes_chaine($chaine) {
	if (function_exists('saisies_extraire_groupes')) {
		return saisies_extraire_groupes($chaine);
	}
	return extraire_groupes_chaine($chaine);
}
