<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

function association_test_notifications_charger_langue($langue) {
    $index = 'association_test_notifications_' . $langue;
    $GLOBALS['idx_lang'] = $index;
    include ASSOCIATION_TEST_PLUGIN_ROOT . '/lang/notifications_' . $langue . '.php';
    return $GLOBALS[$index] ?? array();
}

function association_test_notifications_placeholders($texte) {
    preg_match_all('/@[A-Za-z0-9_]+@/i', (string) $texte, $matches);
    $placeholders = array_values(array_unique(array_map('strtolower', $matches[0])));
    sort($placeholders);
    return $placeholders;
}

function association_test_run_notifications_evenements_i18n_suite() {
    $fichiers = array_merge(
        glob(ASSOCIATION_TEST_PLUGIN_ROOT . '/notifications/*activite*.html'),
        glob(ASSOCIATION_TEST_PLUGIN_ROOT . '/notifications/inc/inc-*.html')
    );
    $fichiers[] = ASSOCIATION_TEST_PLUGIN_ROOT . '/inc/fonctions/facteur_envoyer_mail_activites.php';

    $cles = array();
    $erreurs = array();
    foreach ($fichiers as $fichier) {
        $contenu = file_get_contents($fichier);
        if (preg_match('/<:association:|[\'\"]association:/', $contenu)) {
            $erreurs[] = basename($fichier) . ' utilise encore le domaine asso';
        }
        preg_match_all('/<:notifications:([A-Za-z0-9_]+)/', $contenu, $matches_squelettes);
        preg_match_all('/[\'\"]notifications:([A-Za-z0-9_]+)[\'\"]/', $contenu, $matches_php);
        foreach (array_merge($matches_squelettes[1], $matches_php[1]) as $cle) {
            $cles[$cle] = true;
        }
    }

    $langues = array(
        'fr' => association_test_notifications_charger_langue('fr'),
        'en' => association_test_notifications_charger_langue('en'),
        'es' => association_test_notifications_charger_langue('es'),
        'cs' => association_test_notifications_charger_langue('cs'),
    );
    foreach (array_keys($cles) as $cle) {
        foreach ($langues as $langue => $traductions) {
            if (!array_key_exists($cle, $traductions) || trim((string) $traductions[$cle]) === '') {
                $erreurs[] = $langue . ' : clé absente ou vide ' . $cle;
            }
        }
        if (isset($langues['fr'][$cle])) {
            $placeholders_fr = association_test_notifications_placeholders($langues['fr'][$cle]);
            foreach (array('en', 'es', 'cs') as $langue) {
                if (isset($langues[$langue][$cle])) {
                    $placeholders = association_test_notifications_placeholders($langues[$langue][$cle]);
                    if ($placeholders !== $placeholders_fr) {
                        $erreurs[] = $langue . ' : placeholders incohérents pour ' . $cle;
                    }
                }
            }
        }
    }

    if ($erreurs) {
        foreach ($erreurs as $erreur) {
            echo '[KO] notifications-evenements-i18n :: ' . $erreur . PHP_EOL;
        }
        echo 'Resume notifications-evenements-i18n : 0 OK / ' . count($erreurs) . ' KO' . PHP_EOL;
        return 1;
    }

    echo '[OK] notifications-evenements-i18n :: ' . count($cles) . ' clés présentes en FR/EN/ES/CS dans le domaine notifications' . PHP_EOL;
    echo 'Resume notifications-evenements-i18n : 1 OK / 0 KO' . PHP_EOL;
    return 0;
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_notifications_evenements_i18n_suite());
}
