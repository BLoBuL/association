<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/saisies');

/**
 * Calcule le quota de participants applicable a l'inscription courante.
 */
function ie_quota_participants_effectif($gestions_places, $affichage_dans_activites, $id_activite = 0) {
	$quota = !empty($affichage_dans_activites['accompagnants'])
		? max(1, intval($gestions_places['places_limites'] ?? 1))
		: 1;

	if (empty($affichage_dans_activites['places'])
		|| empty($gestions_places['places_evenement'])
		|| !empty($affichage_dans_activites['attentes_illimite'])) {
		return $quota;
	}

	$places_existantes = $id_activite
		? max(0, intval(sql_getfetsel('nombre_inscrits', 'spip_asso_activites', 'id_activite=' . intval($id_activite))))
		: 0;
	$places_disponibles = max(0, intval($gestions_places['places_disponibles'] ?? 0));
	$places_attente = !empty($gestions_places['place_attentes_active'])
		? max(0, intval($gestions_places['places_en_attentes_disponible'] ?? 0))
		: 0;
	$capacite_applicable = max($places_disponibles, $places_attente) + $places_existantes;

	return max(0, min($quota, $capacite_applicable));
}

/**
 * Limite les invites au quota restant apres le participant principal.
 */
function ie_limite_invites_effective($gestions_places, $affichage_dans_activites, $id_activite = 0, $nombre_principaux = 1) {
	$limite_configuree = max(0, intval($affichage_dans_activites['limite_invites'] ?? 5));
	$quota_participants = ie_quota_participants_effectif($gestions_places, $affichage_dans_activites, $id_activite);
	$nombre_principaux = max(1, intval($nombre_principaux));

	return min($limite_configuree, max(0, $quota_participants - $nombre_principaux));
}

function champs_saisie_nb_inscrits($gestions_places, $affichage_dans_activites) {

	$quota_perso = ($affichage_dans_activites['accompagnants']) ? $gestions_places['places_limites'] : 1;

	// $quota_perso = min($gestions_places['places_disponibles'],$quota_perso);

	if ($quota_perso > 1) {
		$nb_personne = [];
		$i = '2';
		$nb_personne += [1 => _T('association_evenements:activite_form_public_nb_personne')];
		while ($i <= $quota_perso) {
			$nb = $i++;
			$nb_personne += [$nb => _T('association_evenements:activite_form_public_nb_personnes', ['nb' => $nb])];
		}
		$saisies_selection_nb_inscrits = [
			'saisie' => 'selection',
			'options' => [
				'nom' => 'nb_inscrits',
				'label' => _T('association_evenements:activite_form_public_nombre_participants'),
				'defaut' => !empty(_request('nb_inscrits')) ? _request('nb_inscrits') : '',
				'obligatoire' => 'oui',
				'cacher_option_intro' => 'oui',
				'datas' => $nb_personne,
				'afficher_si' => '',
			],
		];
	} else {
		$saisies_selection_nb_inscrits = [
			'saisie' => 'hidden',
			'options' => [
				'nom' => 'nb_inscrits',
				'defaut' => 1,
			],
		];
	}

	return $saisies_selection_nb_inscrits;
}

function champs_saisies_selection_membres_famille($data_famille, $accompagnants, $options_invites = []) {
	$saisies = [];
	if ($accompagnants == true) {
		$saisies = [
			'saisie' => 'checkbox',
			'options' => [
				'nom' => 'famille',
				'label' => _T('association_evenements:activite_form_public_choix_membre_famille'),
				'defaut' => !empty(_request('famille')) ? _request('famille') : 'adherent',
				'obligatoire' => 'oui',
				'datas' => $data_famille,
				'afficher_si' => '',
				'class' => 'famille-cards',
			],
		];
	} elseif ($accompagnants == false) {
		$saisies = [
			'saisie' => 'radio',
			'options' => [
				'nom' => 'famille',
				'label' => _T('association_evenements:activite_form_public_choix_membre_famille'),
				'defaut' => !empty(_request('famille')) ? _request('famille') : 'adherent',
				'obligatoire' => 'oui',
				'datas' => $data_famille,
				'afficher_si' => '',
				'class' => 'famille-cards',
			],
		];
	}

	// Champ invités hors famille si l'événement l'autorise
	if (!empty($options_invites['actif'])) {
		$max_invites = max(1, intval($options_invites['max'] ?? 5));
		// On retourne un tableau contenant à la fois la saisie famille et la saisie invités
		return [
			$saisies,
			[
				'saisie' => 'input',
				'options' => [
					'nom' => 'nb_invite',
					'label' => _T('association_evenements:activite_form_public_nb_invites'),
					'explication' => _T('association_evenements:activite_form_public_nb_invites_explication', ['max' => $max_invites]),
					'defaut' => !empty(_request('nb_invite')) ? intval(_request('nb_invite')) : 0,
					'obligatoire' => 'non',
					'type' => 'number',
					'min' => 0,
					'max' => $max_invites,
					'afficher_si' => '',
				],
			],
		];
	}

	return $saisies;
}

