<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }

function autoriser_notifications_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = is_array($qui) ? $qui : ($GLOBALS['visiteur_session'] ?? array());
	if (function_exists('association_est_admin_complet') && function_exists('association_normalize_qui')) {
		return association_est_admin_complet(association_normalize_qui($qui));
	}
	return ($qui['statut'] ?? '') === '0minirezo' && empty($qui['restreint']) && empty($qui['restreint_id']);
}


function autoriser_newsletter_generer($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
}

function autoriser_newsletter_envoyer($faire, $type, $id, $qui, $opt) {
	if (isset($opt['test']) and $opt['test']) {
		return autoriser('modifier', $type, $id, $qui, $opt);
	}
	$qui = association_normalize_qui($qui);
	return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
}

function autoriser_newsletter_instituer($faire, $type, $id, $qui, $opt) {
	if (isset($opt['statut']) and $opt['statut'] === 'publie'){
		$qui = association_normalize_qui($qui);
		return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
	}
	return autoriser('modifier', $type, $id, $qui, $opt);
}

function autoriser_newsletter_modifier($faire, $type, $id, $qui, $opt) {
	static $baked = array();
	$qui = association_normalize_qui($qui);
	if (isset($opt['champ'])
		AND $champ=$opt['champ']
	  AND in_array($champ,array('titre','chapo','texte'))){
		if (!isset($baked[$id]))
			$baked[$id] = sql_getfetsel('baked','spip_newsletters','id_newsletter='.intval($id));
		if ($baked[$id])
			return false;
	}
	if (!isset($opt['statut']))
		$statut = sql_getfetsel("statut", "spip_newsletters", "id_newsletter=".intval($id));
	else
		$statut = $opt['statut'];
	if ($statut === 'publie') {
		return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
	}
	else {
		return in_array($qui['statut'], array('0minirezo', '1comite'));
	}
}
