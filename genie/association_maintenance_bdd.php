<?php
/*
* GESTION DES TÂCHES CRON
* VERSION = 0.1b
*/

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('base/abstract_sql');

// Mapping spécifique des types 'spip_urls' vers la table/colonne réelles
if (!function_exists('asso_table_col_for_type')) {
    function asso_table_col_for_type($type) {
        // Cas particuliers connus
        $special = array(
            'site' => array('spip_syndic', 'id_syndic'), // spip_sites n'existe pas sur certaines installations
            // ajouter d'autres mappings si nécessaire
        );
        if (isset($special[$type])) return $special[$type];
        // Valeur par défaut : spip_{type}s et id_{type}
        return array('spip_' . $type . 's', 'id_' . $type);
    }
}



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

    // 2) INSCRIPTIONS AUX ACTIVITÉS
    $limite_inscriptions = date('Y-m-d H:i:s', $maintenant - ($opt['jours_inscriptions_en_attente'] * 86400));
    $anciennes_non_validees = asso_trouver_inscriptions_non_validees_anciennes($limite_inscriptions, $opt['lot']);
    $resume['inscriptions_non_validees_anciennes'] = [
        'nombre' => count($anciennes_non_validees),
        'ids_activite' => array_column($anciennes_non_validees, 'id_activite'),
    ];
    if (!empty($opt['actions']['supprimer_inscriptions_non_validees']) && $anciennes_non_validees) {
        $resume['supprimer_transactions_inscriptions'] = asso_supprimer_transactions_inscriptions($anciennes_non_validees, $opt['dry_run']);
        if (asso_resultat_en_echec($resume['supprimer_transactions_inscriptions'])) {
            $resume['supprimer_inscriptions_non_validees'] = array(
                'skipped' => true,
                'raison' => 'suppression_transactions_echec',
            );
        } else {
            $resume['supprimer_inscriptions_non_validees'] = asso_supprimer_inscriptions_par_ids(array_column($anciennes_non_validees, 'id_activite'), $opt['dry_run']);
        }
    } else {
        $resume['supprimer_transactions_inscriptions'] = array('skipped' => true);
        $resume['supprimer_inscriptions_non_validees'] = array('skipped' => true);
    }

    if (!empty($inactifs)) {
        if (!empty($opt['actions']['anonymiser_inscriptions_inactifs'])) {
            $resume['anonymiser_inscriptions_inactifs'] = asso_anonymiser_inscriptions_auteurs($inactifs, $opt['dry_run']);
        } else {
            $resume['anonymiser_inscriptions_inactifs'] = array('skipped' => true);
        }
    }

    // 3) NETTOYAGE ORPHELINS / OBSOLÈTES
    if (!empty($opt['actions']['supprimer_cotisations_orphelines'])) {
        $resume['supprimer_cotisations_orphelines'] = asso_supprimer_cotisations_orphelines($opt['dry_run'], $opt['lot']);
    } else {
        $resume['supprimer_cotisations_orphelines'] = array('skipped' => true);
    }

    if (!empty($opt['actions']['supprimer_cotisations_non_encaissees'])) {
        $resume['supprimer_cotisations_non_encaissees_anciennes'] = asso_supprimer_cotisations_non_encaissees_anciennes($maintenant, $opt['mois_non_encaisse'], $opt['dry_run'], $opt['lot']);
    } else {
        $resume['supprimer_cotisations_non_encaissees_anciennes'] = array('skipped' => true);
    }

    if (!empty($opt['actions']['supprimer_transactions_orphelines'])) {
        $resume['supprimer_transactions_orphelines'] = asso_supprimer_transactions_orphelines($opt['dry_run'], $opt['lot']);
    } else {
        $resume['supprimer_transactions_orphelines'] = array('skipped' => true);
    }

    if (!empty($opt['actions']['supprimer_participations_orphelines'])) {
        $resume['supprimer_participations_evenements_orphelines'] = asso_supprimer_participations_evenements_orphelines($opt['dry_run'], $opt['lot']);
    } else {
        $resume['supprimer_participations_evenements_orphelines'] = array('skipped' => true);
    }

    if (!empty($opt['actions']['supprimer_participations_obsoletes'])) {
        $resume['supprimer_participations_evenements_obsoletes'] = asso_supprimer_participations_evenements_obsoletes($opt['dry_run'], $opt['lot'], $opt['jours_inscriptions_en_attente']);
    } else {
        $resume['supprimer_participations_evenements_obsoletes'] = array('skipped' => true);
    }

    // 4) NETTOYAGE URLS
    if (!empty($opt['actions']['supprimer_urls_mailsubscriber'])) {
        $resume['supprimer_urls_mailsubscriber'] = asso_supprimer_urls_par_type('mailsubscriber', $opt['dry_run'], 10000);
    } else {
        $resume['supprimer_urls_mailsubscriber'] = array('skipped' => true);
    }
    if (!empty($opt['actions']['supprimer_urls_obsoletes'])) {
        $resume['supprimer_urls_obsoletes'] = asso_supprimer_urls_obsoletes($opt['dry_run'], 10000);
    } else {
        $resume['supprimer_urls_obsoletes'] = array('skipped' => true);
    }

    // 5) NETTOYAGE MAILSUBSCRIBERS
    if (!empty($opt['actions']['supprimer_mailsubscribers_orphelines'])) {
        $resume['supprimer_mailsubscribers_orphelines'] = asso_supprimer_mailsubscribers_orphelines($opt['dry_run'], $opt['lot']);
    } else {
        $resume['supprimer_mailsubscribers_orphelines'] = array('skipped' => true);
    }

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
    $in = sql_in('id_auteur', $ids_auteurs);

    $avec = [];

    // Encaissement via comptes
    $res = sql_select('DISTINCT id_auteur', 'spip_asso_comptes', "$in AND recette > 0");
    while ($row = sql_fetch($res)) {
        $avec[] = intval($row['id_auteur']);
    }

    // Encaissement via transactions rattachées au périmètre association uniquement.
    $res2 = sql_select(
        'DISTINCT t.id_auteur',
        'spip_transactions AS t',
        "$in AND t.statut=" . sql_quote('ok')
        . ' AND ('
        . 'EXISTS (SELECT 1 FROM spip_asso_comptes AS c WHERE c.id_transaction = t.id_transaction)'
        . ' OR EXISTS (SELECT 1 FROM spip_asso_activites AS a WHERE a.id_transaction = t.id_transaction)'
        . ')'
    );
    while ($row = sql_fetch($res2)) {
        $avec[] = intval($row['id_auteur']);
    }

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
    $resultat = [
        'supprimer_mailsubscribers' => asso_supprimer_mailsubscribers_pour_auteurs($ids_auteurs, $dry_run),
        'supprimer_transactions_auteurs' => asso_supprimer_transactions_auteurs($ids_auteurs, $dry_run),
        'supprimer_comptes_auteurs' => asso_supprimer_comptes_auteurs($ids_auteurs, $dry_run),
    ];

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
 * Supprimer les écritures de comptes/cotisations des auteurs.
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_comptes_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['supprimes' => 0];
    $in = sql_in('id_auteur', $ids_auteurs);

    $nb = $dry_run ? sql_countsel('spip_asso_comptes', $in) : sql_delete('spip_asso_comptes', $in);
    return ['supprimes' => intval($nb)];
}

