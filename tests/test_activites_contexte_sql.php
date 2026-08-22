<?php

define('_ECRIRE_INC_VERSION', true);

$GLOBALS['association_metas'] = array(
	'meta_cfg_event_inscription_sur_repetition' => 'source_et_repetition',
);
$where_capture = array();

function droit_auteur_evenements($id_auteur) {
	return array(
		'',
		'restreint',
		array(
			'condition' => 'AND id_evenement IN (184,187)',
			'affichage' => true,
		),
	);
}

function test_plugin_actif($plugin) {
	return $plugin === 'agenda';
}

function sql_allfetsel($select, $table, $where, $group = '', $order = '') {
	global $where_capture;
	$where_capture = $where;
	return array(array('id_evenement' => 184));
}

require dirname(__DIR__) . '/prive/squelettes/contenu/activites_fonctions.php';

$contexte = association_activites_contexte(13);

if (($where_capture[2] ?? '') !== 'id_evenement IN (184,187)') {
	echo "La condition SQL historique conserve un opérateur AND surnuméraire.\n";
	exit(1);
}
if (($contexte['selection_id_evenements'][0] ?? 0) !== 184) {
	echo "La sélection des événements autorisés est incorrecte.\n";
	exit(1);
}

echo "OK: le contexte activités produit des conditions SQL SPIP valides.\n";
