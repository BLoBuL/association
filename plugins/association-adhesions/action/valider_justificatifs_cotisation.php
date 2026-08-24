<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function action_valider_justificatifs_cotisation_dist() {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();
    list($id_compte, $mode) = array_pad(explode('-', (string)$arg, 2), 2, 'oui');
    $id_compte = intval($id_compte);

    if (!$id_compte || !autoriser('modifier', 'asso_compte', $id_compte)) {
        include_spip('inc/minipres');
        minipres(_T('info_acces_interdit'));
        return;
    }

    include_spip('inc/justificatifs_cotisation');
    $resultat = association_justificatifs_cotisation_marquer($id_compte, $mode !== 'non');
    if ($resultat['ok'] && $mode === 'non') {
        // Uniquement "A revoir" informe l'adherent, afin d'eviter un email
        // supplementaire lors d'un simple controle positif.
        include_spip('inc/cotisations');
        include_spip('inc/cotisations_stockage');
        $compte = association_cotisation_lire_par_compte($id_compte);
        if ($compte) {
            $categorie = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . intval($compte['id_categorie'] ?? 0));
            $transaction = !empty($compte['id_transaction'])
                ? sql_fetsel('*', 'spip_transactions', 'id_transaction=' . intval($compte['id_transaction']))
                : array();
            notifier_cotisation_adherent($compte, $categorie ?: array(), $transaction ?: array(), 'justificatifs-a-revoir');
        }
    }
    $retour = _request('redirect') ?: generer_url_ecrire('editer_asso_cotisation', 'id_compte=' . $id_compte);
    // #SELF est échappé pour le HTML lorsqu'il est imbriqué dans
    // #URL_ACTION_AUTEUR. Restaurer les séparateurs avant de construire la
    // redirection HTTP, sinon certains navigateurs refusent la réponse.
    $retour = str_replace('&amp;', '&', $retour);
    $retour = parametre_url(
        $retour,
        $resultat['ok'] ? 'justificatifs_ok' : 'justificatifs_erreur',
        $resultat['message'],
        '&'
    );
    redirige_par_entete($retour);
}
