<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['test_auteurs'] = array(
    1 => array('id_auteur' => 1, 'statut_interne' => 'prospect'),
);
$GLOBALS['test_categories'] = array();
$GLOBALS['test_transactions'] = array();
$GLOBALS['test_comptes'] = array();
$GLOBALS['test_cotisations'] = array();
$GLOBALS['test_config'] = array(
    'association_metas/meta_cfg_cotisations_multidevises' => 'oui',
);
$GLOBALS['test_bank_result'] = 101;
$GLOBALS['test_bank_calls'] = 0;
$GLOBALS['test_statuts'] = array();
$GLOBALS['test_select_rows'] = array();

function include_spip($chemin) { return true; }
function association_log($canal, $message, $niveau = 'info') { return true; }
function lire_config($cle) { return $GLOBALS['test_config'][$cle] ?? null; }
function charger_fonction($fonction, $repertoire) {
    if ($fonction === 'inserer_transaction' && $repertoire === 'bank') {
        return function ($montant, $options) {
            $GLOBALS['test_bank_calls']++;
            $resultat = $GLOBALS['test_bank_result'];
            if (is_numeric($resultat) && intval($resultat) > 0) {
                $GLOBALS['test_transactions'][intval($resultat)] = array(
                    'id_transaction' => intval($resultat),
                    'montant' => $montant,
                    'montant_ht' => $options['montant_ht'],
                    'id_auteur' => $options['id_auteur'],
                    'devise' => $options['devise'],
                    'statut' => 'attente',
                );
            }
            return $resultat;
        };
    }
    return function () { return array(); };
}
function sql_fetsel($select, $table, $where) {
    preg_match('/(?:id_auteur|id_categorie|id_compte|id_transaction)\s*=\s*(\d+)/', $where, $m);
    $id = intval($m[1] ?? 0);
    if ($table === 'spip_auteurs') return $GLOBALS['test_auteurs'][$id] ?? false;
    if ($table === 'spip_asso_categories_adherents') return $GLOBALS['test_categories'][$id] ?? false;
    if ($table === 'spip_asso_comptes') return $GLOBALS['test_comptes'][$id] ?? false;
    if ($table === 'spip_asso_cotisations') return $GLOBALS['test_cotisations'][$id] ?? false;
    if ($table === 'spip_transactions') return $GLOBALS['test_transactions'][$id] ?? false;
    return false;
}
function sql_getfetsel($select, $table, $where) {
    $row = sql_fetsel('*', $table, $where);
    return $row[$select] ?? null;
}
function sql_allfetsel($select, $table, $where = '') {
    if ($table !== 'spip_asso_categories_adherents') {
        return array();
    }
    return array_values(array_filter(array_map(function ($row) use ($select) {
        if (($row['document_justificatif'] ?? 'non') !== 'oui') {
            return null;
        }
        return array($select => $row[$select] ?? null);
    }, $GLOBALS['test_categories'])));
}
function sql_countsel($table, $where) { return 0; }
function _T($cle, $args = array()) { return $cle; }
function sql_updateq($table, $valeurs, $where) {
    preg_match('/(?:id_compte|id_transaction)\s*=\s*(\d+)/', $where, $m);
    $id = intval($m[1] ?? 0);
    $cible = $table === 'spip_transactions' ? 'test_transactions' : 'test_comptes';
    $GLOBALS[$cible][$id] = array_merge($GLOBALS[$cible][$id] ?? array(), $valeurs);
    return true;
}
function sql_select($select, $table, $where = '', $group = '', $order = '') {
    $GLOBALS['test_select_rows'] = array_values(array_filter($GLOBALS['test_categories'], function ($row) use ($where) {
        if ($where === 'statut="ok"') return ($row['statut'] ?? '') === 'ok';
        if ($where === 'statut!="supprime"') return ($row['statut'] ?? '') !== 'supprime';
        return true;
    }));
    usort($GLOBALS['test_select_rows'], fn($a, $b) => ($b['cotisation'] ?? 0) <=> ($a['cotisation'] ?? 0));
    return true;
}
function sql_fetch($resultat) { return array_shift($GLOBALS['test_select_rows']) ?: false; }
function bank_devise_defaut() { return array('code' => 'EUR', 'symbole' => '€'); }
function intl_devise_defaut() { return $GLOBALS['test_config']['intl/devise_defaut'] ?? 'EUR'; }
function intl_lister_devises() { return array('CNY' => array('symbole' => '¥'), 'EUR' => array('symbole' => '€')); }
function association_adhesions_compte_cotisation_creer($date, $montant, $justification, $imputation, $journal, $id_auteur, $reinscription, $id_categorie, $statut, $id_transaction, $date_fin_validite = null) {
    $id = count($GLOBALS['test_comptes']) + 1;
    $GLOBALS['test_comptes'][$id] = compact('id_auteur', 'id_categorie', 'id_transaction') + array(
        'id_compte' => $id,
        'montant' => $montant,
        'justification' => $justification,
        'statut_cotisation' => $statut,
        'reinscription' => $reinscription,
        'date' => $date,
        'date_fin_validite' => $date_fin_validite,
    );
    return $id;
}
function association_adhesions_compte_cotisation_modifier($date, $montant, $justification, $imputation, $journal, $reinscription, $id_categorie, $id_compte, $statut, $id_transaction, $date_fin_validite = null) {
    $GLOBALS['test_comptes'][$id_compte] = array_merge($GLOBALS['test_comptes'][$id_compte], array(
        'montant' => $montant,
        'id_categorie' => $id_categorie,
        'statut_cotisation' => $statut,
        'reinscription' => $reinscription,
        'date' => $date,
        'date_fin_validite' => $date_fin_validite,
    ));
}
function changer_statut_cotisation($id_compte, $origine = '', $notifier = true) {
    $GLOBALS['test_statuts'][] = compact('id_compte', 'origine', 'notifier');
}
include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations_devises.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations_stockage.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/api_cotisations.php';

