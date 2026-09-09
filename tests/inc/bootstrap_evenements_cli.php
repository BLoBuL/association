<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    define('_ECRIRE_INC_VERSION', 'test');
}

if (!defined('ASSOCIATION_TEST_PLUGIN_ROOT')) {
    define('ASSOCIATION_TEST_PLUGIN_ROOT', dirname(__DIR__, 2));
}

require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/association_evenements_fonctions.php';
require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-communication/association_communication_fonctions.php';

if (!defined('_LOG_ERREUR')) {
    define('_LOG_ERREUR', 'ERREUR');
}
if (!defined('_LOG_DEBUG')) {
    define('_LOG_DEBUG', 'DEBUG');
}
if (!defined('_LOG_INFO')) {
    define('_LOG_INFO', 'INFO');
}
if (!defined('_LOG_INFO_IMPORTANTE')) {
    define('_LOG_INFO_IMPORTANTE', 'INFO');
}
if (!defined('_LOG_CRITIQUE')) {
    define('_LOG_CRITIQUE', 'CRIT');
}

$GLOBALS['association_test_now'] = '2026-06-20 12:00:00';

function association_test_default_scenario() {
    return array(
        'association_metas' => array(
            'meta_cfg_event_config_accompagnants' => 'membre_famille',
            'meta_cfg_event_message_responsable' => 'oui',
            'meta_cfg_event_form_info_supp' => 'non',
            'meta_cfg_taxe_evenement' => 0,
            'pages_modalite_evenement' => array('page_cgu'),
            'comptes' => true,
        ),
        'auteur_session' => array(
            'id_auteur' => 1,
        ),
        'visiteur_session' => array(),
        'db' => array(
            'spip_auteurs' => array(
                1 => array(
                    'id_auteur' => 1,
                    'prenom' => 'Jean',
                    'nom_famille' => 'Dupont',
                    'email' => 'jean@example.test',
                    'mobile' => '0102030405',
                    'statut' => '6forum',
                    'statut_interne' => 'ok',
                    'radio_type_adherent' => 'famille',
                    'validite' => '2026-12-31 23:59:59',
                    'prenom_conjoint' => 'Marie',
                    'nom_conjoint' => 'Dupont',
                    'email_conjoint' => 'marie@example.test',
                    'mobile_conjoint' => '0607080910',
                    'prenom_enfant_1' => 'Alice',
                    'date_naissance_enfant_1' => '2016-05-01',
                    'prenom_enfant_2' => 'Leo',
                    'date_naissance_enfant_2' => '2018-09-12',
                    'invite_1' => 'Invite Un',
                    'invite_2' => 'Invite Deux',
                ),
                2 => array(
                    'id_auteur' => 2,
                    'prenom' => 'Nora',
                    'nom_famille' => 'Martin',
                    'email' => 'nora@example.test',
                    'mobile' => '0700000000',
                    'statut' => '6forum',
                    'statut_interne' => 'prospect',
                    'radio_type_adherent' => 'individuel',
                    'validite' => '2026-12-31 23:59:59',
                ),
                3 => array(
                    'id_auteur' => 3,
                    'prenom' => 'Admin',
                    'nom_famille' => 'Blobul',
                    'email' => 'admin@example.test',
                    'mobile' => '0999999999',
                    'statut' => '0minirezo',
                    'statut_interne' => 'ok',
                    'radio_type_adherent' => 'famille',
                    'validite' => '2026-12-31 23:59:59',
                ),
            ),
            'spip_evenements' => array(
                1 => array(
                    'id_evenement' => 1,
                ),
            ),
            'spip_asso_activites' => array(),
            'spip_transactions' => array(),
            'spip_asso_categories_activites' => array(
                1 => array(
                    'id_categorie' => 1,
                    'valeur' => 'Adherent',
                    'titre' => 'Tarif adherent',
                    'statut' => 'ok',
                    'quantite' => 1,
                    'commentaires' => 'Tarif standard adherent',
                    'type_inscrit' => 'adherent',
                    'montant' => 20,
                    'montant_symbole' => '20 EUR',
                ),
                2 => array(
                    'id_categorie' => 2,
                    'valeur' => 'Enfant',
                    'titre' => 'Tarif enfant',
                    'statut' => 'ok',
                    'quantite' => 1,
                    'commentaires' => 'Tarif enfant',
                    'type_inscrit' => 'enfant',
                    'montant' => 5,
                    'montant_symbole' => '5 EUR',
                ),
                3 => array(
                    'id_categorie' => 3,
                    'valeur' => 'Public',
                    'titre' => 'Tarif public',
                    'statut' => 'ok',
                    'quantite' => 1,
                    'commentaires' => 'Tarif public',
                    'type_inscrit' => 'indifferent',
                    'montant' => 15,
                    'montant_symbole' => '15 EUR',
                ),
                4 => array(
                    'id_categorie' => 4,
                    'valeur' => 'Couple',
                    'titre' => 'Tarif couple',
                    'statut' => 'ok',
                    'quantite' => 2,
                    'commentaires' => 'Tarif groupe pour couple',
                    'type_inscrit' => 'couple',
                    'montant' => 30,
                    'montant_symbole' => '30 EUR',
                ),
                5 => array(
                    'id_categorie' => 5,
                    'valeur' => 'Invite',
                    'titre' => 'Tarif invite',
                    'statut' => 'ok',
                    'quantite' => 1,
                    'commentaires' => 'Tarif invite',
                    'type_inscrit' => 'invite',
                    'montant' => 18,
                    'montant_symbole' => '18 EUR',
                ),
            ),
            'spip_asso_categories_activites_liens' => array(
                array('id_evenement' => 1, 'id_categorie' => 1, 'montant' => 20),
                array('id_evenement' => 1, 'id_categorie' => 2, 'montant' => 5),
                array('id_evenement' => 1, 'id_categorie' => 3, 'montant' => 15),
                array('id_evenement' => 1, 'id_categorie' => 4, 'montant' => 30),
                array('id_evenement' => 1, 'id_categorie' => 5, 'montant' => 18),
            ),
            'spip_articles' => array(
                10 => array(
                    'id_article' => 10,
                    'titre' => '001. Conditions generales',
                    'page' => 'page_cgu',
                    'statut' => 'publie',
                ),
            ),
            'spip_mailsubscribers' => array(),
        ),
        'events' => array(
            1 => array(
                'payant' => true,
                'accompagnants' => true,
                'places' => true,
                'validation' => false,
                'validation_sur_paiement' => 'non',
                'attentes_illimite' => false,
                'type_inscrits_evenement' => 'public',
                'condition_inscription' => 'oui',
                'message_condition_inscription' => 'J accepte les conditions',
                'info_supplementaire' => 'email,telephone,date_naissance',
                'date_fermeture_inscription' => '2026-12-31 23:59:59',
                'montant' => array(
                    array('id_categorie' => 1, 'titre' => 'Tarif adherent', 'montant_symbole' => '20 EUR', 'quantite' => 1, 'type_inscrit' => 'adherent', 'commentaires' => 'Tarif standard adherent', 'montant' => 20),
                    array('id_categorie' => 2, 'titre' => 'Tarif enfant', 'montant_symbole' => '5 EUR', 'quantite' => 1, 'type_inscrit' => 'enfant', 'commentaires' => 'Tarif enfant', 'montant' => 5),
                    array('id_categorie' => 3, 'titre' => 'Tarif public', 'montant_symbole' => '15 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent', 'commentaires' => 'Tarif public', 'montant' => 15),
                    array('id_categorie' => 4, 'titre' => 'Tarif couple', 'montant_symbole' => '30 EUR', 'quantite' => 2, 'type_inscrit' => 'couple', 'commentaires' => 'Tarif couple', 'montant' => 30),
                    array('id_categorie' => 5, 'titre' => 'Tarif invite', 'montant_symbole' => '18 EUR', 'quantite' => 1, 'type_inscrit' => 'invite', 'commentaires' => 'Tarif invite', 'montant' => 18),
                ),
            ),
        ),
        'places' => array(
            1 => array(
                'places_limites' => 5,
                'places_disponibles' => 5,
                'places_evenement' => 5,
                'places_en_attentes_disponible' => 2,
                'evenement_date_debut' => '2026-12-25 10:00:00',
                'evenement_reseau_fiafe' => 'non',
            ),
        ),
        'eligibilites' => array(
            1 => array(
                'eligible_inscription' => 'oui',
                'eligibilite_token_inscription' => false,
                'id_activite' => null,
            ),
        ),
        'ouvertures' => array(
            1 => array(
                'inscription_ouverte' => 'oui',
                'statut_ouverture_inscription' => 'inscription_ouverte',
            ),
        ),
        'droits' => array(
            1 => array(null, 'complet'),
        ),
        'plugins' => array(
            'agenda' => true,
            'gis' => true,
            'association_adhesions' => true,
            'association_compta' => true,
            'association_paiements' => true,
            'association_communication' => true,
        ),
        'modification_autorisee' => array(),
        'families' => array(
            1 => array(
                'adherent' => 'Jean Dupont',
                'conjoint' => 'Marie Dupont',
                'enfant_1' => 'Alice Dupont',
                'enfant_2' => 'Leo Dupont',
                'invite_1' => 'Invite Un',
            ),
        ),
    );
}

