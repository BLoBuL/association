<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_adhesions_association_maintenance_bdd_preparer($flux) {
	include_spip('inc/association_adhesions_maintenance');
	$options = (array) ($flux['args']['options'] ?? array());
	$actions = (array) ($options['actions'] ?? array());
	$maintenant = intval($flux['args']['maintenant'] ?? time());
	$limite = date('Y-m-d H:i:s', $maintenant - (intval($options['jours_inactivite'] ?? 365) * 86400));
	$inactifs = asso_recuperer_auteurs_inactifs($limite, intval($options['lot'] ?? 1000));
	$resume = is_array($flux['data']['resume'] ?? null) ? $flux['data']['resume'] : array();
	$resume['auteurs_inactifs'] = array('nombre' => count($inactifs), 'ids' => $inactifs);
	if ($inactifs) {
		list($sans_paiements, $avec_paiements) = asso_separer_auteurs_par_encaissements($inactifs);
		$resume['auteurs_sans_paiements'] = array('nombre' => count($sans_paiements), 'ids' => $sans_paiements);
		$resume['supprimer_auteurs'] = !empty($actions['supprimer_auteurs_sans_paiements']) && $sans_paiements
			? asso_supprimer_auteurs($sans_paiements, (bool) ($options['dry_run'] ?? true))
			: array('skipped' => true);
		$resume['auteurs_avec_paiements'] = array('nombre' => count($avec_paiements), 'ids' => $avec_paiements);
		$resume['anonymiser_auteurs'] = !empty($actions['anonymiser_auteurs_avec_paiements']) && $avec_paiements
			? asso_anonymiser_auteurs($avec_paiements, (bool) ($options['dry_run'] ?? true))
			: array('skipped' => true);
	}
	$flux['data']['resume'] = $resume;
	$flux['data']['inactifs'] = $inactifs;
	return $flux;
}

/**
 * Planifie le contrôle des échéances et privilèges d'adhésion.
 */
function association_adhesions_taches_generales_cron($taches) {
	$taches['association_taches_generales'] = 60 * 60 * 6;
	return $taches;
}

/**
 * Ajouter l'accès à la migration vers Familles depuis sa page privée.
 */
function association_adhesions_affiche_milieu($flux) {
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

/**
 * Ajoute l'audit sans envoi des notifications de cotisation à la page commune.
 */
function association_adhesions_association_notifications_audit_html($flux) {
	include_spip('inc/notifications_cotisations_audit');
	$flux['data'] = ($flux['data'] ?? '') . notifications_cotisations_audit_html();
	return $flux;
}

function association_adhesions_saisies_retirer_obligatoire(array $saisies): array {
    foreach ($saisies as &$saisie) {
        if (isset($saisie['options']['obligatoire'])) {
            $saisie['options']['obligatoire'] = 'non';
        }
        if (!empty($saisie['saisies']) && is_array($saisie['saisies'])) {
            $saisie['saisies'] = association_adhesions_saisies_retirer_obligatoire($saisie['saisies']);
        }
    }
    unset($saisie);
    return $saisies;
}

function association_adhesions_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_adhesions_rgpd');
	$flux['data']['cotisations'] = association_adhesions_rgpd_export_cotisations(
		intval($flux['args']['id_auteur'] ?? 0)
	);
	return $flux;
}

function association_adhesions_formulaire_charger($flux) {
    if (
        ($flux['args']['form'] ?? '') === 'editer_auteur'
        && ($GLOBALS['visiteur_session']['statut'] ?? '') === '0minirezo'
        && !empty($flux['data']['_saisies'])
    ) {
        $flux['data']['_saisies'] = association_adhesions_saisies_retirer_obligatoire($flux['data']['_saisies']);
    }
    return $flux;
}

function association_adhesions_association_configuration_categorie_entreprise($flux) {
	$flux['data'] = (bool) sql_getfetsel('id_categorie', 'spip_asso_categories_adherents', "type_adherent='entreprise'");
	return $flux;
}

function association_adhesions_association_configuration_navigation($flux) {
	$flux['data']['adhesion'] = ['ordre' => 20, 'label' => 'association_config:navigation_config_adhesion'];
	$flux['data']['entreprise'] = ['ordre' => 30, 'label' => 'association_config:navigation_config_entreprise'];
	$flux['data']['affichage_prive'] = ['ordre' => 70, 'label' => 'association_config:navigation_config_affichage_prive'];
	$flux['data']['affichage_public'] = ['ordre' => 80, 'label' => 'association_config:navigation_config_affichage_public'];
	return $flux;
}

function association_adhesions_pre_insertion($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_auteurs') {
        $flux['data']['inscription'] = date('Y-m-d H:i:s');
        $flux['data']['statut_interne'] = 'prospect';
    }
    return $flux;
}

