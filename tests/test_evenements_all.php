<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';
require_once __DIR__ . '/test_evenements_helpers.php';
require_once __DIR__ . '/test_evenements_charger.php';
require_once __DIR__ . '/test_evenements_verifier.php';
require_once __DIR__ . '/test_evenements_traiter.php';
require_once __DIR__ . '/test_evenements_recapitulatif.php';
require_once __DIR__ . '/test_evenement_defauts.php';
require_once __DIR__ . '/test_notifications_evenements_i18n.php';
require_once __DIR__ . '/test_diagnostic_formulaire_inscription.php';

$exit = 0;
$exit |= association_test_run_helpers_suite();
$exit |= association_test_run_charger_suite();
$exit |= association_test_run_verifier_suite();
$exit |= association_test_run_traiter_suite();
$exit |= association_test_run_recapitulatif_suite();
$exit |= association_test_run_evenement_defauts_suite();
$exit |= association_test_run_notifications_evenements_i18n_suite();
$exit |= association_test_run_diagnostic_formulaire_inscription_suite();
$commande_matrice_charger = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/test_charger_inscriptions_matrix.php');
$code_matrice_charger = 0;
passthru($commande_matrice_charger, $code_matrice_charger);
$exit |= ($code_matrice_charger === 0 ? 0 : 1);

exit($exit ? 1 : 0);
