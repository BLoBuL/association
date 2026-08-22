<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/comptes');
include_spip('inc/boutons');
// inclure le nouvel utilitaire de notifications
include_spip('inc/notifications_emails');

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
    $flux['association_taches_generales'] = 60*60*6; //Toutes les six heures
    $flux['association_expiration_auto_evenement'] = 60*30; //Toutes les 1/2 heures
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



function association_formulaire_charger($flux)
{
    if ($flux['args']['form'] == 'editer_evenement'
        and $id_evenement = $flux['data']['id_evenement']
    ) {
        $categorie_querie = sql_select('*', 'spip_asso_categories_activites');
        while ($categorie = sql_fetch($categorie_querie)) {
            $id_categorie = intval($categorie['id_categorie']);
            // Récupérer la ligne liée au tarif (peut être absente)
            $montant_row = sql_fetsel(
                'montant',
                'spip_asso_categories_activites_liens',
                'id_evenement=' . intval($id_evenement) . ' AND id_categorie=' . $id_categorie
            );
            // Sécuriser l'accès : si aucune ligne, on expose une valeur vide
            $montant_val = '';
            if (is_array($montant_row) && array_key_exists('montant', $montant_row)) {
                $montant_val = $montant_row['montant'];
            }

            $flux['data']['categorie_prix_' . $id_categorie] = $montant_val;
        }
    }

    // Quand un admin édite un auteur, supprimer les contraintes "obligatoire"
    // sur les saisies des champs extras pour ne pas bloquer une édition partielle.
    if (
        $flux['args']['form'] === 'editer_auteur'
        and ($GLOBALS['visiteur_session']['statut'] ?? '') === '0minirezo'
        and !empty($flux['data']['_saisies'])
    ) {
        $flux['data']['_saisies'] = association_saisies_retirer_obligatoire($flux['data']['_saisies']);
    }

    return $flux;
}

/**
 * Parcourt récursivement un tableau de saisies et met obligatoire='non' sur chacune.
 *
 * @param array $saisies
 * @return array
 */
function association_saisies_retirer_obligatoire(array $saisies): array
{
    foreach ($saisies as &$saisie) {
        if (isset($saisie['options']['obligatoire'])) {
            $saisie['options']['obligatoire'] = 'non';
        }
        // Gérer les saisies imbriquées (fieldset, etc.)
        if (!empty($saisie['saisies']) and is_array($saisie['saisies'])) {
            $saisie['saisies'] = association_saisies_retirer_obligatoire($saisie['saisies']);
        }
    }
    unset($saisie);
    return $saisies;
}
function association_formulaire_traiter($flux)
{
    if ($flux['args']['form'] == 'editer_evenement' and $id_evenement = $flux['data']['id_evenement']) {
        $categorie_querie = sql_select('*', 'spip_asso_categories_activites');
        while ($categorie = sql_fetch($categorie_querie)) {
            $id_categorie = $categorie['id_categorie'];
            $res = sql_select("*", 'spip_asso_categories_activites_liens', "id_evenement='$id_evenement' AND id_categorie=$id_categorie", '', '', '', '', '', 'continue');
            $row = sql_fetch($res);
            sql_free($res);
            $value = _request('categorie_prix_'. $id_categorie);
            $insertion = array(
                'id_evenement'  => $id_evenement,
                'id_categorie'  => $categorie['id_categorie'],
                'montant'       => is_numeric($value)? $value : '');
            if ($row) {
                sql_updateq('spip_asso_categories_activites_liens', $insertion, "id_evenement='$id_evenement' AND id_categorie=$id_categorie");
            } else {
                sql_insertq('spip_asso_categories_activites_liens', $insertion);
            }
            unset($_REQUEST['categorie_prix_'. $id_categorie]);
            unset($_REQUEST['cextra_categorie_prix_'. $id_categorie]);
        }

        // --- Ajout : après avoir modifié les liens de catégories pour l'événement,
        // synchroniser immédiatement les répétitions (si l'événement est une source) ---
        association_sync_repetitions_tarifs(intval($id_evenement));
    }// Eviter la création des colonnes
    return $flux;
}
/**
 * Enregistrer les informations des évènements en pre edition
 *
 * @param array $flux
 * @return array
 */
function association_pre_edition($flux)
{
    if ($flux['args']['table']=='spip_evenements' and $id_evenement = $flux['data']['id_evenement']) {
        // Si PAYANT + SANS VALIDATION = PAS DE FILE D'ATTENTE

        /*pour corriger le bug le champs mode_paiement*/
        if (is_array($_REQUEST['mode_paiement'])) {
            $string_mode_paiement= implode(',', $_REQUEST['mode_paiement']);
            set_request('mode_paiement', $string_mode_paiement);
        }
        if (is_array($_REQUEST['responsables'])) {
            $string_responsables= implode(',', $_REQUEST['responsables']);
            set_request('responsables', $string_responsables);
        }
    }
    // print_r($flux);
    return $flux;
}
/**
 * Enregistrer les informations des évènements en post edition
 *
 * @param array $flux
 * @return array
 */
