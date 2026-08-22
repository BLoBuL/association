<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

association_test_charger_bootstrap();
association_test_charger_appliquer_fixture(array(
    'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true)),
    'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
    'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok'),
));

$resultat = formulaires_inscription_evenement_multi_public_charger_dist(1, null);
$noms = association_test_charger_extraire_noms($resultat['_saisies'] ?? array());
$ok = !empty($resultat['_saisies_par_etapes']) && in_array('famille', $noms, true);

echo ($ok ? '[OK]' : '[KO]') . " saisies multi public famille\n";
exit($ok ? 0 : 1);
