<?php

// Exécuter les entrées sensibles avec un jeton valide mais sans droit métier.
define('_ECRIRE_INC_VERSION', 1);
function include_spip($fichier) {}
function charger_fonction($nom, $type) {
	if ($nom !== 'securiser_action') { throw new RuntimeException('Chargement inattendu'); }
	return static function () { return 12; };
}
function autoriser(...$args) { $GLOBALS['droits_verifies'][] = $args; return false; }
function _request($nom) { return $nom === 'id_evenement' ? 42 : 12; }
function _T($cle) { return $cle; }
function sql_getfetsel($champ, $table, $where) { return 42; }
function sql_updateq(...$args) { throw new RuntimeException('Écriture interdite'); }
function sql_insertq(...$args) { throw new RuntimeException('Insertion interdite'); }
function sql_delete(...$args) { throw new RuntimeException('Suppression interdite'); }
function association_capacite_disponible($nom) { return true; }
function association_programmer_campagne($campagne) { throw new RuntimeException('Envoi interdit'); }

$actions = array(
	'association-ventes' => array('editer_asso_ventes'),
	'association-dons' => array('editer_asso_dons'),
	'association-prets' => array('editer_asso_ressources'),
	'association-compta' => array('ajouter_destinations', 'modifier_destinations', 'editer_asso_plan', 'editer_asso_comptes'),
	'association-adhesions' => array('supprimer_adherents', 'envoyer_relances', 'envoyer_email_collectif_adherent'),
	'association-evenements' => array('ajouter_activites', 'modifier_activites', 'envoyer_email_collectif_activite'),
);
$nombre = 0;
foreach ($actions as $module => $noms) {
	foreach ($noms as $nom) {
		require dirname(__DIR__) . '/plugins/' . $module . '/action/' . $nom . '.php';
		$GLOBALS['droits_verifies'] = array();
		$fonction = 'action_' . $nom;
		$fonction();
		if (!$GLOBALS['droits_verifies']) { throw new RuntimeException('Autorisation absente : ' . $nom); }
		$nombre++;
	}
}
echo "OK : $nombre actions refusent un appel sans droit, sans écriture ni email.\n";
