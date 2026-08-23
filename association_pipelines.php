<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/boutons');

/**
 * Complète les ressources chargées dans l'espace privé.
 *
 * @param string $flux
 * @return string
 */
function association_header_prive($flux) {
	// La barre est générée par son squelette public ; le pipeline est conservé
	// pour compatibilité avec les installations existantes.
	generer_url_public('barre_generalisee.js');

	return $flux;
}

/**
 * Déclare les composants jQuery UI encore utilisés par les formulaires privés.
 *
 * @param array $scripts
 * @return array
 */
function association_jqueryui_plugins($scripts) {
	$scripts[] = 'jquery.ui.datepicker';
	$scripts[] = 'jquery.ui.i18n/jquery.ui.datepicker-fr';
	$scripts[] = 'jquery.ui.autocomplete';

	return array_values(array_unique($scripts));
}

/**
 * Enregistrer les tâches crons
 * (déclaré dans paquet.xml via <pipeline nom="taches_generales_cron" inclure="association_pipelines.php" />)
 * Après ajout/modification : vider le cache SPIP pour régénérer charger_pipelines.php
 */
function association_taches_generales_cron($flux)
{
    $flux['association_maintenance_bdd'] = 60*60*24*7; //Tous les 7 jours
    return $flux;
}


/*
 * Pipeline du menu de navigation SPIP privé
 * Déplacer les boutons de certains plugins vers le menu association
 *
/* https://remixicon.com/
 */
function association_ajouter_menus( $flux ) {
    $menu_items = [
        'adherents' => '010. ',
        'cotisations' => '020. ',
        'activites' => '030. ',
        'benevoles' => '040. ',
        //'transactions' => '050. ',
        'configurer_association' => '099. '
    ];


    if($GLOBALS['association_metas']['dons']) {
        $menu_items['dons'] = '060. ';
    }
    if($GLOBALS['association_metas']['comptes']) {
        $menu_items['comptes'] = '070. ';
    }
    if($GLOBALS['association_metas']['prets']) {
        $menu_items['prets'] = '080. ';
    }
    // Filtrer les éléments du menu selon les autorisations
    foreach ($menu_items as $key => $order) {
        if (!autoriser($key.'_menu', '', 0, $GLOBALS['visiteur_session'])) {
            unset($menu_items[$key]);
        }
    }
    if (version_compare($GLOBALS['spip_version_branche'], '4.2', '>=')) {
        //Bouton principal
        $mon_flux['association'] =
            new \Spip\Admin\Bouton(
                icone_association('association'),
                _T('association:titre_menu_association'),
                generer_url_ecrire("navigation", "menu=association")
            );

        foreach ($menu_items as $key => $order) {
            $mon_flux['association']->sousmenu[$key] = new \Spip\Admin\Bouton(
                find_in_theme("images/{$key}-xx.svg"),
                "<span class='d-none'>{$order}</span>" . _T('association:titre_onglet_' . $key),
                generer_url_ecrire($key)
            );
        }
    }else{
        $mon_flux['association'] =
            new Bouton(
                icone_association('association'),
                _T('association:titre_menu_association'),
                generer_url_ecrire("navigation", "menu=association")
            );

        foreach ($menu_items as $key => $order) {
            $mon_flux['association']->sousmenu[$key] = new Bouton(
                find_in_theme("images/{$key}-xx.svg"),
                "<span class='d-none'>{$order}</span>" . _T('association:titre_onglet_' . $key),
                generer_url_ecrire($key)
            );
        }
    }
    // On insere mon flux en deuxieme position
    $flux = array_merge(
        array_slice($flux, 0, 2),
        ['association' => $mon_flux['association']],
        array_slice($flux, 2)
    );
    return $flux;

}

function icone_association( $prefix_plugin ){
    $icone = find_in_theme( "images/$prefix_plugin-xx.svg" );
    //le logo du paquet
    if ( !file_exists( $icone )
    ) {
        // fouiller_paquet est parfois défini par d'autres paquets ; vérifier avant appel
        if (function_exists('fouiller_paquet')) {
            $icone = find_in_path(fouiller_paquet($prefix_plugin, 'logo'));
        } else {
            $icone = '';
        }
    }
    //essai sans le s final
    if ( !file_exists( $icone )
    ) {
        $prefix_plugin = substr($prefix_plugin, 0, -1);
        $icone = find_in_theme("images/$prefix_plugin-xx.svg");
    }

    return $icone;
}





/**
 * Parcourt récursivement un tableau de saisies et met obligatoire='non' sur chacune.
 *
 * @param array $saisies
 * @return array
 */


/**
 * Enregistrer les informations des évènements en pre edition
 *
 * @param array $flux
 * @return array
 */

/**
 * Enregistrer les informations des évènements en post edition
 *
 * @param array $flux
 * @return array
 */

/**
 * Enregistrer des informations lors de l'insertion d'un auteur en base
 *
 * @param array $flux
 * @return array
 */

/**
 * Enregistrer les informations de cotisations en post insertion d'un auteur en base
 *
 * @param array $flux
 * @return array
 */


/*
 * Afficher la date d'inscription sur la fiche de l'auteur
 *
 * @param array $flux
 * @return array
 *
 */

