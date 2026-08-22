<?php

define('_ECRIRE_INC_VERSION', 1);

function _T($key) {
    return $key;
}

function saisies_tableau2chaine($data) {
    return $data;
}

require_once dirname(__DIR__) . '/formulaires/inc/adherents_recherche_avancee.php';

$fieldset_vide = array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'fieldset_vide',
        'label' => 'Groupe vide',
    ),
    'saisies' => array(
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'champ_desactive',
                'label' => 'Champ désactivé',
            ),
        ),
    ),
);

$resultat_vide = adherents_recherche_avancee_formater_saisie(
    $fieldset_vide,
    array('champ_actif'),
    'multicritere'
);

if ($resultat_vide !== array()) {
    fwrite(STDERR, "Un fieldset sans champ actif ne doit pas être rendu.\n");
    exit(1);
}

$fieldset_mixte = $fieldset_vide;
$fieldset_mixte['saisies'][] = array(
    'saisie' => 'input',
    'options' => array(
        'nom' => 'champ_actif',
        'label' => 'Champ actif',
    ),
);

$resultat_mixte = adherents_recherche_avancee_formater_saisie(
    $fieldset_mixte,
    array('champ_actif'),
    'multicritere'
);

if (count($resultat_mixte['saisies'] ?? array()) !== 1
    || ($resultat_mixte['saisies'][0]['options']['nom'] ?? '') !== '_input_champ_actif_recherche_multicritere') {
    fwrite(STDERR, "Un fieldset mixte doit conserver uniquement ses champs actifs.\n");
    exit(1);
}

echo "OK: les fieldsets de recherche vides sont supprimés.\n";