function champs_saisies_inscrits($affichage_dans_activites, $nb_inscrits = 1, $info_auteur_connecte = []) {
	$defaut = [];
	$query_auteur = null;
	$array_type_adherents = [];

	// Initialiser les défauts vides pour chaque inscrit possible
	for ($j = 1; $j <= 7; $j++) {
		$defaut["inscrit_$j"] = ['prenom' => '', 'nom' => '', 'email' => '', 'telephone' => '', 'date_naissance' => ''];
	}

	if (isset($info_auteur_connecte['id_auteur']) && $id_auteur = $info_auteur_connecte['id_auteur']) {
		$query_auteur = sql_fetsel('*', 'spip_auteurs', "id_auteur= $id_auteur");

		if ($query_auteur) {
			$defaut['inscrit_1'] = ['prenom' => $query_auteur['prenom'] ?? '', 'nom' => $query_auteur['nom_famille'] ?? '', 'email' => $query_auteur['email'] ?? '', 'telephone' => $query_auteur['mobile'] ?? ''];
			$defaut['inscrit_2'] = ['prenom' => $query_auteur['prenom_conjoint'] ?? '', 'nom' => $query_auteur['nom_conjoint'] ?? '', 'email' => $query_auteur['email_conjoint'] ?? '', 'telephone' => $query_auteur['mobile_conjoint'] ?? ''];
			// Fix E : pré-remplir le nom de famille (partagé) pour les enfants
			$nom_famille_defaut = $query_auteur['nom_famille'] ?? '';
			$defaut['inscrit_3'] = ['prenom' => $query_auteur['prenom_enfant_1'] ?? '', 'nom' => $nom_famille_defaut, 'date_naissance' => $query_auteur['date_naissance_enfant_1'] ?? ''];
			$defaut['inscrit_4'] = ['prenom' => $query_auteur['prenom_enfant_2'] ?? '', 'nom' => $nom_famille_defaut, 'date_naissance' => $query_auteur['date_naissance_enfant_2'] ?? ''];
			$defaut['inscrit_5'] = ['prenom' => $query_auteur['prenom_enfant_3'] ?? '', 'nom' => $nom_famille_defaut, 'date_naissance' => $query_auteur['date_naissance_enfant_3'] ?? ''];
			$defaut['inscrit_6'] = ['prenom' => $query_auteur['prenom_enfant_4'] ?? '', 'nom' => $nom_famille_defaut, 'date_naissance' => $query_auteur['date_naissance_enfant_4'] ?? ''];
			$defaut['inscrit_7'] = ['prenom' => $query_auteur['prenom_enfant_5'] ?? '', 'nom' => $nom_famille_defaut, 'date_naissance' => $query_auteur['date_naissance_enfant_5'] ?? ''];
		}
	}

	// Déterminer les types d'adhérents autorisés pour les catégories de tarif
	if ($affichage_dans_activites['payant'] == true) {
		if ($query_auteur) {
			if (($query_auteur['statut_interne'] ?? '') == 'ok' && in_array($query_auteur['statut'] ?? '', ['1comite', '0minirezo'])) {
				$array_type_adherents = ['adherent', 'indifferent', 'benevole'];
			} elseif (($query_auteur['statut_interne'] ?? '') == 'ok') {
				$array_type_adherents = ['adherent', 'indifferent'];
			} else {
				$array_type_adherents = ['non_adherent', 'indifferent'];
			}
		} else {
			// Utilisateur non connecté : proposer les tarifs non_adherent et indifferent
			$array_type_adherents = ['non_adherent', 'indifferent'];
		}
	}

	$ids = [];
	$saisies_total = [];
	$nb_inscrits = intval($nb_inscrits);
	$saisies_total = [];

	for ($i = 1; $i <= $nb_inscrits; $i++) {
		$id_inscrit = 'inscrit_' . $i;

		$saisies_total[$id_inscrit] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => "fieldset_$id_inscrit",
				'label' => _T('association_evenements:fieldset_inscrit', ['nb' => $i]),
			],
			'saisies' => [
				[
					'saisie' => 'input',
					'options' => [
						'nom' => "prenom_$id_inscrit",
						'label' => _T('association_evenements:activite_form_public_prenom_inscrit'),
						'attributs' => 'autocomplete="given-name"',
						'defaut' => !empty(_request("prenom_$id_inscrit")) ? _request("prenom_$id_inscrit") : ($defaut[$id_inscrit]['prenom'] ?? ''),
						'obligatoire' => 'oui',
						// Ne pas permettre la modification du prénom si la valeur est connue (conjoint, enfants, invités)
						// 'afficher_si' => $afficher_si,
					],
				],
				[
					'saisie' => 'input',
					'options' => [
						'nom' => "nom_$id_inscrit",
						'label' => _T('association_evenements:activite_form_public_nom_inscrit'),
						'attributs' => 'autocomplete="family-name"',
						'defaut' => !empty(_request("nom_$id_inscrit")) ? _request("nom_$id_inscrit") : ($defaut[$id_inscrit]['nom'] ?? ''),
						'obligatoire' => 'oui',
						// Ne pas permettre la modification du nom si la valeur est connue (conjoint, enfants, invités)
						// 'disable' => ($i > 1 && !empty($defaut[$id_inscrit]['nom'])) ? true : false,
						// 'afficher_si' => $afficher_si,
					],
				],
			],
		];
		$info_supp = is_array($affichage_dans_activites) ? ($affichage_dans_activites['info_supplementaire'] ?? '') : '';
		$champs_saisies_info_supplementaire = champs_saisies_info_supplementaire($id_inscrit, $info_supp, $i, $defaut);

		if (!empty(_request('association'))) {
			$saisies_site_fiafe = generer_saisie_site_fiafe('public', _request('association'));
			foreach (array_slice($champs_saisies_info_supplementaire[$id_inscrit]['saisies'], 0, 1) as $champs_saisie_info_supplementaire) {
				$saisies_total[$id_inscrit]['saisies'][] = $champs_saisie_info_supplementaire;
			}
			foreach ($saisies_site_fiafe as $saisie_site_fiafe) {
				$saisies_total[$id_inscrit]['saisies'][] = $saisie_site_fiafe;
			}
		}
		if ($affichage_dans_activites['payant'] == true) {
			$tableau_categories = $affichage_dans_activites['montant'];
			// Fix A : inscrit_3+ → tarifs enfant ; inscrit_1-2 → tarifs membre + couple
			if ($i >= 3) {
				// Enfants : membres si le parent est membre actif
				$types_enfant = ['enfant', 'indifferent'];
				if (in_array('adherent', $array_type_adherents)) {
					$types_enfant[] = 'adherent';
				}
				$types_pour_inscrit = array_unique($types_enfant);
			} elseif ($i === 1) {
				// Premier inscrit : tous ses droits tarifaires + couple
				$types_pour_inscrit = array_unique(array_merge($array_type_adherents, ['couple']));
			} else {
				// inscrit_2+ : tarifs membre sans bénévole, sans couple (réservé au premier inscrit)
				$types_sans_benevole = array_diff($array_type_adherents, ['benevole', 'couple']);
				$types_pour_inscrit = array_unique(array_values($types_sans_benevole));
			}
			$champs_saisies_saisies_tarifs = champs_saisies_tarifs($id_inscrit, 'inscrit_1', $types_pour_inscrit, $tableau_categories, $i);

			// $saisies_total= array_merge_recursive($saisies_total,$champs_saisies_info_supplementaire);
			$saisies_total = array_merge_recursive($saisies_total, $champs_saisies_info_supplementaire, $champs_saisies_saisies_tarifs);
		} else {
			$saisies_total = array_merge_recursive($saisies_total, $champs_saisies_info_supplementaire);
		}

	}

	return $saisies_total;
}

