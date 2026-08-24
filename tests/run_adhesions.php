<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$racine_bo = dirname(__DIR__);

$tests = array(
    $racine_bo . '/tests/test_adhesions_api.php',
    $racine_bo . '/tests/test_adhesions_echeances.php',
    $racine_bo . '/tests/test_adhesions_parametrage.php',
    $racine_bo . '/tests/test_adhesions_notifications_audit.php',
    $racine_bo . '/tests/test_adhesions_justificatifs_bo.php',
    $racine_bo . '/tests/test_adhesions_formulaire_cotisation_bo.php',
    $racine_bo . '/tests/test_validite_reinscription_scolaire.php',
    $racine_bo . '/tests/test_architecture_spip4.php',
);

$echecs = array();
foreach ($tests as $test) {
    echo "\n=== " . basename($test) . " ===\n";
    if (!is_file($test)) {
        echo "ECHEC: fichier de test introuvable: $test\n";
        $echecs[] = $test;
        continue;
    }

    $commande = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test);
    passthru($commande, $code);
    if ($code !== 0) {
        $echecs[] = $test;
    }
}

if ($echecs) {
    echo "\nSuite adhésion en échec (" . count($echecs) . ").\n";
    exit(1);
}

echo "\nSuite adhésion réussie.\n";
exit(0);
