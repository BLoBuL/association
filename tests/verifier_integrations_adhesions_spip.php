<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre lance dans le contexte SPIP.\n");
	exit(1);
}

include_spip('inc/association_adhesions_integrations');

$acces_restreint = association_adhesions_integration_active('accesrestreint');
$gis = association_adhesions_integration_active('gis');
$zones = association_adhesions_zones_options();
$point = association_adhesions_gis_point_auteur((int) ($GLOBALS['visiteur_session']['id_auteur'] ?? 0));

if (!$acces_restreint && $zones) {
	fwrite(STDERR, "ECHEC: des zones sont lues alors qu'Acces restreint est inactif.\n");
	exit(1);
}
if (!$gis && $point) {
	fwrite(STDERR, "ECHEC: un point est lu alors que GIS est inactif.\n");
	exit(1);
}

echo json_encode(array(
	'accesrestreint_actif' => $acces_restreint,
	'gis_actif' => $gis,
	'nombre_zones' => count($zones),
	'point_session_present' => !empty($point),
), JSON_UNESCAPED_SLASHES) . "\n";