/**
 * Supprimer les transactions liées à des auteurs (si table disponible).
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_transactions_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['supprimes' => 0, 'ignore' => true];
    $in = sql_in('id_auteur', $ids_auteurs);

    $nb = $dry_run ? sql_countsel('spip_transactions', $in) : sql_delete('spip_transactions', $in);
    return ['supprimes' => intval($nb)];
}

/**
 * Supprimer les mailsubscribers/mailsubscriptions liés aux emails d'auteurs.
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_mailsubscribers_pour_auteurs(array $ids_auteurs, $dry_run = true) {
    $out = ['mailsubscribers_supprimes' => 0, 'mailsubscriptions_supprimees' => 0, 'mailshots_destinataires_supprimees' => 0, 'ignore' => false];

    // Supposer la présence des tables mailsubscribers / mailsubscriptions
    $has_ms = true;
    $has_msubs = true;
    $has_mailshots_dest = true; // on suppose la table et la colonne présentes
    if (!$has_ms && !$has_msubs) {
        $out['ignore'] = true;
        return $out;
    }

    // Récupération des emails des auteurs
    $emails = [];
    $res = sql_select('email', 'spip_auteurs', sql_in('id_auteur', $ids_auteurs) . ' AND email<>""');
    while ($row = sql_fetch($res)) {
        $emails[] = $row['email'];
    }
    $emails = array_values(array_unique(array_filter($emails)));
    if (!$emails) return $out;
    $emails_md5 = array_map('md5', $emails);

    if ($has_ms) {
        // Trouver les ids des abonnés à supprimer
        $ids_ms = [];
        $where_ms = '(' . sql_in('email', $emails) . ' OR ' . sql_in('email', $emails_md5) . ')';
        $res2 = sql_select('id_mailsubscriber', 'spip_mailsubscribers', $where_ms);
        while ($r = sql_fetch($res2)) {
            $ids_ms[] = intval($r['id_mailsubscriber']);
        }
        if ($ids_ms) {
            $out['mailsubscriptions_supprimees'] = $dry_run
                ? sql_countsel('spip_mailsubscriptions', sql_in('id_mailsubscriber', $ids_ms))
                : sql_delete('spip_mailsubscriptions', sql_in('id_mailsubscriber', $ids_ms));
            $out['mailshots_destinataires_supprimees'] = $dry_run
                ? sql_countsel('spip_mailshots_destinataires', sql_in('id_mailsubscriber', $ids_ms))
                : sql_delete('spip_mailshots_destinataires', sql_in('id_mailsubscriber', $ids_ms));
            $out['mailsubscribers_supprimes'] = $dry_run
                ? sql_countsel('spip_mailsubscribers', sql_in('id_mailsubscriber', $ids_ms))
                : sql_delete('spip_mailsubscribers', sql_in('id_mailsubscriber', $ids_ms));
        }
    } elseif ($has_msubs) {
        // Si seulement spip_mailsubscriptions et si une colonne email existe
        // la colonne email existe
        $where_msubs = '(' . sql_in('email', $emails) . ' OR ' . sql_in('email', $emails_md5) . ')';
        $out['mailsubscriptions_supprimees'] = $dry_run
            ? sql_countsel('spip_mailsubscriptions', $where_msubs)
            : sql_delete('spip_mailsubscriptions', $where_msubs);
    }
    return $out;
}

/**
 * Anonymiser les auteurs et leurs donnees metier via l'API RGPD du plugin.
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
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

/**
 * Lister les inscriptions anciennes jamais validées.
 *
 * Critères:
 * \- `valider = 0`
 * \- `date < $limite` (DATETIME)
 *
 * @param string $limite Format 'Y\-m\-d H:i:s'
 * @param int $lot
 * @return array Liste de lignes [id_activite, id_transaction]
 */