function test_assert($condition, $message) {
    if (!$condition) {
        echo "ECHEC: $message\n";
        exit(1);
    }
    echo "OK: $message\n";
}

function test_reset($validation, $cotisation = 100, $devise = '') {
    $GLOBALS['test_categories'] = array(10 => array(
        'id_categorie' => 10,
        'cotisation' => $cotisation,
        'devise' => $devise,
        'validation' => $validation,
        'type_adherent' => 'adherent',
        'document_justificatif' => 'non',
    ));
    $GLOBALS['test_transactions'] = array();
    $GLOBALS['test_comptes'] = array();
    $GLOBALS['test_statuts'] = array();
    $GLOBALS['test_bank_result'] = 101;
    $GLOBALS['test_bank_calls'] = 0;
    $GLOBALS['test_config'] = array(
        'association_metas/meta_cfg_cotisations_multidevises' => 'oui',
    );
}

function test_uploads_justificatifs($noms) {
    return array('document_justificatif' => array(
        'name' => $noms,
        'type' => array_map(fn($nom) => str_ends_with($nom, '.pdf') ? 'application/pdf' : 'image/jpeg', $noms),
        'tmp_name' => array_fill(0, count($noms), ''),
        'error' => array_fill(0, count($noms), UPLOAD_ERR_OK),
        'size' => array_fill(0, count($noms), 1000),
    ));
}

$GLOBALS['test_categories'][12] = array('id_categorie' => 12, 'cotisation' => 20, 'validation' => 'auto', 'type_adherent' => 'adherent', 'document_justificatif' => 'oui');
$controle = cotisation_verifier_documents_justificatifs(12, array(), 'new');
test_assert(!$controle['valide'], 'une catégorie configurée refuse un champ justificatif totalement absent');
$controle = cotisation_verifier_documents_justificatifs(12, test_uploads_justificatifs(array('recto.jpg', 'verso.jpg')), 'new');
test_assert($controle['valide'], 'deux images JPEG valides satisfont la règle documentaire');
$controle = cotisation_verifier_documents_justificatifs(12, test_uploads_justificatifs(array('recto.exe', 'verso.jpg')), 'new');
test_assert(!$controle['valide'], 'un format de fichier non autorisé est refusé');
$trop_lourd = test_uploads_justificatifs(array('recto.pdf', 'verso.pdf'));
$trop_lourd['document_justificatif']['size'][0] = 11 * 1024 * 1024;
$controle = cotisation_verifier_documents_justificatifs(12, $trop_lourd, 'new');
test_assert(!$controle['valide'], 'un justificatif de plus de 10 Mo est refusé');
$upload_incomplet = test_uploads_justificatifs(array('recto.pdf', 'verso.pdf'));
$upload_incomplet['document_justificatif']['error'][1] = UPLOAD_ERR_PARTIAL;
$controle = cotisation_verifier_documents_justificatifs(12, $upload_incomplet, 'new');
test_assert(!$controle['valide'], 'une erreur d upload est refusée');
$GLOBALS['test_bank_calls'] = 0;
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 12, 'origine' => 'public'));
test_assert(($resultat['statut'] ?? '') === 'erreur' && $GLOBALS['test_bank_calls'] === 0, 'l API bloque un justificatif manquant avant Bank');

