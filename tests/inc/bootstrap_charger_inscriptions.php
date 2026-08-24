<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    define('_ECRIRE_INC_VERSION', true);
}

if (!defined('ASSOCIATION_TEST_PLUGIN_ROOT')) {
    define('ASSOCIATION_TEST_PLUGIN_ROOT', dirname(__DIR__, 2));
}

require_once ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/association_evenements_fonctions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$GLOBALS['association_test_charger_logs'] = array();
$GLOBALS['association_test_charger_php_warnings'] = array();
$GLOBALS['association_test_traiter_traces'] = array();
$GLOBALS['association_test_charger_fixture'] = array();
$GLOBALS['association_test_charger_request'] = array();
$GLOBALS['association_test_charger_sql_rows'] = array();
$GLOBALS['association_test_charger_sql_index'] = 0;

function association_test_charger_fixture_par_defaut() {
    $auteur_ok = array(
        'id_auteur' => 1,
        'prenom' => 'Jean',
        'nom_famille' => 'Dupont',
        'email' => 'jean@example.test',
        'mobile' => '0600000001',
        'statut_interne' => 'ok',
        'statut' => '1comite',
        'validite' => '2027-12-31 23:59:59',
        'radio_type_adherent' => '',
        'prenom_conjoint' => 'Marie',
        'nom_conjoint' => 'Dupont',
        'email_conjoint' => 'marie@example.test',
        'mobile_conjoint' => '0600000002',
        'prenom_enfant_1' => 'Alice',
        'prenom_enfant_2' => 'Bob',
        'prenom_enfant_3' => '',
        'prenom_enfant_4' => '',
        'prenom_enfant_5' => '',
        'date_naissance_enfant_1' => '2015-05-01',
        'date_naissance_enfant_2' => '2018-03-10',
        'date_naissance_enfant_3' => '',
        'date_naissance_enfant_4' => '',
        'date_naissance_enfant_5' => '',
        'invite_1' => 'Invite A',
        'invite_2' => 'Invite B',
    );

    $auteur_non_membre = array(
        'id_auteur' => 2,
        'prenom' => 'Nora',
        'nom_famille' => 'Martin',
        'email' => 'nora@example.test',
        'mobile' => '0600000003',
        'statut_interne' => 'ko',
        'statut' => '6forum',
        'validite' => '2024-12-31 23:59:59',
        'radio_type_adherent' => '',
    );

    return array(
        'affichage' => array(
            'payant' => false,
            'accompagnants' => false,
            'montant' => array(),
            'montant_symbole' => 'EUR',
            'type_inscrits_evenement' => 'public',
            'date_fermeture_inscription' => '2026-12-31 23:59:59',
            'info_supplementaire' => 'email,telephone',
            'condition_inscription' => 'non',
            'message_condition_inscription' => '',
        ),
        'gestions_places' => array(
            'places_limites' => 4,
            'places_disponibles' => 10,
            'places_evenement' => 10,
            'places_en_attentes_disponible' => 2,
            'evenement_date_debut' => '2026-06-01',
            'evenement_reseau_fiafe' => false,
        ),
        'eligibilite_inscription_evenement' => array(
            'eligible_inscription' => 'oui',
            'id_activite' => null,
            'eligibilite_token_inscription' => false,
        ),
        'ouverture_inscription_evenement' => array(
            'inscription_ouverte' => 'oui',
            'statut_ouverture_inscription' => 'inscription_ouverte',
        ),
        'eligibilite_modification_evenement' => 'possible',
        'article_cgu' => array(
            'id_article' => 10,
            'titre' => '001. Conditions generales',
        ),
        'info_adherent' => array(),
        'alerte' => '',
        'familles' => array(
            1 => array(
                'adherent' => 'Jean Dupont',
                'conjoint' => 'Marie Dupont',
                'enfant_1' => 'Alice Dupont',
                'enfant_2' => 'Bob Dupont',
            ),
        ),
        'auteurs' => array(
            1 => $auteur_ok,
            2 => $auteur_non_membre,
        ),
        'activites' => array(
            501 => array(
                'id_activite' => 501,
                'id_evenement' => 1,
                'id_auteur' => 1,
                'id_transaction' => 901,
                'statut' => 'ok',
                'nombre_inscrits' => 2,
                'participants_json' => json_encode(array('adherent' => array(), 'conjoint' => array())),
                'tarifs_selectionnes' => serialize(array()),
                'nom_participants' => 'Jean Dupont, Marie Dupont',
                'commentaire' => 'Commentaire test',
                'annotation' => 'Annotation test',
                'association' => '',
                'email_inscrit' => 'jean@example.test',
                'tel_inscrit' => '0600000001',
                'prenom_inscrit' => 'Jean',
                'nom_inscrit' => 'Dupont',
            ),
            601 => array(
                'id_activite' => 601,
                'id_evenement' => 1,
                'id_auteur' => 1,
                'id_transaction' => 902,
                'statut' => 'ok',
                'nombre_inscrits' => 2,
                'participants_json' => json_encode(array('adherent' => array(), 'conjoint' => array())),
                'tarifs_selectionnes' => serialize(array(
                    11 => array(
                        'id_participants' => array('adherent', 'conjoint'),
                        'nombre' => 2,
                        'montant' => 24,
                    ),
                )),
                'nom_participants' => 'Jean Dupont, Marie Dupont',
                'commentaire' => 'Commentaire multi',
                'annotation' => 'Annotation multi',
                'association' => '',
                'email_inscrit' => 'jean@example.test',
                'tel_inscrit' => '0600000001',
                'prenom_inscrit' => 'Jean',
                'nom_inscrit' => 'Dupont',
            ),
            701 => array(
                'id_activite' => 701,
                'id_evenement' => 1,
                'id_auteur' => 0,
                'id_transaction' => 903,
                'statut' => 'preinscrit',
                'nombre_inscrits' => 2,
                'participants_json' => json_encode(array('inscrit_1' => array(), 'inscrit_2' => array())),
                'tarifs_selectionnes' => serialize(array(
                    11 => array('id_participants' => array('inscrit_1', 'inscrit_2'), 'nombre' => 2, 'montant' => 24),
                )),
                'nom_participants' => 'Paul Test, Lea Test',
                'commentaire' => 'Commentaire public multi',
                'annotation' => '',
                'association' => '',
                'email_inscrit' => 'public@example.test',
                'tel_inscrit' => '0600000010',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Test',
            ),
        ),
        'transactions' => array(
            901 => array('id_transaction' => 901, 'statut' => 'ok', 'transaction_hash' => 'hash901'),
            902 => array('id_transaction' => 902, 'statut' => 'ok', 'transaction_hash' => 'hash902'),
            903 => array('id_transaction' => 903, 'statut' => 'attente', 'transaction_hash' => 'hash903'),
        ),
        'evenements' => array(
            1 => array('id_evenement' => 1, 'validation_sur_paiement' => 'non'),
        ),
        'comptes' => array(),
        'calculator' => array('statut' => 'ok'),
        'traiter' => array('next_id_activite' => 1200, 'next_id_transaction' => 2200),
    );
}