function asso_trouver_inscriptions_non_validees_anciennes($limite, $lot = 1000) {
    $rows = [];
    $res = sql_select(
        'id_activite,id_transaction',
        'spip_asso_activites',
        "statut IN ('ok','attente') AND date < " . sql_quote($limite),
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $rows[] = [
            'id_activite' => intval($row['id_activite']),
            'id_transaction' => intval($row['id_transaction'])
        ];
    }
    return $rows;
}

/**
 * Supprimer des inscriptions par leurs identifiants.
 *
 * @param int[] $ids_activite
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_inscriptions_par_ids(array $ids_activite, $dry_run = true) {
    if (!$ids_activite) return ['supprimees' => 0];
    $in = sql_in('id_activite', $ids_activite);
    $nb = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);
    return ['supprimees' => intval($nb)];
}

/**
 * Supprimer les transactions liées à des inscriptions (si table existante).
 *
 * @param array $inscriptions Lignes avec `id_transaction`
 * @param bool $dry_run
 * @return array
 */
function asso_supprimer_transactions_inscriptions(array $inscriptions, $dry_run = true) {
    $ids_tx = array_values(array_unique(array_filter(array_map('intval', array_column($inscriptions, 'id_transaction')))));
    if (!$ids_tx) return ['supprimes' => 0];

    $in = sql_in('id_transaction', $ids_tx);
    $nb = $dry_run ? sql_countsel('spip_transactions', $in) : sql_delete('spip_transactions', $in);
    return ['supprimes' => intval($nb)];
}

/**
 * Anonymiser les inscriptions d'une liste d'auteurs (RGPD).
 *
 * @param int[] $ids_auteurs
 * @param bool $dry_run
 * @return array
 */
function asso_anonymiser_inscriptions_auteurs(array $ids_auteurs, $dry_run = true) {
    if (!$ids_auteurs) return ['anonymisees' => 0];
    $in = sql_in('id_auteur', $ids_auteurs);
    if ($dry_run) {
        return [
            'anonymisees' => intval(sql_countsel('spip_asso_activites', $in)),
            'dry_run' => true,
        ];
    }
    $maj = [
        'nom_inscrit' => '',
        'prenom_inscrit' => '',
        'email_inscrit' => '',
        'tel_inscrit' => '',
        'ip_inscrit' => '',
    ];
    $n = sql_updateq('spip_asso_activites', $maj, $in);
    if ($n === false) {
        return ['anonymisees' => 0, 'dry_run' => false, 'erreur' => 'anonymisation_inscriptions_echouee'];
    }
    return ['anonymisees' => intval($n), 'dry_run' => false];
}


/**
 * Supprimer les cotisations orphelines (sans auteur) non rattachées à une transaction encaissée.
 * Maintenant: si une transaction liée existe et n'est PAS réglée (statut <> 'ok'), on la supprime aussi.
 *
 * Ne PAS supprimer la cotisation si une transaction liée est `ok`.
 *
 * Retour:
 *  - supprimees: nombre de cotisations supprimées
 *  - ids: ids des cotisations supprimées
 *  - protegees: nombre de cotisations conservées car transaction encaissée
 *  - transactions_supprimees: nombre de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param bool $dry_run
 * @param int  $lot
 * @return array
 */
