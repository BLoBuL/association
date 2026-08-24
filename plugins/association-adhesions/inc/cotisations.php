<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/api_cotisations');
include_spip('inc/notifications_emails');

/**
 * Retourne la liste des délais dâ€™échéance configurés pour un type dâ€™adhérent.
 * Permet dâ€™unifier la lecture des metas (stockées sérialisées) et de
 * différencier les entreprises/adhérents individuels.
 *
 * @param string $type_adherent 'adherent' ou 'entreprise'.
 * @return int[] Tableau trié de jours (ex: [7, 15, 30]).
 */
function association_obtenir_delais_echeance($type_adherent = 'adherent') {
    $metas = $GLOBALS['association_metas'] ?? [];
    $meta_cle_generique = 'notification_echeance_cotisation';
    $meta_cle_entreprise = 'notification_echeance_cotisation_entreprise';

    $valeur_brute = null;
    if ($type_adherent === 'entreprise' && !empty($metas[$meta_cle_entreprise])) {
        $valeur_brute = $metas[$meta_cle_entreprise];
    }
    if ($valeur_brute === null) {
        $valeur_brute = $metas[$meta_cle_generique] ?? [];
    }

    if (!is_array($valeur_brute)) {
        $decoded = @unserialize($valeur_brute);
        if ($decoded !== false || $valeur_brute === 'b:0;') {
            $valeur_brute = $decoded;
        } elseif (is_string($valeur_brute) && trim($valeur_brute) !== '') {
            $valeur_brute = explode(',', $valeur_brute);
        } else {
            $valeur_brute = [];
        }
    }

    $delais = array_map('intval', (array) $valeur_brute);
    $delais = array_values(array_unique(array_filter($delais, fn($v) => $v >= 0)));
    sort($delais);

    return $delais;
}

/**
 * Fichier central de gestion des cotisations / notifications
 * ---------------------------------------------------------
 * RÃ´le principal :
 * - Traiter les changements d'état des cotisations (création, attente, validation, encaissement)
 * - Envoyer les notifications (adhérent et admin/trésorerie) via les fonds de templates
 * - Fournir un contexte (tableau associatif) utilisé par les templates de `notifications/`
 *
 * Conventions importantes :
 * - Les templates de notifications attendent un contexte plat (tableau associatif) contenant
 *   les clés listées ciâ€‘dessous. Toutes les clés sont garanties d'exister (au moins valeurs vides)
 *   pour simplifier le rendu cÃ´té squelette.
 *
 * Contexte exposé aux templates (exemples de clés) :
 * - email_adherent : email de l'adhérent (string)
 * - nom_adherent : nom affiché (string)
 * - nom_entreprise : nom de l'entreprise si entreprise sinon ''
 * - id_compte : identifiant de la cotisation (int)
 * - id_transaction : identifiant de la transaction (int|string)
 * - montant : montant de la transaction (int ou string selon source)
 * - type_adherent : 'adherent' ou 'entreprise'
 * - type_cotisation : libellé de la catégorie de cotisation (string)
 * - url_editer_cotisation : URL interne vers l'édition de la cotisation (string)
 * - url_voir_adherent : URL interne vers la fiche adhérent (string)
 * - url_transaction : URL interne vers la transaction (string)
 *
 * Clés de langue importantes (dans `lang/notifications_fr.php`) :
 * - cotisation_attente_admin_*  (title/intro/chapo/lien_.../sujet_template)
 * - cotisation_demande_admin_*  (title/intro/chapo/lien_.../sujet_template)
 * - cotisation_encaissement_admin_* (title/intro/chapo/sujet_template)
 * - cotisation_admin_label_qui / _type / _montant (labels du bloc résumé)
 * - email_formule_politesse, email_signature
 *
 * Notes :
 * - Les fichiers de squelette `notifications/*.html` sont des squelettes SPIP ;
 *   l'analyse statique (hors SPIP) peut signaler des warnings mais ces templates
 *   sont valides lorsqu'ils sont rendus par SPIP avec le contexte ciâ€‘dessus.
 */

function mise_a_jour_cotisation($row_cotisation, $query_transaction)
{
    api_traiter_cotisation(
        [
        'id_auteur' => intval($row_cotisation['id_auteur']),
        'id_compte' => intval($row_cotisation['id_compte']),
        'id_categorie' => intval($row_cotisation['id_categorie']),
        'id_transaction' => intval($row_cotisation['id_transaction']),
        'notifier' => true,
        'origine' => 'encaissement_paiement'
        ]
    );
}

/**
 * Indique si une cotisation doit activer immédiatement l’adhérent.
 *
 * Une validation privée en statut ok conserve le comportement historique.
 * Une cotisation publique gratuite et automatique est également activée sans
 * attendre un retour Bank qui n’existera jamais.
 */
function association_cotisation_doit_activer($origine, $statut_cotisation, $montant_transaction = 0) {
    if ($statut_cotisation !== 'ok') {
        return false;
    }

    return $origine === 'prive'
        || ($origine === 'public' && floatval($montant_transaction) <= 0);
}

/**
 * Change le statut d'une cotisation et met à jour les informations associées.
 *
 * @param int $id_auteur ID de l'auteur.
 * @param int $id_compte ID du compte de cotisation.
 * @param string $statut Nouveau statut de la cotisation (ex: 'ok', 'attente').
 * @param string|null $reinscription Type d'inscription (facultatif).
 * @return void
 */
function changer_statut_cotisation($id_compte, $origine = '',$notifier = true){
	include_spip('inc/cotisations_stockage');
    // Entrée silencieuse : on ne logue que les erreurs
    // Récupère les informations de la cotisation
	$query_cotisation = association_cotisation_lire_par_compte($id_compte);
    $query_categories = sql_fetsel('*', 'spip_asso_categories_adherents', "id_categorie=" . $query_cotisation['id_categorie']);
    $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=" . intval($query_cotisation['id_transaction']));
    if (!$query_transaction) {
        $query_transaction = array(
            'id_transaction' => 0,
            'statut' => 'ok',
            'montant' => 0,
        );
    }
    $id_auteur = $query_cotisation['id_auteur'];
    $reinscription = $query_cotisation['reinscription'];
    /* PUBLIC */
    // Notifications envoyées lors d'action publique par l'adhérent lui-mÃªme
    if($origine == 'public' AND $query_cotisation['statut_cotisation'] == 'demande' AND $query_transaction ['statut'] != 'ok' ) {
        // Création d'une cotisation en attente de validation
        notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'validation_pre-paiement');
        // Notifier également la trésorerie/admin via le helper
        notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction, 'attente_validation');
    }
    elseif($origine == 'public' AND $query_cotisation['statut_cotisation'] == 'attente' AND $query_transaction ['statut'] != 'ok') {
        // Création d'une cotisation en attente de paiement
        notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');
        // Notifier également la trésorerie/admin via le helper
        notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction,  'attente_paiement');
    }

    /* PRIVE */
    // Notifications envoyées lors d'action privée par un administrateur ou un trésorier
    // Silence : on n'écrit pas de log pour les événements normaux ici

    // Activation: lors d'une action privée, on doit activer l'adhérent si
    // la cotisation passe en statut 'ok' (validation manuelle par un admin).
    // Ne pas lier l'activation au mode de validation de la catégorie ici :
    // l'administrateur qui valide manuellement une cotisation s'attend
    // généralement à ce que les comptes liés soient activés.
    $do_activation_prive = association_cotisation_doit_activer(
        $origine,
        $query_cotisation['statut_cotisation'] ?? '',
        $query_transaction['montant'] ?? 0
    );

    $activation_done_prive = false;
    if ($do_activation_prive) {
        try {
            // Si l'auteur associé à la cotisation est un compte secondaire,
            // inverser la relation pour faire de lui le principal avant activation.
            $auteur_compte_principal = intval(sql_getfetsel('auteur_compte_principal', 'spip_auteurs', 'id_auteur=' . intval($id_auteur)));
            if ($auteur_compte_principal && $auteur_compte_principal != $id_auteur) {
                inverser_relation_compte($id_auteur);
            }
            // Passer l'originateur (id_auteur) pour éviter duplications de notification
            activer_adherent($id_auteur, $reinscription, true, $id_auteur);
            $activation_done_prive = true;
        } catch (Throwable $e) {
            association_log('cotisations', 'Erreur lors de activer_adherent pour id_auteur=' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');
        }
        // Recharger lâ€™état de la cotisation après activation (utile pour la notification qui suit)
		$query_cotisation = association_cotisation_lire_par_compte($id_compte);
    }
    else {
        // Log explicite en cas d'absence d'activation pour faciliter le debug
        association_log('cotisations', 'Activation privée non déclenchée pour id_compte=' . intval($id_compte) . ' origine=' . $origine . ' statut_cotisation=' . ($query_cotisation['statut_cotisation'] ?? '') . ' validation_mode=' . ($query_categories['validation'] ?? ''), 'critique');
    }

    if($notifier) {
        if ($do_activation_prive) {
            // Après activation, notifier l'adhérent (aucun log pour succès)
            notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'activation');
        } elseif ($origine == 'prive' and $query_categories['validation'] == 'pre-paiement' and $query_cotisation['statut_cotisation'] == 'demande') {
            // Cas oÃ¹ la validation est manuelle et la cotisation passe en demande de paiement
            notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'validation_pre-paiement');
        } elseif ($origine == 'prive' and $query_categories['validation'] == 'pre-paiement' and $query_cotisation['statut_cotisation'] == 'attente') {
            // Cas oÃ¹ la validation est manuelle et la cotisation passe en attente de paiement
            notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');
        } elseif ($origine == 'prive' and $query_categories['validation'] == 'post-paiement' and $query_cotisation['statut_cotisation'] == 'attente') {
            // Cas oÃ¹ la validation est automatique et la cotisation passe en attente de paiement
            notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');
            // Cas oÃ¹ la validation est automatique et la cotisation passe en attente de paiement
        } elseif ($origine == 'prive' and $query_categories['validation'] == 'auto' and $query_cotisation['statut_cotisation'] == 'attente') {
            // Cas oÃ¹ la validation est automatique et la cotisation passe en attente de paiement
            notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');
        }


    }
    /* PAIEMENT */
    // Notifications envoyées lors d'un encaissement de paiement via l'API BANK
    // Silence : pas de log de décision paiement sauf erreur
    if($origine == 'encaissement_paiement' AND $query_transaction ['statut'] == 'ok' ) {
        if($query_categories['validation'] != 'post-paiement') {
            // Encaissement: activation possible (silencieux)
            // Si le payeur est un compte secondaire, inverser la relation
            // pour que le payeur devienne principal avant d'activer.
            try {
                $auteur_compte_principal = intval(sql_getfetsel('auteur_compte_principal', 'spip_auteurs', 'id_auteur=' . intval($id_auteur)));
                if ($auteur_compte_principal && $auteur_compte_principal != $id_auteur) {
                    // inverser la relation : le payeur devient principal
                    inverser_relation_compte($id_auteur);
                }
                // Appeler l'activation en passant l'originateur (le payeur) pour
                // éviter l'envoi de doublons de notifications.
                activer_adherent($id_auteur, $reinscription, true, $id_auteur);
                // succès silencieux
             } catch (Throwable $e) {
                 association_log('cotisations', 'Erreur lors de activer_adherent pour id_auteur=' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');
             }
			$query_cotisation = association_cotisation_lire_par_compte($id_compte);
             notifier_cotisation_adherent($query_cotisation,$query_categories,$query_transaction,'activation');
            // notification envoyée (silencieuse)
        }else{
            // Cas post-paiement: passer la cotisation en 'demande' et ne PAS activer l'adhérent
            // Post-paiement : état modifié (silencieux)
            sql_updateq(
                'spip_asso_comptes',
                ['statut_cotisation' => 'demande'],
                'id_compte=' . intval($id_compte)
            );
			association_cotisation_synchroniser_depuis_compte($id_compte, array('statut' => 'demande'));
            // Recharger lâ€™état de la cotisation avant notification
			$query_cotisation = association_cotisation_lire_par_compte($id_compte);

            // Envoyer lâ€™email dâ€™information post-paiement (silencieux)
            notifier_cotisation_adherent($query_cotisation,$query_categories,$query_transaction,'validation_post-paiement');
        }
        // Notifier également la trésorerie / admins que le paiement a été reÃ§u (post-paiement)
        notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction, 'encaissement_paiement');

        if ($GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_adhesion'] == 'oui' && intval($query_transaction['montant']) > 0) {
            job_queue_add('facteur_envoyer_recu_adhesion', 'Notification - facteur_envoyer_recu_adhesion', [$query_cotisation['id_auteur'], $query_cotisation['id_transaction'], 'encaissement', ''], '', true, 0, 0);
        }
    }
}

