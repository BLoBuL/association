<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Gère l'enregistrement ou la modification des coordonnées GPS d'un auteur.
 *
 * Cette fonction utilise un service de géocodage pour obtenir les coordonnées GPS
 * d'un auteur en fonction de son adresse. Elle peut créer ou mettre à jour un point GIS
 * associé à l'auteur, et envoyer des notifications en cas de succès ou d'échec.
 *
 * @since 3.1.0
 * @version 0.4b
 *
 * @param int $id_auteur L'identifiant de l'auteur.
 * @param string $action L'action à effectuer : 'creation' ou 'modification'.
 */
function gis_auteur($id_auteur, $action, $id_gis = null) {
	include_spip('inc/association_adhesions_integrations');
	if (!association_adhesions_integration_active('gis')) {
		return false;
	}
	include_spip('action/editer_gis');
	if ($action === 'suppression') {
		return $id_gis ? gis_supprimer((int) $id_gis) : false;
	}
	// Appel au service de géocodage
	include_spip('inc/config');
	include_spip('inc/gis_geocode');
	include_spip('action/editer_objet');

	// Récupération de la configuration des notifications GIS
	$notification_gis_config_action = $GLOBALS['association_metas']['notification_gis_config_action'] ?? [];
	if (is_string($notification_gis_config_action)) {
		$notification_gis_config_action = @unserialize($notification_gis_config_action, ['allowed_classes' => false]);
	}
	$notification_gis_config_action = is_array($notification_gis_config_action) ? $notification_gis_config_action : [];
	$query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
	$nom_auteur = $query_auteur['prenom'] . ' ' . $query_auteur['nom_famille'];

	// Vérification des conditions pour traiter l'auteur
	if ($query_auteur['statut_interne'] == 'ok'
		&& !empty($query_auteur['adresse'])
		&& $query_auteur['oui_non_confidentialite_gmap'] == 'oui'
		&& $query_auteur['webmestre'] == 'non'
		&& empty($query_auteur['auteur_compte_principal'])) {

		// Construction de l'adresse pour le géocodage
		$ville = empty($query_auteur['ville']) ? $GLOBALS['association_metas']['ville'] : $query_auteur['ville'];
		$code_postal = $query_auteur['code_postal'];
		$pays = !isset($query_auteur['pays']) ? $GLOBALS['association_metas']['pays'] : $query_auteur['pays'];
		$string_recherche = ($GLOBALS['association_metas']['pays'] == 'United Kingdom')
			? $query_auteur['adresse'] . ', ' . $code_postal . ', ' . $pays
			: $query_auteur['adresse'] . ', ' . $code_postal . ' ' . $ville . ', ' . $pays;

		$config = lire_config('gis', []);

		$json = gis_geocode_request('search', [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
			'accept-language' => 'en',
			'q' => $string_recherche,
		]);
		$geocoder = json_decode($json, true);

		// Si des coordonnées sont trouvées
		if (!empty($geocoder) && !empty($geocoder['features'])) {
			$coordonnees_gps = $geocoder['features'][0]['geometry']['coordinates'];
			$adresse_complete = $geocoder['features'][0]['properties'];
			$adresse = $query_auteur['adresse'];
			$city_county = $adresse_complete['city'] ?? $adresse_complete['county'];

			// Préparation des données GIS
			$c = [
				'titre' => $nom_auteur,
				'lon' => $coordonnees_gps[0],
				'lat' => $coordonnees_gps[1],
				'zoom' => $config['zoom'] ?? '16',
				'adresse' => $adresse,
				'code_postal' => $adresse_complete['postcode'],
				'ville' => $city_county,
				'departement' => $adresse_complete['district'],
				'region' => $adresse_complete['state'],
				'pays' => $adresse_complete['country'],
				'code_pays' => $adresse_complete['countrycode'],
			];
			$point_auteur_gis = association_adhesions_gis_point_auteur($id_auteur);

			// Mise à jour ou création du point GIS
			if ($action == 'modification' && !empty($point_auteur_gis)
				&& ($coordonnees_gps[0] != $point_auteur_gis['lon'] || $coordonnees_gps[1] != $point_auteur_gis['lat'])) {
				gis_modifier($point_auteur_gis['id_gis'], $c);

				// Notification en cas de modification
				if (!empty($GLOBALS['association_metas']['notification_gis_config_email'])
					&& in_array('modification_adherent', $notification_gis_config_action, true)) {
					job_queue_add('facteur_envoyer_notification_gis', 'Notification - Mise à jour adresse GPS', [
						$id_auteur, $nom_auteur, $string_recherche, $adresse_complete, $point_auteur_gis['id_gis'], 'modification',
					], '', true, 0, 0);
				}
			} elseif ($action == 'creation' || empty($point_auteur_gis)) {
				$id_gis = objet_inserer('gis');
				gis_modifier($id_gis, $c);
				gis_associer($id_gis, ['id_auteur' => $id_auteur]);
			}
		} else {
			// Notification en cas d'échec
			if (!empty($GLOBALS['association_metas']['notification_gis_config_email'])
				&& in_array('echec_adherent', $notification_gis_config_action, true)) {
				job_queue_add('facteur_envoyer_notification_gis', 'Notification - Echec création adresse GPS', [
					$id_auteur, $nom_auteur, $string_recherche, false, false, 'echec',
				], '', true, 0, 0);
			}
		}
	}
	return true;
}
