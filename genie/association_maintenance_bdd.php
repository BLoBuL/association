<?php
/*
* GESTION DES TÂCHES CRON
* VERSION = 0.1b
*/

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('base/abstract_sql');




/**
 * Exécute les tâches de maintenance planifiées pour l'association.
 *
 * Cette fonction est appelée via une tâche CRON et effectue les actions suivantes :
 * - Analyse les comptes des auteurs non-actifs :
 *     - Supprime les comptes auteur inactifs qui n'ont jamais eut de cotisation encaissée
 *     - Supprime les cotisations (spip_asso_comptes) de ces auteurs
 *     - Supprime les transactions (spip_transactions) de ces auteurs
 *     - Supprime les mailsubscriptions (spip_mailsubscriber & mailsubscriptions) de ces auteurs *
 *     - Anonymise les comptes auteur inactifs qui ont eut des cotisations encaissées
 *
 * - Analyse les inscriptions aux activités :
 *   - Supprime les inscriptions anciennes jamais validées
 *   - Supprime les transactions liées à ces inscriptions
 *   - Anonymise les inscriptions aux activités des auteurs inactifs *
 *
 * - Nettoyage de items orphelins ou obsoletes:
 *    - Supprime les cotisations orphelines
 *    - Supprime les transactions orphelines
 *    - Supprime les cotisations non-encaissées plus anciennees que 6 mois
 *    - Supprime les participations aux evenements orphelines
 * - Nettoyage des urls de redirection orphelines (spip_urls)
 *    - On supprime les urls de redirection type = mailsubscriber
 *    - On supprime les urls de redirection obsolete qu'elle que soit leur type
 *
 * @param string $tache Nom de la tâche exécutée (non utilisé ici).
 * @return bool Retourne `true` une fois la tâche terminée.
 *
 * Pour sécuriser, le `dry_run` est actif par défaut. Désactiver quand
 * les procédures ont été validées.
 */

/**
 * Point d'entrée CRON.
 * Options supportées:
 *  - dry_run: bool (true par défaut) \- n'effectue aucune écriture si activé
 *  - jours_inactivite: int (365 par défaut) \- anciens auteurs inactifs
 *  - jours_inscriptions_en_attente: int (90 par défaut)
 *  - mois_non_encaisse: int (6 par défaut)
 *  - lot: int (1000 par défaut) \- taille des traitements par lot
 * @param string $tache Nom de la tâche (non utilisé)
 * @return array
 */