/**
 * Résout le sujet d'email pour les notifications de cotisation.
 * Centralise la logique de sélection des clés de langue selon le type
 * d'événement et le type d'adhérent (entreprise/adherent) afin d'éviter
 * des duplications dans le code d'envoi.
 *
 * @param string $type Type de notification (ex: 'attente_paiement', 'activation', 'echeance_echu', 'attente_admin', 'demande_admin', 'encaissement_admin')
 * @param array $contexte Contexte plat fourni aux templates (contient type_adherent, nom_adherent, nom_entreprise, reinscription, type_cotisation, delai, date_validite, etc.)
 * @return string Sujet localisé prÃªt à Ãªtre utilisé dans l'entÃªte de l'email
 */
function notifications_cotisation_trouver_sujet($type, $contexte = array()){
    // valeurs par défaut
    $type = (string)$type;
    $contexte = is_array($contexte) ? $contexte : array();
    $type_adherent = $contexte['type_adherent'] ?? ($GLOBALS['association_metas']['type_adherent_defaut'] ?? 'adherent');
    $reinscription = ($contexte['reinscription'] ?? '') === 'reinscription';

    // choix du nom affiché
    if (($contexte['type_adherent'] ?? '') === 'entreprise') {
        $nom = trim((string)($contexte['nom_entreprise'] ?? ''));
    } else {
        $nom = trim((string)($contexte['nom_adherent'] ?? ''));
    }
    if ($nom === '') {
        $nom = trim((string)($contexte['nom'] ?? ''));
    }
    $label_cotisation = $contexte['label_cotisation'] ?? '';

    // mapping simple des types vers les clés de langue
    switch ($type) {
        case 'attente_paiement':
            $cle = $type_adherent === 'entreprise' ? 'attente_paiement_entreprise_sujet' : 'attente_paiement_sujet';
            break;
        case 'validation_post-paiement':
            $cle = $type_adherent === 'entreprise' ? 'validation_post_paiement_entreprise_sujet' : 'validation_post_paiement_sujet';
            break;
        case 'validation_pre-paiement':
            $cle = $type_adherent === 'entreprise' ? 'validation_pre_paiement_entreprise_sujet' : 'validation_pre_paiement_sujet';
            break;
        case 'activation':
            // distinguer inscription / réinscription si possible
            if ($reinscription) {
                $cle = $type_adherent === 'entreprise' ? 'activation_cotisation_reinscription_entreprise_sujet' : 'activation_cotisation_reinscription_sujet';
            } else {
                $cle = $type_adherent === 'entreprise' ? 'activation_cotisation_inscription_entreprise_sujet' : 'activation_cotisation_inscription_sujet';
            }
            break;
        case 'echeance_preventive':
            $cle = 'email_notification_echeances_sujet';
            break;
        case 'echeance_echu':
            $cle = 'email_notification_echeance_echu_sujet';
            break;
        // notifications admin
        case 'attente_admin':
            $cle = 'cotisation_attente_admin_sujet';
            break;
        case 'demande_admin':
            $cle = 'cotisation_demande_admin_sujet';
            break;
        case 'encaissement_admin':
            $cle = 'cotisation_encaissement_admin_sujet';
            break;
        case 'justificatifs-a-revoir':
            $cle = 'justificatifs_a_revoir_sujet';
            break;
        default:
            // fallback général : tenter de composer un sujet lisible
            $cle = '';
    }

    // récupérer la chaÃ®ne localisée si possible
    // Préparer les arguments de traduction, en exposant id_compte et montant formaté
    $montant_fmt = '';
    if (isset($contexte['montant']) && $contexte['montant'] !== '') {
        $m = $contexte['montant'];
        if (is_numeric($m)) {
            // format number: 0 decimals if integer, otherwise 2 decimals, french thousand sep
            $dec = ($m == intval($m)) ? 0 : 2;
            $montant_fmt = number_format(floatval($m), $dec, ',', ' ');
        } else {
            $montant_fmt = (string)$m;
        }
        $devise = strtoupper(trim((string) ($contexte['devise'] ?? '')));
        if ($devise === '') {
            $devise = strtoupper(trim((string) lire_config('intl/devise_defaut')));
        }
        if ($devise !== '') {
            $montant_fmt .= ' ' . $devise;
        }
    }

    $args = array(
        'id_compte' => $contexte['id_compte'] ?? '',
        'montant' => $montant_fmt,
        'nom' => $nom,
        'type' => $label_cotisation,
        'delai' => $contexte['nb_jour_differences'] ?? ($contexte['delai'] ?? ''),
        'date_validite' => $contexte['date_validite'] ?? ($contexte['validite'] ?? ''),
    );

    if ($cle) {
        // Try domain-prefixed translation first (plugin lang file)
        $sujet = _T('notifications:' . $cle, $args);
        if (!$sujet || $sujet === 'notifications:' . $cle) {
            // fallback: try without domain
            $sujet = _T($cle, $args);
            if (!$sujet || $sujet === $cle) {
                $sujet = '';
            }
        }
    } else {
        $sujet = '';
    }

    // Ne pas composer de fallback cÃ´té code : on doit utiliser les textes des fichiers de langue.
    // Si la traduction est manquante, logger l'erreur et renvoyer une chaÃ®ne vide pour laisser
    // la gestion d'envoi décider (ou détecter le sujet manquant en log).
    if (empty($sujet)) {
        if ($cle) {
            association_log('cotisations', 'notifications_cotisation_trouver_sujet: traduction manquante pour cle=' . $cle . ' type=' . $type . ' args=' . json_encode($args), 'critique');
            // log contextuel complet pour débogage (critique)
            association_log('cotisations', $contexte, 'critique');
        } else {
            association_log('cotisations', 'notifications_cotisation_trouver_sujet: aucune clé de traduction pour type=' . $type . ' args=' . json_encode($args), 'critique');
            association_log('cotisations', $contexte, 'critique');
        }
        $sujet = '';
    }

    return $sujet;
}

/**
 * Worker job : rendre le template et envoyer l'email.
 * Arguments attendus par job_queue_add :
 *   - destinataires (string|array)
 *   - sujet (string)
 *   - template (string) nom du fond à recuperer
 *   - contexte (array) contexte plat pour le fond
 *   - bcc (string|array|false)
 *   - opts (array) options additionnelles
 */
