<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_metas'] = array(
    'config_destinataires_creation_cotisation_tresorier' => 'tresorier@example.test; invalide',
    'config_destinataires_creation_cotisation_tresorier_entreprise' => 'entreprise@example.test',
    'meta_cfg_envoi_recu_paiement_adhesion' => 'oui',
);
$GLOBALS['test_envois'] = 0;

function include_spip($chemin) { return true; }
function bank_devise_defaut() { return array('code' => 'EUR', 'symbole' => '€'); }
function association_log() { return true; }
function lire_config($cle, $defaut = '') { return $GLOBALS['association_metas'][$cle] ?? $defaut; }
function parser_emails_depuis_config($valeur) {
    $emails = preg_split('/[;,\s]+/', (string)$valeur, -1, PREG_SPLIT_NO_EMPTY);
    return array_values(array_filter($emails, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)));
}
function _T($cle, $args = array()) {
    $sujets = array(
        'notifications:attente_paiement_sujet' => 'Paiement attendu',
        'notifications:validation_pre_paiement_sujet' => 'Validation requise',
        'notifications:validation_post_paiement_sujet' => 'Validation du paiement',
        'notifications:activation_cotisation_inscription_sujet' => 'Adhesion activee',
        'notifications:cotisation_attente_admin_sujet' => 'Cotisation a encaisser',
        'notifications:cotisation_demande_admin_sujet' => 'Cotisation a valider',
        'notifications:cotisation_encaissement_admin_sujet' => 'Paiement recu',
        'notifications:email_notification_echeances_sujet' => 'Echeance prochaine',
        'notifications:email_notification_echeance_echu_sujet' => 'Adhesion echue',
        'association_adhesions:email_recu_encaissement_adhesion_sujet' => 'Recu de paiement',
        'notifications:justificatifs_a_revoir_sujet' => 'Documents a fournir de nouveau',
    );
    return $sujets[$cle] ?? $cle;
}
function test_assert($condition, $message) {
    if (!$condition) { echo "ECHEC: $message\n"; exit(1); }
    echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/notifications_cotisations_audit.php';

$audit = notifications_cotisations_audit();
$resume = notifications_cotisations_audit_resume($audit);
test_assert(count($audit) === 11, 'les onze scenarios email adhesion sont inventories');
test_assert($resume === array('total' => 11, 'ok' => 11, 'attention' => 0, 'erreur' => 0), 'gabarits, sujets et configuration sont conformes');
test_assert(!str_contains(json_encode($audit), 'tresorier@example.test'), 'les adresses de tresorerie ne sont jamais exposees dans le rapport');
test_assert(($audit[4]['destinataires'] ?? '') === '2 adresse(s) de tresorerie valide(s)', 'le rapport compte les destinataires valides et dedupliques');
test_assert($GLOBALS['test_envois'] === 0, 'la generation de l audit ne declenche aucun envoi');

$catalogue = notifications_cotisations_audit_catalogue();
$catalogue[0]['template'] = 'notifications/inexistant';
$audit_invalide = notifications_cotisations_audit(array($catalogue[0]));
test_assert($audit_invalide[0]['statut'] === 'erreur', 'un gabarit manquant est detecte comme erreur');

$GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_adhesion'] = 'non';
$scenarios_recu = array_values(array_filter($catalogue, fn($scenario) => $scenario['id'] === 'recu'));
$scenario_recu = $scenarios_recu[0];
$audit_recu = notifications_cotisations_audit(array($scenario_recu));
test_assert($audit_recu[0]['statut'] === 'attention', 'un recu desactive est signale sans etre considere comme une panne');

$source_cotisations = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/inc/cotisations.php');
$source_action = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/action/valider_justificatifs_cotisation.php');
$source_action = str_replace("\r\n", "\n", $source_action);
$source_page_notifications = file_get_contents(PLUGIN_ROOT . '/plugins/association-communication/prive/squelettes/contenu/notifications.html');
$source_pipelines_adhesions = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/association_adhesions_pipelines.php');
test_assert(!str_contains($source_page_notifications, '|notifications_cotisations_audit_html'), 'la page Communication ne depend plus directement du domaine Adhesions');
test_assert(str_contains($source_page_notifications, '|association_notifications_audits_html'), 'la page Communication utilise le contrat d audit extensible');
test_assert(str_contains($source_pipelines_adhesions, 'association_notifications_audit_html'), 'Adhesions contribue a l audit commun par pipeline');
test_assert(!str_contains($source_cotisations, "bank_paiement/email_ticket_admin"), 'les notifications de cotisation ne dependent plus de email_ticket_admin');
test_assert(str_contains($source_cotisations, "'documents_recus'") && str_contains($source_cotisations, "'justificatifs_controles'"), 'le contexte email expose l etat documentaire');
test_assert(str_contains($source_action, "'justificatifs-a-revoir'") && !str_contains($source_action, "'justificatifs-valides'"), 'seul le retour A revoir programme une notification adherent');
test_assert(str_contains($source_action, "str_replace('&amp;', '&', \$retour)"), 'la redirection de validation restaure les separateurs HTML');
test_assert(str_contains($source_action, "\$resultat['message'],\n        '&'"), 'la redirection impose un separateur HTTP');
foreach (array('cotisation-attente_admin', 'cotisation-demande_admin', 'cotisation-encaissement_admin') as $template_admin) {
    $source_template = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/notifications/' . $template_admin . '.html');
    test_assert(str_contains($source_template, 'justificatifs_cotisation_admin'), "le template $template_admin affiche l etat documentaire conditionnel");
}

echo "Tests audit notifications adhesion termines.\n";