function champs_saisies_famille($id_auteur, $affichage_dans_activites) {
	$query_auteur = sql_fetsel('*', 'spip_auteurs', "id_auteur= $id_auteur");

	$saisies_total = [];
	if ($query_auteur['statut_interne'] == 'ok' and in_array($query_auteur['statut'], ['1comite', '0minirezo'])) {
		$array_type_adherents = ['adherent', 'indifferent', 'benevole'];
	} elseif ($query_auteur['statut_interne'] == 'ok') {
		$array_type_adherents = ['adherent', 'indifferent'];
	} else {
		$array_type_adherents = ['non_adherent', 'indifferent'];
	}

	$famille['adherent'] = ['prenom' => $query_auteur['prenom'], 'nom' => $query_auteur['nom_famille'], 'email' => $query_auteur['email'], 'telephone' => $query_auteur['mobile']];
	$famille['conjoint'] = ['prenom' => $query_auteur['prenom_conjoint'], 'nom' => $query_auteur['nom_conjoint'], 'email' => $query_auteur['email_conjoint'], 'telephone' => $query_auteur['mobile_conjoint']];
	$famille['enfant_1'] = ['prenom' => $query_auteur['prenom_enfant_1'], 'date_naissance' => $query_auteur['date_naissance_enfant_1']];
	$famille['enfant_2'] = ['prenom' => $query_auteur['prenom_enfant_2'], 'date_naissance' => $query_auteur['date_naissance_enfant_2']];
	$famille['enfant_3'] = ['prenom' => $query_auteur['prenom_enfant_3'] ?? '', 'date_naissance' => $query_auteur['date_naissance_enfant_3'] ?? ''];
	$famille['enfant_4'] = ['prenom' => $query_auteur['prenom_enfant_4'] ?? '', 'date_naissance' => $query_auteur['date_naissance_enfant_4'] ?? ''];
	$famille['enfant_5'] = ['prenom' => $query_auteur['prenom_enfant_5'] ?? '', 'date_naissance' => $query_auteur['date_naissance_enfant_5'] ?? ''];
	$famille['invite_1'] = ['prenom' => $query_auteur['invite_1'] ?? ''];
	$famille['invite_2'] = ['prenom' => $query_auteur['invite_2'] ?? ''];

	if (!empty(_request('famille'))) {
		$request_famille = is_array(_request('famille')) ? array_flip(_request('famille')) : [_request('famille') => 0];
		$data_famille_selection = array_intersect_key($famille, $request_famille);
		$nb_membres = count($data_famille_selection); // nombre total de membres inscrits
		$has_conjoint = array_key_exists('conjoint', $data_famille_selection); // conjoint sélectionné ?
		$i = 0;

		foreach ($data_famille_selection as $cle => $value) {
			$saisies_total[$cle] = [
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'fieldset_inscrit_' . $cle,
					'label' => _T('association_evenements:fieldset_inscrit', ['nb' => $famille[$cle]['prenom']]),
				],
				'saisies' => [
					[
						'saisie' => 'input',
						'options' => [
							'nom' => "prenom_$cle",
							'label' => _T('association_evenements:activite_form_public_prenom_inscrit'),
							'defaut' => !empty(_request($cle)) ? _request($cle) : ($famille[$cle]['prenom'] ?? ''),
							'obligatoire' => 'oui',
							// Ne pas permettre la modification du prénom si connu (conjoint, enfants, invités)
							'disable_avec_post' => !empty($famille[$cle]['prenom']) ? 'oui' : false,
							// 'afficher_si' => $afficher_si,
						],
					],
					[
						'saisie' => 'input',
						'options' => [
							'nom' => "nom_$cle",
							'label' => _T('association_evenements:activite_form_public_nom_inscrit'),
							'defaut' => !empty(_request($cle)) ? _request($cle) : ($famille[$cle]['nom'] ?? ''),
							'obligatoire' => 'oui',
							// Ne pas permettre la modification du nom si connu (conjoint, enfants, invités)
							/* 'disable' => !empty($famille[$cle]['nom']) ? true : false, */
							'disable_avec_post' => !empty($famille[$cle]['nom']) ? 'oui' : false,
							// 'afficher_si' => $afficher_si,
						],
					],
				],
			];

			$info_supp = is_array($affichage_dans_activites) ? ($affichage_dans_activites['info_supplementaire'] ?? '') : '';
			$champs_saisies_info_supplementaire = champs_saisies_info_supplementaire($cle, $info_supp, ++$i, $famille);

			if ($affichage_dans_activites['payant'] == true) {
				$tableau_categories = $affichage_dans_activites['montant'];
				$premier_inscrit = _request('famille');

				// Fix B : forcer 'adherent' comme référence du masquage si présent dans la sélection
				$premier_inscrit_cle = (is_array($premier_inscrit) && in_array('adherent', $premier_inscrit))
					? 'adherent'
					: (is_array($premier_inscrit) ? $premier_inscrit[0] : $premier_inscrit);

				// Déterminer les types de tarifs applicables selon le rôle du membre
				$array_type_adherents_inscrit = association_types_tarifs_par_role($cle, $array_type_adherents);

				$champs_saisies_saisies_tarifs = champs_saisies_tarifs($cle, $premier_inscrit_cle, $array_type_adherents_inscrit, $tableau_categories, $i, $has_conjoint);

				// $saisies_total= array_merge_recursive($saisies_total,$champs_saisies_info_supplementaire);
				$saisies_total = array_merge_recursive($saisies_total, $champs_saisies_info_supplementaire, $champs_saisies_saisies_tarifs);
			} else {
				$saisies_total = array_merge_recursive($saisies_total, $champs_saisies_info_supplementaire);
			}

		}
	}
	return $saisies_total;
}