/*
 * Enregistrer les informations de cotisations validées si le paiement est accepté.
 *
 * Cette fonction est déclenchée après la notification d'un règlement via le système bancaire.
 * Elle vérifie si le règlement est réussi, puis met à jour les informations associées
 * aux activités ou cotisations liées à la transaction.
 *
 * @param array $flux Données du flux contenant les informations de la transaction.
 * @return array Retourne le flux inchangé si le règlement n'est pas réussi, ou après traitement.
 */


/**
 * Met à jour les informations de participation à une activité après un paiement réussi.
 *
 * Cette fonction gère la validation de l'inscription ou l'encaissement du paiement
 * pour une activité, en mettant à jour le statut et le journal de l'activité.
 * Elle envoie également des notifications et met à jour les comptes si nécessaire.
 *
 * @param array $flux Données du flux contenant les informations de la participation.
 * @param array $query_activite Données de l'activité associée.
 * @param int $id_transaction Identifiant de la transaction.
 * @param string $date Date actuelle au format 'Y-m-d H:i:s'.
 */



/**
 * Rediriger vers la page que l'on souhaite en fonction du backend ou du frontend
 * INFOS : Pas sûr que ça fontionne à 100%. A voir avec le temps et les retours.
 * NE FONCTIONNE PAS : test_espace_prive()
 *
 * @param array $flux
 * @return array
 *
 */


/**
 * Normaliser une entrée d'emails (chaîne ou tableau) et renvoyer un tableau d'emails valides ou false.
 * Gère les séparateurs `,` et `;`, les formats "Nom <email@domaine>", et utilise `email_valide()` si disponible.
 *
 * @param string|array|null $input
 * @return array|false
 */
// La normalisation des emails est désormais centralisée dans inc/notifications_emails.php

/**
 * Indiquer si l'admin courant peut ignorer les champs extra obligatoires
 * pour un auteur cible donné.
 *
 * @param int $id_auteur
 * @return bool
 */


/**
 * Assouplir les obligations des champs extra inscription3 en amont,
 * au niveau de leur définition de saisie.
 *
 * Ce hook est appelé par inscription3 via le pipeline i3_definition_champs.
 *
 * @param array $flux
 * @return array
 */


/**
 * Nettoyer récursivement les saisies pour retirer le caractère obligatoire
 * sur une liste de champs.
 *
 * @param array $saisies
 * @param array $champs_a_assouplir
 * @return array
 */


/**
 * Assouplir les saisies obligatoires de editer_auteur pour les admins
 * qui modifient la fiche d'un autre auteur.
 *
 * @param array $flux
 * @return array
 */


/**
 * Neutraliser une condition impossible à évaluer dans une vue partielle.
 *
 * Champs Extras et Crayons peuvent demander la vue d'un seul champ. Saisies 6
 * reçoit alors une structure qui ne contient pas les champs pilotant son
 * `afficher_si` et journalise une erreur critique. La sélection de la vue ayant
 * déjà été faite par l'appelant, conserver cette condition n'apporte rien.
 *
 * @param array $saisies
 * @return array
 */


/**
 * @param array $saisies
 * @param array $saisies_par_nom
 * @return array
 */


/**
 * Indiquer si un champ de requête est effectivement renseigné.
 * Gère les scalaires, tableaux et dates découpées jour/mois/année.
 *
 * @param string $champ
 * @return bool
 */


/**
 * Assouplir les erreurs de champs extra obligatoires pour les administrateurs
 * lors de la modification d'une fiche auteur tierce.
 *
 * Cette fonction ne supprime que les erreurs d'obligation portant sur des
 * champs extra vides. Les autres validations restent actives.
 *
 * @param array $flux Données du formulaire, incluant les erreurs à vérifier.
 * @return array Données du formulaire avec les erreurs filtrées.
 */


/**
 * Récupère les liens (id_categorie => montant) pour un événement donné.
 */


/**
 * Synchroniser les liens de catégories/prix d'un événement source vers ses répétitions.
 * Optimisé pour ne faire que les INSERT/UPDATE/DELETE nécessaires.
 */




/**
 * Déclarer la saisie 'formulaires' comme disponible pour iextras
 */
function association_saisies_lister_disponibles($flux) {
    $flux['formulaires'] = array(
        'titre' => 'Formulaires (Formidable)',
        'description' => 'Saisie permettant de sélectionner un formulaire publié (plugin Formidable).',
        'icone' => 'images/saisies_formulaire-xx.svg',
        'categorie' => 'choix',
        'yaml' => 'formulaires.yaml',
        'fichier' => 'formulaires.html'
    );
    return $flux;
}

/**
 * Pipeline `corbeille_table_infos` : déclare les objets mailsubscribers gérables via le plugin Corbeille.
 *
 * @param array $flux Tableau des objets corbeille déjà déclarés
 * @return array
 */


/**
 * Ajouter l'accès à la migration Association sur la page des familles.
 *
 * La logique métier reste dans Association, propriétaire de la relation
 * `auteur_compte_principal`. Le socle Familles n'est pas spécialisé.
 *
 * @param array $flux
 * @return array
 */
function association_affiche_milieu($flux) {
	if (($flux['args']['exec'] ?? '') !== 'familles') {
		return $flux;
	}

	include_spip('inc/association_familles');
	if (!association_familles_integration_disponible()) {
		return $flux;
	}

	$flux['data'] .= recuperer_fond('prive/objets/contenu/lien_migration_familles_association');
	return $flux;
}

