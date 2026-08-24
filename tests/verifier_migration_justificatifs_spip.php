<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre execute dans le contexte SPIP.\n");
	exit(2);
}

$legacy = sql_allfetsel(
	'l.id_document,l.id_objet AS id_compte,c.id_cotisation,l.vu',
	'spip_documents_liens AS l LEFT JOIN spip_asso_cotisations AS c ON c.id_compte=l.id_objet',
	"l.objet='compte'",
	'',
	'l.id_document'
) ?: array();
$hors_cotisations = array_values(array_filter($legacy, fn($lien) => empty($lien['id_cotisation'])));
$migrables = array_values(array_filter($legacy, fn($lien) => !empty($lien['id_cotisation'])));
$canoniques = (int) sql_countsel('spip_documents_liens', "objet='cotisation'");
$doublons = 0;
foreach ($migrables as $lien) {
	if (!empty($lien['id_cotisation']) && sql_countsel(
		'spip_documents_liens',
		"objet='cotisation' AND id_objet=" . (int) $lien['id_cotisation'] . ' AND id_document=' . (int) $lien['id_document']
	)) {
		$doublons++;
	}
}
echo json_encode(array(
	'ok' => true,
	'liens_compte' => count($legacy),
	'liens_migrables' => count($migrables),
	'liens_cotisation' => $canoniques,
	'doublons_a_fusionner' => $doublons,
	'liens_compte_hors_cotisation' => count($hors_cotisations),
	'schema' => (string) sql_getfetsel('valeur', 'spip_meta', "nom='association_adhesions_base_version'"),
	'metas_schema' => sql_allfetsel('nom,valeur', 'spip_meta', "nom LIKE 'association_adhesions%version'", '', 'nom'),
), JSON_UNESCAPED_SLASHES) . "\n";