function notifier_cotisation_envoyer($destinataires, $sujet, $template, $contexte = array(), $bcc = false, $opts = array()) {
    include_spip('inc/filtres');
    // Normaliser les arguments
    $destinataires = is_array($destinataires) ? $destinataires : $destinataires;
    $opts = is_array($opts) ? $opts : array();

    // Rendu du template (fait dans le worker pour alléger la requête initiale)
    try {
        $html = recuperer_fond($template, $contexte);
    } catch (Throwable $e) {
        association_log('cotisations', 'notifier_cotisation_envoyer: erreur rendu template ' . $template . ' : ' . $e->getMessage(), 'erreur');
        return array('success' => false, 'message' => 'Erreur rendu template');
    }

    // Forcer envoi direct dans le worker (ne pas re-queue)
    $opts['use_queue'] = false;
    $opts['enqueued'] = true;

    // Appel centralisé pour envoyer
    $res = facteur_envoyer_app($destinataires, $sujet, $html, $bcc, $opts);
    if (is_array($res) && empty($res['success'])) {
        association_log('cotisations', 'notifier_cotisation_envoyer: envoi échoué : ' . ($res['message'] ?? ''), 'erreur');
    }
    return $res;
}

/**
 * Envoie des notifications par email à un adhérent en fonction du type d'action.
 *
 * Cette fonction gère l'envoi d'emails pour différents types de notifications
 * liés aux cotisations, comme l'attente de paiement, la validation post-paiement,
 * la validation pré-paiement, ou l'activation de l'adhésion.
 *
 * @param array $query_cotisation Données de la cotisation (id_auteur, id_compte, etc.).
 * @param array $query_categories Données de la catégorie de l'adhérent.
 * @param array $query_transaction Données de la transaction associée.
 * @param string $type Type de notification à envoyer (ex: 'attente_paiement', 'activation').
 * @param array $options Contexte additionnel (nb_jour_differences, email_override, etc.).
 * @return bool Retourne true si la notification a été envoyée, false sinon.
 */
