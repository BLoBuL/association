<?php

define('_ECRIRE_INC_VERSION', 1);

$GLOBALS['association_test_zones_liees'] = array();
$GLOBALS['association_test_where'] = '';

function sql_allfetsel($select, $table, $where = '') {
    $GLOBALS['association_test_where'] = $where;
    return array_map(static function ($id_zone) {
        return array('id_zone' => $id_zone);
    }, $GLOBALS['association_test_zones_liees']);
}

require_once dirname(__DIR__) . '/plugins/association-adhesions/inc/fonctions/priviliges_adherent.php';

function association_test_zone_assert($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "ECHEC: {$message}\n");
        exit(1);
    }
}

$GLOBALS['association_test_zones_liees'] = array(2, 4);
association_test_zone_assert(
    association_auteur_zones_adherent_liees(array(2, 4), 42) === array(2, 4),
    'Les liaisons vers toutes les zones configurees doivent etre reconnues'
);
association_test_zone_assert(
    $GLOBALS['association_test_where'] === "id_zone IN (2,4) AND objet='auteur' AND id_objet=42",
    'La recherche doit cibler les zones configurees et l auteur'
);

$GLOBALS['association_test_zones_liees'] = array(2);
$zones_manquantes = array_values(array_diff(
    association_zones_adherent_normaliser(array('2', 4, 4, 0)),
    association_auteur_zones_adherent_liees(array(2, 4), 42)
));
association_test_zone_assert(
    $zones_manquantes === array(4),
    'Une zone configuree manquante doit etre detectee sans confondre les autres zones'
);
association_test_zone_assert(
    association_auteur_zones_adherent_liees(array(), 42) === array(),
    'Une configuration vide ne doit produire aucune liaison'
);

fwrite(STDOUT, "OK: controle cible des zones adherent/documentation\n");
