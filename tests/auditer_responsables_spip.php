<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}

include_spip('inc/association_evenements_responsables');

$evenements = sql_allfetsel(
	'id_evenement, id_article, responsables',
	'spip_evenements',
	'inscription=1',
	'',
	'id_evenement'
);
$empreinte = array();
$avec_responsables = 0;
$responsables_actifs = 0;

foreach ($evenements ?: array() as $evenement) {
	$id_evenement = intval($evenement['id_evenement']);
	$id_article = intval($evenement['id_article']);
	$ids = association_evenements_responsables_ids($id_evenement);
	$choix = association_evenements_responsables_choix($id_evenement, $id_article);

	if ($ids) {
		$avec_responsables++;
		$responsables_actifs += count($ids);
	}
	if ($choix['defaut'] !== $ids) {
		fwrite(STDERR, "Défauts incohérents pour l’événement {$id_evenement}.\n");
		exit(1);
	}
	if (array_diff($ids, array_keys($choix['choix']))) {
		fwrite(STDERR, "Responsable sélectionné absent des choix pour l’événement {$id_evenement}.\n");
		exit(1);
	}
	foreach ($ids as $id_auteur) {
		$actif = sql_countsel(
			'spip_auteurs',
			array('id_auteur=' . intval($id_auteur), "statut_interne='ok'")
		);
		if (intval($actif) !== 1) {
			fwrite(STDERR, "Responsable inactif retourné pour l’événement {$id_evenement}.\n");
			exit(1);
		}
	}
	$empreinte[$id_evenement] = $ids;
}

echo 'OK: ' . count($evenements ?: array()) . ' événements ouverts audités, '
	. $avec_responsables . ' avec responsables, '
	. $responsables_actifs . ' sélections actives, empreinte '
	. hash('sha256', json_encode($empreinte)) . ".\n";
