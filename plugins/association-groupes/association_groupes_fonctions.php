<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }


function filtre_roles_association($fonctions_benevole = []) {
	include_spip('inc/fonctions/roles_association');
	$roles = roles_association();

	foreach ($roles as $nom_champ => $role) {
		// Filtrer par fonctions autorisées si liste fournie
		if (!empty($fonctions_benevole) AND !in_array($nom_champ, $fonctions_benevole)) {
			unset($roles[$nom_champ]);
			continue;
		}

		// Supprimer les groupes vides
		if (isset($role['groupes']) and is_array($role['groupes'])) {
			$roles[$nom_champ]['groupes'] = array_filter($role['groupes']);
		}
	}

	return $roles;
}