function association_post_edition($flux)
{
    // Sécuriser l'accès à la table pour éviter les erreurs PHP 8+
    $table = $flux['args']['table'] ?? null;

    // Détection plus robuste de l'ID de l'événement (args ou data)
    $id_evenement = intval($flux['args']['id_objet'] ?? $flux['data']['id_evenement'] ?? $flux['data']['id_objet'] ?? 0);

    if ($table == 'spip_evenements' && $id_evenement > 0) {
        // Récupérer l'événement modifié (sélectif pour éviter de charger des champs inutiles)
        $evenement_source = sql_fetsel('id_evenement,id_evenement_source,payant', 'spip_evenements', 'id_evenement=' . $id_evenement);

        // Si c'est bien un événement source payant -> déléguer à la fonction optimisée
        if ($evenement_source
            && intval($evenement_source['id_evenement_source']) === 0
            && intval($evenement_source['payant']) === 1
        ) {
            association_sync_repetitions_tarifs(intval($id_evenement));
        }
    }

    if ($table == 'spip_auteurs' and $id_auteur = $flux['args']['id_objet'] and test_plugin_actif('gis')) {
        include_spip('inc/fonctions/gis_auteur');
        gis_auteur($id_auteur, 'modification');
    }

    if ($table == 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande > 0 && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_edition'));
        }
    }

    return $flux;
}
/**
 * Enregistrer des informations lors de l'insertion d'un auteur en base
 *
 * @param array $flux
 * @return array
 */
function association_pre_insertion($flux)
{
    /*PRE INSERTION DES AUTEURS*/
    if ($flux['args']['table']=='spip_auteurs') {
        $date = date('Y-m-d H:i:s');
        $flux['data']['inscription'] = $date;
        $flux['data']['statut_interne'] = 'prospect';
    }
    /*PRE INSERTION DES EVENEMENTS*/
    if ($flux['args']['table']=='spip_evenements') {
        if ($_REQUEST['inscription'] == '1') {
            if (is_array($_REQUEST['mode_paiement'])) {
                $string_mode_paiement= implode(',', $_REQUEST['mode_paiement']);
                set_request('mode_paiement', $string_mode_paiement);
            }
            if (is_array($_REQUEST['responsables'])) {
                $string_responsables= implode(',', $_REQUEST['responsables']);
                set_request('responsables', $string_responsables);
            }
        }
    }
    return $flux;
}
/**
 * Enregistrer les informations de cotisations en post insertion d'un auteur en base
 *
 * @param array $flux
 * @return array
 */
function association_post_insertion($flux)
{
    if (($flux['args']['table'] ?? '') == 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande > 0 && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_insertion'));
        }
    }

    /*POST INSERTION DES EVENEMENTS*/
    if ($flux['args']['table']=='spip_evenements' and $id_evenement = $flux['args']['id_objet'] and $_REQUEST['inscription'] == '1') {
        if (is_array($_REQUEST['mode_paiement'])) {
            $string_mode_paiement= implode(',', $_REQUEST['mode_paiement']);
            set_request('mode_paiement', $string_mode_paiement);
        }
        if (is_array($_REQUEST['responsables'])) {
            $string_responsables= implode(',', $_REQUEST['responsables']);
            set_request('responsables', $string_responsables);
        }
        /*ICI ON TRAVAILLE LES INSERTIONS DES REPETITIONS POUR CREER LES LIENS AVEC LES CATEGORIES DE PRIX */
        //Id de l'événement source
        $id_evenement_source = $flux['data']['id_evenement_source'];
        //On récupère toutes les colonnes de l'événement
        $query_evenement_source = sql_fetsel('*', 'spip_evenements', 'id_evenement='.intval($id_evenement_source));
        //On recherche les liens de categorie de l'événement source
        $query_liens_categories_evenements = sql_select('*', 'spip_asso_categories_activites_liens', 'id_evenement='.intval($id_evenement_source));
        //On ne travaille que les événements source payant
        if ($query_evenement_source['id_evenement_source'] == 0 and  $query_evenement_source['payant'] == 1) {
            //Puis on boucle sur les catégorie à ajouter
            while ($lien_categorie_evenements = sql_fetch($query_liens_categories_evenements)) {
                $insertion = array(
                    'id_evenement'  => $id_evenement,
                    'id_categorie'  => $lien_categorie_evenements['id_categorie'],
                    'montant'       => $lien_categorie_evenements['montant']
                );
                if ($lien_categorie_evenements['montant']) {
                    sql_insertq('spip_asso_categories_activites_liens', $insertion);
                }
            }
        }
    }
    return $flux;
}

/*
 * Afficher la date d'inscription sur la fiche de l'auteur
 *
 * @param array $flux
 * @return array
 *
 */