function association_test_array_replace_recursive($base, $replace) {
    foreach ($replace as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = association_test_array_replace_recursive($base[$key], $value);
        } else {
            $base[$key] = $value;
        }
    }
    return $base;
}

function association_test_reset_env($override = array()) {
    $scenario = association_test_array_replace_recursive(association_test_default_scenario(), $override);

    $GLOBALS['association_test_scenario'] = $scenario;
    $GLOBALS['association_metas'] = $scenario['association_metas'];
    $GLOBALS['auteur_session'] = $scenario['auteur_session'];
    $GLOBALS['visiteur_session'] = $scenario['visiteur_session'];
    $GLOBALS['_TEST_REQUEST'] = array();
    $GLOBALS['_TEST_SIDE_EFFECTS'] = array(
        'logs' => array(),
        'cookies' => array(),
        'jobs' => array(),
        'transactions' => array(),
        'comptes' => array(),
    );
    $GLOBALS['_TEST_SEQ'] = array(
        'spip_asso_activites' => 100,
        'spip_transactions' => 200,
    );

    $_GET = array();
    $_POST = array();
    $_REQUEST = array();
    $_FILES = array();
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    return $scenario;
}

function association_test_set_request(array $request, $as_post = true) {
    $GLOBALS['_TEST_REQUEST'] = $request;
    $_REQUEST = $request;
    $_GET = $request;
    $_POST = $as_post ? $request : array();
}