function association_test_charger_reset_contexte() {
    $GLOBALS['association_test_charger_fixture'] = association_test_charger_fixture_par_defaut();
    $GLOBALS['association_test_charger_request'] = array();
    $GLOBALS['association_test_charger_logs'] = array();
    $GLOBALS['association_test_charger_php_warnings'] = array();
    $GLOBALS['association_test_traiter_traces'] = array(
        'insert_transactions' => array(),
        'update_transactions' => array(),
        'update_comptes' => array(),
        'insert_activites' => array(),
        'update_activites' => array(),
        'notifications' => array(),
        'jobs' => array(),
        'cookies' => array(),
    );
    $GLOBALS['association_metas'] = array(
        'meta_cfg_event_config_accompagnants' => 'tout',
        'meta_cfg_event_message_responsable' => 'oui',
    );
    $GLOBALS['visiteur_session'] = array();
    unset($GLOBALS['message_erreur']);
}

association_test_charger_reset_contexte();

set_error_handler(function ($severity, $message, $file, $line) {
    $GLOBALS['association_test_charger_php_warnings'][] = array(
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line,
    );

    return true;
});

function association_test_charger_bootstrap() {
    static $bootstrapped = false;
    if ($bootstrapped) {
        return;
    }

    include_spip('formulaires/inscription_evenement_public');
    include_spip('formulaires/inscription_evenement');
    include_spip('formulaires/inscription_evenement_multi');
    include_spip('formulaires/inscription_evenement_multi_public');

    $bootstrapped = true;
}

