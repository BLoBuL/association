<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
//DOCUMENTATION
//https://contrib.spip.net/Formulaire-de-configuration-avec-le-plugin-Saisies

include_spip('formulaires/inc/configurer_association');

function formulaires_configurer_association_saisies_dist($config = '', $lister_champs = ''){
$saisies=array();

$visiteur_session = $GLOBALS['visiteur_session'] ?? array();
$disable_meta_admin = !is_array($visiteur_session) || ($visiteur_session['statut'] ?? '') !== '0minirezo';

if($config == 'info' OR empty($config)){
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'info_asso',
        'label' => _T('association_config:config_info_asso_fieldset'),
    ),
    'saisies' => array(
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'nom',
                'label' => _T('association_config:config_nom_label'),
                'defaut' => lire_config('nom_site'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'rue',
                'label' => _T('association_config:config_rue_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'cp',
                'label' => _T('association_config:config_codepostal_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'ville',
                'label' => _T('association_config:config_ville_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'pays',
                'label' => _T('association_config:config_pays_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'email',
                'label' => _T('association_config:config_email_label'),
                'type' => 'text',
                'explication' => _T('association_config:config_email_explication_multi'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'telephone',
                'label' => _T('association_config:config_telephone_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'num_enregistrement',
                'label' => _T('association_config:config_num_enregistrement_label'),
            ),
        ),
        array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'info_complementaires',
                'label' => _T('association_config:config_info_complementaire_label'),
                'rows' => 3,
                'cols' => 80,
            ),
        ),
    ),
);
}
include_spip('formulaires/inc/configurer_association_communication');
$saisies = array_merge($saisies, association_communication_configurer_saisies($config));
include_spip('formulaires/inc/configurer_association_adhesions');
$saisies = array_merge($saisies, association_adhesions_configurer_saisies($config, $disable_meta_admin));
include_spip('formulaires/inc/configurer_association_evenements');
$saisies = array_merge($saisies, association_evenements_configurer_saisies($config, $disable_meta_admin));
include_spip('formulaires/inc/configurer_association_paiements');
$saisies = array_merge($saisies, association_paiements_configurer_saisies($config, $disable_meta_admin));
include_spip('formulaires/inc/configurer_association_compta');
$saisies = array_merge($saisies, association_compta_configurer_saisies($config));
if($config == 'maintenance_bdd' OR empty($config)) {
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_maintenance_bdd_fieldset',
            'label' => _T('association_config:config_maintenance_bdd_fieldset'),
            'explication' => _T('association_config:config_maintenance_bdd_explication'),
            'restrictions' => array(
                'voir' => array('auteur' => 'webmestre'),
                'modifier' => array('auteur' => 'webmestre'),
            ),
        ),
        'saisies' => array(
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_bdd_enable',
                    'label' => _T('association_config:config_maintenance_bdd_enable_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_enable_explication'),
                    'data' => array('oui' => _T('association_config:oui'), 'non' => _T('association_config:non')),
                    'defaut' => 'oui',
                ),
            ),
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_dry_run',
                    'label' => _T('association_config:config_maintenance_bdd_dry_run_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_dry_run_explication'),
                    'data' => array('oui' => _T('association_config:oui'), 'non' => _T('association_config:non')),
                    'defaut' => 'oui',
                ),
            ),
			association_config_maintenance_input('lot', 1000),
            // Bouton pour exécuter un dry-run immédiatement (visible uniquement aux webmestres via restrictions du fieldset)
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'exec_maintenance_dry_run',
                    'label' => _T('association_config:config_maintenance_bdd_exec_dry_run_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_exec_dry_run_explication'),
                    'type' => 'submit',
                    'valeur' => '1',
                    'class' => 'bouton_action',
                ),
            ),
        ),
    );
}
if ($config == 'debug' OR empty($config)) {
    // Accessible uniquement aux webmestres
    $est_webmestre = isset($GLOBALS['visiteur_session']['webmestre'])
        && $GLOBALS['visiteur_session']['webmestre'] === 'oui';

    if ($est_webmestre) {
        include_spip('inc/association_log');

        $saisies_debug = array();
        foreach (association_log_categories_defaut() as $cat => $libelle) {
            $saisies_debug[] = array(
                'saisie' => 'case',
                'options' => array(
                    'nom'         => 'debug_log_cat_' . $cat,
                    'label'       => _T('association_config:config_debug_cat_' . $cat . '_label', array(), $libelle),
                    'label_case'  => _T('association_config:config_debug_cat_' . $cat . '_label', array(), $libelle),
                    'explication' => _T('association_config:config_debug_cat_' . $cat . '_explication', array(), ''),
                ),
            );
        }

        $saisies[] = array(
            'saisie'  => 'fieldset',
            'options' => array(
                'nom'          => 'config_debug_fieldset',
                'label'        => _T('association_config:config_debug_fieldset'),
                'explication'  => _T('association_config:config_debug_explication'),
                'restrictions' => array(
                    'voir'     => array('auteur' => 'webmestre'),
                    'modifier' => array('auteur' => 'webmestre'),
                ),
            ),
            'saisies' => $saisies_debug,
        );
    }
}

