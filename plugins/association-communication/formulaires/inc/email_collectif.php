<?php

/*\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/saisies');
include_spip('inc/cvtupload');
include_spip('inc/filtres');
include_spip('inc/email_collectif');
include_spip('formulaires/inc/adherents_recherche_avancee'); // fonctions du moteur de recherche
function association_formulaire_email_collectif_fichiers() {
	return ['visuel_principal', 'documents_joints'];
}
function association_formulaire_email_collectif_saisies($mode = 'adherent') {
	$saisies_recherche_avancee = adherents_recherche_avancee_saisies();
	$type_destinataires_evenement = _request('type_destinataires_evenement') ?: 'inscrits_evenement';
	if ($mode === 'evenement') {
		foreach ($saisies_recherche_avancee as &$saisie_recherche) {
			$saisie_recherche['options']['afficher_si'] = '@type_destinataires_evenement@=="adherents_association"';
		}
		unset($saisie_recherche);
	}

	$options_saisies = [
		'options' => [
			// Changer l'intitulé du bouton final de validation
			'texte_submit' => 'Envoyer l\'email',
			'etapes_activer' => true,
			'etapes_suivant' => 'Suivant',
			'etapes_precedent' => 'Précédent',
			'etapes_navigation' => 'on',
			'etapes_precedent_suivant_titrer' => '',
		],
	];
	$saisies = $options_saisies;

	// ETAPE 1
	$saisies_fieldset_edition_message = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'fieldset_edition_message_nom',
			'label' => '<:association_communication:email_collectif_fieldset_edition_message_label:>',
		],
		'saisies' => [
			[
				'saisie' => 'input',
				'options' => [
					'label' => '<:association_communication:email_collectif_sujet_label:>',
					'nom' => 'sujet',
					'defaut' => '',
					'obligatoire' => 'oui',
				],
			],
			[
				'saisie' => 'input',
				'options' => [
					'label' => '<:association_communication:email_collectif_titre_label:>',
					'nom' => 'titre',
					'explication' => '<:association_communication:email_collectif_titre_explication:>',
					'defaut' => '',
					'obligatoire' => 'oui',
				],
			],
			[
				'saisie' => 'input',
				'options' => [
					'label' => '<:association_communication:email_collectif_chapeau_label:>',
					'nom' => 'chapeau',
					'explication' => '<:association_communication:email_collectif_chapeau_explication:>',
					'defaut' => '',
				],
			],
			[
				'saisie' => 'fichiers',
				'options' => [
					'nom' => 'visuel_principal',
					'label' => '<:association_communication:email_visuel_principal_explication:>',
					'nb_fichiers' => 1,
				],
				'verifier' => [
					'type' => 'fichiers',
					'options' => [
						'taille_max' => 12000,
						'mime' => 'specifique',
						'mime_specifique' => ['image/jpeg', 'image/png'],
					],
				],
			],
			[
				'saisie' => 'textarea',
				'options' => [
					'label' => '<:association_communication:email_collectif_texte_label:>',
					'nom' => 'texte',
					// 'conteneur_class' => 'date_debut',
					'explication' => '<:association_communication:email_collectif_texte_explication:>',
					'defaut' => '',
					'obligatoire' => 'oui',
				],
			],
			...($mode === 'evenement' ? [[
				'saisie' => 'radio',
				'options' => [
					'label' => '<:association_communication:email_collectif_destinataires_label:>',
					'nom' => 'type_destinataires_evenement',
					'explication' => '<:association_communication:email_collectif_destinataires_explication:>',
					'defaut' => 'inscrits_evenement',
					'data' => [
						'inscrits_evenement' => '<:association_communication:email_collectif_destinataires_inscrits_evenement:>',
						'adherents_association' => '<:association_communication:email_collectif_destinataires_adherents_association:>',
					],
					'obligatoire' => 'oui',
				],
			]] : []),
			[
				'saisie' => 'radio',
				'options' => [
					'label' => $mode === 'evenement'
						? '<:association_communication:email_collectif_inclure_evenement_label:>'
						: '<:association_communication:ajouter_information_paiement_label:>',
					'nom' => $mode === 'evenement'
						? 'inclure_contenu_evenement'
						: 'ajouter_information_paiement',
					'explication' => $mode === 'evenement'
						? '<:association_communication:email_collectif_inclure_evenement_explication:>'
						: '<:association_communication:ajouter_information_paiement_explication:>',
					'defaut' => $mode === 'evenement' ? 'oui' : 'non',
					'data' => ['oui' => 'Oui', 'non' => 'Non'],
				],
			],
			[
				'saisie' => 'fichiers',
				'options' => [
					'nom' => 'documents_joints',
					'label' => '<:association_communication:email_documents_joints_label:>',
					'explication' => '<:association_communication:email_documents_joints_explication:>',
					'nb_fichiers' => 3,
				],
				'verifier' => [
					'type' => 'fichiers',
					'options' => [
						'taille_max' => 12000,
					],
				],
			],
		],
	];

	// STRUCTURE COMPLETE DU FORMULAIRE MULTIETAPE
	// ETAPE 1 - CREATION DU MESSAGE
	$saisies_etape_1 = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'message',
			'label' => '<:association_communication:email_collectif_message_titre:>',
		],
		'saisies' => [
			// $saisies_fieldset_gabarit,
			$saisies_fieldset_edition_message,
			[
				'saisie' => 'hidden',
				'options' => [
					'nom' => 'texte_html',
					'defaut' => '',
				],
			],
		],
	];
	if ($mode === 'evenement') {
		$saisies_etape_1['saisies'][] = [
			'saisie' => 'hidden',
			'options' => [
				'nom' => 'id_evenement',
				'defaut' => intval(_request('id_evenement')),
			],
		];
		$saisies_etape_1['saisies'][] = [
			'saisie' => 'hidden',
			'options' => [
				'nom' => 'gabarit_evenement',
				'defaut' => _request('gabarit_evenement') ?: 'libre',
			],
		];
	}
	$saisies[] = $saisies_etape_1;
	// ETAPE 2 VERIFICATION DU MESSAGE GENERE
	$saisies_etape_2 = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'visualisation_message',
			'label' => '<:association_communication:email_collectif_fieldset_visualisation_message_label:>',
		],
	];
	$saisies[] = $saisies_etape_2;
	if ($mode === 'evenement' && $type_destinataires_evenement === 'inscrits_evenement') {
		// ETAPE 3 - LISTE DIRECTE DES INSCRITS A L'EVENEMENT
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'inscrits_evenement',
				'label' => '<:association_communication:email_collectif_inscriptions_evenement_label:>',
			],
		];
	} else {
		// ETAPE 3 - RECHERCHE DES ADHERENTS
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'recherche',
				'label' => '<:association_communication:email_collectif_fieldset_recherche_label:>',
			],
			'saisies' => $saisies_recherche_avancee,
		];
		// ETAPE 4 - RESULTAT DE LA RECHERCHE
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'resultat_recherche',
				'label' => 'Résultat ',
			],
		];
	}
	// ETAPE 4 SELECTION DES DESTINATAIRES
	/*Vérifier le domaine de l'email expéditeur
	Utilisez l'email public par défaut*/
	$website_url = $GLOBALS['meta']['adresse_site'];
	$website_domain = parse_url($website_url, PHP_URL_HOST);
	// $domain non utilisé â€” supprimé (replace() n'est pas une fonction PHP valide)

	$saisies_etape_5 = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'option_envoi',
			'label' => '<:association_communication:email_collectif_option_envoi_label:>',
			// 'explication' => '<:association_communication:email_collectif_select_gabarit_explication:>',
			// 'conteneur_class' => 'fieldset_operateur',
		],
		'saisies' => [
			[
				'saisie' => 'input',
				'options' => [
					'label' => '<:association_communication:email_collectif_nom_expediteur_label:>',
					'nom' => 'nom_expediteur',
					// 'conteneur_class' => 'date_debut',
					// 'explication' => '<:association_communication:email_collectif_nom_expediteur_explication:>',
					'defaut' => '',
				],
			],
			[
				'saisie' => 'input',
				'options' => [
					'label' => '<:association_communication:email_collectif_email_expediteur_label:>',
					'nom' => 'email_expediteur',
					// 'conteneur_class' => 'date_debut',
					'explication' => _T('association_communication:email_collectif_email_expediteur_explication', ['domaine' => $website_domain]),
					'defaut' => '',
				],
			],
			[
				'saisie' => 'date',
				'options' => [
					'label' => '<:association_communication:email_collectif_email_date_envoi_label:>',
					'nom' => 'date_envoi',
					// 'conteneur_class' => 'date_debut',
					'explication' => '<:association_communication:email_collectif_email_date_envoi_explication:>',
					'horaire' => 'oui',
					'heure_pas' => 4,
					// 'defaut' =>,
				],
			],
		],
	];
	$saisies[] = $saisies_etape_5;
	return $saisies;
}
function association_formulaire_email_collectif_charger($mode = 'adherent', $id_evenement = 0) {
	$contexte = [];
	$id_evenement = $mode === 'evenement'
		? intval($id_evenement ?: _request('id_evenement'))
		: 0;
	$contexte['mode_email_collectif'] = $mode;
	$contexte['id_evenement'] = $id_evenement;
	$type_destinataires_evenement = _request('type_destinataires_evenement') ?: 'inscrits_evenement';
	$contexte['type_destinataires_evenement'] = $type_destinataires_evenement;
	$etape = intval(_request('_etape') ?: 1);
	$source_inscrits_evenement = $mode === 'evenement'
		&& $type_destinataires_evenement === 'inscrits_evenement';
	$contexte['afficher_liste_inscrits_evenement'] = $source_inscrits_evenement && $etape === 3;
	$contexte['afficher_liste_adherents'] = !$source_inscrits_evenement && $etape === 4;
	$contexte['afficher_recapitulatif'] = $etape === ($source_inscrits_evenement ? 5 : 6);
	if ($id_evenement) {
		$gabarit_evenement = _request('gabarit_evenement') ?: 'libre';
		$contexte['gabarit_evenement'] = $gabarit_evenement;
		$contexte['inscriptions_evenement'] = association_email_collectif_inscriptions_evenement($id_evenement);
		$contexte['id_activites_evenement'] = array_keys($contexte['inscriptions_evenement']);
		$selection_activites = _request('selecteur_activite_evenement');
		if (!is_array($selection_activites)) {
			$selection_activites = $selection_activites ? [$selection_activites] : [];
		}
		$selection_affichee = (bool) _request('selection_evenement_affichee');
		$selection_activites = $type_destinataires_evenement === 'inscrits_evenement'
			? association_email_collectif_selection_activites_initiale(
				$contexte['inscriptions_evenement'],
				$selection_activites,
				$selection_affichee
			)
			: [];
		if (!_request('_etape')) {
			$gabarit = association_email_collectif_gabarit_evenement($id_evenement, $gabarit_evenement);
			$contexte = array_merge($contexte, $gabarit);
		}
		$contexte['selecteur_activite_evenement'] = $selection_activites;
		$contexte['selection_evenement_affichee'] = $selection_affichee || intval(_request('_etape')) >= 4;
	}
	$selected_gabarit = _request('select_gabarit');
	$contenu_gabarit = [];
	if (!empty($selected_gabarit)) {
		$email_collectif = sql_fetsel('*', 'spip_articles', "page = '$selected_gabarit'");
		// $contenu_gabarit['visuel_principal'] = $email_collectif['logo_article'];
		$contenu_gabarit['sujet'] = ($email_collectif['soustitre']) ? $email_collectif['soustitre'] : $email_collectif['titre'];
		$contenu_gabarit['titre'] = $email_collectif['titre'];
		$contenu_gabarit['chapeau'] = $email_collectif['chapo'];
		$contenu_gabarit['texte'] = $email_collectif['texte'];
		$contexte['contenu_gabarit'] = $contenu_gabarit;
		$contexte['selected_gabarit'] = $contenu_gabarit;
		$contexte += array_merge($contenu_gabarit, ['selected_gabarit' => $selected_gabarit]);
	}
	if (isset($_GET['_etape']) and $_GET['_etape'] == 2) {
		// récupérer le tableau des données correspondants aux fichiers uploadés ou non
		$fichiers = _request('_fichiers');
		if (is_array($fichiers) and count($fichiers)) {
			// charger la fonction de chargement de document du plugin Medias
			$ajouter_documents = charger_fonction('ajouter_documents', 'action');
			$documents = [];
			// associer les documents uploadé à l'article 1 du site
			foreach ($fichiers as $key => $value) {
				$nom_champs = $key;
				// $nouveaux_docs  = $ajouter_documents('new', $fichiers[$key], 'article', '1', 'auto');
				$id_document = $ajouter_documents('new', $fichiers[$key], '', '', 'auto');
				// echo '<br>$nouveaux_docs <br>';
				$documents[$nom_champs] = $id_document;
				$contexte += $documents;
			}
			$contexte['documents'] = $documents;
			if (!empty($documents['visuel_principal']) && is_array($documents['visuel_principal']) && !empty(array_filter($documents['visuel_principal']))) {
				$_POST['visuel_principal'] = $documents['visuel_principal']['0'];
			}
			if (!empty($documents['documents_joints']) && is_array($documents['documents_joints']) && !empty(array_filter($documents['documents_joints']))) {
				$_POST['documents_joints'] = $documents['documents_joints'];
			}

		}

		$_POST['texte_html'] = htmlspecialchars_decode(propre($_POST['texte']));
	}
	// Etape 3: recherche (rien à charger cÃ´té serveur ici)
	elseif (isset($_GET['_etape']) and $_GET['_etape'] == 4 and !$source_inscrits_evenement) {
		// TRAITEMENT RESULTAT
		// On prépare d'abord les critères pour savoir si l'utilisateur a saisi quelque chose
		$criteres_sql = preparer_criteres_adherents($_POST);

		// Si aucun critère n'est fourni, pour ce formulaire spécifique on veut
		// par défaut la liste des adhérents actifs (statut_interne = 'ok')
		$recherche_adherents = !$id_evenement || $type_destinataires_evenement === 'adherents_association';
		if (empty($criteres_sql) && $recherche_adherents) {
			$liste_auteurs = generer_array_adherents(["statut_interne = 'ok'"]);
		} elseif (empty($criteres_sql)) {
			$liste_auteurs = [];
		} else {
			// Sinon on exécute la recherche normale
			$liste_auteurs = afficher_resultat_recherche_avancee($_POST);
		}

		// Si la recherche a renvoyé vide, appliquer aussi le fallback local vers statut_interne = 'ok'
		if ($recherche_adherents && (empty($liste_auteurs) || !is_array($liste_auteurs) || !count($liste_auteurs))) {
			$liste_auteurs = generer_array_adherents(["statut_interne = 'ok'"]);
		}

		$contexte['criteres_sql'] = $criteres_sql;
		// La vue attend un tableau d'IDs (plat). afficher_resultat_recherche_avancee retourne
		// un tableau associatif [id => data]. Fournir seulement les IDs pour éviter
		// qu'un tableau de structures soit injecté dans une clause SQL IN.
		if (is_array($liste_auteurs) && count($liste_auteurs)) {
			$ids = array_keys($liste_auteurs);
			// garantir des entiers valides
			$ids = array_values(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; }));
			$contexte['liste_auteurs'] = $ids;
			$contexte['id_auteurs'] = $ids;
		} else {
			// Eviter une clause SQL IN vide dans le squelette : fournir '0'
			$contexte['liste_auteurs'] = '0';
			$contexte['id_auteurs'] = '0';
		}
		if (_request('var_mode') === 'debug') {
			association_log('email', 'email_collectif: liste_auteurs -> ' . var_export($contexte['liste_auteurs'], true), 'erreur');
		}
		// $_POST['liste_auteurs']= $liste_auteurs;
	} elseif (isset($_GET['_etape']) and $_GET['_etape'] >= ($source_inscrits_evenement ? 4 : 5)) {
		// $contexte['selecteur_adherent'] = $_POST['selecteur_adherent'];
		$expediteur = information_expediteur_email_collectif();
		$contexte['nom_expediteur'] = $expediteur['nom'];
		// $contexte['email_expediteur'] = $expediteur['email'];
		$_sel = _request('selecteur_adherent');
		$sel = $_sel;
		if (!is_array($sel)) {
			$sel = $sel ? [$sel] : [];
		}
		$selection_activites = _request('selecteur_activite_evenement');
		if (!is_array($selection_activites)) {
			$selection_activites = $selection_activites ? [$selection_activites] : [];
		}
		$contexte['count_selecteur_adherent'] = count(association_email_collectif_resoudre_destinataires(
			$sel,
			$selection_activites,
			$id_evenement
		));
	}

	// Normaliser la valeur fournie au template : toujours un tableau
	$tmp_sel = _request('selecteur_adherent');
	if (!is_array($tmp_sel)) {
		$tmp_sel = $tmp_sel ? [$tmp_sel] : [];
	}
	$contexte['selecteur_adherent'] = $tmp_sel;
	$id_auteur_connecte = $GLOBALS['auteur_session']['id_auteur'];
	// $contexte['_autosave_id'] = $id_auteur_connecte;

	return $contexte;
}
function association_formulaire_email_collectif_verifier($mode = 'adherent', $id_evenement = 0) {
	$erreurs = [];

	// _request evite les notices sur les etapes oÃ¹ le champ n'est pas present
	$email_expediteur = _request('email_expediteur');
	$email_expediteur = is_scalar($email_expediteur) ? trim((string) $email_expediteur) : '';

	if ($email_expediteur !== '') {
		// Preparation domaine du site
		$website_domain = parse_url($GLOBALS['meta']['adresse_site'] ?? '', PHP_URL_HOST);
		$website_domain = is_scalar($website_domain) ? strtolower(trim((string) $website_domain)) : '';

		// Preparation domaine de l'expediteur
		$parties_email = explode('@', $email_expediteur);
		$email_domain = (count($parties_email) >= 2) ? strtolower(trim((string) end($parties_email))) : '';

		// Si le format est ambigu, on laisse la verification standard des saisies gerer l'email
		if ($website_domain && $email_domain) {
			// Autoriser le meme domaine et les sous-domaines du site
			$match_domain = (
				$email_domain === $website_domain
				|| (strlen($email_domain) > strlen($website_domain)
					&& substr($email_domain, -strlen('.' . $website_domain)) === '.' . $website_domain)
			);

			if (!$match_domain) {
				$erreurs['email_expediteur'] = "Le domaine de votre email d'envoi est incorrect.";
			}
		}
	}

	if (count($erreurs)) {
		$erreurs['message_erreur'] = 'Une erreur est presente dans votre saisie';
	}

	return $erreurs;

}
function association_formulaire_email_collectif_traiter($mode = 'adherent', $id_evenement_contexte = 0) {
	$res = [];
	$date = date('Y-m-d H:i:s');
	$titre = _request('titre');
	$sujet = _request('sujet');
	$chapeau = _request('chapeau');
	$texte_html = _request('texte_html');
	$ajouter_information_paiement = _request('ajouter_information_paiement');
	$inclure_contenu_evenement = $mode === 'evenement'
		? (_request('inclure_contenu_evenement') ?: 'oui')
		: 'non';
	$visuel_principal = _request('visuel_principal');
	$documents_joints = _request('documents_joints');
	$selecteur_adherent = _request('selecteur_adherent');
	$selecteur_activite_evenement = _request('selecteur_activite_evenement');
	$type_destinataires_evenement = _request('type_destinataires_evenement') ?: 'inscrits_evenement';
	$id_evenement = $mode === 'evenement'
		? intval($id_evenement_contexte ?: _request('id_evenement'))
		: 0;
	$from_name = _request('nom_expediteur');
	$from_email = _request('email_expediteur');
	$date_envoi = _request('date_envoi');

	$html = recuperer_fond(
		'notifications/email_collectif',
		[
			'titre' => $titre,
			'chapeau' => $chapeau,
			'texte_html' => $texte_html,
			'ajouter_information_paiement' => $ajouter_information_paiement,
			'inclure_contenu_evenement' => $inclure_contenu_evenement,
			'id_evenement' => $id_evenement,
			'mode_email_collectif' => $mode,
			'visuel_principal' => $visuel_principal,
			'documents_joints' => $documents_joints,
		]
	);

	if (!empty($date_envoi) && is_array($date_envoi) && !empty(array_filter($date_envoi))) {
		$date_envoi_jour = $date_envoi['date'];
		$date_envoi_heure = ($date_envoi['heure']) ? $date_envoi['heure'] . ':00' : '00:00:00';
		$date_start = $date_envoi_jour . ' ' . $date_envoi_heure;
		$date_start = affdate($date_start, 'Y-m-d H:i:s');
	} else {
		$date_start = $date;
	}
	// Normaliser et nettoyer la sélection des destinataires avant toute création
	$selecteur_adherent = _request('selecteur_adherent');
	if (!is_array($selecteur_adherent)) {
		$selecteur_adherent = $selecteur_adherent ? [$selecteur_adherent] : [];
	}
	$selecteur_adherent = array_map('intval', $selecteur_adherent);
	$selecteur_adherent = array_values(array_filter($selecteur_adherent, function ($v) { return $v > 0; }));
	if (!is_array($selecteur_activite_evenement)) {
		$selecteur_activite_evenement = $selecteur_activite_evenement ? [$selecteur_activite_evenement] : [];
	}

	$selecteur_activite_evenement = array_values(array_filter(array_map('intval', $selecteur_activite_evenement)));
	if ($mode === 'evenement' && $type_destinataires_evenement === 'adherents_association') {
		$selecteur_activite_evenement = [];
	} elseif ($mode === 'evenement') {
		$selecteur_adherent = [];
	}
	$destinataires = association_email_collectif_resoudre_destinataires(
		$selecteur_adherent,
		$selecteur_activite_evenement,
		$id_evenement
	);
	$count_selecteur_adherent = count($destinataires);
	if (!$count_selecteur_adherent) {
		return [
			'editable' => true,
			'message_erreur' => _T('association_communication:email_collectif_aucun_destinataire'),
		];
	}

	include_spip('inc/email_collectif');
	$id_mailshot = association_communication_mailshot_creer($sujet, $html, $destinataires, [
		'id_evenement' => $id_evenement,
		'date' => $date,
		'date_start' => $date_start,
		'from_name' => $from_name,
		'from_email' => $from_email,
	]);

	if (!$id_mailshot) {
		$res['message_erreur'] = 'Erreur lors de la création de l\'envoi';
	} else {
		$res['message_ok'] = 'Votre message va partir dans les minutes qui viennent';
		$res['redirect'] = generer_url_ecrire('mailshot', 'id_mailshot=' . $id_mailshot);
	}
	return $res;
}
function information_expediteur_email_collectif() {
	$expediteur = [];
	$id_auteur_connecte = $GLOBALS['auteur_session']['id_auteur'];
	$query_auteur = sql_fetsel('*', 'spip_auteurs', "id_auteur= $id_auteur_connecte");
	$expediteur['nom'] = $query_auteur['prenom'] . ' ' . $query_auteur['nom_famille'];
	$expediteur['email'] = ($query_auteur['input_email_membres_asso']) ? $query_auteur['input_email_membres_asso'] : $query_auteur['email'];
	return $expediteur;
}