function champs_saisies_tarifs($id_inscrit, $premier_inscrit, $array_type_adherents, $tableau_categories, $compteur, $has_conjoint = false) {
	$saisies_tarifs = $tarif_de_groupe = $afficher_si = $saisies_total = [];

	// Validation defensive : s'assurer que $tableau_categories est un tableau non vide
	if (empty($tableau_categories) || !is_array($tableau_categories)) {
		// Rien à générer si pas de catégories de tarif
		return [];
	}

	usort($tableau_categories, 'trierparMontant');
	$contexte_tarif = [
		'role' => $id_inscrit,
		'has_conjoint' => $has_conjoint,
	];
	$datas_categories = generer_array_categories_participation(
		$tableau_categories,
		'datas',
		$array_type_adherents,
		$compteur,
		$contexte_tarif
	);
	$valeur_postee = _request("categorie_$id_inscrit");

	// Indexation des tarifs de groupe par quantité couverte :
	// tarifs_par_quantite[2] = [id1, id2, ...] → tarifs qui couvrent 2 personnes (rang 1 + rang 2)
	// tarifs_par_quantite[3] = [id3, ...] → couvrent 3 personnes, etc.
	$tarifs_par_quantite = [];
	foreach ($tableau_categories as $value) {
		$type = $value['type_inscrit'] ?? 'indifferent';
		// Un tarif 'couple' couvre toujours 2 personnes par définition,
		// même si quantite n'a pas encore été migré à 2 en base.
		$q = ($type === 'couple') ? 2 : intval($value['quantite']);
		// Indexer tous les tarifs de groupe (quantite > 1) sélectionnables par le premier inscrit.
		if ($q > 1 && ($type === 'couple' || in_array($type, $array_type_adherents))) {
			$tarifs_par_quantite[$q][] = $value['id_categorie'];
		}
	}

	if ($id_inscrit != $premier_inscrit && !empty($tarifs_par_quantite)) {
		// On masque l'inscrit N uniquement si un tarif groupe couvrant >= N personnes est sélectionné.
		// On collecte les id de tarifs dont la quantite >= $compteur (rang de cet inscrit).
		$tarifs_couvrant_ce_rang = [];
		foreach ($tarifs_par_quantite as $q => $ids) {
			if ($q >= $compteur) {
				$tarifs_couvrant_ce_rang = array_merge($tarifs_couvrant_ce_rang, $ids);
			}
		}

		if (!empty($tarifs_couvrant_ce_rang)) {
			// Ce rang est couvert par un tarif groupe du premier inscrit → masquer le champ
			// Masquer ce champ si le premier inscrit a sélectionné un tarif groupe couvrant ce rang.
			// Syntaxe saisies : !(...) pour nier, IN pour tester l'appartenance à une liste.
			$afficher_si = '!(@categorie_' . $premier_inscrit . '@ IN "' . implode(',', $tarifs_couvrant_ce_rang) . '")';
			$defaut = false;
		} else {
			// Aucun tarif groupe ne couvre ce rang → toujours visible et sélectionnable
			$afficher_si = false;
		}
	} else {
		$afficher_si = false;
	}
	// Ne jamais désactiver le champ : soit il est masqué (afficher_si), soit il est pleinement actif
	$disable = false;

	$saisies_tarifs[$id_inscrit]['saisies'][] = [
		'saisie' => 'radio',
		'options' => [
			'nom' => "categorie_$id_inscrit",
			// 'cacher_option_intro' => 1,
			'label' => _T('association_evenements:activite_form_public_tarif'),
			'defaut' => ($valeur_postee !== null && $valeur_postee !== '') ? $valeur_postee : false,
			'obligatoire' => 'oui',
			'datas' => $datas_categories,
			'afficher_si' => $afficher_si,
			'disable' => $disable,
		],
	];

	return $saisies_tarifs;
}