function association_test_charger_fixture($key, $default = null) {
    return array_key_exists($key, $GLOBALS['association_test_charger_fixture'])
        ? $GLOBALS['association_test_charger_fixture'][$key]
        : $default;
}

function association_test_charger_appliquer_fixture(array $scenario) {
    association_test_charger_reset_contexte();

    if (!empty($scenario['fixture']) && is_array($scenario['fixture'])) {
        $GLOBALS['association_test_charger_fixture'] = array_replace_recursive(
            $GLOBALS['association_test_charger_fixture'],
            $scenario['fixture']
        );
    }

    if (!empty($scenario['metas']) && is_array($scenario['metas'])) {
        $GLOBALS['association_metas'] = array_replace($GLOBALS['association_metas'], $scenario['metas']);
    }

    if (!empty($scenario['session']) && is_array($scenario['session'])) {
        $GLOBALS['visiteur_session'] = $scenario['session'];
    }

    if (!empty($scenario['request']) && is_array($scenario['request'])) {
        foreach ($scenario['request'] as $cle => $valeur) {
            set_request($cle, $valeur);
        }
    }
}

function association_test_charger_extraire_id_depuis_where($where, $cle) {
    if (preg_match('/' . preg_quote($cle, '/') . '\s*=\s*([0-9]+)/', (string) $where, $matches)) {
        return intval($matches[1]);
    }

    return null;
}

