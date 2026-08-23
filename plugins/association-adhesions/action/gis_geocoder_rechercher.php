<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Proxy vers le service du geocoder
 *
 * Cette fonction permet de transmettre une requete auprès du service
 * de recherche d'adresse d'OpenStreetMap (Nomatim ou Photon).
 *
 * Seuls les arguments spécifiques au service sont transmis.
 */
function action_gis_geocoder_rechercher_dist() {
	include_spip('inc/modifier');
	/* On filtre les arguments à renvoyer à Nomatim (liste blanche) */
	$arguments = collecter_requests(['format', 'q', 'limit', 'addressdetails', 'accept-language', 'lat', 'lon'], []);
	include_spip('inc/gis_geocode');
	if ($data = gis_geocode_request(_request('mode'), $arguments)) {
		header('Content-Type: application/json; charset=UTF-8');
		echo $data;
	}
}
/*
function action_gis_geocoder_rechercher_dist() {

	// initialisation des valeurs de config
	include_spip('inc/config');
	$config = lire_config('gis', []);
    $api_key_google = $config['api_key_google'];

	include_spip('inc/modifier');
	 //On filtre les arguments à renvoyer à Nomatim (liste blanche)
	$arguments = collecter_requests(['format', 'q', 'limit', 'addressdetails', 'accept-language', 'lat', 'lon'], []);

    if($api_key_google){

        $url = 'https://maps.googleapis.com/maps/api/geocode/';
        $format= 'json';

        $arguments_googleapis = array();
        $arguments_googleapis['address']  = $arguments['q'];
        $arguments_googleapis['key']  = $api_key_google;
        $request_googleapis = "{$url}{$format}?" . http_build_query($arguments_googleapis);
        include_spip('inc/distant');
        $data = recuperer_url($request_googleapis);
        $data = $data['page'];

    }else{
       include_spip('inc/gis_geocode');
	   $data = gis_geocode_request(_request('mode'), $arguments);
   }


	if ($data) {
		header('Content-Type: application/json; charset=UTF-8');
association_log('gis', 'gis_geocoder_rechercher: reponse', 'debug', array('data' => $data));
		echo $data;
	}


}*/
