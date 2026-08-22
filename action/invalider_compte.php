<?php
function action_invalider_compte_dist() {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $id_compte = $securiser_action();

    include_spip('inc/autoriser');
    if (!autoriser('modifier', 'asso_compte', intval($id_compte))) {
        return false;
    }

    if (intval($id_compte) > 0) {
        sql_updateq('spip_asso_comptes', array('vu' => 0), 'id_compte='.intval($id_compte));
    }
    // Redirection vers la page d'origine
    if ($redirect = _request('redirect')) {
        include_spip('inc/headers');

        $redirect = html_entity_decode($redirect, ENT_QUOTES, 'UTF-8'); // -> & au lieu de &amp;
        redirige_par_entete($redirect);
    }else{
        return true;
    }

}