function association_afficher_contenu_objet($flux)
{
    // INSCRIPTIONS
    if ($flux['args']['type']=='auteur'
        and $id_auteur = $flux['args']['id_objet']
        and $date_inscription = sql_getfetsel('inscription', 'spip_auteurs', 'id_auteur='.intval($id_auteur))
    ) {
        $date_inscription = ($date_inscription == '0000-00-00 00:00:00') ? _T('association:non_renseignee') : affdate($date_inscription);
        $flux['data'] .= "<div>" . propre(_T('association:date_inscription') . " : " . $date_inscription) ."</div>";
    }
    // EVENEMENTS
    elseif ($flux['args']['type']=='evenement'
        and $id_evenement = $flux['args']['id_objet']
    ) {
        $evenement_querie = sql_fetsel('payant', 'spip_evenements', 'id_evenement='.$id_evenement);
        if ($evenement_querie['payant']) {
            $categorie_querie = sql_select('*', 'spip_asso_categories_activites');
            $flux['data'] .= "<h3>" . propre(_T('association:evenement_montant_label')) . "</h3>";
            while ($categorie = sql_fetch($categorie_querie)) {
                $id_categorie = $categorie['id_categorie'];
                $montant = sql_fetsel('*', 'spip_asso_categories_activites_liens', "id_evenement=$id_evenement AND id_categorie=$id_categorie", '', "montant DESC");
                if (!isset($montant['montant']) || $montant['montant'] === '') {
                    // Montant non défini / désactivé : rien à afficher pour cette catégorie
                    continue;
                } elseif ($montant['montant'] == 0) {
                    $flux['data'] .= "<div>" . propre($categorie['valeur'] . " : " . _T('association:montant_gratuit')) ."</div>";
                } else {
                    $result = affiche_monnaie($montant['montant']);
                    $flux['data'] .= "<div>" . propre($categorie['valeur'] . " : " . $result) ."</div>";
                }
            }
            sql_free($categorie_querie);
        }
    }
    //exit();
    return $flux;
}
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
function association_trig_bank_notifier_reglement($flux)
{
    // Vérifie si le règlement est réussi. Si ce n'est pas le cas, retourne le flux sans modification.
    if (!is_successful_reglement($flux['args'])) {
        return $flux;
    }
    // Récupère l'identifiant de la transaction.
    $id_transaction = $flux['args']['id_transaction'];

    // Récupère les détails de la transaction depuis la table `spip_transactions`.
    $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=$id_transaction");

    // Vérifie si la transaction est liée à une activité.
    if ($query_activite = sql_fetsel('*', 'spip_asso_activites', "id_transaction=$id_transaction")) {
        // Met à jour les informations de participation à l'activité.
        mise_a_jour_participation($query_activite, $query_transaction);
    }
    // Sinon, vérifie si la transaction est liée à une cotisation.
    elseif ($query_cotisation = sql_fetsel('*', 'spip_asso_comptes', "id_transaction=$id_transaction")) {
        // Inclut les fichiers nécessaires pour gérer les cotisations.
        include_spip('inc/cotisations');
        include_spip('inc/api_cotisations');

        // Met à jour les informations de la cotisation.
        mise_a_jour_cotisation($query_cotisation, $query_transaction);
    }
    // Sinon, vérifie si la transaction est liée à une commande Blobul générique.
    elseif (!empty($query_transaction['id_commande']) && function_exists('association_commande_comptable_synchroniser')) {
        association_commande_comptable_synchroniser(
            intval($query_transaction['id_commande']),
            array(
                'id_transaction' => intval($id_transaction),
                'forcer_paiement' => true,
                'source' => 'trig_bank_notifier_reglement',
            )
        );
    }

    // Retourne le flux après traitement.
    return $flux;
}
function is_successful_reglement($reglement)
{
    return $reglement['succes']
        and $reglement['type'] == 'acte'
        and $reglement['id_transaction'];
}
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
function mise_a_jour_participation($query_activite, $query_transaction)
{
    $date = date('Y-m-d H:i:s');

    // Récupère l'identifiant de l'événement et les informations associées
    $id_transaction = $query_activite['id_transaction'];
    $id_evenement = $query_activite['id_evenement'];
    $query_evenement = sql_fetsel('*', 'spip_evenements', "id_evenement=$id_evenement");
    $id_activite = $query_activite['id_activite'];

    // Vérifie si l'inscription doit être validée automatiquement après le paiement
    if ($query_activite['statut'] != 'ok' && $query_evenement['validation_sur_paiement'] == 'oui') {
        $entree_journal = "$date : " . _T('association:journal_inscription_validation_paiement') . '<br>' . $query_activite['journal'];
        sql_updateq('spip_asso_activites', ["statut" => 'ok', "journal" => $entree_journal], "id_transaction=$id_transaction");

        // Ajoute une tâche pour envoyer une notification de validation d'inscription
        job_queue_add(
            'facteur_envoyer_mail_activites',
            'Notification - Validation inscription automatique suite à un paiement réussi',
            [$id_evenement, "inscription_frontend", [$id_activite]],
            '',
            false,
            0,
            0
        );
    } else {
        // Met à jour le journal pour indiquer l'encaissement du paiement
        $entree_journal = "$date : " . _T('association:journal_encaissement_paiement') . '<br>' . $query_activite['journal'];
        sql_updateq('spip_asso_activites', ["journal" => $entree_journal], "id_transaction=$id_transaction");
    }

    // Valide les comptes associés à l'activité si la gestion des comptes est activée
    if ($GLOBALS['association_metas']['comptes']) {
        valider_compte_activite($id_transaction);
    }



    // Envoie un reçu de paiement si l'option est activée
    if ($GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_participation'] == 'oui') {
        job_queue_add(
            'facteur_envoyer_recu_participation',
            'Notification - Recu encaissement',
            [$query_activite['email_inscrit'], $id_transaction, $id_activite, 'encaissement', ''],
            '',
            true,
            0,
            0
        );
    }
}


/**
 * Rediriger vers la page que l'on souhaite en fonction du backend ou du frontend
 * INFOS : Pas sûr que ça fontionne à 100%. A voir avec le temps et les retours.
 * NE FONCTIONNE PAS : test_espace_prive()
 *
 * @param array $flux
 * @return array
 *
 */