$GLOBALS['test_auteurs'][1] = array(
    'id_auteur' => 1,
    'statut_interne' => 'prospect',
    'type_adherent' => 'adherent',
);
$GLOBALS['test_categories'] = array(
    12 => array(
        'id_categorie' => 12,
        'statut' => 'ok',
        'cotisation' => 20,
        'valeur' => 'Adhésion',
        'commentaires' => '',
        'eligibilite' => 'inscription',
        'type_adherent' => 'adherent',
        'nombre_enfants' => 0,
        'document_justificatif' => 'oui',
    ),
);
$saisies = api_cotisations_saisies_communes(array('id_auteur' => 1, 'origine' => 'public'));
$saisie_justificatif = $saisies[1] ?? array();
$options_justificatif = $saisies[1]['options'] ?? array();
test_assert(
    !isset($options_justificatif['afficher_si'])
        && !isset($options_justificatif['obligatoire'])
        && !isset($saisie_justificatif['verifier']),
    'les justificatifs de l unique catégorie restent visibles sans doubler la validation métier par la saisie fichier'
);

foreach (array('pre-paiement' => 'demande', 'post-paiement' => 'attente', 'auto' => 'attente') as $validation => $statut_attendu) {
    test_reset($validation);
    $resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10, 'origine' => 'public'));
    test_assert($resultat['statut_cotisation'] === $statut_attendu, "$validation produit le statut $statut_attendu");
    test_assert($resultat['montant'] === 100.0, "$validation conserve le montant de cotisation");
    test_assert(count($GLOBALS['test_statuts']) === 1, "$validation déclenche la transition métier");
}

foreach (array('auto' => 'ok', 'pre-paiement' => 'demande', 'post-paiement' => 'demande') as $validation => $statut_attendu) {
    test_reset($validation, 0);
    $resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10, 'origine' => 'public'));
    test_assert($resultat['statut_cotisation'] === $statut_attendu, "une cotisation gratuite $validation produit le statut $statut_attendu");
    test_assert($resultat['id_transaction'] === 0, "une cotisation gratuite $validation ne crée pas de transaction");
    test_assert($GLOBALS['test_bank_calls'] === 0, "une cotisation gratuite $validation n’appelle pas Bank");
}

test_reset('auto', 0);
$resultat = api_traiter_cotisation(array(
    'id_auteur' => 1,
    'id_categorie' => 10,
    'origine' => 'prive',
    'justification' => 'Cotisation synthétique #1',
));
test_assert(
    ($GLOBALS['test_comptes'][1]['justification'] ?? '') === 'Cotisation synthétique #1',
    'la justification préparée par le formulaire est conservée par l API'
);

test_reset('auto', 100);
$resultat = api_traiter_cotisation(array(
    'id_auteur' => 1,
    'id_categorie' => 10,
    'origine' => 'prive',
    'montant' => 75.5,
    'date_operation' => '2026-05-12',
    'date_fin_validite' => '2027-05-11',
));
test_assert($resultat['montant'] === 75.5, 'le BO conserve le montant réellement saisi');
test_assert(($GLOBALS['test_comptes'][1]['date'] ?? '') === '2026-05-12 00:00:00', 'le BO conserve la date comptable saisie');
test_assert(($GLOBALS['test_comptes'][1]['date_fin_validite'] ?? '') === '2027-05-11', 'le BO transmet la validité propre à la cotisation');

test_reset('auto', 100);
$resultat = api_traiter_cotisation(array(
    'id_auteur' => 1,
    'id_categorie' => 10,
    'origine' => 'public',
    'montant' => 1,
    'date_operation' => '2020-01-01',
));
test_assert($resultat['montant'] === 100.0, 'le public ne peut pas remplacer le tarif de catégorie');
test_assert(($GLOBALS['test_comptes'][1]['date'] ?? '') !== '2020-01-01 00:00:00', 'le public ne peut pas antidater la cotisation');

test_reset('auto', 80);
$GLOBALS['test_config']['/association_metas/meta_cfg_taxe'] = 20;
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10, 'montant_don' => 20));
test_assert($resultat['montant'] === 100.0, 'le don est ajouté à la cotisation');
test_assert(abs($GLOBALS['test_transactions'][101]['montant_ht'] - 80.0) < 0.001, 'la taxe configurée est appliquée au montant HT');