function association_test_current_db() {
    return $GLOBALS['association_test_scenario']['db'];
}

function association_test_set_db($db) {
    $GLOBALS['association_test_scenario']['db'] = $db;
}

function association_test_db_table($table) {
    $db = association_test_current_db();
    return isset($db[$table]) ? $db[$table] : array();
}

function association_test_db_write_table($table, $rows) {
    $db = association_test_current_db();
    $db[$table] = $rows;
    association_test_set_db($db);
}

function association_test_next_id($table, $field) {
    $seq = isset($GLOBALS['_TEST_SEQ'][$table]) ? $GLOBALS['_TEST_SEQ'][$table] : 1;
    $GLOBALS['_TEST_SEQ'][$table] = $seq + 1;
    return $seq;
}

function association_test_is_direct_script($file) {
    return isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath($file);
}

function association_test_fail($message) {
    throw new Exception($message);
}

function association_test_assert_true($condition, $message) {
    if (!$condition) {
        association_test_fail($message);
    }
}

function association_test_assert_equals($expected, $actual, $message) {
    if ($expected != $actual) {
        association_test_fail($message . ' | attendu=' . var_export($expected, true) . ' obtenu=' . var_export($actual, true));
    }
}

function association_test_assert_same($expected, $actual, $message) {
    if ($expected !== $actual) {
        association_test_fail($message . ' | attendu=' . var_export($expected, true) . ' obtenu=' . var_export($actual, true));
    }
}