function asso_supprimer_cotisations_orphelines($dry_run = true, $lot = 1000) {

    $orphans = [];
    // Cotisations orphelines = sans auteur
    $res = sql_select(
        'c.id_compte,c.id_transaction',
        'spip_asso_comptes AS c LEFT JOIN spip_auteurs AS a ON a.id_auteur=c.id_auteur',
        'a.id_auteur IS NULL',
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $orphans[intval($row['id_compte'])] = intval($row['id_transaction']);
    }
    if (!$orphans) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => 0,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    $protegees = [];
    // Protéger celles liées à une transaction encaissée (statut ok)
    $ids_tx = array_values(array_unique(array_filter($orphans)));
    if ($ids_tx) {
        $in_tx = sql_in('t.id_transaction', $ids_tx);
        $res2 = sql_select(
            't.id_transaction',
            'spip_transactions AS t',
            $in_tx . ' AND t.statut=' . sql_quote('ok')
        );
        $tx_ok = [];
        while ($r = sql_fetch($res2)) {
            $tx_ok[] = intval($r['id_transaction']);
        }
        if ($tx_ok) {
            foreach ($orphans as $id_compte => $id_tx) {
                if ($id_tx && in_array($id_tx, $tx_ok, true)) {
                    $protegees[] = $id_compte;
                }
            }
        }
    }

    // Cotisations à supprimer
    $a_supprimer = array_values(array_diff(array_keys($orphans), $protegees));
    if (!$a_supprimer) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    if (count($a_supprimer) > $lot) {
        $a_supprimer = array_slice($a_supprimer, 0, $lot);
    }

    // Suppression des cotisations
    $in = sql_in('id_compte', $a_supprimer);
    $nb_cotisations = $dry_run
        ? sql_countsel('spip_asso_comptes', $in)
        : sql_delete('spip_asso_comptes', $in);

    if ($nb_cotisations === false) {
        return [
            'supprimees' => 0,
            'ids' => $a_supprimer,
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_cotisations_orphelines_echouee'
        ];
    }

    // Transactions liées non réglées à supprimer
    $transactions_supprimees = 0;
    $transactions_ids = [];
    // On suppose la présence de la table spip_transactions
    {
         // Collecter les id_transaction des cotisations supprimées
         $tx_ids = [];
         foreach ($a_supprimer as $id_compte) {
             $id_tx = $orphans[$id_compte];
             if ($id_tx) {
                 $tx_ids[] = $id_tx;
             }
         }
         $tx_ids = array_values(array_unique($tx_ids));
         if ($tx_ids) {
             $in_tx = sql_in('id_transaction', $tx_ids) . ' AND statut<>' . sql_quote('ok');
             // Récupérer la liste exacte (filtrée statut <> ok) pour retour
             $res_tx = sql_select('id_transaction', 'spip_transactions', $in_tx);
             while ($r = sql_fetch($res_tx)) {
                 $transactions_ids[] = intval($r['id_transaction']);
             }
             if ($transactions_ids) {
                 $where_del = sql_in('id_transaction', $transactions_ids);
                 $transactions_supprimees = $dry_run
                     ? sql_countsel('spip_transactions', $where_del)
                     : sql_delete('spip_transactions', $where_del);
                 if ($transactions_supprimees === false) {
                     return [
                         'supprimees' => intval($nb_cotisations),
                         'ids' => $a_supprimer,
                         'protegees' => count($protegees),
                         'transactions_supprimees' => 0,
                         'transactions_ids' => $transactions_ids,
                         'erreur' => 'suppression_transactions_cotisations_orphelines_echouee'
                     ];
                 }
             }
         }
    }

    return [
        'supprimees' => intval($nb_cotisations),
        'ids' => $a_supprimer,
        'protegees' => count($protegees),
        'transactions_supprimees' => intval($transactions_supprimees),
        'transactions_ids' => $transactions_ids
    ];
}


