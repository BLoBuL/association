<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_metas'] = array(
    'mode_paiement_adhesion' => serialize(array('stripe_test', 'virement_test')),
    'meta_cfg_cotisations_multidevises' => 'oui',
);
$GLOBALS['test_request'] = array();
$GLOBALS['test_ecriture'] = array();

function include_spip($chemin) { return true; }
function _request($cle) { return $GLOBALS['test_request'][$cle] ?? null; }
function _T($cle, $args = array()) { return $cle; }
function bank_devise_defaut() { return array('code' => 'EUR', 'symbole' => '€'); }
function intl_devise_defaut() { return 'CNY'; }
function intl_lister_devises() { return array('CNY' => array('symbole' => '¥'), 'EUR' => array('symbole' => '€')); }
function bank_lister_configs() {
    return array(
        array('id' => 'stripe_test', 'presta' => 'stripe', 'label' => 'Carte'),
        array('id' => 'virement_test', 'presta' => 'virement', 'label' => 'Virement'),
        array('id' => 'paypal_interdit', 'presta' => 'paypal', 'label' => 'Non autorisé'),
    );
}
function bank_config_id($config) { return $config['id']; }
function sql_fetsel($select, $table, $where) { return $GLOBALS['test_lecture'] ?? false; }
function sql_insertq($table, $data) { $GLOBALS['test_ecriture'] = $data; return 42; }
function sql_updateq($table, $data, $where) { $GLOBALS['test_ecriture'] = $data; return true; }
function generer_url_ecrire($page) { return $page; }

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations_devises.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/formulaires/editer_asso_categorie_cotisation.php';

function test_assert($condition, $message) {
    if (!$condition) { echo "ECHEC: $message\n"; exit(1); }
    echo "OK: $message\n";
}
function test_saisie_par_nom($saisies, $nom) {
    foreach ($saisies as $saisie) {
        if (($saisie['options']['nom'] ?? '') === $nom) return $saisie;
    }
    return null;
}
function test_requete_categorie($cotisation) {
    return array(
        'valeur' => 'Cotisation test',
        'statut' => 'ok',
        'eligibilite' => 'tout',
        'type_adherent' => 'adherent',
        'nombre_enfants' => 0,
        'document_justificatif' => 'non',
        'validation' => 'auto',
        'commentaires' => '',
        'cotisation' => $cotisation,
        'devise' => 'CNY',
        'mode_paiement' => array('stripe_test', 'virement_test'),
    );
}

$saisies = formulaires_editer_asso_categorie_cotisation_saisies_dist(0);
foreach (array('valeur', 'statut', 'eligibilite', 'type_adherent', 'nombre_enfants', 'document_justificatif', 'validation', 'devise', 'cotisation', 'mode_paiement') as $champ) {
    test_assert(test_saisie_par_nom($saisies, $champ) !== null, "le paramètre $champ est exposé dans le formulaire");
}

$validation = test_saisie_par_nom($saisies, 'validation');
test_assert(($validation['options']['defaut'] ?? '') === 'auto', 'le mode de validation par défaut est auto');
$modes = test_saisie_par_nom($saisies, 'mode_paiement');
test_assert(array_keys($modes['options']['datas']) === array('stripe_test', 'virement_test'), 'seuls les moyens autorisés par mode_paiement_adhesion sont proposés');
$devise = test_saisie_par_nom($saisies, 'devise');
test_assert(($devise['options']['defaut'] ?? '') === 'CNY', 'la devise par défaut de la catégorie vient de la configuration Intl du site');
test_assert(array_keys($devise['options']['data']) === array('CNY', 'EUR'), 'les autres devises Intl restent proposées uniquement sur la catégorie');

$GLOBALS['test_lecture'] = array('id_categorie' => 7, 'devise' => '');
$charge = formulaires_editer_asso_categorie_cotisation_charger_dist(7);
test_assert($charge['devise'] === 'CNY', 'une catégorie historique sans devise reprend la configuration Intl lors de son édition');
$GLOBALS['test_lecture'] = false;

