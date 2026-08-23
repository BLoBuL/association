<?php

define('_ECRIRE_INC_VERSION', true);
$GLOBALS['test_request'] = array();

function include_spip($fichier) { return true; }
function _request($cle) { return $GLOBALS['test_request'][$cle] ?? null; }
function _T($cle) { return $cle; }

require dirname(__DIR__) . '/plugins/association-compta/formulaires/editer_asso_destinations.php';

$erreurs = formulaires_editer_asso_destinations_verifier_dist();
if (($erreurs['intitule'] ?? '') !== 'info_obligatoire' || empty($erreurs['message_erreur'])) {
	fwrite(STDERR, "Une destination sans intitulé n'est pas refusée\n");
	exit(1);
}

$GLOBALS['test_request']['intitule'] = 'Destination de recette';
if (formulaires_editer_asso_destinations_verifier_dist() !== array()) {
	fwrite(STDERR, "Une destination intitulée est refusée\n");
	exit(1);
}

echo "Validation des destinations conforme\n";
