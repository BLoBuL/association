<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

association_test_charger_bootstrap();
association_test_charger_appliquer_fixture(array(
    'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true)),
    'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
    'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok'),
    'request' => array(
        'cvtm_prev_post' => base64_encode(serialize(array('famille' => array('adherent', 'enfant_1')))),
    ),
));

$post_converti = ie_convertir_post(array('cvtm_prev_post' => _request('cvtm_prev_post')));
$data = ie_format_post('multi_public', 1, array(
    'cvtm_prev_post' => _request('cvtm_prev_post'),
    'prenom_adherent' => 'Jean',
    'nom_adherent' => 'Dupont',
    'prenom_enfant_1' => 'Alice',
    'nom_enfant_1' => 'Dupont',
));
$ok = ($post_converti['cvtm_prev_post']['famille'] ?? array()) === array('adherent', 'enfant_1')
    && intval($data['nombre_participants'] ?? 0) === 2;

echo ($ok ? '[OK]' : '[KO]') . " rehydratation multi-etapes famille\n";
exit($ok ? 0 : 1);