/**
 * Génère les fieldsets de saisie pour les invités hors famille.
 * Réutilise champs_saisies_inscrits (même nomenclature inscrit_N) et post-traite
 * le label et la condition afficher_si selon @nb_invite@.
 *
 * @param int   $nb_invite               Nombre max d'invités (limite de l'événement)
 * @param array $affichage_dans_activites Config de l'activité
 * @return array Tableau de fieldsets saisies
 */
function champs_saisies_invites($nb_invite, $affichage_dans_activites) {
	$nb_invite = max(0, intval($nb_invite));
	if ($nb_invite <= 0) {
		return [];
	}

	// Générer les saisies via champs_saisies_inscrits (inscrit_1…inscrit_N, sans auteur connecté)
	$saisies_raw = champs_saisies_inscrits($affichage_dans_activites, $nb_invite, []);

	$saisies_total = [];
	$j = 0;
	foreach ($saisies_raw as $fieldset) {
		$j++;
		// Label invité à la place du label générique "Inscrit N"
		$fieldset['options']['label'] = _T('association_evenements:fieldset_invite_ext', ['nb' => $j]);
		// Masquer dynamiquement si nb_invite < rang de cet invité
		$fieldset['options']['afficher_si'] = '@nb_invite@ >= ' . $j;
		$saisies_total[] = $fieldset;
	}

	return $saisies_total;
}