function association_bank_redirige_apres_retour_transaction($flux)
{
    ## PERMET DE REDIRIGER OU ON VEUT ##
    $id_transaction = $flux['args']['id_transaction'];
    // Recherche si on est sur l'espace privé
    if ($id_transaction
        and isset($GLOBALS['visiteur_session']['id_auteur'])
        and test_espace_prive()
        and include_spip("inc/autoriser")
        and autoriser("regler", "transaction", $id_transaction)) {
        if ($query_activite = sql_fetsel('*', 'spip_asso_activites', "id_transaction=".intval($id_transaction))) {
            // Redirection vers la fiche de l'activité.
            //$flux['data'] = generer_url_ecrire('editer_asso_activite', 'id='.$query_activite['id_activite']);
            $flux['data'] = generer_url_ecrire('voir_activites', 'id='.$query_activite['id_evenement']);
        } else {
            // Redirection vers la fiche de l'auteur.
            $id_auteur = $flux['args']['row']['id_auteur'];
            $flux['data'] = generer_url_ecrire('voir_adherent', 'id_auteur='.$id_auteur);
        }
    }
    return $flux;
}

/**
 * Normaliser une entrée d'emails (chaîne ou tableau) et renvoyer un tableau d'emails valides ou false.
 * Gère les séparateurs `,` et `;`, les formats "Nom <email@domaine>", et utilise `email_valide()` si disponible.
 *
 * @param string|array|null $input
 * @return array|false
 */
// La normalisation des emails est désormais centralisée dans inc/notifications_emails.php

 function association_notifications_destinataires($flux)
 {
     $quoi = $flux['args']['quoi'];
     $options = $flux['args']['options'];

     // Initialiser la variable pour éviter l'erreur "Undefined variable"
     $liste_email_desti_notifs = array();

    /**
     * Cas de la validation ou invalidation d'un compte d'un utilisateur
     * Cas également de l'inscription d'un auteur
     * Envoi à l'utilisateur ($options['type'] == 'user')
     */
    if (
        ($quoi=='instituerauteur' and $options['statut_ancien'] == '8aconfirmer' and $options['type'] == 'user')
        or
        ($quoi=='i3_inscriptionauteur' and $options['type'] == 'user'))
        {
        $id_auteur = $flux['args']['id'];
        include_spip('base/abstract_sql');
        $mail = sql_getfetsel("email", "spip_auteurs", "id_auteur=".intval($id_auteur));
        if ($mail) {
            // Éviter le doublon : inscription3 a peut-être déjà poussé cet email
            if (!in_array($mail, (array)$flux['data'])) {
                $flux['data'][] = $mail;
                spip_log('association_notifications_destinataires: ajouté destinataire user=' . $mail, 'association' . _LOG_DEBUG);
            } else {
                spip_log('association_notifications_destinataires: destinataire user=' . $mail . ' déjà présent, ignoré', 'association' . _LOG_DEBUG);
            }
        }
    }

    /**
     * Cas de la validation ou invalidation d'un compte d'un utilisateur
     * Envoi aux administrateurs ($options['type'] == 'admin')
     */
    elseif (($quoi=='instituerauteur'
            and $options['statut_ancien'] == '8aconfirmer'
            and $options['type'] == 'admin') or
        ($quoi=='i3_inscriptionauteur'
            and $options['type'] == 'admin')) {

        // Utiliser la fonction centralisée pour collecter et normaliser les destinataires admins
        $dests = association_collecter_destinataires_admins();
        if ($dests) {
            $flux['data'] = $dests;
            spip_log('association_notifications_destinataires: destinataires admin=' . var_export($dests, true), 'association' . _LOG_DEBUG);
        } else {
            $flux['data'] = array();
        }
    }

    return $flux;
}