test_reset('auto', 600, 'CNY');
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10));
test_assert($GLOBALS['test_transactions'][101]['devise'] === 'CNY', 'la devise CNY de la catégorie est transmise à Bank');

test_reset('auto', 100, '');
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10));
test_assert($GLOBALS['test_transactions'][101]['devise'] === 'EUR', 'une catégorie historique conserve la devise par défaut du site');

$resultat = api_traiter_cotisation(array('id_auteur' => 0, 'id_categorie' => 10));
test_assert(($resultat['statut'] ?? '') === 'erreur', 'un auteur invalide est refusé');
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 999));
test_assert(($resultat['statut'] ?? '') === 'erreur', 'une catégorie inexistante est refusée');

test_reset('auto');
$GLOBALS['test_bank_result'] = array('statut' => 'erreur', 'message' => 'Paiement indisponible');
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10));
test_assert(($resultat['message'] ?? '') === 'Paiement indisponible', 'une erreur Bank est propagée sans créer de cotisation');
test_assert(count($GLOBALS['test_comptes']) === 0, 'aucun compte de cotisation n’est créé si Bank échoue');

test_reset('auto', 0);
$resultat = api_traiter_cotisation(array('id_auteur' => 1, 'id_categorie' => 10, 'montant_don' => -1));
test_assert(($resultat['statut'] ?? '') === 'erreur', 'un montant négatif ne peut pas emprunter le parcours gratuit');

$GLOBALS['test_auteurs'][1] = array(
    'id_auteur' => 1,
    'statut_interne' => 'prospect',
    'radio_type_adherent' => 'adherent',
    'type_adherent' => 'adherent',
    'nb_enfants' => 0,
);
$GLOBALS['test_categories'] = array(
    10 => array('id_categorie' => 10, 'statut' => 'ok', 'cotisation' => 100, 'valeur' => 'Individuel', 'commentaires' => '', 'eligibilite' => 'inscription', 'type_adherent' => 'adherent', 'nombre_enfants' => 0),
    11 => array('id_categorie' => 11, 'statut' => 'ok', 'cotisation' => 200, 'valeur' => 'Entreprise', 'commentaires' => '', 'eligibilite' => 'inscription', 'type_adherent' => 'entreprise', 'nombre_enfants' => 0),
    12 => array('id_categorie' => 12, 'statut' => 'ok', 'cotisation' => 90, 'valeur' => 'Renouvellement', 'commentaires' => '', 'eligibilite' => 'reinscription', 'type_adherent' => 'adherent', 'nombre_enfants' => 0),
    13 => array('id_categorie' => 13, 'statut' => 'supprime', 'cotisation' => 50, 'valeur' => 'Archive', 'commentaires' => '', 'eligibilite' => 'inscription', 'type_adherent' => 'adherent', 'nombre_enfants' => 0),
);
$categories = preparer_liste_categories(1, 'public', 'inscription', 'adherent');
test_assert(array_keys($categories) === array(10), 'le public ne voit que les catégories actives, éligibles et adaptées à son type');
$GLOBALS['test_categories'][10]['devise'] = 'CNY';
$categories = preparer_liste_categories(1, 'public', 'inscription', 'adherent');
test_assert(strpos($categories[10], '100&nbsp;CNY') !== false, 'le formulaire public affiche la devise propre à la catégorie');
$categories = preparer_liste_categories(1, 'public', 'reinscription', 'adherent');
test_assert(array_keys($categories) === array(12), 'la réinscription utilise sa catégorie dédiée');

$GLOBALS['test_auteurs'][2] = array(
    'id_auteur' => 2,
    'statut_interne' => 'prospect',
    'radio_type_adherent' => 'enfant',
    'type_adherent' => 'enfant',
    'nb_enfants' => 2,
);
$GLOBALS['test_categories'] = array(
    20 => array('id_categorie' => 20, 'statut' => 'ok', 'cotisation' => 100, 'valeur' => 'Famille', 'commentaires' => '', 'eligibilite' => 'tout', 'type_adherent' => 'enfant', 'nombre_enfants' => 0),
    21 => array('id_categorie' => 21, 'statut' => 'ok', 'cotisation' => 120, 'valeur' => 'Deux enfants', 'commentaires' => '', 'eligibilite' => 'tout', 'type_adherent' => 'enfant', 'nombre_enfants' => 2),
    22 => array('id_categorie' => 22, 'statut' => 'ok', 'cotisation' => 140, 'valeur' => 'Trois enfants', 'commentaires' => '', 'eligibilite' => 'tout', 'type_adherent' => 'enfant', 'nombre_enfants' => 3),
);
$categories = preparer_liste_categories(2, 'public', 'inscription', 'enfant');
test_assert(array_keys($categories) === array(21), 'une famille reçoit la catégorie correspondant exactement au nombre d’enfants');