function notifier_cotisation_adherent($query_cotisation,$query_categories,$query_transaction,$type='',$options = array()){
     $reinscription = isset($query_cotisation['reinscription']) ? $query_cotisation['reinscription'] : '';

     association_log('cotisations', 'Notification (type=' . $type . ') - notifier_cotisation_adherent pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');

     if (empty($type)) {
         association_log('cotisations', 'Notification - aucun type de notif defini', 'critique');
         return false;
     }

     // Préparer le contexte plat
    // Si l'appel fournit un contexte déjà préparé (mode test), l'utiliser
    $options = is_array($options) ? $options : [];
    // Passer $options à la préparation du contexte pour permettre des appels
    // du type notifier_cotisation_adherent(array(), array(), array(), 'activation', ['id_auteur' => X])
    // qui doivent construire un contexte uniquement à partir de l'id_auteur fourni.
    $contexte_notification = notifier_cotisation_preparer_contexte($query_cotisation, $query_categories, $query_transaction, $options);
    // Si la préparation du contexte échoue, on abandonne : la fonction appelante
    // (notifier_cotisation_preparer_contexte) a déjà logué les détails pour debug.
    if ($contexte_notification === false) {
        association_log('cotisations', 'notifier_cotisation_adherent: impossible de construire le contexte de notification', 'critique');
        return false;
    }

    // Déterminer si l'envoi doit passer par la queue (par défaut true).
    // L'appel doit fournir explicitement 'use_queue' => false pour forcer l'envoi immédiat.
    $use_queue = true;
    if (array_key_exists('use_queue', $options)) {
        $use_queue = (bool)$options['use_queue'];
    }

    // Helper local pour envoi synchrone via facteur (compat: utilise use_queue=false)
    $send_now = function($dest, $sujet, $html, $bcc = false) {
        include_spip('inc/fonctions/facteur_envoyer_app');
        $res = facteur_envoyer_app($dest, $sujet, $html, $bcc, array('use_queue' => false));
        if (is_array($res)) {
            return !empty($res['success']);
        }
        return (bool)$res;
    };

    // (Options merging handled below with an allowlist to avoid leaking arrays/objects into templates.
    if (!empty($options) && is_array($options)) {
        // Autoriser uniquement un petit set de clés scalaires à enrichir le contexte
        $autorise = array('nb_jour_differences', 'email_override', 'use_queue', 'lang', 'validite', 'id_categorie', 'id_auteur', 'id_compte', 'montant', 'nom_adherent', 'nom_entreprise', 'statut');
        foreach ($autorise as $k) {
            if (array_key_exists($k, $options) && (is_scalar($options[$k]) || is_null($options[$k]))) {
                $contexte_notification[$k] = $options[$k];
            }
        }
    }

     $email_adherent = $contexte_notification['email_adherent'];
    if (!empty($options['email_override'])) {
        // Normaliser email_override : accepter string ou array -> garder premier email valide
        $override = $options['email_override'];
        if (is_array($override)) {
            $first = '';
            foreach ($override as $m) {
                $m = trim($m);
                if ($m && filter_var($m, FILTER_VALIDATE_EMAIL)) {
                    $first = $m;
                    break;
                }
            }
            if ($first) $email_adherent = $first;
        } else {
            $override = trim((string)$override);
            if ($override && filter_var($override, FILTER_VALIDATE_EMAIL)) {
                $email_adherent = $override;
            }
        }
    }
     $nom_adherent = $contexte_notification['nom_adherent'];
     $type_adherent = $contexte_notification['type_adherent'];

     // Précharger la liste des adresses 'adh' configurées (une seule lecture)
     $emails_adh = parser_emails_depuis_config(isset($GLOBALS['association_metas']['config_destinataires_creation_cotisation_adh']) ? $GLOBALS['association_metas']['config_destinataires_creation_cotisation_adh'] : '');
    // Si parser renvoie false, garder false; sinon s'assurer qu'on a bien un tableau
    if ($emails_adh === false) {
        $emails_adh = false;
    }

    // Silence : on n'écrit pas de log pour les événements normaux ici

    // Déterminer le sujet via la fonction centralisée
    $sujet = notifications_cotisation_trouver_sujet($type, $contexte_notification);

    // Bloquer l'envoi si le sujet est manquant : pas de fallback cÃ´té code
    if (empty($sujet)) {
        association_log('cotisations', 'notifier_cotisation_adherent: envoi bloqué - sujet manquant pour type=' . $type . ' id_compte=' . intval($contexte_notification['id_compte'] ?? 0) . ' args=' . json_encode(array('nom' => $contexte_notification['nom_cible'] ?? '', 'type' => $contexte_notification['type_cotisation'] ?? '')), 'critique');
        // log complet du contexte pour debug
        association_log('cotisations', $contexte_notification, 'critique');
        return false;
    }

    if ($type=="attente_paiement") {
        // Envoie d'un email avec les instructions en cas de passage à "attente de paiement".
        // sujet déjà résolu.

        // choisir le template
        $template = find_in_path('notifications/nouvel_adherent_email_paiement_instructions.html') ? 'notifications/nouvel_adherent_email_paiement_instructions' : 'notifications/cotisation-attente_paiement';
        // préparer BCC si des adresses adh existent
        $bcc = !empty($emails_adh) ? (count($emails_adh) == 1 ? $emails_adh[0] : $emails_adh) : false;
        if ($use_queue) {
            // Générer et envoyer dans un job : rendu du template et envoi seront faits par le worker
            job_queue_add('notifier_cotisation_envoyer', 'Notification - attente_paiement ' . intval($contexte_notification['id_compte'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, $bcc, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (attente_paiement) pour ' . $email_adherent, 'info');
            return true;
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $ok = $send_now($email_adherent, $sujet, $html, $bcc);
            if (!$ok) spip_log('Envoi direct attente_paiement échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
            return $ok;
        }
    }

    elseif ($type=="validation_post-paiement") {
        // sujet déjà résolu via notifications_cotisation_trouver_sujet
        $template = 'notifications/cotisation-validation_post-paiement';
        $bcc = !empty($emails_adh) ? (count($emails_adh) == 1 ? $emails_adh[0] : $emails_adh) : false;
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - validation_post-paiement ' . intval($contexte_notification['id_compte'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, $bcc, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (validation_post-paiement) pour ' . $email_adherent, 'info');
            return true;
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $ok = $send_now($email_adherent, $sujet, $html, $bcc);
            if (!$ok) spip_log('Envoi direct validation_post-paiement échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
            return $ok;
        }
    }
    elseif ($type=="validation_pre-paiement") {
        // sujet déjà résolu via notifications_cotisation_trouver_sujet
        $template = 'notifications/cotisation-validation_pre-paiement';
        $bcc = !empty($emails_adh) ? (count($emails_adh) == 1 ? $emails_adh[0] : $emails_adh) : false;
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - validation_pre-paiement ' . intval($contexte_notification['id_compte'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, $bcc, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (validation_pre-paiement) pour ' . $email_adherent, 'info');
            return true;
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $ok = $send_now($email_adherent, $sujet, $html, $bcc);
            if (!$ok) spip_log('Envoi direct validation_pre_paiement échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
            return $ok;
        }
    }

    elseif ($type=="activation") {
        // sujet déjà résolu via notifications_cotisation_trouver_sujet

        if ($GLOBALS['association_metas']['meta_cfg_donation'] == 'oui' && $query_cotisation['recette'] != $query_transaction['montant']) {
            $contexte_notification['montant_don'] = $query_transaction['montant'] - $query_cotisation['recette'];
        }

        $template = find_in_path('notifications/nouvel_adherent_email_paiement_valide_instructions.html') ? 'notifications/nouvel_adherent_email_paiement_valide_instructions' : 'notifications/cotisation-activation';
        $bcc = !empty($emails_adh) ? (count($emails_adh) == 1 ? $emails_adh[0] : $emails_adh) : false;
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - activation ' . intval($contexte_notification['id_compte'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, $bcc, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (activation) pour ' . $email_adherent, 'info');
            return true;
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $ok = $send_now($email_adherent, $sujet, $html, $bcc);
            if (!$ok) spip_log('Envoi direct activation échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
            return $ok;
        }
    }
    elseif ($type === 'justificatifs-a-revoir') {
        $template = 'notifications/cotisation-justificatifs-a-revoir';
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - justificatifs a revoir ' . intval($contexte_notification['id_compte'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (justificatifs-a-revoir) pour le compte ' . intval($contexte_notification['id_compte'] ?? 0), 'info');
            return true;
        }
        $html = recuperer_fond($template, $contexte_notification);
        return $send_now($email_adherent, $sujet, $html);
    }
    elseif ($type == 'echeance_preventive') {
        // sujet déjà résolu via notifications_cotisation_trouver_sujet

        $contexte_notification['nb_jour_differences'] = $contexte_notification['nb_jour_differences'] ?? 0;
        // Le contexte est désormais nettoyé en amont (merge contrôlé).
        $template = 'notifications/notification_echeances_adherent';
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - echeance_preventive ' . intval($contexte_notification['id_auteur'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification mise en file (echeance_preventive) pour ' . $email_adherent, 'info');
            return true;
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $ok = $send_now($email_adherent, $sujet, $html);
            if (!$ok) spip_log('Envoi direct echeance_preventive échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
            return $ok;
        }
    }
    elseif ($type == 'echeance_echu') {
        // If this is an entreprise and the configuration disables notifying companies when echu, skip
        $contexte_notification['nb_jour_differences'] = $contexte_notification['nb_jour_differences'] ?? 0;
        // Option config key: 'notification_echeance_notifier_echu_entreprise' (values 'oui'/'non')
        if (($type_adherent === 'entreprise') ) {
            $cfg = '';
            if (isset($GLOBALS['association_metas']['notification_echeance_notifier_echu_entreprise'])) {
                $cfg = $GLOBALS['association_metas']['notification_echeance_notifier_echu_entreprise'];
            } else {
                $cfg = lire_config('notification_echeance_notifier_echu_entreprise', 'oui');
            }
            if ($cfg !== '' && strtolower($cfg) === 'non') {
                // Skip echu for entreprise (silencieux)
                return true;
            }
        }
         // Le contexte est désormais nettoyé en amont (merge contrÃ´lé). Aucune sanitisation ad-hoc ici.
          $template = 'notifications/notification_echeances_adherent_echu';
          if ($use_queue) {
              job_queue_add('notifier_cotisation_envoyer', 'Notification - echeance_echu ' . intval($contexte_notification['id_auteur'] ?? 0), array($email_adherent, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
              association_log('cotisations', 'Notification mise en file (echeance_echu) pour ' . $email_adherent, 'info');
              return true;
          } else {
              $html = recuperer_fond($template, $contexte_notification);
              $ok = $send_now($email_adherent, $sujet, $html);
              if (!$ok) spip_log('Envoi direct echeance_echu échoué pour ' . $email_adherent, 'association' . _LOG_ERREUR);
              return $ok;
          }
      }
      return true;
}

/**
 * Prépare un contexte plat pour les notifications de cotisation.
 * Retourne false si on ne peut pas construire un contexte (par ex. email manquant).
 *
 * @param array|int|null $query_cotisation Ligne de spip_asso_comptes ou id_compte
 * @param array $query_categories Ligne de spip_asso_categories_adherents
 * @param array $query_transaction Ligne de spip_transactions
 * @param array $options Options additionnelles (email_override, validite, nb_jour_differences, type_adherent, nom_entreprise, nom_adherent)
 * @return array|false Contexte plat prÃªt pour recuperer_fond() ou false en erreur
 */
function notifier_cotisation_preparer_contexte($query_cotisation = null, $query_categories = array(), $query_transaction = array(), $options = array()){
    include_spip('inc/filtres'); // email_valide()

    // Normaliser $query_cotisation : accepter id_compte ou tableau
    if (is_numeric($query_cotisation) && intval($query_cotisation) > 0) {
		include_spip('inc/cotisations_stockage');
		$query_cotisation = association_cotisation_lire_par_compte($query_cotisation);
    }
    $query_cotisation = is_array($query_cotisation) ? $query_cotisation : array();
    $query_categories = is_array($query_categories) ? $query_categories : array();
    $query_transaction = is_array($query_transaction) ? $query_transaction : array();
    $options = is_array($options) ? $options : array();

    // id_auteur
    $id_auteur = intval($query_cotisation['id_auteur'] ?? $options['id_auteur'] ?? 0);
    $query_auteur = array();
    if ($id_auteur > 0) {
        $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
        if (!$query_auteur) $query_auteur = array();
    }

    // email : priorité override -> transaction -> cotisation -> auteur
    $email = '';
    if (!empty($options['email_override'])) {
        if (is_array($options['email_override'])) {
            foreach ($options['email_override'] as $m) {
                $m = trim($m);
                if ($m && filter_var($m, FILTER_VALIDATE_EMAIL)) { $email = $m; break; }
            }
        } else {
            $m = trim((string)$options['email_override']);
            if ($m && filter_var($m, FILTER_VALIDATE_EMAIL)) $email = $m;
        }
    }
    if (empty($email)) {
        $email = $query_transaction['email'] ?? $query_cotisation['email'] ?? $query_auteur['email'] ?? '';
    }

    // Construire le nom affiché
    $nom_entreprise =  $query_auteur['nom_entreprise'] ?? '';
    $nom_adherent = $query_auteur['nom_famille'] . ' ' . $query_auteur['prenom'] ?? '';

    // Type adherent
    $type_auteur_adherent = $query_auteur['radio_type_adherent'] ?? 'adherent';

    // validite : priorité options > cotisation > auteur
    $validite = $options['validite'] ?? ($query_cotisation['validite'] ?? ($query_auteur['validite'] ?? ''));
    // si format Y-m-d H:i:s convert to d/m/Y for templates that expect human date
    $date_validite = '';
    if ($validite) {
        // accept both 2026-09-30 00:00:00 and 30/09/2026
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $validite)) {
            $date_validite = @date('d/m/Y', strtotime($validite));
        } else {
            $date_validite = $validite;
        }
    }

    // montant & transaction
    $montant = $query_transaction['montant'] ?? $query_cotisation['montant'] ?? ($options['montant'] ?? 0);
    $id_transaction = $query_transaction['id_transaction'] ?? ($query_cotisation['id_transaction'] ?? ($options['id_transaction'] ?? ''));
    $devise = $query_transaction['devise'] ?? ($query_categories['devise'] ?? ($options['devise'] ?? ''));
    if (function_exists('association_cotisation_resoudre_devise')) {
        $devise = association_cotisation_resoudre_devise($devise);
    } elseif (trim((string) $devise) === '') {
        $devise = lire_config('intl/devise_defaut');
    }

    // type_cotisation (libellé)
    $type_cotisation = $query_categories['type_adherent']  ?? '';
    $label_cotisation = $query_categories['valeur']  ?? '';

    // id_compte / id_categorie
    $id_compte = intval($query_cotisation['id_compte'] ?? ($options['id_compte'] ?? 0));
    $id_categorie = intval($query_cotisation['id_categorie'] ?? ($options['id_categorie'] ?? 0));

    // statut (auteur statut interne) pour warning admin
    $statut = $query_auteur['statut_interne'] ?? ($query_cotisation['statut_cotisation'] ?? ($options['statut'] ?? ''));

    // préparer la liste des catégories (préfiltrée si possible)
    $liste_categories_adherents = array();
    if (function_exists('preparer_liste_categories')) {
        // origine: 'prive' par défaut
        $reinscription = $query_cotisation['reinscription'] ?? ($options['reinscription'] ?? '');
        try {
            $liste_categories_adherents = preparer_liste_categories($id_auteur, 'prive', $reinscription, $type_auteur_adherent);
        } catch (Throwable $e) {
            association_log('cotisations', 'Erreur preparer_liste_categories: ' . $e->getMessage(), 'erreur');
            $liste_categories_adherents = array();
        }
    }

    // Assemble le contexte plat
    $contexte = array(
        'email_adherent' => $email,
        'nom_adherent' => $nom_adherent,
        'nom_entreprise' => $nom_entreprise,
        'id_compte' => $id_compte,
        'id_auteur' => $id_auteur,
        'reinscription' => $query_cotisation['reinscription'] ?? ($options['reinscription'] ?? ''),
        'montant' => $montant,
        'devise' => strtoupper(trim((string) $devise)),
        'id_transaction' => $id_transaction,
        'transaction_hash' => $query_transaction['transaction_hash'] ?? '',
        'validite' => $validite,
        'date_validite' => $date_validite,
        'statut' => $statut,
        'nom_cible' => $nom_entreprise ?: $nom_adherent,
        'contact_reference' => $query_auteur['nom_famille'] ?? $nom_adherent,
        'id_categorie' => $id_categorie,
        'liste_categories_adherents' => $liste_categories_adherents,
        'type_adherent' => $type_auteur_adherent,
        'type_cotisation' => $type_cotisation,
        'label_cotisation' => $label_cotisation,
    );

    // Etat documentaire commun aux emails adherent et administrateur.
    // Le bloc reste silencieux pour les categories qui n'exigent rien.
    $justificatifs_requis = (($query_categories['document_justificatif'] ?? 'non') === 'oui');
    $etat_justificatifs = array('total' => 0, 'valides' => 0, 'complet' => false, 'controle' => false);
    if ($id_compte > 0) {
        include_spip('inc/justificatifs_cotisation');
        if (function_exists('association_justificatifs_cotisation_etat')) {
            $etat_justificatifs = association_justificatifs_cotisation_etat($id_compte);
        }
    }
    $contexte['justificatifs_requis'] = $justificatifs_requis ? 'oui' : 'non';
    $contexte['documents_recus'] = intval($etat_justificatifs['total']);
    $contexte['documents_controles'] = intval($etat_justificatifs['valides']);
    $contexte['justificatifs_complets'] = !empty($etat_justificatifs['complet']) ? 'oui' : 'non';
    $contexte['justificatifs_controles'] = !empty($etat_justificatifs['controle']) ? 'oui' : 'non';

    // exposer nb_jour_differences si fourni
    if (array_key_exists('nb_jour_differences', $options)) {
        $contexte['nb_jour_differences'] = intval($options['nb_jour_differences']);
    }

    // Vérifier email valide ; si absent on retourne false (appelant doit gérer)
    if (empty($contexte['email_adherent']) || !filter_var($contexte['email_adherent'], FILTER_VALIDATE_EMAIL)) {
        // Pas d'email valide pour la notification : renvoyer false pour indiquer impossibilité
        // Log critique détaillé pour diagnostic
        $msg = 'notifier_cotisation_preparer_contexte: email absent ou invalide pour id_compte=' . $id_compte . ' / id_auteur=' . $id_auteur;
        association_log('cotisations', $msg, 'critique');
        // Logger les paramètres d'entrée complets (query_cotisation, query_categories, query_transaction, options)
        $input = array(
            'query_cotisation' => $query_cotisation,
            'query_categories' => $query_categories,
            'query_transaction' => $query_transaction,
            'options' => $options,
        );
        association_log('cotisations', $input, 'critique');
        // Logger le contexte construit (peut Ãªtre vide ou partiel)
        association_log('cotisations', $contexte, 'critique');
        return false;
    }

    return $contexte;
}

