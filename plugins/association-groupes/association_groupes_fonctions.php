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

function association_groupes_nom_membre($civilite, $prenom, $nom_famille) {
	return trim(implode(' ', array_filter(array(
		trim((string) $civilite),
		trim((string) $prenom),
		trim((string) $nom_famille),
	), 'strlen')));
}

function association_groupes_telephone($numero) {
	$numero = preg_replace('/\D/', '', (string) $numero);
	if ($numero === '') {
		return '';
	}

	return trim((string) preg_replace('/(\d{2})/', '$1&nbsp;', $numero));
}