/**
 * Supprimer les transactions orphelines (non liées et non `ok`).
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_transactions_orphelines($dry_run = true, $lot = 1000) {
    // Préparer une limite temporelle (1 an)
    $limite = date('Y-m-d H:i:s', time() - 365 * 86400);

    // WHERE par type / condition (chacun dans sa variable)
    // Colonnes supposées présentes
    $where_date = 't.date_transaction<=' . sql_quote($limite);

    $where_commandes = '';
    if (test_plugin_actif('commandes')) {
        $where_commandes = '(t.id_commande IS NULL OR t.id_commande=0 OR (t.id_commande>0 AND NOT EXISTS (SELECT 1 FROM spip_commandes AS cmd WHERE cmd.id_commande = t.id_commande)))';
    } else {
        $where_commandes = '(t.id_commande IS NULL OR t.id_commande=0)';
    }

    $where_formidable = '';
    if (test_plugin_actif('formidable')) {
        $where_formidable = 'NOT (t.tracking_id>0 AND t.parrain LIKE ' . sql_quote('formidable:%') . ' AND EXISTS (SELECT 1 FROM spip_formulaires_reponses AS r WHERE r.id_formulaires_reponse = t.tracking_id))';
    } else {
        $where_formidable = 'NOT (t.tracking_id>0 AND t.parrain LIKE ' . sql_quote('formidable:%') . ')';
    }

    // Conditions liées aux objets de l'application : utiliser NOT EXISTS pour éviter les duplications dues aux JOIN
    $where_comptes = 'NOT EXISTS (SELECT 1 FROM spip_asso_comptes AS c WHERE c.id_transaction = t.id_transaction)';
    $where_activites = 'NOT EXISTS (SELECT 1 FROM spip_asso_activites AS a WHERE a.id_transaction = t.id_transaction)';

    // Statut de la transaction
    $where_statut = 't.statut<>' . sql_quote('ok');

    // Composer la clause WHERE finale depuis les morceaux non vides
    $where_parts = array_filter([
        $where_comptes,
        $where_activites,
        $where_statut,
        $where_date,
        $where_commandes,
        $where_formidable
    ]);

    $where = $where_parts ? implode(' AND ', $where_parts) : '0'; // '0' pour sécurité

    $ids = [];
    // On sélectionne uniquement la table des transactions et on laisse le WHERE tester l'existence dans les tables liées
    $res = sql_select(
        't.id_transaction',
        'spip_transactions AS t',
        $where,
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $ids[] = intval($row['id_transaction']);
    }
    if (!$ids) return ['supprimees' => 0];

    $in = sql_in('id_transaction', $ids);
    $nb = $dry_run ? sql_countsel('spip_transactions', $in) : sql_delete('spip_transactions', $in);

    if ($nb === false) {
        association_log('cron', 'Erreur suppression transactions orphelines (' . $where . ')', 'erreur');
    }

    return ['supprimees' => intval($nb), 'ids' => $ids, 'limite' => ($where_date ? $limite : null)];
}

/**
 * Supprimer les cotisations non encaissées trop anciennes, uniquement si
 * aucune transaction encaissée ne leur est liée.
 * Puis supprimer les transactions liées non encaissées.
 *
 * Critères:
 *  - c.objet = 'cotisation'
 *  - c.statut_cotisation <> 'ok'
 *  - c.date <= $limite (N mois en arrière)
 *  - (si table transactions) (t.id_transaction IS NULL OR t.statut <> 'ok')
 *
 * Retour:
 *  - supprimees: nb cotisations supprimées
 *  - ids: ids des cotisations supprimées
 *  - limite: date limite utilisée
 *  - transactions_supprimees: nb de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param int $maintenant Timestamp courant
 * @param int $mois Nombre de mois de rétention
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_cotisations_non_encaissees_anciennes($maintenant, $mois, $dry_run = true, $lot = 1000) {
    // Calcul de la date limite (simple: mois courant - N)
    $limit_ts = mktime(date('H',$maintenant), date('i',$maintenant), date('s',$maintenant),
        date('m',$maintenant) - intval($mois), date('d',$maintenant), date('Y',$maintenant));
    $limite = date('Y-m-d H:i:s', $limit_ts);

    $ids_cot = [];
    $tx_ids_candidates = [];

    // Jointure pour exclure les cotisations liées à une transaction encaissée
    $where = "c.objet='cotisation'"
        . " AND c.statut_cotisation<>" . sql_quote('ok')
        . " AND c.date<=" . sql_quote($limite)
        . " AND (t.id_transaction IS NULL OR t.statut<>" . sql_quote('ok') . ")";
    $res = sql_select(
        'c.id_compte,c.id_transaction',
        'spip_asso_comptes AS c LEFT JOIN spip_transactions AS t ON t.id_transaction=c.id_transaction',
        $where,
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $idc = intval($row['id_compte']);
        $ids_cot[] = $idc;
        $idt = intval($row['id_transaction']);
        if ($idt) {
            $tx_ids_candidates[] = $idt;
        }
    }

    if (!$ids_cot) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'limite' => $limite,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    // Suppression des cotisations
    $in_cot = sql_in('id_compte', $ids_cot);
    $nb_cot = $dry_run
        ? sql_countsel('spip_asso_comptes', $in_cot)
        : sql_delete('spip_asso_comptes', $in_cot);

    if ($nb_cot === false) {
        return [
            'supprimees' => 0,
            'ids' => $ids_cot,
            'limite' => $limite,
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_cotisations_non_encaissees_echouee'
        ];
    }

    // Suppression des transactions non encaissées associées
    $transactions_supprimees = 0;
    $transactions_ids = [];
    if ($tx_ids_candidates) {
        $tx_ids_candidates = array_values(array_unique(array_filter($tx_ids_candidates)));
        if ($tx_ids_candidates) {
            $where_tx = sql_in('id_transaction', $tx_ids_candidates) . ' AND statut<>' . sql_quote('ok');
            // Lister exactement celles à supprimer
            $res_tx = sql_select('id_transaction', 'spip_transactions', $where_tx);
            while ($r = sql_fetch($res_tx)) {
                $transactions_ids[] = intval($r['id_transaction']);
            }
            if ($transactions_ids) {
                $in_tx_final = sql_in('id_transaction', $transactions_ids);
                $transactions_supprimees = $dry_run
                    ? sql_countsel('spip_transactions', $in_tx_final)
                    : sql_delete('spip_transactions', $in_tx_final);
                if ($transactions_supprimees === false) {
                    return [
                        'supprimees' => intval($nb_cot),
                        'ids' => $ids_cot,
                        'limite' => $limite,
                        'transactions_supprimees' => 0,
                        'transactions_ids' => $transactions_ids,
                        'erreur' => 'suppression_transactions_cotisations_non_encaissees_echouee'
                    ];
                }
            }
        }
    }

    return [
        'supprimees' => intval($nb_cot),
        'ids' => $ids_cot,
        'limite' => $limite,
        'transactions_supprimees' => intval($transactions_supprimees),
        'transactions_ids' => $transactions_ids
    ];
}
/**
 * Supprimer les participations à des événements orphelines (événement supprimé)
 * et non réglées (pas de transaction ou transaction non `ok`).
 * Puis supprimer les transactions liées non encaissées.
 *
 * Retour:
 *  - supprimees: nb d'inscriptions supprimées
 *  - ids: ids des inscriptions supprimées
 *  - transactions_supprimees: nb de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_participations_evenements_orphelines($dry_run = true, $lot = 1000) {
    // Participations dont l'événement est orphelin et non réglées
    $ids = [];
    $tx_ids = [];

    $from = 'spip_asso_activites AS a
             LEFT JOIN spip_evenements AS e ON e.id_evenement=a.id_evenement
             LEFT JOIN spip_transactions AS t ON t.id_transaction=a.id_transaction';

    $where = 'e.id_evenement IS NULL AND (a.id_transaction IS NULL OR a.id_transaction=0 OR (a.id_transaction>0 AND (t.id_transaction IS NULL OR t.statut<>' . sql_quote('ok') . ')))';

    $res = sql_select(
        'a.id_activite, a.id_transaction',
        $from,
        $where,
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $ids[] = intval($row['id_activite']);
        if (!empty($row['id_transaction'])) {
            $tx_ids[] = intval($row['id_transaction']);
        }
    }

    if (!$ids) return ['supprimees' => 0];

    // Dédoublonnage et limitation par lot
    $ids = array_values(array_unique($ids));
    if (count($ids) > $lot) {
        $ids = array_slice($ids, 0, $lot);
    }

    $in = sql_in('id_activite', $ids);
    $nb = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);

    if ($nb === false) {
        return [
            'supprimees' => 0,
            'ids' => $ids,
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_participations_orphelines_echouee'
        ];
    }

    // Supprimer les transactions liées non encaissées
    $tx_supprimees = 0;
    $tx_ids_final = [];
    if ($tx_ids) {
        $tx_ids = array_values(array_unique($tx_ids));
        $where_tx = sql_in('id_transaction', $tx_ids) . ' AND statut<>' . sql_quote('ok');
        $res_tx = sql_select('id_transaction', 'spip_transactions', $where_tx);
        while ($r = sql_fetch($res_tx)) {
            $tx_ids_final[] = intval($r['id_transaction']);
        }
        if ($tx_ids_final) {
            $in_tx = sql_in('id_transaction', $tx_ids_final);
            $tx_supprimees = $dry_run ? sql_countsel('spip_transactions', $in_tx) : sql_delete('spip_transactions', $in_tx);
            if ($tx_supprimees === false) {
                return [
                    'supprimees' => intval($nb),
                    'ids' => $ids,
                    'transactions_supprimees' => 0,
                    'transactions_ids' => $tx_ids_final,
                    'erreur' => 'suppression_transactions_participations_orphelines_echouee'
                ];
            }
        }
    }

    return [
        'supprimees' => intval($nb),
        'ids' => $ids,
        'transactions_supprimees' => intval($tx_supprimees),
        'transactions_ids' => $tx_ids_final
    ];
}

/**
 * Supprimer les participations aux événements obsolètes :
 * Inscriptions pour des événements passés depuis plus de N jours
 * et dont le statut n'est pas 'ok'.
 * Ne rien supprimer si l'inscription est liée à une transaction réglée (statut='ok').
 * Supprimer les transactions liées si elles existent et ne sont pas réglées.
 *
 * Retour:
 *  - supprimees: nb d'inscriptions supprimées
 *  - ids: ids des inscriptions supprimées
 *  - protegees: nb d'inscriptions conservées car transaction réglée
 *  - transactions_supprimees: nb de transactions supprimées
 *  - transactions_ids: ids des transactions supprimées
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_participations_evenements_obsoletes($dry_run = true, $lot = 1000, $jours = 90) {
    // Utiliser la valeur fournie ($jours) ou 90 par défaut
    $jours = intval($jours) > 0 ? intval($jours) : 90;

    // Calculer la date limite (activites dont date < limite seront considérées obsolètes)
    $limite = date('Y-m-d H:i:s', time() - ($jours * 86400));

    $ids = [];
    $tx_ids = [];
    $protegees = [];

    // Rejoindre les tables pour vérifier l'existence de la transaction et son statut
    $from = 'spip_asso_activites AS a
             LEFT JOIN spip_evenements AS e ON e.id_evenement=a.id_evenement
             LEFT JOIN spip_transactions AS t ON t.id_transaction=a.id_transaction';

    // Critères : événement passé (e.date < limite) OR si pas de table evenements on utilise a.date < limite
    // et statut de l'inscription différent de 'ok'
    $where = "( (e.date_fin IS NOT NULL AND e.date_fin < " . sql_quote($limite) . ") OR (e.id_evenement IS NULL AND a.date < " . sql_quote($limite) . ") )"
        . " AND (a.statut IS NULL OR a.statut<>" . sql_quote('ok') . ")";

    $res = sql_select(
        'a.id_activite, a.id_transaction, t.statut AS tx_statut',
        $from,
        $where,
        '',
        '',
        intval($lot)
    );

    while ($row = sql_fetch($res)) {
        $id_act = intval($row['id_activite']);
        $ids[] = $id_act;
        $id_tx = !empty($row['id_transaction']) ? intval($row['id_transaction']) : 0;
        if ($id_tx) {
            // Si la transaction est réglée, on protège l'inscription
            if (!empty($row['tx_statut']) && $row['tx_statut'] === 'ok') {
                $protegees[] = $id_act;
            } else {
                $tx_ids[] = $id_tx;
            }
        }
    }

    if (!$ids) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => 0,
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    // Exclure les inscriptions protégées (liées à transaction 'ok')
    $ids_a_supprimer = array_values(array_diff(array_unique($ids), array_unique($protegees)));
    if (!$ids_a_supprimer) {
        return [
            'supprimees' => 0,
            'ids' => [],
            'protegees' => count($protegees),
            'transactions_supprimees' => 0,
            'transactions_ids' => []
        ];
    }

    // Limiter au lot
    if (count($ids_a_supprimer) > $lot) {
        $ids_a_supprimer = array_slice($ids_a_supprimer, 0, $lot);
    }

    // Supprimer les inscriptions
    $in = sql_in('id_activite', $ids_a_supprimer);
    $nb_suppr = $dry_run ? sql_countsel('spip_asso_activites', $in) : sql_delete('spip_asso_activites', $in);

    if ($nb_suppr === false) {
        return [
            'supprimees' => 0,
            'ids' => $ids_a_supprimer,
            'protegees' => count(array_unique($protegees)),
            'transactions_supprimees' => 0,
            'transactions_ids' => [],
            'erreur' => 'suppression_participations_obsoletes_echouee'
        ];
    }

    // Supprimer les transactions non réglées associées aux inscriptions supprimées
    $transactions_supprimees = 0;
    $transactions_ids = [];
    if (true) {
        // Collecter les id_transaction correspondants aux ids_a_supprimer
        // Récupérer depuis la table pour être sûr du statut
        $res_tx = sql_select('DISTINCT a.id_transaction', 'spip_asso_activites AS a', $in . ' AND a.id_transaction IS NOT NULL AND a.id_transaction<>0');
        $tx_candidats = [];
        while ($r = sql_fetch($res_tx)) {
            $tx_candidats[] = intval($r['id_transaction']);
        }
        // Ajouter aussi ceux détectés précédemment
        if ($tx_ids) {
            $tx_candidats = array_merge($tx_candidats, $tx_ids);
        }
        $tx_candidats = array_values(array_unique(array_filter($tx_candidats)));

        if ($tx_candidats) {
            // Ne supprimer que celles qui ne sont pas 'ok'
            $where_tx = sql_in('id_transaction', $tx_candidats) . ' AND statut<>' . sql_quote('ok');
            $res_tx2 = sql_select('id_transaction', 'spip_transactions', $where_tx);
            while ($r2 = sql_fetch($res_tx2)) {
                $transactions_ids[] = intval($r2['id_transaction']);
            }
            if ($transactions_ids) {
                $in_tx = sql_in('id_transaction', $transactions_ids);
                $transactions_supprimees = $dry_run ? sql_countsel('spip_transactions', $in_tx) : sql_delete('spip_transactions', $in_tx);
                if ($transactions_supprimees === false) {
                    return [
                        'supprimees' => intval($nb_suppr),
                        'ids' => $ids_a_supprimer,
                        'protegees' => count(array_unique($protegees)),
                        'transactions_supprimees' => 0,
                        'transactions_ids' => $transactions_ids,
                        'erreur' => 'suppression_transactions_participations_obsoletes_echouee'
                    ];
                }
            }
        }
    }

    return [
        'supprimees' => intval($nb_suppr),
        'ids' => $ids_a_supprimer,
        'protegees' => count(array_unique($protegees)),
        'transactions_supprimees' => intval($transactions_supprimees),
        'transactions_ids' => $transactions_ids
    ];
}

/**
 * Supprimer les urls de redirection obsolètes.
 * On boucle sur type et id_objet pour vérifier l'existence de l'objet.
 * Les urls n'ont pas d'ID, on utilise l'url elle-même comme identifiant.
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_urls_obsoletes($dry_run = true, $lot = 1000) {
    $urls = [];
    $ignores = [];
    $types = [];
    $res_types = sql_select('DISTINCT type', 'spip_urls');
    while ($row = sql_fetch($res_types)) {
        $types[] = $row['type'];
    }
    if (!$types) return ['supprimees' => 0, 'ignore' => true];

    foreach ($types as $type) {
        // Obtenir table/col réelles (mapping inline, garanti)
        if ($type === 'site') {
            $table = 'spip_syndic';
            $col = 'id_syndic';
        } else {
            $table = 'spip_' . $type . 's';
            $col = 'id_' . $type;
        }

         // Si le type est malformé (ex: contient un point -> base.qualifiee), on nettoie ces urls directement
         if (preg_match('/\./', $type) || !preg_match('/^[a-z0-9_]+$/i', $type)) {
             $res_bad = sql_select('url', 'spip_urls', 'type=' . sql_quote($type), '', '', intval($lot));
             while ($r = sql_fetch($res_bad)) {
                 $urls[] = $r['url'];
             }
             continue;
         }

         // Tentative protégée : effectuer la requête LEFT JOIN ; si échec SQL (table/colonne absente), on retombe sur la solution de repli
         $join_query = "u.url";
         $join_from = "spip_urls AS u LEFT JOIN $table AS o ON o.$col=u.id_objet AND u.type=" . sql_quote($type);
         $join_where = "u.type=" . sql_quote($type) . " AND o.$col IS NULL";

         $res = @sql_select($join_query, $join_from, $join_where, '', '', intval($lot));
         if ($res === false) {
            $ignores[] = $type;
            association_log('cron', 'Nettoyage URLs ignoré pour type=' . $type . ' : table ou colonne cible introuvable.', 'erreur');
            continue;
        }

        while ($row = sql_fetch($res)) {
            $urls[] = $row['url'];
        }
    }

    if (!$urls) return ['supprimees' => 0, 'types_ignores' => $ignores];
    $urls = array_values(array_unique($urls));
    if (count($urls) > $lot) {
        $urls = array_slice($urls, 0, $lot);
    }
    $in = sql_in('url', $urls);
    $nb = $dry_run ? sql_countsel('spip_urls', $in) : sql_delete('spip_urls', $in);
    return ['supprimees' => intval($nb), 'urls' => $urls, 'types_ignores' => $ignores];
}

/**
 * Supprime les URLs d'un type spécifique dans la table `spip_urls`.
 *
 * Cette fonction permet de supprimer les URLs associées à un type donné.
 * Elle effectue une suppression par lot pour éviter de traiter un trop grand nombre d'entrées à la fois.
 *
 * Critères:
 * - Les URLs à supprimer sont filtrées par leur type (`type`).
 *
 * Paramètres:
 * @param string $type Le type des URLs à supprimer (ex: 'mailsubscriber').
 * @param bool $dry_run Si `true`, aucune suppression n'est effectuée, seulement un comptage.
 * @param int $lot Le nombre maximum d'entrées à traiter dans un lot.
 *
 * Retour:
 * @return array Un tableau contenant:
 * - `supprimees` (int): Le nombre d'URLs supprimées ou comptées.
 * - `ids` (array): Les identifiants des URLs à supprimer.
 */