function genie_association_maintenance_bdd($tache) {
    association_log('cron', 'Associaspip: Tâche CRON maintenance - début', 'info');

    // Lire les metas de configuration si disponibles
    $metas = isset($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();

    // Helper pour interpréter les valeurs de case/boolean stockées ("on", "oui", "1")
    $is_true = function($v) {
        if (is_bool($v)) return $v;
        $v = (string)$v;
        return in_array(strtolower($v), array('1', 'on', 'oui', 'true'), true);
    };

    // Options construites automatiquement depuis les metas, avec valeurs par défaut
    $options = array(
        // activation générale
        'enabled' => isset($metas['meta_cfg_maintenance_bdd_enable']) ? $is_true($metas['meta_cfg_maintenance_bdd_enable']) : true,
        // dry_run
        'dry_run' => isset($metas['meta_cfg_maintenance_dry_run']) ? $is_true($metas['meta_cfg_maintenance_dry_run']) : true,
        // seuils
        'jours_inactivite' => isset($metas['meta_cfg_maintenance_jours_inactivite']) ? intval($metas['meta_cfg_maintenance_jours_inactivite']) : 365,
        'jours_inscriptions_en_attente' => isset($metas['meta_cfg_maintenance_jours_inscriptions_attente']) ? intval($metas['meta_cfg_maintenance_jours_inscriptions_attente']) : 90,
        'mois_non_encaisse' => isset($metas['meta_cfg_maintenance_mois_non_encaisse']) ? intval($metas['meta_cfg_maintenance_mois_non_encaisse']) : 6,
        'lot' => isset($metas['meta_cfg_maintenance_lot']) ? intval($metas['meta_cfg_maintenance_lot']) : 1000,
        // Actions : par défaut on réplique le comportement historique (exécuter toutes les actions)
        'actions' => array(
            'supprimer_auteurs_sans_paiements' => isset($metas['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) ? $is_true($metas['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) : true,
            'anonymiser_auteurs_avec_paiements' => isset($metas['meta_cfg_maintenance_anonymiser_auteurs_avec_paiements']) ? $is_true($metas['meta_cfg_maintenance_anonymiser_auteurs_avec_paiements']) : true,
            'supprimer_inscriptions_non_validees' => isset($metas['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) ? $is_true($metas['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) : true,
            'anonymiser_inscriptions_inactifs' => isset($metas['meta_cfg_maintenance_anonymiser_inscriptions_inactifs']) ? $is_true($metas['meta_cfg_maintenance_anonymiser_inscriptions_inactifs']) : true,
            'supprimer_cotisations_orphelines' => isset($metas['meta_cfg_maintenance_supprimer_cotisations_orphelines']) ? $is_true($metas['meta_cfg_maintenance_supprimer_cotisations_orphelines']) : true,
            'supprimer_cotisations_non_encaissees' => isset($metas['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) ? $is_true($metas['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) : true,
            'supprimer_transactions_orphelines' => isset($metas['meta_cfg_maintenance_supprimer_transactions_orphelines']) ? $is_true($metas['meta_cfg_maintenance_supprimer_transactions_orphelines']) : true,
            'supprimer_participations_orphelines' => isset($metas['meta_cfg_maintenance_supprimer_participations_orphelines']) ? $is_true($metas['meta_cfg_maintenance_supprimer_participations_orphelines']) : true,
            'supprimer_participations_obsoletes' => isset($metas['meta_cfg_maintenance_supprimer_participations_obsoletes']) ? $is_true($metas['meta_cfg_maintenance_supprimer_participations_obsoletes']) : true,
            'supprimer_urls_mailsubscriber' => isset($metas['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) ? $is_true($metas['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) : true,
            'supprimer_urls_obsoletes' => isset($metas['meta_cfg_maintenance_supprimer_urls_obsoletes']) ? $is_true($metas['meta_cfg_maintenance_supprimer_urls_obsoletes']) : true,
            'supprimer_mailsubscribers_orphelines' => isset($metas['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) ? $is_true($metas['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) : true,
        ),
    );

    association_log('cron', 'Associaspip: Options maintenance construites depuis metas: ' . json_encode($options), 'info');

    // Exécution et journalisation du résumé
    $resume = association_maintenance_bdd_run(time(), $options);
    // Générer le rapport dans le cache
    $date = date('Y-m-d H:i');
    $dir_rapports = _DIR_TMP . 'rapports/';
    if (!is_dir($dir_rapports)) {
        mkdir($dir_rapports, 0755, true);
    }
    $chemin = $dir_rapports . 'maintenance_asso_' . $date .'.json';
    ecrire_fichier($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $resume;
}

/**
 * Exécuter la maintenance BDD.
 *
 * @param int|null $maintenant Timestamp courant (injecté pour tests)
 * @param array $options Options listées ci\-dessus
 * @return array Résumé détaillé des actions
 */
function association_maintenance_bdd_run($maintenant = null, array $opt = []) {
    $maintenant = $maintenant ?: time();
    $resume = [];

    // Respecter l'activation globale
    if (isset($opt['enabled']) && !$opt['enabled']) {
        association_log('cron', 'Associaspip: Maintenance BDD SKIPPED (désactivée via configuration).', 'info');
        return array('skipped' => true);
    }

    // 1) AUTEURS INACTIFS
    $limite_inactifs = date('Y-m-d H:i:s', $maintenant - ($opt['jours_inactivite'] * 86400));
    $inactifs = asso_recuperer_auteurs_inactifs($limite_inactifs, $opt['lot']);
    $resume['auteurs_inactifs'] = [
        'nombre' => count($inactifs),
        'ids' => $inactifs,
    ];

    if ($inactifs) {
        // 1.a) Séparer ceux sans aucune cotisation encaissée de ceux avec historique encaissé
        [$sans_paiements, $avec_paiements] = asso_separer_auteurs_par_encaissements($inactifs);

        // 1.b) Suppression "dure" des comptes sans encaissements + données associées
        $resume['auteurs_sans_paiements'] = [
            'nombre' => count($sans_paiements),
            'ids' => $sans_paiements,
        ];
        if (!empty($opt['actions']['supprimer_auteurs_sans_paiements']) && $sans_paiements) {
            $resume['supprimer_auteurs'] = asso_supprimer_auteurs($sans_paiements, $opt['dry_run']);
        } else {
            $resume['supprimer_auteurs'] = array('skipped' => true);
        }

        // 1.c) Anonymisation des comptes avec encaissements
        $resume['auteurs_avec_paiements'] = [
            'nombre' => count($avec_paiements),
            'ids' => $avec_paiements,
        ];
        if (!empty($opt['actions']['anonymiser_auteurs_avec_paiements']) && $avec_paiements) {
            $resume['anonymiser_auteurs'] = asso_anonymiser_auteurs($avec_paiements, $opt['dry_run']);
        } else {
            $resume['anonymiser_auteurs'] = array('skipped' => true);
        }
    }

    $resume = pipeline('association_maintenance_bdd_executer', array(
        'args' => array(
            'maintenant' => $maintenant,
            'options' => $opt,
            'inactifs' => $inactifs,
        ),
        'data' => $resume,
    ));
    $resume = is_array($resume) ? $resume : array();

    association_log('cron', 'Associaspip: Maintenance - résumé: ' . json_encode($resume), 'info');
    return $resume;
}

/**
 * Détecte si un sous-résultat indique un échec technique.
 *
 * @param mixed $resultat
 * @return bool
 */
function asso_resultat_en_echec($resultat) {
    if ($resultat === false) {
        return true;
    }
    if (!is_array($resultat)) {
        return false;
    }
    foreach ($resultat as $cle => $valeur) {
        if ($cle === 'erreur' && $valeur) {
            return true;
        }
        if (is_array($valeur) && asso_resultat_en_echec($valeur)) {
            return true;
        }
        if ($valeur === false) {
            return true;
        }
    }
    return false;
}

/**
 * Détecter les auteurs inactifs.
 *
 * Critères: Auteur avec (au moins un):
 * `statut = 5poubelle`
 * 'statut = 6forum et validite = '' et inscription < $limite_inactifs' et statut_interne = 'prospect'
 * 'statut = 8aconfirmer et inscription < $limite_inactifs'
 *
 *
 * @param string $limite_inactifs Format 'Y\-m\-d H:i:s'
 * @param int $lot Taille du lot
 * @return int[] Liste d'id_auteur
 */
function asso_recuperer_auteurs_inactifs($limite_inactifs, $lot = 1000) {
    $ids = [];

    $where = "("
        . "(statut='5poubelle')" // tous les auteurs à la poubelle
        . "OR (statut_interne='sorti')" // tous les auteurs désactivé
        . " OR (statut='6forum' AND validite='' AND inscription < " . sql_quote($limite_inactifs) . " AND statut_interne='prospect')"
        . " OR (statut='8aconfirmer' AND inscription < " . sql_quote($limite_inactifs) . ")"
        . ")";
    $res = sql_select('id_auteur', 'spip_auteurs', $where, '', '', intval($lot));
    while ($row = sql_fetch($res)) {
        $ids[] = intval($row['id_auteur']);
    }
    return $ids;
}

/**
 * Séparer les auteurs inactifs selon la présence d'encaissements.
 *
 * Critères (au moins un):
 * \- Une ligne `spip_asso_comptes` avec `recette > 0`
 * \- Une `spip_transactions` avec `statut = ok` (si table existante)
 *
 * @param int[] $ids_auteurs
 * @return array [sans_paiements[], avec_paiements[]]
 */
function asso_separer_auteurs_par_encaissements(array $ids_auteurs) {
    if (!$ids_auteurs) return [[], []];
    $avec = pipeline('association_maintenance_auteurs_encaisses', array(
        'args' => array('ids_auteurs' => array_values(array_map('intval', $ids_auteurs))),
        'data' => array(),
    ));
    $avec = is_array($avec) ? $avec : array();
    $avec = array_values(array_unique($avec));
    $sans = array_values(array_diff($ids_auteurs, $avec));
    return [$sans, $avec];
}

/**
 * Supprimer définitivement des auteurs.
 * On supprime dans la foulée les informations liées :
 * - cotisations dont statut_cotisation est <> 'ok'
 * - transactions dont statut est <> 'ok'
 * - mailsubscriptions/mailsubscribers (en verifiant sur son email ou le md5 de son email) *
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['supprimes' => 0];

    $in = sql_in('id_auteur', $ids_auteurs);
    $resultat = pipeline('association_maintenance_supprimer_donnees_auteurs', array(
        'args' => array(
            'ids_auteurs' => array_values(array_map('intval', $ids_auteurs)),
            'dry_run' => (bool) $dry_run,
        ),
        'data' => array(),
    ));
    $resultat = is_array($resultat) ? $resultat : array();

    if (asso_resultat_en_echec($resultat)) {
        $resultat['supprimes'] = 0;
        $resultat['erreur'] = 'suppression_associee_echouee';
        $resultat['suppression_auteurs_skippee'] = true;
        return $resultat;
    }

    // Supprimer les auteurs
    $resultat['supprimes'] = $dry_run ? sql_countsel('spip_auteurs', $in) : sql_delete('spip_auteurs', $in);
    if ($resultat['supprimes'] === false) {
        $resultat['supprimes'] = 0;
        $resultat['erreur'] = 'suppression_auteurs_echouee';
    }

    return $resultat;
}

/**
 * Anonymiser les auteurs et leurs données métier via l'API RGPD du plugin.
 */
function asso_anonymiser_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['anonymises' => 0];

    include_spip('inc/rgpd_anonymisation');

    if ($dry_run) {
        return [
            'anonymises' => count($ids_auteurs),
            'dry_run' => true,
            'ids' => array_values(array_map('intval', $ids_auteurs)),
        ];
    }

    $ok = 0;
    $erreurs = [];
    $details = [];
    foreach ($ids_auteurs as $id) {
        $id = intval($id);
        if ($id <= 0) {
            continue;
        }

        $auteur = sql_fetsel('id_auteur,nom,email,login', 'spip_auteurs', 'id_auteur=' . $id);
        if (!$auteur) {
            $erreurs[$id] = 'auteur introuvable';
            continue;
        }

        $maj = [
            'nom' => 'Anonyme ' . intval($id),
            'email' => 'anon+' . intval($id) . '@example.invalid',
            'login' => 'anon_' . intval($id),
        ];

        // sql_updateq retourne bool|int selon connecteur; on incrémente si pas d'erreur.
        $n = sql_updateq('spip_auteurs', $maj, 'id_auteur=' . $id);
        if ($n === false) {
            $erreurs[$id] = 'echec anonymisation auteur';
            continue;
        }

        $resultat = association_rgpd_anonymiser_auteur($id, $auteur);
        if (empty($resultat['ok'])) {
            $erreurs[$id] = $resultat['erreur'] ?? 'echec anonymisation donnees association';
            continue;
        }

        $details[$id] = $resultat['resume'] ?? [];
        $ok++;
    }

    return [
        'anonymises' => intval($ok),
        'dry_run' => false,
        'details' => $details,
        'erreurs' => $erreurs,
    ];
}