function association_mailsubscriber_formater_informations_liees($champs_extra, $liste_informations_segmentables)
{
    $flux=array();

    if ((!empty($liste_informations_segmentables) and !in_array($champs_extra['options']['nom'], $liste_informations_segmentables)) or $champs_extra['saisie'] == 'fieldset') {
        unset($champs_extra);
        return false;
    }
    $nom_champs_extra = $champs_extra['options']['nom'];

    if ($champs_extra['saisie'] == 'selection') {
        //Ici on permet les selection multiple
        $champs_extra['saisie'] = 'selection_multiple';
        //$champs_extra['options']['multiple'] = 'oui';
    } elseif ($champs_extra['saisie'] == 'radio') {
        //Ici on transforme les choix radio en checkbox
        $champs_extra['saisie'] = 'checkbox';
    }

    // Sécuriser l'accès à options et datas pour éviter les notices
    $opts = isset($champs_extra['options']) && is_array($champs_extra['options']) ? $champs_extra['options'] : array();

    $res = array(
        'titre' => $champs_extra['options']['label'],
        'saisie' => $champs_extra['saisie'],
        'options' => array(
            'nom' => $opts['nom'] ?? '',
            'label' => $opts['label'] ?? '',
            'datas' => isset($opts['datas']) ? $opts['datas'] : array(),
        )
    );

    return $res;
}
function association_mailsubscriber_informations_liees($flux)
{
    include_spip('inc/filtres');
    $liste_champs_extra = lire_config('champs_extras_spip_auteurs');
    $liste_informations_segmentables = null;
    $raw_segment = isset($GLOBALS['association_metas']['selection_segment']) ? $GLOBALS['association_metas']['selection_segment'] : null;
    if (is_string($raw_segment) && $raw_segment !== '') {
        $decoded = @unserialize($raw_segment);
        $liste_informations_segmentables = ($decoded !== false || $raw_segment === 'b:0;') ? $decoded : null;
    } elseif (is_array($raw_segment)) {
        $liste_informations_segmentables = $raw_segment;
    }
    if (isset($flux['args']['declarer'])) {
        foreach ($liste_champs_extra as $champs_extra) {
            $nom_champs_extra = $champs_extra['options']['nom'];

            if ($champs_extra['saisie'] == 'fieldset') {
                foreach ($champs_extra['saisies'] as $champs_fieldset_saisies) {
                    $nom_champs_extra = $champs_fieldset_saisies['options']['nom'];
                    $flux['data'][$nom_champs_extra] = association_mailsubscriber_formater_informations_liees($champs_fieldset_saisies, $liste_informations_segmentables);
               }
            } else {
                $flux['data'][$nom_champs_extra]=association_mailsubscriber_formater_informations_liees($champs_extra, $liste_informations_segmentables);
            }
        };
        $flux['data'] = array_filter($flux['data']);


        $flux['data']['adherent'] = array(
            'titre' => $t = 'Adhérent de l\'association',
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'adherent',
                'label' => $t,
                'datas' => array('oui' => 'Oui','non' => 'Non'),
            )
        );
        $flux['data']['statut_interne'] = array(
            'titre' => $t = 'Statut de l\'adhérent',
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'statut_interne',
                'label' => $t,
                'datas' => array('ok' => 'A jour','prospect' => 'Nouvel inscrit','echu' => 'Echus', 'relance' => 'Relancé','sorti' => 'Désactivé'),
            )
        );
        //Année d'inscription
        $flux['data']['inscription'] = array(
            'titre' => $t = 'Année de première inscription',
            'saisie' => 'selection_multiple',
            'options' => array(
                'nom' => 'inscription',
                'label' => $t,
                'datas' => array('2026' => '2026','2025' => '2025','2024' => '2024','2023' => '2023','2022' => '2022','2021' => '2021','2020' => '2020','2019' => '2019', '2018' => '2018', '2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013' ),
                'size' => '5',
            )
        );
        //Année de validité
        $flux['data']['validite'] = array(
            'titre' => $t = 'Année de validité',
            'saisie' => 'selection_multiple',
            'options' => array(
                'nom' => 'validite',
                'label' => $t,
                'datas' => array('2026' => '2026','2025' => '2025','2024' => '2024','2023' => '2023','2022' => '2022','2021' => '2021','2020' => '2020','2019' => '2019', '2018' => '2018', '2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013' ),
                'size' => '5',
            )
        );
        if (!empty($liste_informations_segmentables) AND in_array('quartier_coeur', $liste_informations_segmentables) AND in_array('quartier_rattachement', $liste_informations_segmentables)) {
            $flux['data']['quartiers'] = array(
                'titre' =>  $t =  'Quartiers (rattachement ou coeur)',
                'saisie' => 'selection_multiple',
                'options' => array(
                    'nom' => 'quartiers',
                    'label' => $t,
                    'datas' => $flux['data']['quartier_rattachement']['options']['datas'],
                )
            );
        }
    }
    // Sécuriser l'accès à ['email'] pour éviter les notices quand l'argument est absent
    if (isset($flux['args']['email']) && $flux['args']['email'] && !isset($flux['args']['declarer'])) {
        $email = $flux['args']['email'];
        $liste_segment_perso = array("statut_interne","inscription","validite");

        if ($liste_informations_segmentables) {
            $liste_segments =  array_merge($liste_informations_segmentables, $liste_segment_perso);
        } else {
            $liste_segments = $liste_segment_perso;
        }
        // La configuration des segments peut conserver des champs supprimés.
        // Ne jamais les inclure dans la requête : le schéma SQL réel fait foi.
        $description_auteurs = sql_showtable('spip_auteurs', true);
        $champs_auteurs = isset($description_auteurs['field']) && is_array($description_auteurs['field'])
            ? array_keys($description_auteurs['field'])
            : array();
        $liste_segments_valides = array_values(array_filter(array_unique($liste_segments), function ($champ) use ($champs_auteurs) {
            return is_string($champ) && in_array($champ, $champs_auteurs, true);
        }));
        $liste_segments_obsoletes = array_values(array_diff($liste_segments, $liste_segments_valides));
        if ($liste_segments_obsoletes) {
            spip_log('Champs de segments obsoletes ignores : ' . implode(',', $liste_segments_obsoletes), 'association' . _LOG_INFO_IMPORTANTE);
        }
        $liste_segments = $liste_segments_valides;
        if (!$liste_segments) {
            return $flux;
        }
        // Protection contre injection et récupération sûre
        $query_auteur = sql_fetsel($liste_segments, "spip_auteurs", "email = " . sql_quote($email));

        if (!$query_auteur || !is_array($query_auteur)) {
            // Rien à faire si l'auteur n'existe pas
            return $flux;
        }

        $inscription = !empty($query_auteur['inscription']) ? affdate($query_auteur['inscription'], 'Y') : '';
        $validite = !empty($query_auteur['validite']) ? affdate($query_auteur['validite'], 'Y') : '';
        foreach ($liste_segments as $info_segment) {
            $val = isset($query_auteur[$info_segment]) ? $query_auteur[$info_segment] : '';
            // On verifie si il s'agit d'une valeur multiple (radio,checkbox ou selection_multiple) avec séparateur ","
            if (is_string($val) && strpos($val, ',') !== false) {
                $flux['data'][$info_segment] = explode(',', $val);
            } else {
                $flux['data'][$info_segment] = $val;
            }
        }
        if (!empty($query_auteur['statut_interne'])) {
            $flux['data']['statut_interne'] = $query_auteur['statut_interne'];
            $flux['data']['adherent'] = 'oui';
        } else {
            $flux['data']['adherent'] = 'non';
        }
        if (!empty($inscription)) {
            $flux['data']['inscription'] = $inscription;
        }
        if (!empty($validite)) {
            $flux['data']['validite'] = $validite;
        }

        if (isset($query_auteur['quartier_rattachement']) or isset($query_auteur['quartier_coeur'])) {
            $flux['data']['quartiers'] = array($query_auteur['quartier_rattachement'] ?? '',$query_auteur['quartier_coeur'] ?? '');
        }
    }
    return $flux;
}