function asso_supprimer_urls_par_type($type, $dry_run = true, $lot = 1000) {
    $ids = [];
    $ignores = [];
    // Mapping inline garanti (site -> spip_syndic)
    if ($type === 'site') {
        $table = 'spip_syndic';
        $col = 'id_syndic';
    } else {
        $table = 'spip_' . $type . 's';
        $col = 'id_' . $type;
    }

     // Si type invalide -> supprimer toutes les urls de ce type
     if (preg_match('/\./', $type) || !preg_match('/^[a-z0-9_]+$/i', $type)) {
         $res_all = sql_select('url', 'spip_urls', 'type=' . sql_quote($type), '', '', intval($lot));
         while ($r = sql_fetch($res_all)) {
             $ids[] = $r['url'];
         }
     } else {
        // Tentative protégée : JOIN ; si échec SQL (table/colonne absente), on récupère toutes les urls de ce type
        $join_query = "u.url";
        $join_from = "spip_urls AS u LEFT JOIN $table AS o ON o.$col=u.id_objet AND u.type=" . sql_quote($type);
        $join_where = "u.type=" . sql_quote($type) . " AND o.$col IS NULL";

        $res = @sql_select($join_query, $join_from, $join_where, '', '', intval($lot));
        if ($res === false) {
            $ignores[] = $type;
            association_log('cron', 'Nettoyage URLs ignoré pour type=' . $type . ' : table ou colonne cible introuvable.', 'erreur');
        } else {
            while ($row = sql_fetch($res)) {
                $ids[] = $row['url'];
            }
        }
    }

    if (!$ids) return ['supprimees' => 0, 'types_ignores' => $ignores];
    $in = sql_in('url', $ids);
    $nb = $dry_run ? sql_countsel('spip_urls', $in) : sql_delete('spip_urls', $in);
    return ['supprimees' => intval($nb), 'ids' => $ids, 'types_ignores' => $ignores];
}

