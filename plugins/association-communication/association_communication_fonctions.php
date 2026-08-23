<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }

// Les tableaux de notifications sont inclus depuis plusieurs plugins metier.
// Charger leurs filtres depuis le fichier de fonctions global du plugin permet
// a SPIP de les connaitre avant de compiler chaque squelette inclus.
include_spip('prive/squelettes/contenu/notifications_fonctions');

/**
 * Assemble les audits de notifications fournis par les plugins métier.
 */
function association_notifications_audits_html() {
	$flux = pipeline('association_notifications_audit_html', array('data' => ''));
	return is_array($flux) ? (string) ($flux['data'] ?? '') : '';
}


function association_condition_auteurs_newsletter($statut_interne) {
    return "statut IN ('6forum','1comite','0minirezo') AND statut_interne=" . sql_quote($statut_interne);
}

function preparer_liste_auteurs_newsletter($statut_interne){
$where = association_condition_auteurs_newsletter($statut_interne);
$auteurs = sql_allfetsel("CONCAT(nom_famille,' ',prenom) AS nom,email", "spip_auteurs", $where );

return $auteurs;
}

function mailsubscribers_synchro_list_newsletter_statut_interne_ok() {
	$auteurs = preparer_liste_auteurs_newsletter('ok');
	return $auteurs;
}

function mailsubscribers_synchro_list_newsletter_statut_interne_prospect() {
	$auteurs = preparer_liste_auteurs_newsletter('prospect');
	return $auteurs;
}

function mailsubscribers_synchro_list_newsletter_statut_interne_echu() {
	$auteurs = preparer_liste_auteurs_newsletter('echu');
	return $auteurs;
}

function mailsubscribers_synchro_list_newsletter_statut_interne_relance() {
	$auteurs = preparer_liste_auteurs_newsletter('relance');
	return $auteurs;
}

function mailsubscribers_synchro_list_newsletter_liste_conjoints() {
$where = "statut IN ('6forum','1comite','0minirezo') AND email_conjoint != '' AND statut_interne=" . sql_quote("ok");
$auteurs = sql_allfetsel("CONCAT(nom_conjoint,' ',prenom_conjoint) AS nom,email_conjoint AS email", "spip_auteurs", $where);

return $auteurs;
}

function formulaires_editer_newsletter_traiter($id_newsletter='new', $retour='', $lier_trad=0, $config_fonc='newsletter_edit_config', $row=array(), $hidden=''){
$baked = 1;
$statut = (intval($id_newsletter)?sql_getfetsel('statut','spip_newsletters','id_newsletter='.intval($id_newsletter)):'prepa');
if (in_array($statut,array('prepa','prop','prog')))
$baked = _request('baked');
if ($baked){
// pas de modif des contenu editoriaux si on est cuit
set_request('chapo');
set_request('texte');
set_request('patron');
}
else {
// pas de modif des contenues bruts si on est en preparation
set_request('html_email');
set_request('html_page');
set_request('texte_email');
}

// AJOUT BLOBUL 15/18/2018
// CORRIGE LE BUG d"INCOMPATIBILITE DE CEXTRA DANS LA PROGRAMMATION DES NEWSLETTER
if (_request('formulaire_action') =='programmer_newsletter'){
unset ($_POST['date_premier_jour_calendrier']);
unset ($_POST['date_dernier_jour_calendrier']);
unset ($_POST['cextra_date_premier_jour_calendrier']);
unset ($_POST['cextra_date_dernier_jour_calendrier']);
}
// FIN

$res = formulaires_editer_objet_traiter('newsletter',$id_newsletter,'',$lier_trad,$retour,$config_fonc,$row,$hidden);
if (!$baked AND $res['id_newsletter']) {
// mettre a jour les liens vers les articles selectionnes
$possibles = array('article'=>'*','rubrique'=>'*');
$liens = array();
if ($selection = _request('selection_edito')){
    foreach ($selection as $s){
        $s = explode("|",$s);
        list($objet,$id_objet) = $s;
        if (isset($possibles[$objet])){
            $liens[$objet][] = $id_objet;
        }
    }
}
include_spip("action/editer_liens");
objet_dissocier(array("newsletter"=>$res['id_newsletter']),$possibles);
if (count($liens))
    objet_associer(array("newsletter"=>$res['id_newsletter']),$liens);
// regenerer le html et texte...
// sauf si c'est une nl prog (statut=prog)
if(!_request('statut')=='prog'){
    $generer_newsletter = charger_fonction("generer_newsletter","action");
    $generer_newsletter($res['id_newsletter']);
}
}
return $res;
}