function association_adhesions_post_edition($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_auteurs' && ($id_auteur = intval($flux['args']['id_objet'] ?? 0)) && test_plugin_actif('gis')) {
        include_spip('inc/fonctions/gis_auteur');
        gis_auteur($id_auteur, 'modification');
    }
    return $flux;
}

function association_adhesions_afficher_contenu_objet($flux) {
    if (($flux['args']['type'] ?? '') !== 'auteur' || !($id_auteur = intval($flux['args']['id_objet'] ?? 0))) {
        return $flux;
    }
    $date = sql_getfetsel('inscription', 'spip_auteurs', 'id_auteur=' . $id_auteur);
    if ($date) {
        $date = ($date === '0000-00-00 00:00:00') ? _T('association_adhesions:non_renseignee') : affdate($date);
        $flux['data'] .= '<div>' . propre(_T('association_adhesions:date_inscription') . ' : ' . $date) . '</div>';
    }
    return $flux;
}

function association_adhesions_formulaire_verifier($flux) {
    $form = $flux['args']['form'] ?? '';
    if (!in_array($form, array('inscription', 'editer_auteur'), true)) {
        return $flux;
    }
    $erreurs = is_array($flux['data'] ?? null) ? $flux['data'] : array();
    $configuration = lire_config('association_metas/meta_cfg_age_limit_enfants');
    $configuration = is_scalar($configuration) ? trim((string) $configuration) : '';
    if ($configuration === '' || !is_numeric($configuration) || ($limite = intval($configuration)) <= 0) {
        if ($configuration !== '' && !is_numeric($configuration)) {
            spip_log('meta_cfg_age_limit_enfants invalide: ' . $configuration, 'association' . _LOG_ERREUR);
        }
        return $flux;
    }
    foreach ($_REQUEST as $nom => $valeur) {
        if (!is_string($nom) || stripos($nom, 'enfant') === false || stripos($nom, 'naissance') === false) {
            continue;
        }
        $date = is_scalar($valeur) ? trim((string) $valeur) : '';
        if ($date === '' && is_scalar(_request($nom . '_annee')) && trim((string) _request($nom . '_annee')) !== '') {
            $date = sprintf('%04d-%02d-%02d', intval(_request($nom . '_annee')), max(1, intval(_request($nom . '_mois'))), max(1, intval(_request($nom . '_jour'))));
        }
        if ($date === '' || ($timestamp = strtotime($date)) === false) {
            continue;
        }
        $naissance = new DateTime('@' . $timestamp);
        $naissance->setTimezone(new DateTimeZone(date_default_timezone_get()));
        if ((new DateTime())->diff($naissance)->y > $limite) {
            $erreurs[$nom] = _T('association_adhesions:erreur_age_enfant', array('limite' => $limite));
        }
    }
    $flux['data'] = $erreurs;
    return $flux;
}

function association_adhesions_association_compta_cotisation_synchroniser($flux) {
    $id_compte = intval($flux['args']['id_compte'] ?? 0);
    if (!$id_compte) {
        return $flux;
    }
    include_spip('inc/cotisations_stockage');
    $flux['data'] = association_cotisation_synchroniser_depuis_compte(
        $id_compte,
        is_array($flux['args']['donnees'] ?? null) ? $flux['args']['donnees'] : array()
    );
    return $flux;
}


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

function association_saisies_afficher_si_saisies($saisies)
{
    if (!is_array($saisies) || !$saisies) {
        return $saisies;
    }

    include_spip('inc/saisies');
    $saisies_par_nom = saisies_lister_par_nom($saisies);
    return association_saisies_retirer_conditions_orphelines($saisies, $saisies_par_nom);
}

function association_saisies_retirer_conditions_orphelines($saisies, $saisies_par_nom)
{
    foreach ($saisies as $cle => $saisie) {
        if (!is_array($saisie)) {
            continue;
        }

        $condition = (string) ($saisie['options']['afficher_si'] ?? '');
        if ($condition !== '' && preg_match_all('/@([^@]+)@/', $condition, $references)) {
            foreach ($references[1] as $reference) {
                $nom = preg_replace('/\[.*$/', '', $reference);
                if (
                    strpos($nom, 'config:') !== 0
                    && strpos($nom, 'plugin:') !== 0
                    && !isset($saisies_par_nom[$nom])
                ) {
                    unset($saisies[$cle]['options']['afficher_si']);
                    break;
                }
            }
        }

        if (!empty($saisie['saisies']) && is_array($saisie['saisies'])) {
            $saisies[$cle]['saisies'] = association_saisies_retirer_conditions_orphelines(
                $saisie['saisies'],
                $saisies_par_nom
            );
        }
    }

    return $saisies;
}

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