/**
 * Supprimer les mailsubscribers orphelins (pas d'auteur associé via email).
 *
 * @param bool $dry_run
 * @param int $lot
 * @return array
 */
function asso_supprimer_mailsubscribers_orphelines($dry_run = true, $lot = 1000) {
    // on suppose la table spip_mailsubscribers présente

    $ids = [];
    // LEFT JOIN sur auteurs via email ; si aucun auteur correspondant alors orphelin
    $res = sql_select(
        'ms.id_mailsubscriber',
        'spip_mailsubscribers AS ms LEFT JOIN spip_auteurs AS a ON a.email = ms.email',
        'a.id_auteur IS NULL',
        '',
        '',
        intval($lot)
    );
    while ($row = sql_fetch($res)) {
        $ids[] = intval($row['id_mailsubscriber']);
    }
    if (!$ids) return ['supprimees' => 0, 'ids' => []];

    $in = sql_in('id_mailsubscriber', $ids);
    $nb = $dry_run ? sql_countsel('spip_mailsubscribers', $in) : sql_delete('spip_mailsubscribers', $in);

    // Supposer la présence de spip_mailshots_destinataires
    if ($dry_run) {
        $dest = sql_countsel('spip_mailshots_destinataires', $in);
    } else {
        $dest = sql_delete('spip_mailshots_destinataires', $in);
    }

    return ['supprimees' => intval($nb), 'ids' => $ids, 'mailshots_destinataires_supprimees' => intval($dest)];
}