function include_spip($path) {
    $path = str_replace('\\', '/', $path);
    $candidats = array(
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

    foreach ($candidats as $candidat) {
        if (file_exists($candidat)) {
            include_once $candidat;
            return true;
        }
    }

    return false;
}

include_spip('inc/comptes');

function _request($cle = null) {
    if ($cle === null) {
        return $GLOBALS['association_test_charger_request'];
    }

    return $GLOBALS['association_test_charger_request'][$cle] ?? null;
}

function set_request($cle, $valeur) {
    $GLOBALS['association_test_charger_request'][$cle] = $valeur;
}

if (!defined('_LOG_CRITIQUE')) {
    define('_LOG_CRITIQUE', 'CRIT');
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
if (!defined('_LOG_ERREUR')) {
    define('_LOG_ERREUR', 'ERREUR');
}

function spip_log($message, $canal = '') {
    $GLOBALS['association_test_charger_logs'][] = array('canal' => $canal, 'message' => $message);
}

function _T($cle, $args = array()) {
    if (!empty($args)) {
        return $cle . ' ' . json_encode($args);
    }

    return $cle;
}

function autoriser($action = '', $type = '', $id = null) {
    return true;
}

function affdate($date, $format = null) {
    return $date;
}

if (!function_exists('email_valide')) {
    function email_valide($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

function supprimer_numero($texte) {
    return preg_replace('/^[0-9]+[. -]*/', '', (string) $texte);
}

function generer_url_entite($id, $objet, $args = '') {
    return 'spip.php?page=' . $objet . '&id=' . intval($id) . ($args ? '&' . $args : '');
}

function generer_objet_url($id, $objet, $args = '') {
    return generer_url_entite($id, $objet, $args);
}

function typo($texte) {
    return (string) $texte;
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
            $current = $GLOBALS['association_metas'] ?? array();
            continue;
        }
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return $default;
        }
        $current = $current[$segment];
    }

    return $current;
}

function pipeline($nom, $flux) {
    if ($nom === 'association_paiements_reglement_traiter') {
        foreach (array(
            'association_evenements_association_paiements_reglement_traiter',
            'association_adhesions_association_paiements_reglement_traiter',
            'association_compta_association_paiements_reglement_traiter',
        ) as $traiter) {
            if (function_exists($traiter)) {
                $flux = $traiter($flux);
            }
        }
    }
    return $flux;
}

function sql_quote($valeur) {
    return "'" . addslashes((string) $valeur) . "'";
}

function sql_in($field, $values) {
    return '[[IN:' . $field . ':' . implode('|', (array) $values) . ']]';
}

function sql_allfetsel($select, $table, $where = '', $group = '', $order = '') {
	$fixture = $table === 'spip_transactions' ? 'transactions' : $table;
    $rows = array_values(association_test_charger_fixture($fixture, array()));
	if ($table === 'spip_transactions' && preg_match('/\[\[IN:id_transaction:([^\]]+)\]\]/', (string) $where, $matches)) {
		$ids = array_map('intval', explode('|', $matches[1]));
		$rows = array_values(array_filter($rows, function ($row) use ($ids) {
			return in_array((int) ($row['id_transaction'] ?? 0), $ids, true);
		}));
	}
    if ($table === 'spip_articles' && strpos((string) $where, 'statut') !== false) {
        $rows = array_values(array_filter($rows, function ($row) {
            return ($row['statut'] ?? '') === 'publie';
        }));
    }
    return $rows;
}

function charger_fonction($nom, $type = 'inc') {
    if ($type === 'bank' && $nom === 'inserer_transaction') {
        return function ($montant_total, $options = array()) {
            $cfg = association_test_charger_fixture('traiter', array('next_id_transaction' => 2200));
            $id = intval($cfg['next_id_transaction'] ?? 2200) + count($GLOBALS['association_test_traiter_traces']['insert_transactions']);
            $GLOBALS['association_test_traiter_traces']['insert_transactions'][] = array(
                'id_transaction' => $id,
                'montant_total' => $montant_total,
                'options' => $options,
            );

            $transactions = association_test_charger_fixture('transactions', array());
            $transactions[$id] = array(
                'id_transaction' => $id,
                'statut' => 'attente',
                'transaction_hash' => 'hash' . $id,
                'montant' => $montant_total,
            );
            $GLOBALS['association_test_charger_fixture']['transactions'] = $transactions;

            return $id;
        };
    }

    return $nom;
}

function generer_url_public($page, $args = '') {
    return 'public:' . $page . ($args ? '?' . $args : '');
}

function generer_url_ecrire($exec, $args = '') {
    return 'ecrire:' . $exec . ($args ? '?' . $args : '');
}

function spip_setcookie($nom, $valeur, $expiration = 0) {
    $GLOBALS['association_test_traiter_traces']['cookies'][] = array(
        'nom' => $nom,
        'valeur' => $valeur,
        'expiration' => $expiration,
    );

    return true;
}

function sql_select($select, $from, $where = '', $group = '', $order = '') {
    $rows = array();
    $auteurs = association_test_charger_fixture('auteurs', array());
    $activites = association_test_charger_fixture('activites', array());

    if (strpos($from, 'spip_asso_categories_activites') !== false) {
        $affichage = association_test_charger_fixture('affichage', array());
        foreach ((array)($affichage['montant'] ?? array()) as $categorie) {
            $montant = $categorie['montant'] ?? ($categorie['montant_symbole'] ?? 0);
            $rows[] = array(
                'id_categorie' => intval($categorie['id_categorie'] ?? 0),
                'titre' => $categorie['titre'] ?? '',
                'montant' => floatval($montant),
                'quantite' => max(1, intval($categorie['quantite'] ?? 1)),
                'type_inscrit' => $categorie['type_inscrit'] ?? 'indifferent',
                'statut' => 'ok',
            );
        }
    } elseif (strpos($from, 'spip_auteurs') !== false) {
        $rows = array_values($auteurs);
    } elseif (strpos($from, 'spip_asso_activites') !== false) {
        $rows = array();
        foreach ($activites as $activite) {
            if (strpos((string)$where, "statut ='liste_attente'") !== false) {
                if (($activite['statut'] ?? '') === 'liste_attente') {
                    $rows[] = $activite;
                }
            } elseif (!empty($activite['id_auteur'])) {
                $rows[] = array('id_auteur' => $activite['id_auteur']);
            }
        }
    }

    $GLOBALS['association_test_charger_sql_rows'] = $rows;
    $GLOBALS['association_test_charger_sql_index'] = 0;

    return 'association_test_sql_resource';
}

function sql_fetch($resource) {
    $index = $GLOBALS['association_test_charger_sql_index'];
    if (!isset($GLOBALS['association_test_charger_sql_rows'][$index])) {
        return false;
    }

    $GLOBALS['association_test_charger_sql_index']++;
    return $GLOBALS['association_test_charger_sql_rows'][$index];
}

function sql_free($resource) {
    return true;
}

function sql_fetsel($select, $from, $where) {
    $auteurs = association_test_charger_fixture('auteurs', array());
    $activites = association_test_charger_fixture('activites', array());
    $transactions = association_test_charger_fixture('transactions', array());
    $evenements = association_test_charger_fixture('evenements', array());
    $comptes = association_test_charger_fixture('comptes', array());

    if ($from === 'spip_articles') {
        return association_test_charger_fixture('article_cgu', array());
    }

    if ($from === 'spip_auteurs') {
        $id_auteur = association_test_charger_extraire_id_depuis_where($where, 'id_auteur');
        if ($id_auteur !== null && isset($auteurs[$id_auteur])) {
            return $auteurs[$id_auteur];
        }

        return reset($auteurs) ?: array();
    }

    if ($from === 'spip_asso_activites') {
        $id_activite = association_test_charger_extraire_id_depuis_where($where, 'id_activite');
        if ($id_activite !== null && isset($activites[$id_activite])) {
            return $activites[$id_activite];
        }

        $id_evenement = association_test_charger_extraire_id_depuis_where($where, 'id_evenement');
        $id_auteur = association_test_charger_extraire_id_depuis_where($where, 'id_auteur');
        foreach ($activites as $activite) {
            if (($id_evenement === null || intval($activite['id_evenement']) === $id_evenement)
                && ($id_auteur === null || intval($activite['id_auteur']) === $id_auteur)) {
                return $activite;
            }
        }

        return array();
    }

    if ($from === 'spip_transactions') {
        $id_transaction = association_test_charger_extraire_id_depuis_where($where, 'id_transaction');
        if ($id_transaction !== null && isset($transactions[$id_transaction])) {
            return $transactions[$id_transaction];
        }

        return array();
    }

    if ($from === 'spip_evenements') {
        $id_evenement = association_test_charger_extraire_id_depuis_where($where, 'id_evenement');
        return ($id_evenement !== null && isset($evenements[$id_evenement])) ? $evenements[$id_evenement] : array();
    }

    if ($from === 'spip_asso_comptes') {
        $id_transaction = association_test_charger_extraire_id_depuis_where($where, 'id_transaction');
        foreach ($comptes as $compte) {
            if ($id_transaction === null || intval($compte['id_transaction'] ?? 0) === $id_transaction) {
                return $compte;
            }
        }
        return array();
    }

    return array();
}

function sql_insertq($table, $data) {
    if ($table === 'spip_asso_activites') {
        $cfg = association_test_charger_fixture('traiter', array('next_id_activite' => 1200));
        $id = intval($cfg['next_id_activite'] ?? 1200) + count($GLOBALS['association_test_traiter_traces']['insert_activites']);
        $GLOBALS['association_test_traiter_traces']['insert_activites'][] = array(
            'id_activite' => $id,
            'payload' => $data,
        );
        return $id;
    }

    if ($table === 'spip_transactions') {
        $cfg = association_test_charger_fixture('traiter', array('next_id_transaction' => 2200));
        $id = intval($cfg['next_id_transaction'] ?? 2200) + count($GLOBALS['association_test_traiter_traces']['insert_transactions']);
        $GLOBALS['association_test_traiter_traces']['insert_transactions'][] = array(
            'id_transaction' => $id,
            'payload' => $data,
        );
        return $id;
    }
    return 1;
}

function sql_updateq($table, $data, $where = '') {
    if ($table === 'spip_asso_activites') {
        $GLOBALS['association_test_traiter_traces']['update_activites'][] = array(
            'where' => $where,
            'payload' => $data,
        );
    }
    if ($table === 'spip_transactions') {
        $GLOBALS['association_test_traiter_traces']['update_transactions'][] = array(
            'where' => $where,
            'payload' => $data,
        );
        $id_transaction = association_test_charger_extraire_id_depuis_where($where, 'id_transaction');
        $transactions = association_test_charger_fixture('transactions', array());
        if ($id_transaction !== null && isset($transactions[$id_transaction])) {
            $transactions[$id_transaction] = array_merge($transactions[$id_transaction], $data);
            $GLOBALS['association_test_charger_fixture']['transactions'] = $transactions;
        }
    }
    if ($table === 'spip_asso_comptes') {
        $GLOBALS['association_test_traiter_traces']['update_comptes'][] = array(
            'where' => $where,
            'payload' => $data,
        );
    }

    return true;
}

function job_queue_add($fonction, $description, $args = array(), $fichier = '', $a = false, $b = 0, $c = 0) {
    $id = count($GLOBALS['association_test_traiter_traces']['jobs']) + 1;
    $GLOBALS['association_test_traiter_traces']['jobs'][] = array(
        'id_job' => $id,
        'fonction' => $fonction,
        'description' => $description,
        'args' => $args,
    );
    return $id;
}


function affichage_dans_activites($id_evenement) {
    return association_test_charger_fixture('affichage', array());
}

function gestions_places($id_evenement) {
    return association_test_charger_fixture('gestions_places', array());
}

function eligibilite_inscription_evenement($id_evenement) {
    return association_test_charger_fixture('eligibilite_inscription_evenement', array());
}

function ouverture_inscription_evenement($id_evenement) {
    return association_test_charger_fixture('ouverture_inscription_evenement', array());
}

function eligibilite_modification_evenement($id_activite) {
    return association_test_charger_fixture('eligibilite_modification_evenement', 'possible');
}

function recuperer_info_adherent($association, $email) {
    return association_test_charger_fixture('info_adherent', array());
}

function alerte_inscription_evenement($id_evenement, $id_activite = null) {
    return association_test_charger_fixture('alerte', '');
}

function generer_data_saisie_site_fiafe() {
    return array();
}

function generer_saisie_site_fiafe($mode, $association) {
    return array();
}

function association_test_charger_extraire_noms($structure) {
    $noms = array();

    $walker = function ($node) use (&$walker, &$noms) {
        if (!is_array($node)) {
            return;
        }

        if (isset($node['options']['nom']) && is_string($node['options']['nom'])) {
            $noms[] = $node['options']['nom'];
        }

        foreach ($node as $valeur) {
            if (is_array($valeur)) {
                $walker($valeur);
            }
        }
    };

    $walker($structure);

    return array_values(array_unique($noms));
}

function association_test_charger_assertions(array $scenario, array $resultat) {
    $erreurs = array();
    $noms = association_test_charger_extraire_noms($resultat['_saisies'] ?? array());

    if (!is_array($resultat)) {
        $erreurs[] = 'Le resultat n\'est pas un tableau.';
        return array($erreurs, $noms);
    }

    if (!isset($resultat['_saisies']) || !is_array($resultat['_saisies'])) {
        $erreurs[] = 'La cle _saisies est absente ou invalide.';
    }

    if (($scenario['expected']['editable'] ?? true) !== (bool) ($resultat['editable'] ?? false)) {
        $erreurs[] = 'Valeur editable inattendue.';
    }

    if (($scenario['expected']['creation_inscription'] ?? null) !== null
        && ($resultat['creation_inscription'] ?? null) !== $scenario['expected']['creation_inscription']) {
        $erreurs[] = 'Valeur creation_inscription inattendue.';
    }

    if (($scenario['expected']['modification_inscription'] ?? null) !== null
        && ($resultat['modification_inscription'] ?? null) !== $scenario['expected']['modification_inscription']) {
        $erreurs[] = 'Valeur modification_inscription inattendue.';
    }

    if (($scenario['expected']['multi'] ?? false) && empty($resultat['_saisies_par_etapes'])) {
        $erreurs[] = 'Le formulaire multi n\'a pas de _saisies_par_etapes.';
    }

    foreach (($scenario['expected']['contains'] ?? array()) as $nom) {
        if (!in_array($nom, $noms, true)) {
            $erreurs[] = 'Champ attendu absent: ' . $nom;
        }
    }

    foreach (($scenario['expected']['not_contains'] ?? array()) as $nom) {
        if (in_array($nom, $noms, true)) {
            $erreurs[] = 'Champ inattendu present: ' . $nom;
        }
    }

    return array($erreurs, $noms);
}





