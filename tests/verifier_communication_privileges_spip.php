<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre execute dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/association_communication_privileges');
include_spip('inc/filtres');
$empreinte = function () {
	return hash('sha256', json_encode(array(
		sql_allfetsel('*', 'spip_mailsubscribers', '', '', 'id_mailsubscriber'),
		sql_allfetsel('*', 'spip_mailsubscriptions', '', '', 'id_mailsubscriber,id_mailsubscribinglist'),
	)));
};
$avant = $empreinte();
$auteurs = sql_allfetsel(
	'id_auteur,email,prenom,nom_famille,statut,statut_interne',
	'spip_auteurs',
	"email<>''",
	'',
	'id_auteur'
);
$valides = 0;
$abonnes = 0;
foreach ($auteurs as $auteur) {
	$email = trim((string) ($auteur['email'] ?? ''));
	if (!email_valide($email)) {
		continue;
	}
	$valides++;
	$abonne = association_communication_abonne_lire($email);
	if (!$abonne) {
		continue;
	}
	$abonnes++;
	$id_abonne = (int) $abonne['id_mailsubscriber'];
	$attendus = (int) sql_countsel('spip_mailsubscriptions', 'id_mailsubscriber=' . $id_abonne);
	$obtenus = count(association_communication_abonnements_lire($id_abonne));
	if ($attendus !== $obtenus) {
		fwrite(STDERR, "Lecture incomplete des abonnements pour un auteur.\n");
		exit(1);
	}
}
$apres = $empreinte();
if ($avant !== $apres) {
	fwrite(STDERR, "Le controle en lecture seule a modifie les abonnements.\n");
	exit(1);
}
echo json_encode(array(
	'ok' => true,
	'auteurs_email_valide' => $valides,
	'abonnes_retrouves' => $abonnes,
	'empreinte_stable' => $apres,
), JSON_UNESCAPED_SLASHES) . "\n";
