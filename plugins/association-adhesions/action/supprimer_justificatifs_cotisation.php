<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function action_supprimer_justificatifs_cotisation_dist() {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    [$id_compte, $id_document] = array_pad(explode('-', (string)$securiser_action(), 2), 2, 0);
    $id_compte = intval($id_compte);
    $id_document = intval($id_document);

    if (!$id_compte || !autoriser('modifier', 'asso_compte', $id_compte)) {
        include_spip('inc/minipres');
        minipres(_T('info_acces_interdit'));
        return;
    }

    include_spip('inc/justificatifs_cotisation');
    $resultat = association_justificatifs_cotisation_supprimer($id_compte, $id_document);
    $retour = _request('redirect') ?: generer_url_ecrire('editer_asso_cotisation', 'id_compte=' . $id_compte);
    $retour = str_replace('&amp;', '&', $retour);
    $retour = parametre_url(
        $retour,
        $resultat['ok'] ? 'justificatifs_ok' : 'justificatifs_erreur',
        $resultat['message'],
        '&'
    );
    redirige_par_entete($retour);
}