foreach (array('auto' => 'ok', 'post-paiement' => 'demande') as $validation => $statut_attendu) {
    test_reset($validation, 75);
    $GLOBALS['test_transactions'][205] = array('id_transaction' => 205, 'montant' => 75, 'montant_ht' => 75, 'statut' => 'ok');
    $GLOBALS['test_comptes'][5] = array(
        'id_compte' => 5,
        'id_auteur' => 1,
        'id_categorie' => 10,
        'id_transaction' => 205,
        'reinscription' => 'inscription',
        'statut_cotisation' => 'attente',
    );
    $GLOBALS['test_cotisations'][5] = array(
        'id_cotisation' => 5,
        'id_compte' => 5,
        'id_auteur' => 1,
        'id_categorie' => 10,
        'id_transaction' => 205,
        'inscription' => 'inscription',
        'statut' => 'attente',
    );
    $resultat = api_traiter_cotisation(array(
        'id_auteur' => 1,
        'id_compte' => 5,
        'id_categorie' => 10,
        'origine' => 'encaissement_paiement',
        'montant_don' => 5,
    ));
    test_assert($resultat['statut_cotisation'] === $statut_attendu, "un encaissement $validation produit le statut $statut_attendu");
    test_assert($resultat['id_transaction'] === 205, "un encaissement $validation conserve la transaction existante");
    test_assert($GLOBALS['test_transactions'][205]['montant'] === 80.0, "un encaissement $validation synchronise le montant sans doublon");
    test_assert($GLOBALS['test_transactions'][205]['devise'] === 'EUR', "un encaissement $validation synchronise la devise sans doublon");
}

test_reset('auto', 0);
$GLOBALS['test_comptes'][5] = array(
    'id_compte' => 5,
    'id_auteur' => 1,
    'id_categorie' => 10,
    'id_transaction' => 0,
    'reinscription' => 'inscription',
    'statut_cotisation' => 'ok',
);
$GLOBALS['test_cotisations'][5] = array(
    'id_cotisation' => 5,
    'id_compte' => 5,
    'id_auteur' => 1,
    'id_categorie' => 10,
    'id_transaction' => 0,
    'inscription' => 'inscription',
    'statut' => 'ok',
);
$resultat = api_traiter_cotisation(array(
    'id_auteur' => 1,
    'id_compte' => 5,
    'id_categorie' => 10,
    'origine' => 'prive',
    'montant' => 0,
    'statut_cotisation' => 'ok',
));
test_assert(($resultat['statut'] ?? '') !== 'erreur', 'une cotisation gratuite reste modifiable sans transaction Bank');
test_assert($resultat['id_transaction'] === 0, 'l édition gratuite ne fabrique pas de transaction Bank');

$upgrade_source = file_get_contents(PLUGIN_ROOT . '/association_administrations.php');
$compta_migration_source = file_get_contents(PLUGIN_ROOT . '/plugins/association-compta/inc/association_compta_migration_legacy.php');
$adhesions_migration_source = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/inc/association_adhesions_migration_legacy.php');
$paquet_source = file_get_contents(PLUGIN_ROOT . '/paquet.xml');
test_assert(
    strpos($compta_migration_source, "TABLE spip_asso_comptes MODIFY id_transaction BIGINT NOT NULL DEFAULT '0'") !== false,
    'la migration convertit les installations existantes vers BIGINT'
);
preg_match('/schema="([^"]+)"/', $paquet_source, $schema_paquet);
test_assert(
    !empty($schema_paquet[1]) && version_compare($schema_paquet[1], '1.5.7', '>='),
    'la version de schéma déclenche la migration BIGINT'
);
test_assert(
    strpos($adhesions_migration_source, 'ADD COLUMN devise VARCHAR(3)') !== false
    && version_compare($schema_paquet[1], '1.5.9', '>='),
    'la migration historique ajoute la devise aux catégories de cotisation'
);

echo "Tous les tests API adhésion ont réussi.\n";