function association_test_assert_contains($needle, $haystack, $message) {
    if (strpos((string)$haystack, (string)$needle) === false) {
        association_test_fail($message . ' | introuvable=' . var_export($needle, true) . ' dans ' . var_export($haystack, true));
    }
}

function association_test_collect_names($saisies) {
    $noms = array();
    if (!is_array($saisies)) {
        return $noms;
    }
    foreach ($saisies as $cle => $saisie) {
        if (is_array($saisie) && isset($saisie['options']['nom'])) {
            $noms[] = $saisie['options']['nom'];
        }
        if (is_array($saisie) && isset($saisie['saisies']) && is_array($saisie['saisies'])) {
            $noms = array_merge($noms, association_test_collect_names($saisie['saisies']));
        }
    }
    return $noms;
}

function association_test_run_cases($suite_name, array $cases) {
    $ok = 0;
    $ko = 0;
    foreach ($cases as $name => $callable) {
        try {
            $callable();
            $ok++;
            echo "[OK] $suite_name :: $name\n";
        } catch (Throwable $e) {
            $ko++;
            echo "[KO] $suite_name :: $name\n";
            echo "      " . $e->getMessage() . "\n";
        }
    }
    echo "Resume $suite_name : $ok OK / $ko KO\n";
    return $ko === 0 ? 0 : 1;
}

function include_spip($path) {
    $path = str_replace('\\', '/', $path);
    $candidates = array(
        ASSOCIATION_TEST_PLUGIN_ROOT . '/' . $path . '.php',
        ASSOCIATION_TEST_PLUGIN_ROOT . '/' . $path,
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-compta/' . $path . '.php',
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-compta/' . $path,
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-adhesions/' . $path . '.php',
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-adhesions/' . $path,
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-paiements/' . $path . '.php',
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-paiements/' . $path,
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/' . $path . '.php',
		ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/' . $path,
    );
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            include_once $candidate;
            return true;
        }
    }
    return false;
}

include_spip('inc/comptes');

function _request($key = null) {
    if ($key === null) {
        return isset($GLOBALS['_TEST_REQUEST']) ? $GLOBALS['_TEST_REQUEST'] : array();
    }
    return isset($GLOBALS['_TEST_REQUEST'][$key]) ? $GLOBALS['_TEST_REQUEST'][$key] : null;
}

function set_request($key, $value) {
    $GLOBALS['_TEST_REQUEST'][$key] = $value;
    $_REQUEST[$key] = $value;
    $_POST[$key] = $value;
}

function _T($key, $args = array()) {
    if ($args) {
        return $key . ' ' . json_encode($args);
    }
    return $key;
}

function association_log($canal, $message, $niveau = 'info', $contexte = array()) {
    $GLOBALS['_TEST_SIDE_EFFECTS']['logs'][] = compact('canal', 'message', 'niveau', 'contexte');
}

function spip_log($message, $canal = '') {
    association_log($canal ?: 'spip', $message, 'spip');
}

