<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function filtre_roles_association($fonctions_benevole = []) {
	include_spip('inc/fonctions/roles_association');
	$roles = roles_association();

	foreach ($roles as $nom_champ => $role) {
		// Filtrer par fonctions autorisées si liste fournie
		if (!empty($fonctions_benevole) and !in_array($nom_champ, $fonctions_benevole)) {
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
	return trim(implode(' ', array_filter([
		trim((string) $civilite),
		trim((string) $prenom),
		trim((string) $nom_famille),
	], 'strlen')));
}

function association_groupes_telephone($numero) {
	$numero = preg_replace('/\D/', '', (string) $numero);
	if ($numero === '') {
		return '';
	}

	return trim((string) preg_replace('/(\d{2})/', '$1&nbsp;', $numero));
}

function association_groupes_auteur_resume($id_auteur) {
	$id_auteur = (int) $id_auteur;
	$description = sql_showtable('spip_auteurs', true);
	$champs = array_keys((array) ($description['field'] ?? []));
	$selection = array_values(array_intersect(
		['id_auteur', 'nom', 'email', 'statut', 'prenom', 'nom_famille', 'telephone', 'mobile', 'inscription', 'validite'],
		$champs
	));
	$auteur = $id_auteur && $selection
		? (sql_fetsel(implode(',', $selection), 'spip_auteurs', 'id_auteur=' . $id_auteur) ?: [])
		: [];
	$nom = trim((string) ($auteur['nom'] ?? ''));
	if (!empty($auteur['prenom']) || !empty($auteur['nom_famille'])) {
		$nom = trim(($auteur['prenom'] ?? '') . ' ' . ($auteur['nom_famille'] ?? ''));
	}
	$auteur['nom_affiche'] = $nom;
	$auteur['url_gestion'] = association_plugin_actif('association_adhesions')
		? generer_url_ecrire('voir_adherent', 'id_auteur=' . $id_auteur)
		: generer_url_ecrire('auteur', 'id_auteur=' . $id_auteur);

	return $auteur;
}

function association_groupes_url_auteurs() {
	return association_plugin_actif('association_adhesions')
		? generer_url_ecrire('adherents')
		: generer_url_ecrire('auteurs');
}