// Ici on envoie que les noms des saisies pour la fonction de traitement et identifier qui ne sont pas toujours envoyés si vide comme les checkbox
    if($lister_champs) {
        // On aplatit les saisies
        $liste_saisies =saisies_lister_par_nom($saisies,false);
        // je veux boucler sur saisies pour extraire uniquement le nom de chaque saisie pour les mettre dans un tableau
        foreach ($liste_saisies as $saisie) {
            $liste_champs[] = $saisie['options']['nom'];
        }
        return $liste_champs;
    }else{
        return $saisies;
    }
}
// Fonction pour charger les valeurs de configuration
function formulaires_configurer_association_charger_dist($config) {
	$contexte = deserialize_values($GLOBALS['association_metas']);

    return $contexte;
}
// Fonction pour vérifier les valeurs du formulaire
function formulaires_configurer_association_verifier_dist($config) {
	$erreurs = array();

	include_spip('formulaires/inc/configurer_association_compta_verifier');
	$erreurs = array_merge($erreurs, association_compta_configurer_verifier($config));

	include_spip('formulaires/inc/configurer_association_communication_verifier');
	$erreurs = array_merge($erreurs, association_communication_configurer_verifier($config));

	foreach (array('meta_cfg_maintenance_jours_inactivite', 'meta_cfg_maintenance_jours_inscriptions_attente', 'meta_cfg_maintenance_mois_non_encaisse', 'meta_cfg_maintenance_lot') as $numfield) {
		$valeur = trim((string) _request($numfield));
		if ($valeur !== '' && !ctype_digit($valeur)) {
			$erreurs[$numfield] = _T('association_config:erreur_entier_positif');
		}
	}

	if ($erreurs && !isset($erreurs['message_erreur'])) {
		$erreurs['message_erreur'] = _T('association:erreur_configurer_association_titre');
	}

	return $erreurs;
}
// Fonction pour traiter les valeurs du formulaire
function formulaires_configurer_association_traiter_dist($config){
    include_spip('inc/cvt_configurer');
    $retours = array();

    // On charge la liste des saisies proposées par le formulaire segmenté
    $saisies = formulaires_configurer_association_saisies_dist($config,true);

    // On boucle sur les valeurs reçues pour les traiter et renvoyer un résultat vide pour les checkbox non-cochées
    foreach ($saisies as $saisie) {
        $valeurs[$saisie] =  (_request($saisie) ? _request($saisie) : '');
    }

    // On charge finalement le résultat
    $trace = cvtconf_configurer_stocker('configurer_association', array('_meta_table'=>'association_metas'), $valeurs);

    // Message de confirmation initial (toujours présent)
    $retours['message_ok'] = _T('config_info_enregistree') . $trace;
    $retours['editable'] = true;

    // Sauvegarde des préférences de debug (webmestre uniquement)
    if (isset($GLOBALS['visiteur_session']['webmestre']) && $GLOBALS['visiteur_session']['webmestre'] === 'oui') {
        include_spip('inc/association_log');
        foreach (array_keys(association_log_categories_defaut()) as $cat) {
            $v = _request('debug_log_cat_' . $cat) ? 'on' : 'off';
            ecrire_config('association/debug/categories/' . $cat, $v);
        }
    }

    // Si le bouton d'exécution dry-run a été cliqué, exécuter la maintenance en mode dry-run
    if (isset($_REQUEST['exec_maintenance_dry_run']) || _request('exec_maintenance_dry_run')) {
        // Sécurité : vérifier que l'utilisateur est webmestre
        if (empty($GLOBALS['visiteur_session']) || ($GLOBALS['visiteur_session']['webmestre'] != 'oui')) {
            $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_no_rights');
        } else {
            association_log('cron', 'Associaspip: Lancement dry-run via interface (user id: ' . intval($GLOBALS['visiteur_session']['id_auteur'] ?? 0) . ')', 'info');
            // Charger les fonctions du genie
            include_spip('genie/association_maintenance_bdd');

            // Helper pour interpréter les valeurs
            $is_true = function($v) {
                if (is_bool($v)) return $v;
                $v = (string)$v;
                return in_array(strtolower($v), array('1','on','oui','true'), true);
            };

            // Construire les options depuis les valeurs soumises (avec fallbacks)
            $options = array(
                'enabled' => isset($valeurs['meta_cfg_maintenance_bdd_enable']) ? $is_true($valeurs['meta_cfg_maintenance_bdd_enable']) : true,
                'dry_run' => true, // force dry-run
                'jours_inactivite' => isset($valeurs['meta_cfg_maintenance_jours_inactivite']) ? intval($valeurs['meta_cfg_maintenance_jours_inactivite']) : 365,
                'jours_inscriptions_en_attente' => isset($valeurs['meta_cfg_maintenance_jours_inscriptions_attente']) ? intval($valeurs['meta_cfg_maintenance_jours_inscriptions_attente']) : 90,
                'mois_non_encaisse' => isset($valeurs['meta_cfg_maintenance_mois_non_encaisse']) ? intval($valeurs['meta_cfg_maintenance_mois_non_encaisse']) : 6,
                'lot' => isset($valeurs['meta_cfg_maintenance_lot']) ? intval($valeurs['meta_cfg_maintenance_lot']) : 1000,
                'actions' => array(
                    'supprimer_auteurs_sans_paiements' => isset($valeurs['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) : true,
                    'anonymiser_auteurs_avec_paiements' => false,
                    'supprimer_inscriptions_non_validees' => isset($valeurs['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) : true,
                    'anonymiser_inscriptions_inactifs' => false,
                    'supprimer_cotisations_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_cotisations_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_cotisations_orphelines']) : true,
                    'supprimer_cotisations_non_encaissees' => isset($valeurs['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) : true,
                    'supprimer_transactions_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_transactions_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_transactions_orphelines']) : true,
                    'supprimer_participations_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_participations_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_participations_orphelines']) : true,
                    'supprimer_participations_obsoletes' => isset($valeurs['meta_cfg_maintenance_supprimer_participations_obsoletes']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_participations_obsoletes']) : true,
                    'supprimer_urls_mailsubscriber' => isset($valeurs['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) : true,
                    'supprimer_urls_obsoletes' => isset($valeurs['meta_cfg_maintenance_supprimer_urls_obsoletes']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_urls_obsoletes']) : true,
                    'supprimer_mailsubscribers_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) : true,
                ),
            );

            association_log('cron', 'Associaspip: Options dry-run construites: ' . json_encode($options), 'info');

            // Exécuter la maintenance en dry-run et écrire le rapport
            $resume = association_maintenance_bdd_run(time(), $options);

            $date = date('Y-m-d_H-i-s');
            $dir_rapports = _DIR_TMP . 'rapports/';
            if (!is_dir($dir_rapports)) {
                mkdir($dir_rapports, 0755, true);
            }
            $chemin = $dir_rapports . 'maintenance_asso_dryrun_' . $date .'.json';
            ecrire_fichier($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Message récapitulatif court
            $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_done', array('fichier' => basename($chemin)));
            // Afficher directement le rapport JSON formaté dans l'interface
            $report_json = json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            // Escaper pour affichage HTML et limiter la hauteur avec un conteneur scrollable
            $report_html = '<div class="maintenance_report" style="margin-top:1em;">'
                . '<h4>' . _T('association_config:config_maintenance_bdd_exec_dry_run_report_title') . '</h4>'
                . '<pre style="white-space:pre-wrap;word-wrap:break-word;max-height:480px;overflow:auto;border:1px solid #ddd;padding:8px;background:#f9f9f9;">'
                . htmlspecialchars($report_json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</pre></div>';
            $retours['message_ok'] .= $report_html;
         }
     }

     // On retourne le message de confirmation

     return $retours;
 }


// Ajout : construction d'un résumé humain pour les rapports de maintenance (dry-run)
function maintenance_build_human_summary(array $resume, $max_items = 5) {
    // clés prioritaires à présenter
    $keys = array(
        'auteurs_inactifs',
        'auteurs_sans_paiements',
        'auteurs_avec_paiements',
        'inscriptions_non_validees_anciennes',
        'supprimer_cotisations_orphelines',
        'supprimer_cotisations_non_encaissees_anciennes',
        'supprimer_transactions_orphelines',
        'supprimer_participations_evenements_orphelines',
        'supprimer_participations_evenements_obsoletes',
        'supprimer_urls_obsoletes',
        'supprimer_urls_mailsubscriber',
        'supprimer_mailsubscribers_orphelines',
    );

    $html = '<div class="maintenance_summary" style="margin-top:1em;">';
    $html .= '<h4>' . htmlspecialchars(_T('association_config:config_maintenance_bdd_exec_dry_run_report_title')) . ' - résumé</h4>';
    $html .= '<ul style="list-style:none;padding:0;margin:0;">';

    foreach ($keys as $k) {
        if (!isset($resume[$k])) continue;
        $item = $resume[$k];
        $count = null;
        if (isset($item['nombre'])) $count = intval($item['nombre']);
        elseif (isset($item['supprimees'])) $count = intval($item['supprimees']);
        elseif (isset($item['supprimes'])) $count = intval($item['supprimes']);
        elseif (is_int($item)) $count = $item;
        elseif (is_array($item) && isset($item['ids'])) $count = count($item['ids']);
        else {
            if (is_array($item)) {
                foreach (array('ids','ids_activite','transactions_ids','urls') as $ik) {
                    if (isset($item[$ik]) && is_array($item[$ik])) {
                        $count = count($item[$ik]);
                        break;
                    }
                }
            }
        }
        $label = htmlspecialchars($k);
        $html .= '<li style="margin:6px 0;">';
        $html .= '<strong>' . $label . '</strong>';
        $html .= ' : ' . ($count !== null ? intval($count) : '<em>n/a</em>');

        $candidates = array();
        if (isset($item['ids']) && is_array($item['ids'])) $candidates = $item['ids'];
        elseif (isset($item['ids_activite']) && is_array($item['ids_activite'])) $candidates = $item['ids_activite'];
        elseif (isset($item['urls']) && is_array($item['urls'])) $candidates = $item['urls'];
        elseif (isset($item['ids']) && !is_array($item['ids']) && $item['ids']) $candidates = (array)$item['ids'];
        elseif (is_array($item) && count($item) && array_keys($item) === range(0, count($item)-1)) $candidates = $item;

        if ($candidates) {
            $slice = array_slice($candidates, 0, $max_items);
            $html .= '<div style="font-size:0.9em;margin-top:4px;">';
            $html .= 'Top: <code>' . htmlspecialchars(implode(', ', $slice)) . '</code>';
            if (count($candidates) > $max_items) {
                $html .= ' <em>…+' . (count($candidates) - $max_items) . '</em>';
            }
            $html .= '</div>';
        }
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}

/* Remplacement ciblé : après l'appel à association_maintenance_bdd_run() le code devait écrire le rapport JSON et l'ajouter au message.
   Nous remplaçons cela par : écriture du fichier JSON (inchangé) + génération d'un résumé humain suivi du JSON formaté visible. */

// Exemple de bloc à insérer à l'endroit où $resume a été calculé (dry-run exécuté) :
if (isset($resume) && is_array($resume)) {
    // écriture du fichier rapport (si l'original le faisait)
    $date = date('Y-m-d_H-i-s');
    $dir_rapports = defined('_DIR_TMP') ? _DIR_TMP . 'rapports/' : (_DIR_RACINE . 'tmp/rapports/');
    if (!is_dir($dir_rapports)) {
        @mkdir($dir_rapports, 0755, true);
    }
    $chemin = $dir_rapports . 'maintenance_asso_dryrun_' . $date .'.json';
    if (function_exists('ecrire_fichier')) {
        ecrire_fichier($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        @file_put_contents($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // message récapitulatif court (préservons la clé $retours si présente)
    if (!isset($retours)) $retours = array();
    $retours['message_ok'] = (isset($retours['message_ok']) ? $retours['message_ok'] : '');
    $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_done', array('fichier' => basename($chemin)));

    // construire résumé humain + JSON formaté
    $summary_html = maintenance_build_human_summary($resume, 5);
    $report_json = json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $report_html = '<div class="maintenance_report" style="margin-top:1em;">'
        . '<h4>' . htmlspecialchars(_T('association_config:config_maintenance_bdd_exec_dry_run_report_title')) . '</h4>'
        // résumé humain
        . $summary_html
        // JSON complet en bas, sécurisé
        . '<pre style="white-space:pre-wrap;word-wrap:break-word;max-height:480px;overflow:auto;border:1px solid #ddd;padding:8px;background:#f9f9f9;margin-top:8px;">'
        . htmlspecialchars($report_json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . '</pre></div>';

    $retours['message_ok'] .= $report_html;
}