/**
 * Retourne les types de tarifs applicables selon le rôle du membre dans la famille.
 *
 * - adherent  → types du premier inscrit (adherent/non_adherent + éventuellement benevole)
 * - conjoint  → mêmes types que l'adhérent (membre de droit)
 * - enfant_*  → ['enfant', 'indifferent']
 * - invite_*  → ['invite', 'indifferent']
 *
 * @param string $cle               Clé du membre (adherent, conjoint, enfant_1…, invite_1…)
 * @param array  $types_adherent    Types calculés pour l'adhérent principal
 * @return array
 */
function association_types_tarifs_par_role($cle, $types_adherent) {
	$est_membre = in_array('adherent', $types_adherent);

	if (strpos($cle, 'enfant') === 0) {
		// Les enfants sont membres si le parent est membre actif à jour
		$types = ['enfant', 'indifferent'];
		if ($est_membre) {
			$types[] = 'adherent';
		}
		return array_unique($types);
	}
	if (strpos($cle, 'invite') === 0) {
		// Les invités ne bénéficient pas du statut membre du parent
		return ['invite', 'indifferent'];
	}
	// adherent / inscrit_1 : tous ses droits tarifaires (bénévole inclus si comité) + couple
	if ($cle === 'adherent' || $cle === 'inscrit_1') {
		return array_unique(array_merge($types_adherent, ['couple']));
	}
	// conjoint / inscrit_2+ : tarifs membre sans bénévole (rôle personnel), sans couple (réservé au premier inscrit)
	$types_sans_benevole = array_diff($types_adherent, ['benevole', 'couple']);
	return array_unique(array_values($types_sans_benevole));
}