$source_config = file_get_contents(PLUGIN_ROOT . '/formulaires/configurer_association.php');
$parametres_globaux = array(
    'validite',
    'meta_cfg_cotisations_multidevises',
    'date_scolaire_suivante',
    'date_scolaire_nouvelle',
    'meta_cfg_donation',
    'meta_cfg_donation_defaut',
    'meta_cfg_envoi_recu_paiement_adhesion',
    'meta_cfg_envoi_validation_paiement_adhesion',
    'notification_echeance_cotisation',
    'config_destinataires_creation_cotisation_tresorier',
    'validite_entreprise',
    'date_scolaire_suivante_entreprise',
    'date_scolaire_nouvelle_entreprise',
    'meta_cfg_cotisation_compte_entreprise',
    'notification_echeance_notifier_echu_entreprise',
    'notification_echeance_cotisation_entreprise',
    'config_destinataires_creation_cotisation_tresorier_entreprise',
    'mode_paiement_adhesion',
    'meta_cfg_taxe',
    'pc_cotisations_creance',
    'pc_cotisations_paiement',
    'dc_cotisations',
);
foreach ($parametres_globaux as $parametre) {
    test_assert(str_contains($source_config, "'nom' => '$parametre'"), "le paramètre global $parametre reste déclaré");
}

$GLOBALS['test_request'] = test_requete_categorie('0');
$erreurs = formulaires_editer_asso_categorie_cotisation_verifier_dist(0);
test_assert(!isset($erreurs['cotisation']), 'le formulaire accepte explicitement une cotisation à zéro');

$GLOBALS['test_request'] = test_requete_categorie('-0.01');
$erreurs = formulaires_editer_asso_categorie_cotisation_verifier_dist(0);
test_assert(isset($erreurs['cotisation']), 'le formulaire refuse une cotisation négative');

$GLOBALS['test_request'] = test_requete_categorie('');
$erreurs = formulaires_editer_asso_categorie_cotisation_verifier_dist(0);
test_assert(isset($erreurs['cotisation']), 'le montant reste obligatoire même si zéro est valide');

$GLOBALS['test_request'] = test_requete_categorie('0');
$resultat = formulaires_editer_asso_categorie_cotisation_traiter_dist(0);
test_assert($resultat['id_categorie'] === 42, 'une catégorie gratuite peut être créée');
test_assert($GLOBALS['test_ecriture']['cotisation'] === 0, 'le montant zéro est conservé en base');
test_assert($GLOBALS['test_ecriture']['devise'] === 'CNY', 'la devise sélectionnée est conservée en base');
test_assert($GLOBALS['test_ecriture']['validation'] === 'auto', 'le mode de validation est conservé');
test_assert($GLOBALS['test_ecriture']['eligibilite'] === 'tout', 'l’éligibilité est conservée');
test_assert($GLOBALS['test_ecriture']['mode_paiement'] === 'stripe_test,virement_test', 'les moyens de paiement sont sérialisés sans option interdite');

$GLOBALS['association_metas']['meta_cfg_cotisations_multidevises'] = 'non';
$saisies = formulaires_editer_asso_categorie_cotisation_saisies_dist(0);
test_assert(test_saisie_par_nom($saisies, 'devise') === null, 'le sélecteur de devise est masqué quand le multidevise est désactivé');
test_assert(array_keys(association_cotisation_devises_disponibles()) === array('CNY'), 'seule la devise Intl du site reste disponible par défaut');

$GLOBALS['test_request'] = test_requete_categorie('10');
$GLOBALS['test_request']['devise'] = 'EUR';
$erreurs = formulaires_editer_asso_categorie_cotisation_verifier_dist(0);
test_assert(!isset($erreurs['devise']), 'la devise absente du formulaire n’est pas exigée quand le multidevise est désactivé');
formulaires_editer_asso_categorie_cotisation_traiter_dist(0);
test_assert($GLOBALS['test_ecriture']['devise'] === 'CNY', 'la devise Intl du site est forcée quand le multidevise est désactivé');

echo "Tous les tests de paramétrage des cotisations ont réussi.\n";