/**
 * Indiquer si l'admin courant peut ignorer les champs extra obligatoires
 * pour un auteur cible donné.
 *
 * @param int $id_auteur
 * @return bool
 */
function association_i3_admin_peut_ignorer_obligatoires($id_auteur)
{
    $id_auteur = intval($id_auteur);
    if ($id_auteur <= 0) {
        return false;
    }

    $visiteur = (isset($GLOBALS['visiteur_session']) && is_array($GLOBALS['visiteur_session']))
        ? $GLOBALS['visiteur_session']
        : array();
    $id_session = intval(isset($visiteur['id_auteur']) ? $visiteur['id_auteur'] : 0);
    $est_admin = (isset($visiteur['statut']) && $visiteur['statut'] == '0minirezo');

    return ($est_admin && $id_session > 0 && $id_session != $id_auteur);
}

/**
 * Assouplir les obligations des champs extra inscription3 en amont,
 * au niveau de leur définition de saisie.
 *
 * Ce hook est appelé par inscription3 via le pipeline i3_definition_champs.
 *
 * @param array $flux
 * @return array
 */
function association_i3_definition_champs($flux)
{
    if (!is_array($flux)) {
        return $flux;
    }

    $form = _request('form');
    $exec = _request('exec');
    if ($form !== 'editer_auteur' && !in_array($exec, array('auteur', 'editer_auteur'), true)) {
        return $flux;
    }

    $id_cible = intval(_request('id_auteur'));
    if (!association_i3_admin_peut_ignorer_obligatoires($id_cible)) {
        return $flux;
    }

    foreach ($flux as $champ => $definition) {
        if (!isset($definition['options']) || !is_array($definition['options'])) {
            continue;
        }

        // Les champs coeur restent gérés à part dans i3_verifier_formulaire.
        if (in_array($champ, array('nom', 'email', 'login', 'pass', 'pass2'), true)) {
            continue;
        }

        $flux[$champ]['options']['obligatoire'] = false;
    }

    return $flux;
}

/**
 * Nettoyer récursivement les saisies pour retirer le caractère obligatoire
 * sur une liste de champs.
 *
 * @param array $saisies
 * @param array $champs_a_assouplir
 * @return array
 */
function association_assouplir_saisies_obligatoires($saisies, $champs_a_assouplir)
{
    if (!is_array($saisies) || !is_array($champs_a_assouplir) || !$champs_a_assouplir) {
        return $saisies;
    }

    foreach ($saisies as $k => $saisie) {
        if (!is_array($saisie)) {
            continue;
        }

        if (isset($saisie['saisies']) && is_array($saisie['saisies'])) {
            $saisies[$k]['saisies'] = association_assouplir_saisies_obligatoires($saisie['saisies'], $champs_a_assouplir);
        }

        $nom = isset($saisie['options']['nom']) ? $saisie['options']['nom'] : '';
        if ($nom && in_array($nom, $champs_a_assouplir, true)) {
            $saisies[$k]['options']['obligatoire'] = false;
            unset($saisies[$k]['options']['required']);
            unset($saisies[$k]['options']['aria-required']);
        }
    }

    return $saisies;
}

/**
 * Assouplir les saisies obligatoires de editer_auteur pour les admins
 * qui modifient la fiche d'un autre auteur.
 *
 * @param array $flux
 * @return array
 */
function association_formulaire_saisies($flux)
{
    $form = isset($flux['args']['form']) ? $flux['args']['form'] : '';
    if ($form !== 'editer_auteur') {
        return $flux;
    }

    if (!isset($flux['data']) || !is_array($flux['data'])) {
        return $flux;
    }

    $id_cible = intval(_request('id_auteur'));
    if (!$id_cible && isset($flux['args']['args'][0]) && is_numeric($flux['args']['args'][0])) {
        $id_cible = intval($flux['args']['args'][0]);
    }
    if (!association_i3_admin_peut_ignorer_obligatoires($id_cible)) {
        return $flux;
    }

    $lire_champs_obligatoires = charger_fonction('inscription3_champs_obligatoires', 'inc', true);
    if (!$lire_champs_obligatoires) {
        return $flux;
    }

    $exceptions = array('nom', 'email', 'login', 'pass', 'pass2');
    $champs = $lire_champs_obligatoires($id_cible, 'editer_auteur');
    if (!is_array($champs) || !$champs) {
        return $flux;
    }

    $champs_a_assouplir = array_values(array_diff($champs, $exceptions));
    if (!$champs_a_assouplir) {
        return $flux;
    }

    $flux['data'] = association_assouplir_saisies_obligatoires($flux['data'], $champs_a_assouplir);

    return $flux;
}

