<?php

// TODO Dans le futur il faudra ajouter un champ de selection pour le fichier JSON, on pourrait en prévoir plusieurs par pays
// TODO Désactiver la selection des destinations déjà existants en BDD
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/destinations');
// Fonction de déclaration des saisies
function formulaires_importer_destination_comptable_saisies_dist() {
	$saisies = $saisies_destinations = $saisies_destinations = [];

	// On décode le fichier JSON
	$json_data = decoder_fichier_json('json/destinations_comptables_2024.json');
	// $json_data=decoder_fichier_json('json/destination_comptable_complet_2024.json');

	// On ajoute une saisie pour proposer l'ajout de sous destinations
	/*    $saisies_destinations[] = array(
			'saisie' => 'radio',
			'options' => array(
				'nom' => 'proposer_sousdestination',
				'label' => '<:association_compta:form_import_pc_proposer_sousdestination_label:>',
				'explication' => '<:association_compta:form_import_pc_proposer_sousdestination_explication:>',
				'datas' => array('oui' => '<:association_compta:oui:>', 'non' => '<:association_compta:non:>'),
				'defaut' => 'non',
			),
		);*/

	// On boucle sur les destinations du destination comptable
	$saisies_destinations = [];
	$compteur_niveau = 0;
	foreach ($json_data['Destinations'] as $destination) {
		$fieldset = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'class_' . $destination['Numero'],
				'label' => $destination['Numero'] . ' - ' . $destination['Libelle'],
				// 'explication' => $destination['Description'],
			],
			'saisies' => generer_saisies_destinations([$destination], '', $compteur_niveau),
		];

		$saisies_destinations[] = $fieldset;
	}

	// On ajoute les saisies des destinations au formulaire
	$saisies[] = [
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'import_destination_comptable',
			'label' => '<:association_compta:form_import_pc_fieldset_label:>',
			'explication' => '<:association_compta:form_import_pc_fieldset_explication:>',
		],
		'saisies' => $saisies_destinations,
	];

	return $saisies;
}
// Fonction de chargement du formulaire
function formulaires_importer_destination_comptable_charger_dist() {
	$contexte = [];
	// On prépare le contexte du formulaire
	$contexte = preparer_liste_asso_destination_comptable('array');

	return $contexte;
}
// Fonction de vérification des saisies
function formulaires_importer_destination_comptable_verifier_dist() {
	$erreurs = [];
	/*   if (!$_FILES['fichier']['name']) {
		   $erreurs['fichier'] = _T('association_compta:erreur_fichier_manquant');
	   }*/
	return $erreurs;
}
// Fonction de traitement du formulaire
// Le but est de mettre à jour la table spip_asso_destination avec les données du formulaire
function formulaires_importer_destination_comptable_traiter_dist() {
	$retour = [];

	// On décode le fichier JSON
	// $json_data=decoder_fichier_json('json/destination_comptable_complet_2024.json');
	$json_data = decoder_fichier_json('json/destinations_comptables_2024.json');
	// On récupère la liste des destinations du fichier JSON
	$array_destinations_json = lister_destinations_json($json_data);
	// On récupère la liste des destinations existantes dans la table spip_asso_destination
	$destinations = array_keys(preparer_liste_asso_destination_comptable('array'));
	// On boucle sur toutes les valeurs du formulaire
	foreach ($_POST as $code => $value) {

		// On vérifie si la valeur est un destination en comptant le nombre de caractères
		if (is_integer($code) && $value == 'on' && $code > 9) {

			// Si le destination n'existe pas, on l'ajoute
			$args = ['intitule' => $code . ' - ' . $array_destinations_json[$code]['Libelle'], 'commentaire' => $array_destinations_json[$code]['Description']];
			sql_insertq('spip_asso_destination', $args);
		}
	}
	// On retourne un message de succès
	$retour['message_ok'] = _T('association_compta:message_import_reussi');
	// On renvoi vers la page de gestion des destinations
	$retour['redirect'] = generer_url_ecrire('destinations');

	return $retour;
}
// Fonction de lecture du fichier JSON
function decoder_fichier_json($fichier_json) {
	$file = find_in_path($fichier_json);
	$json = file_get_contents($file);

	// On décode le contenu du fichier JSON
	$json_data = json_decode($json, true);
	if ($file == false) {
		echo _T('association_compta:erreur_fichier_inexistant');

	} elseif ($json_data == false) {
		echo _T('association_compta:erreur_fichier_invalide');
	} else {
		return $json_data;
	}
}
// Fonction pour les lister les destinations avec ID et intitulé inclus dans le json
function lister_destinations_json($json_data) {
	$destinationsList = [];
	foreach ($json_data['Destinations'] as $Destinations) {
		// Check if the class contains accounts
		if (isset($Destinations['SousDestinations'])) {
			// Add the accounts of the class to the list recursively
			lister_destination_recurive($Destinations['SousDestinations'], $destinationsList);
		}
	}
	return $destinationsList;
}
function lister_destination_recurive($destinationsArray, &$destinationsList) {
	foreach ($destinationsArray as $destination) {
		// Add the current account to the list
		$destinationsList[$destination['Numero']] = ['Libelle' => $destination['Libelle'], 'Description' => $destination['Description']];

		// If there are sub-accounts, call the function recursively
		if (isset($destination['SousDestinations'])) {
			lister_destination_recurive($destination['SousDestinations'], $destinationsList);
		}
	}
}
function generer_saisies_destinations($data, $nomParent = '', $compteur_niveau = 0) {
	$resultat = [];

	foreach ($data as $element) {
		$numero = $element['Numero'];
		$nom = $numero;
		$label = $numero . ' - ' . $element['Libelle'];
		$description = $element['Description'] ?? '';
		$destination = substr($numero, 0, 1);
		$case = [
			'saisie' => 'case',
			'options' => [
				'nom' => $nom,
				'label_case' => $label,
				'explication' => $description,
			],

		];

		if ($nomParent) {
			$case['options']['conteneur_class'] = 'destination niveau-' . $compteur_niveau;
			// TODO :Cette condition ne fonctionne pas sur tous les champs, Il faudrait peut être rajouter des fieldsets à chaque niveaux...
			$case['options']['afficher_si'] = '@' . $destination . '@=="on"';
		} else {
			$case['options']['conteneur_class'] = 'classe';
		}
		$resultat[] = $case;

		if (isset($element['destinations'])) {
			$resultat = array_merge($resultat, generer_saisies_destinations($element['destinations'], $nom, $compteur_niveau + 1));
		}

		if (isset($element['SousDestinations'])) {
			$resultat = array_merge($resultat, generer_saisies_destinations($element['SousDestinations'], $nom, $compteur_niveau + 1));
		}
	}

	return $resultat;
}