/**
 * Envoie des notifications par email à un administrateur ou trésorier en fonction du type d'action.
 *
 * Cette fonction gère l'envoi d'emails pour différents types de notifications
 * liés aux cotisations, comme l'attente de validation, la demande de validation,
 * ou l'encaissement d'un paiement.
 *
 * @param array $query_cotisation Données de la cotisation (id_auteur, id_compte, etc.).
 * @param array $query_categories Données de la catégorie de l'adhérent.
 * @param array $query_transaction Données de la transaction associée.
 * @param string $type Type de notification à envoyer (ex: 'attente_validation', 'encaissement_paiement').
 * @param array $options Contexte additionnel (email_override, use_queue, etc.).
 * @return bool Retourne true si la notification a été envoyée, false sinon.
 */
function notifier_cotisation_admin($query_cotisation, $query_categories = [], $query_transaction = [], $type = '', $options = []){
    // Inclure helpers SPIP requis
    include_spip('inc/filtres'); // pour email_valide()

    $reinscription = $query_cotisation['reinscription'];
    association_log('cotisations', 'Notification (type=' . $type . ') - notifier_cotisation_admin pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');

    if (empty($type)) {
        association_log('cotisations', 'Notification - aucun type de notif defini pour notifier_cotisation_admin', 'critique');
        return true; // paramètre manquant : cas réellement fatal pour cette fonction
    }

    // Préparer le contexte commun (désormais plat)
    $contexte_notification = notifier_cotisation_preparer_contexte($query_cotisation, $query_categories, $query_transaction);
    if (!$contexte_notification) {
        association_log('cotisations', 'Notification - impossible de préparer le contexte pour notifier_cotisation_admin', 'critique');
        return true; // contexte invalide : abort
    }

    $contexte_notification['url_editer_cotisation'] = generer_url_ecrire('editer_asso_cotisation', 'id_compte=' . intval($query_cotisation['id_compte'] ?? 0));
    $contexte_notification['url_voir_adherent'] = generer_url_ecrire('voir_adherent', 'id_auteur=' . intval($query_cotisation['id_auteur'] ?? 0));
    $contexte_notification['url_transaction'] = generer_url_ecrire('payer', 'id_transaction=' . intval($query_cotisation['id_transaction'] ?? 0) . '&transaction_hash=' . ($query_transaction['transaction_hash'] ?? ''));

    // Log contextuel réduit (INFO)
    association_log('cotisations', $contexte_notification, 'critique');

   // Précharger la liste des emails tresoriers une seule fois
   $emails_tres = obtenir_emails_tresoriers();

   // Support d'override des destinataires via $options['email_override']
   if (!empty($options) && !empty($options['email_override'])) {
       $override = $options['email_override'];
       // Accepte string (séparateurs ; , espace) ou array
       if (!is_array($override)) {
           $parts = preg_split('/[;,\s]+/', trim((string)$override), -1, PREG_SPLIT_NO_EMPTY);
       } else {
           $parts = $override;
       }
       $valid = array();
       foreach ($parts as $m) {
           $m = trim($m);
           if ($m && filter_var($m, FILTER_VALIDATE_EMAIL)) {
               $valid[] = $m;
           }
       }
       if (!empty($valid)) {
           $emails_tres = $valid; // remplacer la liste par l'override validé
           association_log('cotisations', 'Notification - override destinataires admin utilisé: ' . implode(',', $emails_tres), 'critique');
       } else {
           association_log('cotisations', 'Notification - override destinataires admin fourni mais aucune adresse valide trouvée: ' . print_r($override, true), 'erreur');
       }
   }

   if (empty($emails_tres)) {
       association_log('cotisations', 'Notification admin ignorée : aucun destinataire trésorier valide configuré', 'erreur');
       return false;
   }

    // Déterminer si l'envoi doit passer par la queue (par défaut true).
    // L'appel doit fournir explicitement 'use_queue' => false pour forcer l'envoi immédiat.
    $use_queue = true;
    if (array_key_exists('use_queue', $options)) {
        $use_queue = (bool)$options['use_queue'];
    }
    // Cas : attente de paiement -> notification trésorière
    if ($type == 'attente_paiement'){
        // déléguer la résolution du sujet à la fonction centralisée (type admin)
        $sujet = notifications_cotisation_trouver_sujet('attente_admin', $contexte_notification);
        // Bloquer l'envoi si sujet manquant
        if (empty($sujet)) {
            association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_attente_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');
            association_log('cotisations', $contexte_notification, 'critique');
            return false;
        }
        $template = 'notifications/cotisation-attente_admin';
       association_log('cotisations', 'Notifier sujet (attente_admin): ' . $sujet, 'critique');
       if ($use_queue) {
           job_queue_add('notifier_cotisation_envoyer', 'Notification - attente_admin ' . intval($contexte_notification['id_compte'] ?? 0), array($emails_tres, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
           association_log('cotisations', 'Notification admin mise en file (attente_admin) pour ' . implode(',', (array)$emails_tres), 'info');
       } else {
           $html = recuperer_fond($template, $contexte_notification);
           $res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));
           if (is_array($res) && empty($res['success'])) {
               association_log('cotisations', 'notifier_cotisation_admin: erreur envoi attente_admin : ' . ($res['message'] ?? ''), 'erreur');
           }
       }
    }
   // Cas : demande de validation -> envoi uniquement à la trésorière/admin
   elseif ($type == 'attente_validation'){
       // déléguer la résolution du sujet à la fonction centralisée
       $sujet = notifications_cotisation_trouver_sujet('demande_admin', $contexte_notification);
       // Bloquer l'envoi si sujet manquant
       if (empty($sujet)) {
           association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_demande_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');
           association_log('cotisations', $contexte_notification, 'critique');
           return false;
       }
        $template = 'notifications/cotisation-demande_admin';
        association_log('cotisations', 'Notifier sujet (demande_admin): ' . $sujet, 'critique');
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - demande_admin ' . intval($contexte_notification['id_compte'] ?? 0), array($emails_tres, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification admin mise en file (demande_admin) pour ' . implode(',', (array)$emails_tres), 'info');
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));
            if (is_array($res) && empty($res['success'])) {
                association_log('cotisations', 'notifier_cotisation_admin: erreur envoi demande_admin : ' . ($res['message'] ?? ''), 'erreur');
            }
        }
   }
    // Cas : encaissement de paiement -> prévenir la trésorerie qu'un paiement a été reÃ§u
    elseif ($type == 'encaissement_paiement'){
        // déléguer la résolution du sujet à la fonction centralisée
        $sujet = notifications_cotisation_trouver_sujet('encaissement_admin', $contexte_notification);
        // Bloquer l'envoi si sujet manquant
        if (empty($sujet)) {
            association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_encaissement_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');
            association_log('cotisations', $contexte_notification, 'critique');
            return false;
        }
        // fond spécifique si existant sinon fallback sur cotisation-attente_admin
        $template = find_in_path('notifications/cotisation-encaissement_admin.html') ? 'notifications/cotisation-encaissement_admin' : 'notifications/cotisation-attente_admin';
        association_log('cotisations', 'Notifier sujet (encaissement_admin): ' . $sujet, 'critique');
        if ($use_queue) {
            job_queue_add('notifier_cotisation_envoyer', 'Notification - encaissement_admin ' . intval($contexte_notification['id_compte'] ?? 0), array($emails_tres, $sujet, $template, $contexte_notification, false, array()), '', false, 0, 0);
            association_log('cotisations', 'Notification admin mise en file (encaissement_admin) pour ' . implode(',', (array)$emails_tres), 'info');
        } else {
            $html = recuperer_fond($template, $contexte_notification);
            $res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));
            if (is_array($res) && empty($res['success'])) {
                association_log('cotisations', 'notifier_cotisation_admin: erreur envoi encaissement_admin : ' . ($res['message'] ?? ''), 'erreur');
            }
        }
    }else{
            // Type non géré : log et ne pas bloquer
            association_log('cotisations', 'Notification - type non géré par notifier_cotisation_admin: ' . $type, 'critique');
    }
    return true;
}

