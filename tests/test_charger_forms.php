<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

association_test_charger_bootstrap();

$scenarios = array(
    array(
        'code' => 'public_simple',
        'callable' => 'formulaires_inscription_evenement_public_charger_dist',
        'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => false)),
        'expected' => array('creation_inscription' => 'oui', 'modification_inscription' => 'non'),
    ),
    array(
        'code' => 'prive_simple',
        'callable' => 'formulaires_inscription_evenement_charger_dist',
        'session' => array('id_auteur' => 999, 'statut' => '0minirezo'),
        'expected' => array('creation_inscription' => 'oui', 'modification_inscription' => 'non'),
    ),
);

$echecs = 0;
foreach ($scenarios as $scenario) {
    association_test_charger_appliquer_fixture($scenario);
    $resultat = call_user_func($scenario['callable'], 1, null);
    list($erreurs) = association_test_charger_assertions($scenario, $resultat);
    echo (empty($erreurs) ? '[OK] ' : '[KO] ') . $scenario['code'] . "\n";
    if (!empty($erreurs)) {
        $echecs++;
        foreach ($erreurs as $erreur) echo '  - ' . $erreur . "\n";
    }
}

exit($echecs > 0 ? 1 : 0);
