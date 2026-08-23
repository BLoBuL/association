<?php
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if(!defined('_ECRIRE_INC_VERSION')) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip ('formulaires/inc/adherents_recherche_avancee'); // fonctions du moteur de recherche

function formulaires_adherents_recherche_avancee_saisies(){
    $saisies = adherents_recherche_avancee_saisies();
    return $saisies;
}
function formulaires_adherents_recherche_avancee_charger_dist(){
    $contexte = array();
    $contexte['action'] = generer_url_ecrire('adherents');
	return $contexte;
}
function formulaires_adherents_recherche_avancee_verifier_dist(){
    $erreurs = array();
    if (count($erreurs))
        $erreurs['message_erreur'] = 'Votre saisie contient des erreurs !';
    return $erreurs;
}

function formulaires_adherents_recherche_avancee_traiter_dist(){
    // Sauvegarder les critères de recherche en session pour qu'ils persistent
    // lors de l'utilisation des filtres (période, statut, type)

    // Nettoyer les valeurs vides et inutiles
    $criteres_recherche = array();
    foreach ($_POST as $key => $value) {
        // Ignorer les paramètres système SPIP
        if (in_array($key, array('action', 'formulaire_action', 'formulaire_action_args'))) {
            continue;
        }
        // Garder seulement les valeurs non vides
        if (!empty($value) || $value === '0') {
            $criteres_recherche[$key] = $value;
        }
    }

    // Sauvegarder en session si des critères sont présents
    if (!empty($criteres_recherche)) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['adherents_recherche_avancee'] = $criteres_recherche;

        // Log pour debug
        association_log('adherents', 'Recherche avancée sauvegardée en session: ' . count($criteres_recherche) . ' critères', 'debug');
    }

    return array('message_ok' => _T('association_adhesions:recherche_effectuee'));
}