// Custom comparison function to sort by "montant"
function trierparMontant($a, $b) {

	// Compare by "quantite"
	$montantComparison = $b['quantite'] - $a['quantite'];

	// If "montant" is equal, compare by "montant"
	if ($montantComparison == 0) {
		return $b['montant'] - $a['montant'];
	}

	return $montantComparison;
}

function champs_saisies_info_supplementaire($id_inscrit, $info_supplementaire, $compteur = 0, $defaut_values = []) {
	include_spip('inc/saisies');
	$saisies = [];

	// Initialiser les variables
	$premier_inscrit = false;
	$array_info_supplementaire = [];
	$cas_autres_id = '';

	if ($compteur == 1) {
		$premier_inscrit = true;
	}
	$array_cas = ['email', 'document_identite', 'telephone', 'date_naissance', 'nationalite', 'fonction', 'entreprise'];

	if (!is_array($info_supplementaire)) {
		$array_info_supplementaire = explode(',', $info_supplementaire);
		$cas_autres = array_diff($array_info_supplementaire, $array_cas);
		$array_info_supplementaire = array_flip($array_info_supplementaire);
		$cas_autres_label = implode($cas_autres);
		$cas_autres_label = str_replace('@choix_alternatif', '', $cas_autres_label);
		$cas_autres_id = str_replace(' ', '_', preg_replace("/[^a-zA-Z0-9\s]/", '', $cas_autres_label));
	} else {
		$array_info_supplementaire = [];
	}

	if (array_key_exists('email', $array_info_supplementaire) or $premier_inscrit) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'email',
			'options' => [
				'nom' => "email_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:email'),
				'attributs' => 'autocomplete="email" inputmode="email"',
				'defaut' => !empty(_request("email_$id_inscrit")) ? _request("email_$id_inscrit") : ($defaut_values[$id_inscrit]['email'] ?? ''),
				'obligatoire' => ($premier_inscrit) ? 'oui' : 'non',
			],
			'verifier' => [
				'type' => 'email',
				'options' => [
					'mode' => 'normal',
				],
			],
		];
	}

	if (array_key_exists('telephone', $array_info_supplementaire) or $premier_inscrit) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "telephone_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:telephone'),
				'type' => 'tel',
				'attributs' => 'autocomplete="tel" inputmode="tel"',
				'defaut' => !empty(_request("telephone_$id_inscrit")) ? _request("telephone_$id_inscrit") : ($defaut_values[$id_inscrit]['telephone'] ?? ''),
				'obligatoire' => 'oui',
			],
		];
	}
	if (array_key_exists('document_identite', $array_info_supplementaire)) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'radio',
			'options' => [
				'nom' => "type_document_identite_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:document_identite_label'),
				'explication' => _T('association_evenements:document_identite_explication'),
				'defaut' => !empty(_request('type_document_identite')) ? _request('type_document_identite') : 'passeport',
				'obligatoire' => 'oui',
				'datas' => ['passeport' => _T('association_evenements:passeport'), 'carte_id' => _T('association_evenements:carte_id')],
			],
		];
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "numero_document_identite_$id_inscrit",
				// 'explication' =>  _T('association_evenements:numero_document_identite_explication'),
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:numero_document_identite_label'),
				'defaut' => !empty(_request('numero_document_identite')) ? _request('numero_document_identite') : '',
				'obligatoire' => 'oui',
			],
		];
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'date',
			'options' => [
				'nom' => "date_expiration_document_identite_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:date_expiration_document_identite'),
				'explication' => _T('association_evenements:format_date_explication'),
				'defaut' => !empty(_request('date_expiration_document_identite')) ? _request('date_expiration_document_identite') : '',
				'obligatoire' => 'oui',
			],
			'verifier' => [
				'type' => 'date',
				'options' => [
					'normaliser' => 'datetime',
				],
			],
		];
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "lieu_naissance_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:lieu_naissance'),
				'defaut' => !empty(_request('lieu_naissance')) ? _request('lieu_naissance') : '',
				'obligatoire' => 'oui',
			],
		];
	}

	if (array_key_exists('date_naissance', $array_info_supplementaire)) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'date',
			'options' => [
				'nom' => "date_naissance_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:date_naissance'),
				'explication' => _T('association_evenements:format_date_explication'),
				'defaut' => !empty(_request("date_naissance_$id_inscrit")) ? _request("date_naissance_$id_inscrit") : ($defaut_values[$id_inscrit]['date_naissance'] ?? ''),
				'obligatoire' => 'oui',
			],
			'verifier' => [
				'type' => 'date',
				'options' => [
					'normaliser' => 'datetime',
				],
			],
		];
	}
	if (array_key_exists('nationalite', $array_info_supplementaire)) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "nationalite_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:nationalite'),
				'defaut' => !empty(_request("nationalite_$id_inscrit")) ? _request("nationalite_$id_inscrit") : ($defaut_values[$id_inscrit]['nationalite'] ?? ''),
				'obligatoire' => 'oui',
			],
		];
	}
	if (array_key_exists('fonction', $array_info_supplementaire)) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "fonction_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:fonction'),
				'defaut' => !empty(_request("fonction_$id_inscrit")) ? _request("fonction_$id_inscrit") : ($defaut_values[$id_inscrit]['fonction'] ?? ''),
				'obligatoire' => 'oui',
			],
		];
	}
	if (array_key_exists('entreprise', $array_info_supplementaire)) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => "entreprise_$id_inscrit",
				// 'cacher_option_intro' => 1,
				'label' => _T('association_evenements:entreprise'),
				'defaut' => !empty(_request("entreprise_$id_inscrit")) ? _request("entreprise_$id_inscrit") : ($defaut_values[$id_inscrit]['entreprise'] ?? ''),
				'obligatoire' => 'oui',
			],
		];
	}
	if ($cas_autres_id) {
		$saisies[$id_inscrit]['saisies'][] = [
			'saisie' => 'input',
			'options' => [
				'nom' => $cas_autres_id . '_' . $id_inscrit,
				// 'cacher_option_intro' => 1,
				'label' => $cas_autres_label,
				'defaut' => '',
				'obligatoire' => 'oui',
			],
		];
	}
	// Et hop pour le verifier() de CVT !
	return $saisies;
}