function affdate($date, $format = null) {
    if (!$format) {
        return $date;
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    return date(str_replace(array('Y-m-d 23:59:59', 'Y-m-d'), array('Y-m-d 23:59:59', 'Y-m-d'), $format), $timestamp);
}

function typo($texte) {
    return $texte;
}

function email_valide($email) {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generer_url_public($page, $args = '') {
    return '/public/' . $page . ($args ? '?' . $args : '');
}

function generer_url_ecrire($page, $args = '') {
    return '/ecrire/' . $page . ($args ? '?' . $args : '');
}

function generer_objet_url($id_objet, $objet, $args = '') {
    return '/objet/' . $objet . '/' . $id_objet . ($args ? '?' . $args : '');
}

function generer_url_entite($id_objet, $objet, $args = '') {
    return generer_objet_url($id_objet, $objet, $args);
}

function supprimer_numero($titre) {
    return preg_replace('/^\s*\d+\.\s*/', '', (string) $titre);
}

function sql_quote($value) {
    return "'" . str_replace("'", "\\'", (string) $value) . "'";
}

function sql_in($field, $values) {
    return '[[IN:' . $field . ':' . implode('|', (array) $values) . ']]';
}

function lire_config($path, $default = null) {
    $path = trim((string) $path, '/');
    if ($path === '') {
        return $default;
    }
    $segments = explode('/', $path);
    $current = $GLOBALS;
    foreach ($segments as $segment) {
        if ($segment === 'association_metas') {
            $current = isset($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();
            continue;
        }
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return $default;
        }
        $current = $current[$segment];
    }
    return $current;
}

function test_plugin_actif($plugin) {
    $plugins = isset($GLOBALS['association_test_scenario']['plugins']) ? $GLOBALS['association_test_scenario']['plugins'] : array();
    return !empty($plugins[$plugin]);
}

function minipres() {
    return 'minipres';
}

function pipeline($nom, $flux) {
    return $flux;
}

function autoriser($action = '', $type = '', $id = null, $qui = null) {
    return true;
}

function spip_setcookie($nom, $valeur, $expiration = 0) {
    $GLOBALS['_TEST_SIDE_EFFECTS']['cookies'][] = array(
        'nom' => $nom,
        'valeur' => $valeur,
        'expiration' => $expiration,
    );
}

function job_queue_add($fonction, $description, $arguments = array(), $file = '', $no_duplicate = false, $time = 0, $priority = 0) {
    $job = compact('fonction', 'description', 'arguments', 'file', 'no_duplicate', 'time', 'priority');
    $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'][] = $job;
    return count($GLOBALS['_TEST_SIDE_EFFECTS']['jobs']);
}

function mailsubscribers_obfusquer_email($email) {
    return $email;
}

function charger_fonction($nom, $module, $test = false) {
    if ($module === 'bank' && $nom === 'inserer_transaction') {
        return function ($montant_total, $options = array()) {
            $id_transaction = association_test_next_id('spip_transactions', 'id_transaction');
            $rows = association_test_db_table('spip_transactions');
            $rows[$id_transaction] = array(
                'id_transaction' => $id_transaction,
                'montant' => $montant_total,
                'montant_ht' => isset($options['montant_ht']) ? $options['montant_ht'] : $montant_total,
                'id_auteur' => isset($options['id_auteur']) ? $options['id_auteur'] : 0,
                'transaction_hash' => 'hash-' . $id_transaction,
                'statut' => 'attente',
            );
            association_test_db_write_table('spip_transactions', $rows);
            $GLOBALS['_TEST_SIDE_EFFECTS']['transactions'][] = array('action' => 'insert', 'id_transaction' => $id_transaction, 'montant' => $montant_total);
            return $id_transaction;
        };
    }
    if ($module === 'newsletter' && $nom === 'subscribe') {
        return function ($email, $options = array()) {
            $rows = association_test_db_table('spip_mailsubscribers');
            $rows[] = array('email' => $email, 'options' => $options);
            association_test_db_write_table('spip_mailsubscribers', $rows);
            return true;
        };
    }
    if ($test) {
        return null;
    }
    return function () {
        return null;
    };
}

function bank_devise_defaut() {
    return array('symbole' => 'EUR');
}

if (!function_exists('droit_auteur_evenements')) {
    function droit_auteur_evenements($id_auteur, $id_evenement) {
        $droits = isset($GLOBALS['association_test_scenario']['droits'][$id_evenement]) ? $GLOBALS['association_test_scenario']['droits'][$id_evenement] : array(null, 'complet');
        return $droits;
    }
}

if (!function_exists('affichage_dans_activites')) {
    function affichage_dans_activites($id_evenement) {
        return isset($GLOBALS['association_test_scenario']['events'][$id_evenement]) ? $GLOBALS['association_test_scenario']['events'][$id_evenement] : array();
    }
}

if (!function_exists('gestions_places')) {
    function gestions_places($id_evenement) {
        return isset($GLOBALS['association_test_scenario']['places'][$id_evenement]) ? $GLOBALS['association_test_scenario']['places'][$id_evenement] : array();
    }
}

if (!function_exists('ouverture_inscription_evenement')) {
    function ouverture_inscription_evenement($id_evenement) {
        return isset($GLOBALS['association_test_scenario']['ouvertures'][$id_evenement]) ? $GLOBALS['association_test_scenario']['ouvertures'][$id_evenement] : array(
            'inscription_ouverte' => 'oui',
            'statut_ouverture_inscription' => 'inscription_ouverte',
        );
    }
}

if (!function_exists('eligibilite_inscription_evenement')) {
    function eligibilite_inscription_evenement($id_evenement) {
        return isset($GLOBALS['association_test_scenario']['eligibilites'][$id_evenement]) ? $GLOBALS['association_test_scenario']['eligibilites'][$id_evenement] : array(
            'eligible_inscription' => 'oui',
            'eligibilite_token_inscription' => false,
            'id_activite' => null,
        );
    }
}

if (!function_exists('eligibilite_modification_evenement')) {
    function eligibilite_modification_evenement($id_activite) {
        $mods = isset($GLOBALS['association_test_scenario']['modification_autorisee']) ? $GLOBALS['association_test_scenario']['modification_autorisee'] : array();
        return isset($mods[$id_activite]) ? $mods[$id_activite] : 'possible';
    }
}

if (!function_exists('alerte_inscription_evenement')) {
    function alerte_inscription_evenement($id_evenement, $id_activite) {
        return '';
    }
}

if (!function_exists('recuperer_info_adherent')) {
    function recuperer_info_adherent($association, $email) {
        return array(
            'association' => $association,
            'email_inscrit' => $email,
        );
    }
}

if (!function_exists('generer_saisie_site_fiafe')) {
    function generer_saisie_site_fiafe($mode, $association) {
        return array(
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'site_fiafe',
                    'label' => 'Site FIAFE',
                    'defaut' => $association,
                ),
            ),
        );
    }
}


