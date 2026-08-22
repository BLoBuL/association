<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PLUGIN_ROOT', dirname(__DIR__));
define('_ECRIRE_INC_VERSION', 1);

if (!function_exists('include_spip')) {
    function include_spip($path) {
        if ($path === 'inc/cotisations') {
            return true;
        }

        $try = PLUGIN_ROOT . '/' . str_replace('\\', '/', $path) . '.php';
        if (file_exists($try)) {
            include_once $try;
            return true;
        }

        return false;
    }
}

if (!function_exists('association_log')) {
    function association_log($canal, $message, $niveau = 'info') {
        return true;
    }
}

$GLOBALS['_TEST_AUTEURS_VALIDITE'] = array();

if (!function_exists('sql_fetsel')) {
    function sql_fetsel($select, $from, $where) {
        if ($from !== 'spip_auteurs') {
            return array();
        }

        if (preg_match('/id_auteur\s*=\s*(\d+)/', $where, $match)) {
            $id_auteur = intval($match[1]);
            return $GLOBALS['_TEST_AUTEURS_VALIDITE'][$id_auteur] ?? array();
        }

        return array();
    }
}

if (!function_exists('association_dates_campagne_scolaire')) {
    function association_dates_campagne_scolaire($date_reference = null, $type_adherent = 'adherent') {
        if ($date_reference === null || $date_reference <= '2026-08-31') {
            return array(
                'date_reference' => $date_reference ?: '2026-06-27',
                'date_debut_reinscription' => '2026-06-01',
                'date_fin_validite' => '2026-08-31',
                'date_fin_validite_cible' => '2026-08-31',
                'statut' => 'periode_reinscription',
            );
        }

        return array(
            'date_reference' => $date_reference,
            'date_debut_reinscription' => '2027-06-01',
            'date_fin_validite' => '2027-08-31',
            'date_fin_validite_cible' => '2027-08-31',
            'statut' => 'hors_periode_reinscription',
        );
    }
}

if (!function_exists('association_date_validite_a_accorder')) {
    function association_date_validite_a_accorder($date_reference = null, $type_adherent = 'adherent', $date_validite_actuelle = null) {
        $campagne = association_dates_campagne_scolaire($date_reference, $type_adherent);
        $date_cible = $campagne['date_fin_validite_cible'] ?? null;

        if (!empty($date_cible) && ($campagne['statut'] ?? '') === 'periode_reinscription') {
            $campagne_suivante = association_dates_campagne_scolaire('2026-09-01', $type_adherent);
            $date_cible = $campagne_suivante['date_fin_validite_cible'] ?? $date_cible;
        }

        if ($date_cible && !empty($date_validite_actuelle)) {
            $date_validite_normalisee = substr($date_validite_actuelle, 0, 10);
            if (strtotime($date_validite_normalisee) > strtotime($date_cible)) {
                $date_cible = $date_validite_normalisee;
            }
        }

        return $date_cible;
    }
}

$GLOBALS['association_metas'] = array(
    'validite' => 'scolaire',
    'date_scolaire_suivante' => '01/06',
    'date_scolaire_nouvelle' => '31/08',
    'validite_entreprise' => 'scolaire',
    'date_scolaire_suivante_entreprise' => '01/06',
    'date_scolaire_nouvelle_entreprise' => '31/08',
);

include_once PLUGIN_ROOT . '/inc/fonctions/association_validite_calculator.php';

$tests = array(
    'reinscription_adherent_actif_vers_campagne_suivante' => array(
        'auteur' => array(
            'id_auteur' => 1,
            'statut_interne' => 'ok',
            'radio_type_adherent' => 'adherent',
            'validite' => '2026-08-31 00:00:00',
        ),
        'attendu' => '31/08/2027',
    ),
    'inscription_prospect_reste_sur_campagne_courante' => array(
        'auteur' => array(
            'id_auteur' => 2,
            'statut_interne' => 'prospect',
            'radio_type_adherent' => 'adherent',
            'validite' => '',
        ),
        'attendu' => '31/08/2027',
    ),
    'reinscription_deja_renouvelee_ne_saute_pas_une_campagne' => array(
        'auteur' => array(
            'id_auteur' => 3,
            'statut_interne' => 'ok',
            'radio_type_adherent' => 'adherent',
            'validite' => '2027-08-31 00:00:00',
        ),
        'attendu' => '31/08/2027',
    ),
    'inscription_entreprise_scolaire_vers_campagne_suivante' => array(
        'auteur' => array(
            'id_auteur' => 4,
            'statut_interne' => 'prospect',
            'radio_type_adherent' => 'entreprise',
            'validite' => '',
        ),
        'attendu' => '31/08/2027',
    ),
);

$erreurs = array();

foreach ($tests as $nom => $scenario) {
    $id_auteur = intval($scenario['auteur']['id_auteur']);
    $GLOBALS['_TEST_AUTEURS_VALIDITE'][$id_auteur] = $scenario['auteur'];

    $retour = association_validite_calculator($id_auteur);
    if ($retour !== $scenario['attendu']) {
        $erreurs[] = $nom . ' attendu=' . $scenario['attendu'] . ' obtenu=' . $retour;
    }
}

$GLOBALS['association_metas']['validite'] = 'annee';
$GLOBALS['_TEST_AUTEURS_VALIDITE'][5] = array(
    'id_auteur' => 5,
    'statut_interne' => 'ok',
    'radio_type_adherent' => 'adherent',
    'validite' => '2030-12-31 00:00:00',
);
$retour_annuel = association_validite_calculator(5);
if ($retour_annuel !== '31/12/2031') {
    $erreurs[] = 'validite_annuelle attendue=31/12/2031 obtenue=' . $retour_annuel;
}

if ($erreurs) {
    echo "ECHEC\n";
    echo implode("\n", $erreurs) . "\n";
    exit(1);
}

echo "OK\n";
exit(0);