/**
 * Génère les saisies de modalités (CGU, règlement…) pour les formulaires d'inscription événement.
 *
 * Lit la config BO `pages_modalite_evenement`. Fallback sur `page_cgu` si rien n'est configuré.
 * Affiche une case à cocher obligatoire par page trouvée.
 *
 * @return array Tableau de saisies (vide si aucun article trouvé)
 */
function champs_saisies_modalites_evenement() {
	$saisies = [];

	// Pages configurées dans le BO
	$pages_config = lire_config('/association_metas/pages_modalite_evenement', []);
	if (!is_array($pages_config)) {
		$pages_config = array_filter(array_map('trim', explode(',', (string) $pages_config)));
	}
	// Fallback historique : uniquement la CGU
	if (empty($pages_config)) {
		$pages_config = ['page_cgu'];
	}

	$articles = sql_allfetsel(
		'id_article, titre, page',
		'spip_articles',
		sql_in('page', $pages_config) . " AND statut = 'publie'"
	);

	foreach ($articles as $article) {
		$titre = supprimer_numero($article['titre']);
		$url = generer_objet_url($article['id_article'], 'spip_articles', 'var_zajax=content');
		$saisies[] = [
			'saisie' => 'case',
			'options' => [
				'nom' => $article['page'],
				'label_case' => _T('association_evenements:message_conditions_generales', ['url_CGU' => $url, 'titre_CGU' => $titre]),
				'obligatoire' => 'oui',
			],
		];
	}

	return $saisies;
}