/**
  * Active un adhérent et ses comptes liés (principal ou secondaires).
  * La récursivité est limitée à un seul niveau pour éviter les boucles infinies.
  *
  * @param int $id_auteur Identifiant de l'adhérent à activer
  * @param string $reinscription Type d'inscription ('reinscription' ou '')
  * @param bool $propager Indique si l'activation doit Ãªtre propagée aux comptes liés
  * @return void
  */
  function activer_adherent($id_auteur, $reinscription = '', $propager = true, $originateur = null)
 {

     if (!intval($id_auteur) || $id_auteur <= 0) {
         association_log('cotisations', 'ID auteur invalide pour l\'activation de l\'adhérent', 'erreur');
         return;
     }

     // Inclut les fonctions de gestion de session
     include_spip('inc/session');
     include_spip('inc/fonctions/privileges_adherent');
     // Inclut la fonction pour calculer la validité de l'adhésion
     include_spip('inc/fonctions/association_validite_calculator');
     // Récupère les informations de l'adhérent
     $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
     if (!$query_auteur) {
         association_log('cotisations', 'Adhérent #' . $id_auteur . ' introuvable', 'erreur');
         return;
     }


     // Calcule la nouvelle validite de l'adherent
     $validite = association_validite_calculator($id_auteur);

     $date = implode('-', array_reverse(explode('/', $validite))) . ' 00:00:00';
     $timestamp_validite_cible = strtotime($date);
     $timestamp_validite_actuelle = !empty($query_auteur['validite']) ? strtotime($query_auteur['validite']) : false;


     // Verifie si l'adherent est deja actif et valide
     if (
         $query_auteur['statut_interne'] == 'ok'
         && $timestamp_validite_actuelle !== false
         && $timestamp_validite_cible !== false
         && $timestamp_validite_actuelle >= $timestamp_validite_cible
     ) {
         // Si l'adherent est deja actif et que la validite n'a pas change
         association_log('cotisations', 'Adhérent #' . $id_auteur . ' deja actif et valide', 'critique');
         $reinscription = 'reinscription';
         verifier_privileges_adherent($id_auteur, $reinscription);

     } elseif (
         $query_auteur['statut_interne'] == 'ok'
         && $timestamp_validite_actuelle !== false
         && $timestamp_validite_cible !== false
         && $timestamp_validite_actuelle < $timestamp_validite_cible
     ) {
         // Si l'adherent est actif mais que la validite doit etre mise a jour
         $reinscription = 'reinscription';
         sql_updateq('spip_auteurs', ["validite" => $date], "id_auteur = " . intval($id_auteur));
         actualiser_sessions(['id_auteur' => $id_auteur, 'validite' => $date]);
         verifier_privileges_adherent($id_auteur, $reinscription);

     } else {
         // Si l'adhérent est un nouveau ou un prospect
         $reinscription = 'inscription';
         sql_updateq('spip_auteurs', ["validite" => $date, "statut_interne" => 'ok'], "id_auteur = " . intval($id_auteur));
         // activation normale : log INFO
         actualiser_sessions(['id_auteur' => $id_auteur, 'validite' => $date, 'statut_interne' => 'ok']);

         activer_privileges_adherent($id_auteur, $reinscription);
     }

      // Traitement des comptes liés uniquement si propager=true et si la config l'autorise
      $compte_secondaire_activation = $GLOBALS['association_metas']['config_compte_secondaire_activation'] ?? 'oui';
      if ($propager && $compte_secondaire_activation === 'oui') {
         // Récupération du compte principal si l'adhérent est un compte secondaire
         $compte_principal = sql_fetsel('auteur_compte_principal', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
         if ($compte_principal && isset($compte_principal['auteur_compte_principal']) && intval($compte_principal['auteur_compte_principal']) > 0 &&
             intval($compte_principal['auteur_compte_principal']) != $id_auteur) {
             $id_principal = intval($compte_principal['auteur_compte_principal']);
             // L'activation d'un compte secondaire déclenche l'activation du principal
             // en mode "propager=true" afin que le principal puisse ensuite activer
             // ses comptes secondaires (les appels internes vers chaque secondaire
             // sont faits avec propager=false pour éviter la récursivité infinie).
             // On transmet l'originateur (si présent) ou l'id courant afin d'éviter
             // l'envoi de doublons de notification.
             $origin_to_pass = $originateur ?? $id_auteur;
             activer_adherent($id_principal, $reinscription, true, $origin_to_pass);
         } else {
             // Récupération des comptes secondaires si l'adhérent est un compte principal
             $comptes_secondaires = sql_allfetsel('id_auteur', 'spip_auteurs',
                 'auteur_compte_principal=' . intval($id_auteur) . ' AND ' .
                 sql_in('statut_interne', array('prospect', 'echu', 'ok')));

             if ($comptes_secondaires && count($comptes_secondaires) > 0) {
                 foreach ($comptes_secondaires as $compte) {
                    $id_secondaire = intval($compte['id_auteur']);
                    if ($id_secondaire > 0 && $id_secondaire != $id_auteur) {
                         // Activer le secondaire sans propager
                         activer_adherent($id_secondaire, $reinscription, false, $originateur);
                         // Notifier le secondaire de manière asynchrone (file),
                         // sauf si c'est l'originateur de l'activation (pour éviter double envoi).
                         if (empty($originateur) || intval($originateur) !== $id_secondaire) {
                             try {
                                 // Appel minimal: on ne passe pas de query_cotisation, query_categories,
                                 // query_transaction â€” la préparation du contexte acceptera
                                 // options['id_auteur'] grâce au changement ci-dessus.
                                 notifier_cotisation_adherent(array(), array(), array(), 'activation', array('id_auteur' => $id_secondaire, 'use_queue' => true));
                                 association_log('cotisations', 'Notification d\'activation en file ajoutée pour auteur #' . $id_secondaire, 'critique');
                             } catch (Throwable $e) {
                                 association_log('cotisations', 'Erreur notification activation secondaire id_auteur=' . intval($id_secondaire) . ' : ' . $e->getMessage(), 'erreur');
                             }
                         }
                    }
                 }
             }
         }
     }
     // Si l'adhérent est une entreprise, tenter d'associer aux listes de diffusion configurées
    // Utiliser le critère explicite stocké dans la fiche auteur : radio_type_adherent
    // (champ saisi depuis le formulaire d'adhésion). On compare strictement à 'entreprise'.
    if (isset($query_auteur['radio_type_adherent']) && $query_auteur['radio_type_adherent'] === 'entreprise') {
        associer_liste_diffusion_entreprise($id_auteur, $query_auteur);
    }
 }

/**
 * Associer un compte entreprise aux listes de diffusion configurées.
 * Lecture de la méta 'meta_cfg_liste_diffusion_compte_entreprise' ou
 * 'meta_cfg_liste_diffusion_compte_entreprise' (format chaÃ®ne ou tableau).
 * Tentera d'appeler une fonction d'API de mailing si elle existe (ex: mailsubscribe_subscribe)
 * pour chaque liste. En l'absence d'API, on se contente d'un log.
 *
 * @param int $id_auteur
 * @param array|null $query_auteur (optionnel) pour éviter un SELECT supplémentaire
 * @return void
 */

/**
 * Inverse la relation principal/secondaire lorsque c'est un compte secondaire
 * qui règle une adhésion.
 *
 * Comportement :
 * - Le payeur secondaire ($id_secondaire) devient le nouveau principal (auteur_compte_principal = NULL).
 * - L'ancien principal devient secondaire rattaché au nouveau principal.
 * - Les autres secondaires de l'ancien principal sont rattachés au nouveau principal.
 *
 * Cette opération est loguée pour audit. Elle n'affecte pas les comptes financiers
 * (spip_asso_comptes) ; elle modifie uniquement les relations entre auteurs.
 *
 * @param int $id_secondaire Identifiant du compte secondaire payeur
 * @return bool true si l'opération a été faite, false sinon
 */
function inverser_relation_compte($id_secondaire) {
    $id_secondaire = intval($id_secondaire);
    if (!$id_secondaire) return false;

    // Récupérer l'ancien principal
    $id_principal = intval(sql_getfetsel('auteur_compte_principal', 'spip_auteurs', 'id_auteur=' . $id_secondaire));
    if (!$id_principal || $id_principal == $id_secondaire) {
        // Pas de principal connu -> rien à faire
        association_log('cotisations', 'inverser_relation_compte: aucun principal trouvé pour #' . $id_secondaire, 'critique');
        return false;
    }

    // Récupérer tous les secondaires actuels du principal
    $comptes = sql_allfetsel('id_auteur', 'spip_auteurs', 'auteur_compte_principal=' . intval($id_principal));
    $ids = array();
    if ($comptes) {
        foreach ($comptes as $c) {
            $ids[] = intval($c['id_auteur']);
        }
    }

    // Log avant modification
    association_log('cotisations', 'inverser_relation_compte: avant inversion principal=' . $id_principal . ' -> secondaires=[' . implode(',', $ids) . '] payeur=' . $id_secondaire, 'critique');

    // 1) Mettre le payeur en principal (NULL)
    $r1 = sql_updateq('spip_auteurs', array('auteur_compte_principal' => null), 'id_auteur=' . $id_secondaire);

    // 2) Re-rattacher les autres secondaires (sauf le payeur) au nouveau principal
    if (!empty($ids)) {
        foreach ($ids as $id) {
            if ($id === $id_secondaire) continue;
            sql_updateq('spip_auteurs', array('auteur_compte_principal' => $id_secondaire), 'id_auteur=' . intval($id));
        }
    }

    // 3) Rattacher l'ancien principal au nouveau principal
    sql_updateq('spip_auteurs', array('auteur_compte_principal' => $id_secondaire), 'id_auteur=' . $id_principal);

    // Log après modification
    $comptes_apres = sql_allfetsel('id_auteur,auteur_compte_principal', 'spip_auteurs', 'id_auteur IN (' . implode(',', array_merge(array($id_principal), $ids, array($id_secondaire))) . ')');
    association_log('cotisations', 'inverser_relation_compte: apres inversion: ' . var_export($comptes_apres, true), 'critique');

    return true;
}

function associer_liste_diffusion_entreprise($id_auteur, $query_auteur = null) {
    include_spip('inc/filtres');
    // Charger auteur si besoin
    if ($query_auteur === null) {
        $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
        if (!$query_auteur) return;
    }

    $liste_meta = '';
    // la saisie en config utilise 'meta_cfg_liste_diffusion_compte_entreprise'
    if (!empty($GLOBALS['association_metas']['meta_cfg_liste_diffusion_compte_entreprise'])) {
        $liste_meta = $GLOBALS['association_metas']['meta_cfg_liste_diffusion_compte_entreprise'];
    } else {
        $liste_meta = lire_config('meta_cfg_liste_diffusion_compte_entreprise', '');
    }

    if (empty($liste_meta)) {
        association_log('cotisations', 'Aucune liste de diffusion configurée pour les entreprises', 'critique');
        return;
    }

    // Normaliser : si tableau, garder tel quel, sinon split par , ; espace
    if (is_array($liste_meta)) {
        $lists = $liste_meta;
    } else {
        $parts = preg_split('/[;,\s]+/', trim((string)$liste_meta), -1, PREG_SPLIT_NO_EMPTY);
        $lists = $parts;
    }

    $email = $query_auteur['email'] ?? '';
    $nom = $query_auteur['nom_entreprise'] ?? ($query_auteur['nom_famille'] . ' ' . ($query_auteur['prenom'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        association_log('cotisations', 'associer_liste_diffusion_entreprise : email invalide pour auteur #' . intval($id_auteur), 'erreur');
        return;
    }

    foreach ($lists as $list) {
        $list = trim($list);
        if ($list === '') continue;
        // Si une API de mailing est disponible (fonction standard hypothétique), l'utiliser
        if (function_exists('mailsubscribe_subscribe')) {
            try {
                mailsubscribe_subscribe($list, $email, $nom);
                association_log('cotisations', 'Entreprise #' . intval($id_auteur) . ' abonnée à la liste ' . $list, 'critique');
            } catch (Throwable $e) {
                association_log('cotisations', 'Erreur abonnement liste ' . $list . ' pour entreprise #' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');
            }
        } else {
            // Pas d'API détectée : loguer pour information
            association_log('cotisations', 'associer_liste_diffusion_entreprise : (simulé) entreprise #' . intval($id_auteur) . ' -> liste ' . $list, 'critique');
        }
    }
}

/**
 * Récupère la liste des emails des trésoriers.
 * Si $type === 'entreprise', tente la méta spécifique entreprise puis fallback sur la méta générale.
 * Si $type === null, fusionne les deux méta (générale + entreprise) et déduplique.
 * Retourne false si aucune adresse valide trouvée.
 *
 * @param string|null $type 'entreprise'|'adherent'|null
 * @return array|false
 */
function obtenir_emails_tresoriers($type = null) {
    // Méta keys possibles
    $meta_general = $GLOBALS['association_metas']['config_destinataires_creation_cotisation_tresorier'] ?? null;
    $meta_entreprise = $GLOBALS['association_metas']['config_destinataires_creation_cotisation_tresorier_entreprise'] ?? null;

    // Essayer lire_config si non présent dans $GLOBALS
    if (empty($meta_general)) {
        $meta_general = lire_config('config_destinataires_creation_cotisation_tresorier', '') ;
    }
    if (empty($meta_entreprise)) {
        $meta_entreprise = lire_config('config_destinataires_creation_cotisation_tresorier_entreprise', '');
    }

    // Utiliser le parseur central
    $parseur = function($s) {
        if (empty($s)) {
            return false;
        }
        return parser_emails_depuis_config($s);
    };

    // Cas entreprise strict
    if ($type === 'entreprise') {
        $parsed = $parseur($meta_entreprise);
        if ($parsed !== false) return $parsed;
        $parsed = $parseur($meta_general);
        return $parsed !== false ? $parsed : false;
    }

    // Cas adherent strict
    if ($type === 'adherent') {
        $parsed = $parseur($meta_general);
        return $parsed !== false ? $parsed : false;
    }

    // Cas par défaut : fusionner les deux jeux d'adresses
    $list = array();
    $parsed1 = $parseur($meta_general);
    if (is_array($parsed1)) $list = array_merge($list, $parsed1);
    $parsed2 = $parseur($meta_entreprise);
    if (is_array($parsed2)) $list = array_merge($list, $parsed2);
    $list = array_values(array_unique(array_filter($list, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))));
    return !empty($list) ? $list : false;
}

/**
 * Calcule les dates importantes pour la période scolaire
 *
 * Cette fonction calcule deux dates importantes :
 * - date_debut_reinscription : date à partir de laquelle la réinscription est possible
 * - date_fin_validite : date à laquelle l'adhésion n'est plus valide
 *
 * @param int $annee_reference Année de référence (par défaut année courante)
 * @return array Tableau associatif avec les deux dates au format Y-m-d
 */
function association_normaliser_date_reference($date_reference = null) {
    if ($date_reference instanceof DateTimeInterface) {
        return $date_reference->format('Y-m-d');
    }
    if (is_numeric($date_reference)) {
        return date('Y-m-d', intval($date_reference));
    }
    if (is_string($date_reference) && trim($date_reference) !== '') {
        $timestamp = @strtotime($date_reference);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
    }
    return date('Y-m-d');
}

function association_dates_campagne_scolaire($date_reference = null, $type_adherent = 'adherent') {
    $metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();
    $date_reference = association_normaliser_date_reference($date_reference);
    $ts_reference = strtotime($date_reference);

    $suffixe = ($type_adherent === 'entreprise') ? '_entreprise' : '';
    $meta_suivante = $metas['date_scolaire_suivante' . $suffixe] ?? ($metas['date_scolaire_suivante'] ?? '01/06');
    $meta_nouvelle = $metas['date_scolaire_nouvelle' . $suffixe] ?? ($metas['date_scolaire_nouvelle'] ?? '31/08');

    if (!preg_match('/^(\d{1,2})\/(\d{1,2})$/', (string)$meta_suivante, $match_suivante)
        || !preg_match('/^(\d{1,2})\/(\d{1,2})$/', (string)$meta_nouvelle, $match_nouvelle)) {
        return array(
            'date_reference' => $date_reference,
            'date_debut_reinscription' => null,
            'date_fin_validite' => null,
            'date_fin_validite_cible' => null,
            'statut' => 'hors_periode_reinscription'
        );
    }

    $jour_suivante = intval($match_suivante[1]);
    $mois_suivante = intval($match_suivante[2]);
    $jour_nouvelle = intval($match_nouvelle[1]);
    $mois_nouvelle = intval($match_nouvelle[2]);

    $construire_timestamp = static function ($annee, $mois, $jour) {
        return mktime(0, 0, 0, intval($mois), intval($jour), intval($annee));
    };

    $prochaine_occurrence = static function ($jour, $mois, $reference_ts) use ($construire_timestamp) {
        $annee = intval(date('Y', $reference_ts));
        $candidate = $construire_timestamp($annee, $mois, $jour);
        if ($candidate >= $reference_ts) {
            return date('Y-m-d', $candidate);
        }
        return date('Y-m-d', $construire_timestamp($annee + 1, $mois, $jour));
    };

    $annee_reference = intval(date('Y', $ts_reference));
    $debut_annee_reference = $construire_timestamp($annee_reference, $mois_suivante, $jour_suivante);
    $debut_annee_precedente = $construire_timestamp($annee_reference - 1, $mois_suivante, $jour_suivante);

    $debut_campagne_courante = ($debut_annee_reference <= $ts_reference) ? $debut_annee_reference : $debut_annee_precedente;
    $fin_campagne_courante = strtotime($prochaine_occurrence($jour_nouvelle, $mois_nouvelle, $debut_campagne_courante));

    $periode_ouverte = ($debut_campagne_courante <= $ts_reference && $ts_reference <= $fin_campagne_courante);

    if ($periode_ouverte) {
        $date_debut_reinscription = date('Y-m-d', $debut_campagne_courante);
        $date_fin_validite_cible = date('Y-m-d', $fin_campagne_courante);
    } else {
        $date_debut_reinscription = $prochaine_occurrence($jour_suivante, $mois_suivante, $ts_reference);
        $date_fin_validite_cible = $prochaine_occurrence($jour_nouvelle, $mois_nouvelle, strtotime($date_debut_reinscription));
    }

    return array(
        'date_reference' => $date_reference,
        'date_debut_reinscription' => $date_debut_reinscription,
        'date_fin_validite' => $date_fin_validite_cible,
        'date_fin_validite_cible' => $date_fin_validite_cible,
        'statut' => $periode_ouverte ? 'periode_reinscription' : 'hors_periode_reinscription'
    );
}

function association_mode_validite($type_adherent = 'adherent') {
    $metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();

    if ($type_adherent === 'entreprise') {
        $validite_entreprise = $metas['validite_entreprise'] ?? '';
        if ($validite_entreprise === 'scolaire') {
            return 'scolaire';
        }
        if (in_array($validite_entreprise, array('annee', 'annuelle'), true)) {
            return 'annuelle';
        }

        $mode_entreprise = $metas['entreprise_validation_mode'] ?? '';
        if (in_array($mode_entreprise, array('scolaire', 'date_fixee', 'duree_mois', 'annee', 'annuelle'), true)) {
            return $mode_entreprise;
        }
        if (!empty($metas['entreprise_validation_date'])) {
            return 'date_fixee';
        }
        if (!empty($metas['entreprise_validation_duree_mois'])) {
            return 'duree_mois';
        }

        return 'annuelle';
    }

    $validite = $metas['validite'] ?? 'annuelle';
    return ($validite === 'scolaire') ? 'scolaire' : 'annuelle';
}

function association_date_validite_a_accorder($date_reference = null, $type_adherent = 'adherent', $date_validite_actuelle = null) {
    $metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();
    $date_reference = association_normaliser_date_reference($date_reference);
    $mode_validite = association_mode_validite($type_adherent);

    $date_validite_normalisee = null;
    if (!empty($date_validite_actuelle)) {
        $date_validite_normalisee = association_normaliser_date_reference($date_validite_actuelle);
    }

    if ($mode_validite === 'scolaire') {
        $campagne = association_dates_campagne_scolaire($date_reference, $type_adherent);
        $date_cible = $campagne['date_fin_validite_cible'] ?? null;

        if (!empty($date_cible) && ($campagne['statut'] ?? '') === 'periode_reinscription') {
            $reference_suivante = date('Y-m-d', strtotime($date_cible . ' +1 day'));
            $campagne_suivante = association_dates_campagne_scolaire($reference_suivante, $type_adherent);
            if (!empty($campagne_suivante['date_fin_validite_cible'])) {
                $date_cible = $campagne_suivante['date_fin_validite_cible'];
            }
        }

        if ($date_cible && $date_validite_normalisee && strtotime($date_validite_normalisee) > strtotime($date_cible)) {
            $date_cible = $date_validite_normalisee;
        }

        return $date_cible;
    }

    $date_reference_objet = new DateTime($date_reference);

    if ($type_adherent === 'entreprise' && $mode_validite === 'date_fixee') {
        $date_fixee = $metas['entreprise_validation_date'] ?? '';
        if (preg_match('/^(\d{1,2})\/(\d{1,2})$/', (string)$date_fixee, $match)) {
            $jour = intval($match[1]);
            $mois = intval($match[2]);
            $annee = intval($date_reference_objet->format('Y'));
            $candidate = DateTime::createFromFormat('Y-n-j', $annee . '-' . $mois . '-' . $jour);
            if ($candidate && $candidate < $date_reference_objet) {
                $candidate->modify('+1 year');
            }
            if ($candidate) {
                return $candidate->format('Y-m-d');
            }
        }
    }

    if ($type_adherent === 'entreprise' && $mode_validite === 'duree_mois') {
        $mois_duree = intval($metas['entreprise_validation_duree_mois'] ?? 12);
        if ($mois_duree <= 0) {
            $mois_duree = 12;
        }

        $base = new DateTime($date_reference);
        if ($date_validite_normalisee && strtotime($date_validite_normalisee) > strtotime($date_reference)) {
            $base = new DateTime($date_validite_normalisee);
        }
        $base->add(new DateInterval('P' . $mois_duree . 'M'));
        return $base->format('Y-m-d');
    }

    $base = new DateTime($date_reference);
    if ($date_validite_normalisee && strtotime($date_validite_normalisee) > strtotime($date_reference)) {
        $base = new DateTime($date_validite_normalisee);
    }
    $base->add(new DateInterval('P1Y'));
    return $base->format('Y-m-d');
}

function association_auteurs_colonne_type_adherent() {
    static $colonne = null;
    static $verifie = false;

    if ($verifie) {
        return $colonne;
    }

    $verifie = true;
    $desc = sql_showtable('spip_auteurs', true);
    if (is_array($desc) && !empty($desc['field'])) {
        if (!empty($desc['field']['radio_type_adherent'])) {
            $colonne = 'radio_type_adherent';
        } elseif (!empty($desc['field']['type_adherent'])) {
            $colonne = 'type_adherent';
        }
    }

    return $colonne;
}

function association_contexte_adhesion($id_auteur, $date_reference = null) {
    $id_auteur = intval($id_auteur);
    $date_reference = association_normaliser_date_reference($date_reference);
    $contexte = array(
        'id_auteur' => $id_auteur,
        'date_reference' => $date_reference,
        'type_adherent' => 'adherent',
        'periode_cotisation' => $GLOBALS['association_metas']['validite'] ?? '',
        'statut_interne' => '',
        'date_validite_auteur' => '',
        'date_debut_reinscription' => null,
        'date_fin_validite_cible' => null,
        'periode_reinscription_ouverte' => false,
        'etat_renouvellement' => 'aucun',
        'contexte_inscription' => 'contexte_inscription_inconnu',
        'adhesion_transaction_en_cours' => false
    );

    if (!$id_auteur) {
        return $contexte;
    }

    $champs_auteur = array('id_auteur', 'validite', 'statut_interne');
    $colonne_type_adherent = association_auteurs_colonne_type_adherent();
    if ($colonne_type_adherent) {
        $champs_auteur[] = $colonne_type_adherent;
    }

    $auteur = sql_fetsel(implode(',', $champs_auteur), 'spip_auteurs', 'id_auteur=' . $id_auteur);
    if (!$auteur) {
        return $contexte;
    }

    $type_adherent = ($colonne_type_adherent && !empty($auteur[$colonne_type_adherent]))
        ? $auteur[$colonne_type_adherent]
        : 'adherent';
    $periode_cotisation = ($type_adherent === 'entreprise')
        ? ($GLOBALS['association_metas']['validite_entreprise'] ?? ($GLOBALS['association_metas']['validite'] ?? ''))
        : ($GLOBALS['association_metas']['validite'] ?? '');

    $date_validite_auteur = '';
    $ts_validite_auteur = false;
    if (!empty($auteur['validite'])) {
        $ts_validite_auteur = @strtotime($auteur['validite']);
        if ($ts_validite_auteur !== false) {
            $date_validite_auteur = date('Y-m-d', $ts_validite_auteur);
            $ts_validite_auteur = strtotime($date_validite_auteur);
        }
    }

    $transactions = sql_allfetsel('id_transaction', 'spip_transactions', 'id_auteur=' . $id_auteur . " AND statut IN ('commande','attente')");
    foreach ($transactions as $transaction) {
        $id_transaction = intval($transaction['id_transaction'] ?? 0);
        if ($id_transaction && sql_countsel('spip_asso_cotisations', 'id_transaction=' . $id_transaction . " AND statut IN ('demande','attente')")) {
            $contexte['adhesion_transaction_en_cours'] = true;
            break;
        }
    }

    $contexte['type_adherent'] = $type_adherent;
    $contexte['periode_cotisation'] = $periode_cotisation;
    $contexte['statut_interne'] = $auteur['statut_interne'] ?? '';
    $contexte['date_validite_auteur'] = $date_validite_auteur;

    if ($periode_cotisation === 'scolaire') {
        $campagne = association_dates_campagne_scolaire($date_reference, $type_adherent);
        $contexte['date_debut_reinscription'] = $campagne['date_debut_reinscription'] ?? null;
        $contexte['date_fin_validite_cible'] = association_date_validite_a_accorder($date_reference, $type_adherent, $auteur['validite'] ?? null);
        $contexte['periode_reinscription_ouverte'] = (($campagne['statut'] ?? '') === 'periode_reinscription');

        $ts_fin_cible = !empty($contexte['date_fin_validite_cible']) ? strtotime($contexte['date_fin_validite_cible']) : false;
        if ($contexte['statut_interne'] === 'ok') {
            if ($contexte['periode_reinscription_ouverte'] && $ts_fin_cible !== false) {
                if ($ts_validite_auteur !== false && $ts_validite_auteur >= $ts_fin_cible) {
                    $contexte['etat_renouvellement'] = 'deja_enregistre';
                    $contexte['contexte_inscription'] = 'deja_reinscrit';
                } else {
                    $contexte['etat_renouvellement'] = 'ouvert';
                    $contexte['contexte_inscription'] = 'reinscription';
                }
            } else {
                $contexte['etat_renouvellement'] = 'prochaine_fenetre';
                $contexte['contexte_inscription'] = 'inscrit';
            }
        }
    } elseif ($periode_cotisation === 'annee' && $contexte['statut_interne'] === 'ok') {
        if ($ts_validite_auteur !== false) {
            $limite = strtotime($date_reference . ' +2 months');
            $contexte['etat_renouvellement'] = ($ts_validite_auteur <= $limite) ? 'conseille' : 'aucun';
        }
        $contexte['contexte_inscription'] = 'inscrit';
    }

    if ($contexte['statut_interne'] === 'prospect') {
        $contexte['contexte_inscription'] = 'inscription';
    } elseif (in_array($contexte['statut_interne'], array('echu', 'relance'), true)) {
        $contexte['contexte_inscription'] = 'reinscription';
    } elseif ($contexte['statut_interne'] === 'desactive') {
        $contexte['contexte_inscription'] = 'inscrit';
    }

    return $contexte;
}

function calculer_dates_scolaires($annee_reference = null) {
    return association_dates_campagne_scolaire(null, 'adherent');
}
// Fonction pour décider si on propose l'inscription ou la réinscription à un auteur
function identification_contexte_inscription($id_auteur)
{
    $contexte = association_contexte_adhesion($id_auteur);
    association_log(
        'cotisations',
        'identification_contexte_inscription: id_auteur=' . intval($id_auteur)
        . ' statut_interne=' . ($contexte['statut_interne'] ?? '')
        . ' validite=' . ($contexte['date_validite_auteur'] ?? '')
        . ' campagne=' . json_encode(array(
            'date_reference' => $contexte['date_reference'] ?? '',
            'date_debut_reinscription' => $contexte['date_debut_reinscription'] ?? '',
            'date_fin_validite_cible' => $contexte['date_fin_validite_cible'] ?? '',
            'periode_reinscription_ouverte' => $contexte['periode_reinscription_ouverte'] ?? false,
            'etat_renouvellement' => $contexte['etat_renouvellement'] ?? ''
        ))
        . ' => ' . ($contexte['contexte_inscription'] ?? 'contexte_inscription_inconnu'),
        'critique'
    );

    return $contexte['contexte_inscription'] ?? 'contexte_inscription_inconnu';
}

