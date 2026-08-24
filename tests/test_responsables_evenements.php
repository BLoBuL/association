<?php

define('_ECRIRE_INC_VERSION', true);

$evenements_test = array(
	10 => array('inscription' => 1, 'responsables' => serialize(array(3, 2, 99))),
	11 => array('inscription' => 0, 'responsables' => '2,3'),
);

function sql_fetsel($select, $table, $where) {
	global $evenements_test;
	if ($table === 'spip_evenements' && preg_match('/id_evenement=(\d+)/', $where, $match)) {
		return $evenements_test[intval($match[1])] ?? false;
	}
	return false;
}

function sql_getfetsel($select, $table, $where) {
	return $table === 'spip_evenements' ? 9 : null;
}

function sql_in($champ, $valeurs) {
	return $champ . ' IN (' . implode(',', array_map('intval', $valeurs)) . ')';
}

function sql_allfetsel($select, $table, $where = '', $groupby = '', $orderby = '') {
	if (str_contains($table, 'spip_auteurs_liens')) {
		return array(
			array('id_auteur' => 2, 'nom_famille' => 'Alpha', 'prenom' => 'Anne', 'nom' => ''),
		);
	}
	if ($table === 'spip_auteurs') {
		if (str_contains($select, 'nom_famille')) {
			return array(array('id_auteur' => 3, 'nom_famille' => 'Beta', 'prenom' => 'Bob', 'nom' => ''));
		}
		return array(array('id_auteur' => 2), array('id_auteur' => 3));
	}
	return array();
}

require dirname(__DIR__) . '/plugins/association-evenements/inc/association_evenements_responsables.php';

function responsables_assert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

responsables_assert(
	association_evenements_normaliser_responsables('2, 03;0|invalide') === array(2, 3),
	'La normalisation CSV historique est incorrecte.'
);
responsables_assert(
	association_evenements_normaliser_responsables(serialize(array(3, '2', 3))) === array(3, 2),
	'La normalisation sérialisée historique est incorrecte.'
);
responsables_assert(
	association_evenements_responsables_ids(10) === array(3, 2),
	'Les responsables actifs sélectionnés ne sont pas conservés dans leur ordre.'
);
responsables_assert(
	association_evenements_responsables_ids(11) === array(),
	'Un événement sans inscription ne doit pas produire de destinataires.'
);

$choix = association_evenements_responsables_choix(10);
responsables_assert(
	$choix['choix'] === array(2 => 'Alpha Anne', 3 => 'Beta Bob')
		&& $choix['defaut'] === array(3, 2)
		&& $choix['disable'] === false,
	'Le contrat du champ de responsables est incorrect pour un événement existant.'
);

$choix_nouveau = association_evenements_responsables_choix(0, 9);
responsables_assert(
	$choix_nouveau['defaut'] === array(2),
	'Les auteurs de l’article doivent initialiser un nouvel événement.'
);

$racine_plugin = dirname(__DIR__) . '/plugins/association-evenements';
$sources = '';
$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine_plugin));
foreach ($iterateur as $fichier) {
	if ($fichier->isFile() && in_array($fichier->getExtension(), array('php', 'html'), true)) {
		$sources .= file_get_contents($fichier->getPathname());
	}
}
responsables_assert(
	!str_contains($sources, 'liste_responsables_evenement')
		&& !str_contains($sources, 'function responsables_evenement('),
	'Une API legacy de responsables est encore appelée ou déclarée.'
);

echo "OK: les responsables d’événement utilisent une API métier unique.\n";