/**
 * Indiquer si un champ de requête est effectivement renseigné.
 * Gère les scalaires, tableaux et dates découpées jour/mois/année.
 *
 * @param string $champ
 * @return bool
 */
function association_champ_requete_renseigne($champ)
{
    if (array_key_exists($champ, $_FILES)) {
        $fichier = $_FILES[$champ];
        if (is_array($fichier) && !empty($fichier['name'])) {
            return true;
        }
    }

    $valeur = _request($champ);
    if (is_array($valeur)) {
        foreach ($valeur as $item) {
            if (is_array($item)) {
                foreach ($item as $sous_item) {
                    if (is_scalar($sous_item) && trim((string)$sous_item) !== '') {
                        return true;
                    }
                }
            } elseif (is_scalar($item) && trim((string)$item) !== '') {
                return true;
            }
        }
    } elseif (is_scalar($valeur) && trim((string)$valeur) !== '') {
        return true;
    }

    foreach (array('_jour', '_mois', '_annee') as $suffixe) {
        $partie = _request($champ . $suffixe);
        if (!is_scalar($partie) || trim((string)$partie) === '') {
            return false;
        }
    }

    return false;
}

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
function association_i3_verifier_formulaire($flux)
{
    $form = isset($flux['args']['form']) ? $flux['args']['form'] : '';
    if ($form !== 'editer_auteur') {
        return $flux;
    }

    $id_cible = intval(_request('id_auteur'));
    if (!$id_cible && isset($flux['args']['args'][0]) && is_numeric($flux['args']['args'][0])) {
        $id_cible = intval($flux['args']['args'][0]);
    }
    if ($id_cible <= 0) {
        return $flux;
    }

    if (!association_i3_admin_peut_ignorer_obligatoires($id_cible)) {
        return $flux;
    }

    $id_session = intval(isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : 0);

    if (!isset($flux['data']) || !is_array($flux['data'])) {
        return $flux;
    }

    $lire_champs_obligatoires = charger_fonction('inscription3_champs_obligatoires', 'inc', true);
    if (!$lire_champs_obligatoires) {
        return $flux;
    }

    $champs_obligatoires = $lire_champs_obligatoires($id_cible, 'editer_auteur');
    if (!is_array($champs_obligatoires) || !$champs_obligatoires) {
        return $flux;
    }

    $exceptions = array('nom', 'email', 'login', 'pass', 'pass2');
    $nb_erreurs_initial = count($flux['data']);
    $champs_retires = array();

    foreach ($champs_obligatoires as $champ) {
        if (in_array($champ, $exceptions, true)) {
            continue;
        }

        if (isset($flux['data'][$champ]) && !association_champ_requete_renseigne($champ)) {
            unset($flux['data'][$champ]);
            $champs_retires[] = $champ;
        }
    }

    if (
        $champs_retires
        && isset($flux['data']['message_erreur'])
        && !array_diff(array_keys($flux['data']), array('message_erreur'))
    ) {
        unset($flux['data']['message_erreur']);
    }

    if ($champs_retires) {
        association_log(
            'autorisations',
            'Bypass i3_verifier_formulaire applique sur editer_auteur',
            'info',
            array(
                'id_admin' => $id_session,
                'id_auteur_cible' => $id_cible,
                'nb_erreurs_initial' => $nb_erreurs_initial,
                'nb_erreurs_retires' => count($champs_retires),
                'champs_retires' => $champs_retires,
            )
        );
    }


    return $flux;
}

/**
 * Récupère les liens (id_categorie => montant) pour un événement donné.
 */
function association_get_liens_map($id_evenement)
{
    $id_evenement = intval($id_evenement);
    if (!$id_evenement) {
        return array();
    }

    // Utilise sql_allfetsel pour récupérer en une seule requête
    $rows = sql_allfetsel('id_categorie, montant', 'spip_asso_categories_activites_liens', 'id_evenement=' . $id_evenement);
    $map = array();
    foreach ($rows as $r) {
        $map[intval($r['id_categorie'])] = $r['montant'];
    }
    return $map;
}

/**
 * Synchroniser les liens de catégories/prix d'un événement source vers ses répétitions.
 * Optimisé pour ne faire que les INSERT/UPDATE/DELETE nécessaires.
 */
