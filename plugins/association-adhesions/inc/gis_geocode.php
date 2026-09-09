<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Récupérer les données depuis le geocoder (Nomatim ou Photon) ou Google si une clé API valide est renseignée dans la config du plugin
 *
 * @param string $mode
 *    mode de geocoding : search ou reverse
 * @param array $arguments
 *    liste des arguments de la requête : format, q, limit, addressdetails, accept-language, lat, lon
 * @return string données renvoyées par le geocoder
 */
function gis_geocode_request($mode, $arguments = []) {
	// initialisation des valeurs de config
	include_spip('inc/distant');
	include_spip('inc/config');
	$config = lire_config('gis', []);
	$api_key_google = $config['api_key_google'];

	if (!$mode or !in_array($mode, ['search', 'reverse'])) {
		return '';
	}

	if (!empty($api_key_google)) {
		$geocoder = 'google_maps_platform';
	} else {
		$geocoder = defined('_GIS_GEOCODER') ? _GIS_GEOCODER : 'photon';
	}

	if ($geocoder == 'photon') {
		unset($arguments['format']);
		unset($arguments['addressdetails']);
	}

	if (!empty($arguments) and in_array($geocoder, ['photon', 'nominatim', 'google_maps_platform'])) {

		if ($geocoder == 'google_maps_platform') {

			$url = 'https://maps.googleapis.com/maps/api/geocode/';
			$format = 'json';

			$arguments_googleapis = [];
			if ($mode == 'search') {
				$arguments_googleapis['address'] = $arguments['q'];
			} else {
				$arguments_googleapis['latlng'] = $arguments['lat'] . ',' . $arguments['lon'];
			}

			$arguments_googleapis['key'] = $api_key_google;
			$request_url = "{$url}{$format}?" . http_build_query($arguments_googleapis);
			$data_googleapis = recuperer_url($request_url);
			$data = gis_geocode_google_format($data_googleapis);

		} elseif ($geocoder == 'photon') {
			if (isset($arguments['accept-language'])) {
				// ne garder que les deux premiers caractères du code de langue, car les variantes spipiennes comme fr_fem posent problème
				$arguments['lang'] = substr($arguments['accept-language'], 0, 2);
				unset($arguments['accept-language']);
			}
			if ($mode == 'search') {
				$mode = 'api/';
			} else {
				$mode = 'reverse';
			}
			$url = 'http://photon.komoot.io/';

			$url = defined('_GIS_GEOCODER_URL') ? _GIS_GEOCODER_URL : $url;
			$request_url = "{$url}{$mode}?" . http_build_query($arguments);
			$data = recuperer_url($request_url);

		} else {
			$url = 'http://nominatim.openstreetmap.org/';

			$url = defined('_GIS_GEOCODER_URL') ? _GIS_GEOCODER_URL : $url;
			$request_url = "{$url}{$mode}?" . http_build_query($arguments);
			$data = recuperer_url($request_url);
		}
		return $data['page'];
	} else {
		return '';
	}
}

// Cette fonction à pour but de format les résultat de l'API de Google pour GIS
// Voici de la documentation
// https://developers.google.com/maps/documentation/geocoding/requests-geocoding
// https://developers.google.com/maps/documentation/geocoding/requests-reverse-geocoding
function gis_geocode_google_format($data_googleapis) {

	/* On decode la reponse en JSON */
	$data_googleapis_json_decode = json_decode($data_googleapis['page'], true);

	/* Ici on a les resultats de Google: */
	$data_google_location = $data_googleapis_json_decode['results'][0];

	/* On extrait les info GPS */
	$gps_long = $data_google_location['geometry']['location']['lat'];
	$gps_lat = $data_google_location['geometry']['location']['lng'];
	// Ci-dessous l'adresse complète reformatée pour les humains
	// $adresse_complete = $data_google_location['formatted_address'];

	// Détails de l'adresse fournie par Google
	$address_components = $data_google_location['address_components'];

	// Correspondance des champs, ceux-ci sont très changeant, ca peut etre améliorer
	$properties = [];
	foreach ($address_components as $address_component) {
		if ($address_component['types'][0] == 'street_number') {
			$properties['housenumber'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'premise') {
			// $properties[''] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'route') {
			$properties['street'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'neighborhood') {
			$properties['district'] = $address_component['long_name'];
		} elseif ($address_component['types'][1] == 'sublocality') {
			$properties['city'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'locality') {
			$properties['city'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'administrative_area_level_1') {
			// $properties['state'] = $address_component['long_name'];
			$properties['state'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'administrative_area_level_2') {
			// $properties['county'] == $address_component['long_name'];
			$properties['county'] == $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'postal_code') {
			$properties['postcode'] = $address_component['long_name'];
		} elseif ($address_component['types'][0] == 'country') {
			$properties['country'] = $address_component['long_name'];
			$properties['countrycode'] = $address_component['short_name'];
		}

	}

	/* Ici on recrée un array au format attendu par GIS */
	$data_format_location = ['features' => [
		['geometry' => [
			'coordinates' => [
				$gps_lat,
				$gps_long,
			],
			'type' => 'Point',
		],
			'type' => 'Feature',
			'properties' => $properties,
		],
	],
		'type' => 'FeatureCollection',
	];

	// On va recrée le retour complet en gardant celui d'origine de google pour une utilisation potentielle ultérieure
	$json_complet = array_merge($data_googleapis_json_decode, $data_format_location);
	// On encode le tout en JSON
	$data_googleapis['page'] = json_encode($json_complet);

	return $data_googleapis;
}