function association_test_normalize_from($from) {
    return preg_replace('/\s+/', ' ', trim(str_replace(array('`'), '', $from)));
}

function association_test_joined_categories_rows() {
    $categories = association_test_db_table('spip_asso_categories_activites');
    $liens = association_test_db_table('spip_asso_categories_activites_liens');
    $rows = array();
    foreach ($liens as $lien) {
        $id_categorie = intval($lien['id_categorie']);
        if (!isset($categories[$id_categorie])) {
            continue;
        }
        $rows[] = array_merge($categories[$id_categorie], $lien);
    }
    return $rows;
}

function association_test_table_rows($from) {
    $from = association_test_normalize_from($from);
    if (stripos($from, 'spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a') !== false) {
        return association_test_joined_categories_rows();
    }
    $table = trim($from);
    $rows = association_test_db_table($table);
    if ($table === 'spip_auteurs' || $table === 'spip_asso_activites' || $table === 'spip_transactions' || $table === 'spip_evenements' || $table === 'spip_articles' || $table === 'spip_asso_categories_activites') {
        return array_values($rows);
    }
    return $rows;
}

function association_test_parse_value($value) {
    $value = trim($value);
    $value = trim($value, "'");
    if (is_numeric($value)) {
        return $value + 0;
    }
    return $value;
}

function association_test_match_where(array $row, $where) {
    if (is_array($where)) {
        foreach ($where as $condition) {
            if (!association_test_match_where($row, $condition)) {
                return false;
            }
        }
        return true;
    }
    $where = trim((string) $where);
    if ($where === '') {
        return true;
    }

    $parts = preg_split('/\s+AND\s+/i', $where);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $part = str_replace(array('(', ')'), '', $part);
        $part = preg_replace('/\b[a-z]\./i', '', $part);

        if (preg_match('/^\[\[IN:([^:]+):(.+)\]\]$/', $part, $m)) {
            $field = trim($m[1]);
            $values = explode('|', $m[2]);
            if (!in_array((string) ($row[$field] ?? ''), array_map('strval', $values), true)) {
                return false;
            }
            continue;
        }
        if (preg_match('/^([a-zA-Z0-9_]+)\s+NOT\s+IN\s+\((.+)\)$/i', $part, $m)) {
            $field = $m[1];
            $values = array_map('trim', explode(',', str_replace("'", '', $m[2])));
            if (in_array((string) ($row[$field] ?? ''), array_map('strval', $values), true)) {
                return false;
            }
            continue;
        }
        if (preg_match('/^([a-zA-Z0-9_]+)\s*(<>|!=|=|>|<)\s*(.+)$/', $part, $m)) {
            $field = $m[1];
            $op = $m[2];
            $expected = association_test_parse_value($m[3]);
            $actual = isset($row[$field]) ? $row[$field] : null;
            switch ($op) {
                case '=':
                    if ((string) $actual !== (string) $expected) {
                        return false;
                    }
                    break;
                case '!=':
                case '<>':
                    if ((string) $actual === (string) $expected) {
                        return false;
                    }
                    break;
                case '>':
                    if (!($actual > $expected)) {
                        return false;
                    }
                    break;
                case '<':
                    if (!($actual < $expected)) {
                        return false;
                    }
                    break;
            }
            continue;
        }
    }
    return true;
}