function association_sync_repetitions_tarifs($id_evenement)
{
    $id_evenement = intval($id_evenement);
    if (!$id_evenement) {
        return;
    }

    static $processing_sync = array();
    if (!empty($processing_sync[$id_evenement])) {
        association_log('sync', "association_sync_repetitions_tarifs: skip already processing id_evenement={$id_evenement}", 'info');
        return;
    }
    $processing_sync[$id_evenement] = true;

    // Vérifier l'événement source
    $evenement_source = sql_fetsel('id_evenement,id_evenement_source,payant', 'spip_evenements', 'id_evenement=' . $id_evenement);
    if (!$evenement_source
        || intval($evenement_source['id_evenement_source']) !== 0
        || intval($evenement_source['payant']) !== 1
    ) {
        unset($processing_sync[$id_evenement]);
        return;
    }

    // Récupérer la map des liens du source (id_categorie => montant)
    $liens_source_map = association_get_liens_map($id_evenement);
    if (empty($liens_source_map)) {
        // Rien à propager
        unset($processing_sync[$id_evenement]);
        return;
    }

    // Récupérer toutes les répétitions (id uniquement)
    $repetitions = sql_allfetsel('id_evenement', 'spip_evenements', 'id_evenement_source=' . $id_evenement);
    foreach ($repetitions as $rep_row) {
        $id_rep = intval($rep_row['id_evenement']);
        if (!$id_rep) {
            continue;
        }

        // Récupérer la map actuelle des liens sur la répétition
        $liens_rep_map = association_get_liens_map($id_rep);

        // Calculer diff : insert, update, delete
        $to_insert = array(); // cat => montant
        $to_update = array(); // cat => montant
        $to_delete = array(); // list of cat

        // Source -> rep : insert or update
        foreach ($liens_source_map as $cat => $mont) {
            if (!array_key_exists($cat, $liens_rep_map)) {
                // insérer seulement si montant non vide
                if ($mont !== '' && $mont !== null) {
                    $to_insert[$cat] = $mont;
                }
            } else {
                // mettre à jour si différent (comparaison en string pour prudence)
                if ((string)$liens_rep_map[$cat] !== (string)$mont) {
                    $to_update[$cat] = $mont;
                }
            }
        }
        // Rep contient des catégories absentes du source -> supprimer
        foreach ($liens_rep_map as $cat => $mont) {
            if (!array_key_exists($cat, $liens_source_map)) {
                $to_delete[] = $cat;
            }
        }

        // Appliquer les changements (simple, sans transaction)
        if (!empty($to_delete)) {
            sql_delete(
                'spip_asso_categories_activites_liens',
                'id_evenement=' . $id_rep . ' AND ' . sql_in('id_categorie', array_map('intval', $to_delete))
            );
        }

        foreach ($to_update as $cat => $mont) {
            sql_updateq(
                'spip_asso_categories_activites_liens',
                array('montant' => $mont),
                'id_evenement=' . $id_rep . ' AND id_categorie=' . intval($cat)
            );
        }

        foreach ($to_insert as $cat => $mont) {
            sql_insertq(
                'spip_asso_categories_activites_liens',
                array(
                    'id_evenement' => $id_rep,
                    'id_categorie' => intval($cat),
                    'montant'      => $mont
                )
            );
        }

        association_log('sync', "association_sync_repetitions_tarifs: synced id_evenement_source={$id_evenement} -> id_rep={$id_rep} (ins:" . count($to_insert) . " upd:" . count($to_update) . " del:" . count($to_delete) . ")", 'info');
    }

    unset($processing_sync[$id_evenement]);
}

function association_formulaire_verifier($flux)
{
    $form = $flux['args']['form'] ?? '';
    if (!in_array($form, array('inscription', 'editer_auteur'), true)) {
        return $flux;
    }

    $erreurs = isset($flux['data']) && is_array($flux['data']) ? $flux['data'] : array();

    $age_limit_config = lire_config('association_metas/meta_cfg_age_limit_enfants');
    $age_limit_config = is_scalar($age_limit_config) ? trim((string)$age_limit_config) : '';
    $age_limit = null;
    if ($age_limit_config !== '') {
        if (is_numeric($age_limit_config)) {
            $age_limit = intval($age_limit_config);
        } else {
            spip_log('meta_cfg_age_limit_enfants invalide: ' . $age_limit_config, 'association' . _LOG_ERREUR);
            return $flux;
        }
    }

    if ($age_limit === null || $age_limit <= 0) {
        return $flux;
    }

    foreach ($_REQUEST as $nom => $valeur) {
        if (!is_string($nom)) {
            continue;
        }
        if (stripos($nom, 'enfant') === false || stripos($nom, 'naissance') === false) {
            continue;
        }

        $date_val = '';
        if (is_scalar($valeur) && trim((string)$valeur) !== '') {
            $date_val = trim((string)$valeur);
        } else {
            $jour = _request($nom . '_jour');
            $mois = _request($nom . '_mois');
            $annee = _request($nom . '_annee');
            if (is_scalar($annee) && trim((string)$annee) !== '') {
                $date_val = sprintf(
                    '%04d-%02d-%02d',
                    intval($annee),
                    max(1, intval(is_scalar($mois) ? $mois : 0)),
                    max(1, intval(is_scalar($jour) ? $jour : 0))
                );
            }
        }

        if ($date_val === '') {
            continue;
        }

        $date_obj = false;
        foreach (array('Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d') as $format) {
            $date_test = DateTime::createFromFormat($format, $date_val);
            if ($date_test && $date_test->format($format) === $date_val) {
                $date_obj = $date_test;
                break;
            }
        }
        if (!$date_obj) {
            $ts = strtotime($date_val);
            if ($ts !== false) {
                $date_obj = new DateTime('@' . $ts);
                $date_obj->setTimezone(new DateTimeZone(date_default_timezone_get()));
            }
        }
        if (!$date_obj) {
            continue;
        }

        $age = (new DateTime())->diff($date_obj)->y;
        if ($age > $age_limit) {
            $erreurs[$nom] = _T('association:erreur_age_enfant', array('limite' => $age_limit));
        }
    }

    $flux['data'] = $erreurs;
    return $flux;
}

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
function association_corbeille_table_infos($flux) {
    $flux['mailsubscribers'] = [
        'statut'    => 'poubelle',
        'tableliee' => ['spip_mailsubscriptions', 'spip_mailsubscriptions_optins'],
    ];
    $flux['mailsubscribinglists'] = [
        'statut'    => 'poubelle',
        'tableliee' => ['spip_mailsubscriptions', 'spip_mailsubscriptions_optins'],
    ];
    return $flux;
}

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