function association_test_sort_rows(array $rows, $order) {
    $order = trim((string) $order);
    if ($order === '') {
        return $rows;
    }
    $parts = preg_split('/\s+/', preg_replace('/\b[a-z]\./i', '', $order));
    $field = $parts[0];
    $direction = isset($parts[1]) ? strtoupper($parts[1]) : 'ASC';
    usort($rows, function ($a, $b) use ($field, $direction) {
        $av = $a[$field] ?? null;
        $bv = $b[$field] ?? null;
        if ($av == $bv) {
            return 0;
        }
        $cmp = ($av < $bv) ? -1 : 1;
        return $direction === 'DESC' ? -$cmp : $cmp;
    });
    return $rows;
}

function sql_allfetsel($select, $from, $where = '', $group = '', $order = '') {
    $rows = association_test_table_rows($from);
    $filtered = array();
    foreach ($rows as $row) {
        if (association_test_match_where($row, $where)) {
            $filtered[] = $row;
        }
    }
    return association_test_sort_rows($filtered, $order);
}

function sql_select($select, $from, $where = '', $group = '', $order = '', $limit = '', $having = '', $serveur = '', $option = '') {
    return new ArrayIterator(sql_allfetsel($select, $from, $where, $group, $order));
}

function sql_fetch($result) {
    if ($result instanceof Iterator && $result->valid()) {
        $current = $result->current();
        $result->next();
        return $current;
    }
    return false;
}

function sql_free($result) {
    return true;
}

function sql_fetsel($select, $from, $where = '', $group = '', $order = '') {
    $rows = sql_allfetsel($select, $from, $where, $group, $order);
    return $rows ? $rows[0] : array();
}

function sql_getfetsel($select, $from, $where = '', $group = '', $order = '') {
    $row = sql_fetsel($select, $from, $where, $group, $order);
    if (!$row) {
        return null;
    }
    if (isset($row[$select])) {
        return $row[$select];
    }
    return reset($row);
}

function sql_insertq($table, $data) {
    $rows = association_test_db_table($table);
    if ($table === 'spip_asso_activites') {
        $data['id_activite'] = association_test_next_id($table, 'id_activite');
        $rows[$data['id_activite']] = $data;
        association_test_db_write_table($table, $rows);
        return $data['id_activite'];
    }
    if ($table === 'spip_transactions') {
        $data['id_transaction'] = association_test_next_id($table, 'id_transaction');
        $rows[$data['id_transaction']] = $data;
        association_test_db_write_table($table, $rows);
        return $data['id_transaction'];
    }
    $rows[] = $data;
    association_test_db_write_table($table, $rows);
    return count($rows);
}

function sql_updateq($table, $data, $where) {
    $rows = association_test_db_table($table);
    $updated = 0;
    foreach ($rows as $key => $row) {
        if (association_test_match_where($row, $where)) {
            $rows[$key] = array_merge($row, $data);
            $updated++;
        }
    }
    association_test_db_write_table($table, $rows);
    return $updated;
}

function sql_delete($table, $where) {
    $rows = association_test_db_table($table);
    foreach ($rows as $key => $row) {
        if (association_test_match_where($row, $where)) {
            unset($rows[$key]);
        }
    }
    association_test_db_write_table($table, $rows);
    return true;
}
