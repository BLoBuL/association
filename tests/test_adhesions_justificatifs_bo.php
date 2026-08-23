<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['test_liens'] = array(
    array('id_document' => 1, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'non'),
    array('id_document' => 2, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'non'),
);
function _T($cle) { return $cle; }
function autoriser($faire, $type, $id) { return true; }
function sql_quote($valeur) { return "'" . addslashes($valeur) . "'"; }
function sql_countsel($table, $where) {
    if (preg_match('/id_document=(\d+)/', $where, $doc)) {
        $id_document = intval($doc[1]);
        return count(array_filter($GLOBALS['test_liens'], function ($l) use ($where, $id_document) {
            if ($l['id_document'] !== $id_document) return false;
            if (!str_contains($where, 'NOT (')) return true;
            preg_match('/id_objet=(\d+)/', $where, $objet);
            return !($l['objet'] === 'compte' && $l['id_objet'] === intval($objet[1] ?? 0));
        }));
    }
    $attendu_vu = str_contains($where, "vu='oui'");
    return count(array_filter($GLOBALS['test_liens'], function ($l) use ($where, $attendu_vu) {
        preg_match('/id_objet=(\d+)/', $where, $m);
        return $l['id_objet'] === intval($m[1] ?? 0) && (!$attendu_vu || $l['vu'] === 'oui');
    }));
}
function sql_allfetsel($select, $table, $where) {
    preg_match('/id_objet=(\d+)/', $where, $m);
    preg_match('/id_document=(\d+)/', $where, $doc);
    return array_map(
        fn($l) => array('id_document' => $l['id_document']),
        array_values(array_filter(
            $GLOBALS['test_liens'],
            fn($l) => $l['objet'] === 'compte'
                && $l['id_objet'] === intval($m[1] ?? 0)
                && (empty($doc[1]) || $l['id_document'] === intval($doc[1]))
        ))
    );
}
function sql_updateq($table, $valeurs, $where) {
    preg_match('/id_objet=(\d+)/', $where, $m);
    foreach ($GLOBALS['test_liens'] as &$l) if ($l['id_objet'] === intval($m[1] ?? 0)) $l['vu'] = $valeurs['vu'];
    unset($l);
    return true;
}
function include_spip($fichier) {
    $GLOBALS['test_include_spip'][] = $fichier;
}
function supprimer_lien_document($id_document, $objet, $id_objet, $supprime, $check) {
    $GLOBALS['test_liens'] = array_values(array_filter(
        $GLOBALS['test_liens'],
        fn($l) => !($l['id_document'] === $id_document && $l['objet'] === $objet && $l['id_objet'] === $id_objet)
    ));
    return true;
}
function action_supprimer_document_dist($id_document) {
	if (!empty($GLOBALS['test_echec_suppression'])) {
		return false;
	}
    $GLOBALS['test_liens'] = array_values(array_filter(
        $GLOBALS['test_liens'],
        fn($l) => $l['id_document'] !== $id_document
    ));
    $GLOBALS['test_documents_supprimes'][] = $id_document;
    return true;
}
function objet_associer($source, $cible) {
	$GLOBALS['test_liens'][] = array(
		'id_document' => intval($source['document']),
		'id_objet' => intval($cible['compte']),
		'objet' => 'compte',
		'vu' => 'non',
	);
	return true;
}
function suivre_invalideur($quoi) { return true; }
function test_assert($condition, $message) {
    if (!$condition) { echo "ECHEC: $message\n"; exit(1); }
    echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/justificatifs_cotisation.php';
$etat = association_justificatifs_cotisation_etat(20);
test_assert($etat['complet'] && !$etat['controle'], 'deux documents non vus sont prêts à contrôler');
$resultat = association_justificatifs_cotisation_marquer(20, true);
test_assert($resultat['ok'] && association_justificatifs_cotisation_etat(20)['controle'], 'la validation marque tous les liens document comme contrôlés');
test_assert(in_array('inc/invalideur', $GLOBALS['test_include_spip'] ?? array(), true), 'la validation charge le suivi d invalidation SPIP');
$resultat = association_justificatifs_cotisation_marquer(20, false);
test_assert($resultat['ok'] && !association_justificatifs_cotisation_etat(20)['controle'], 'le retour à revoir est possible');
$GLOBALS['test_liens'] = array_slice($GLOBALS['test_liens'], 0, 1);
test_assert(!association_justificatifs_cotisation_marquer(20, true)['ok'], 'un dossier incomplet ne peut pas être validé');
$source_squelette = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/prive/inclure/justificatifs_cotisation.html');
$source_helper = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/inc/justificatifs_cotisation.php');
test_assert(
    str_contains($source_helper, "autoriser('modifier', 'document', \$document_id)")
        && str_contains($source_helper, "objet_associer(array('document' => \$document_id), array('compte' => \$id_compte))"),
    'un échec de suppression réassocie le document après le contrôle préalable'
);
test_assert(str_contains($source_squelette, 'justificatifs-entete') && str_contains($source_squelette, 'justificatifs-pied'), 'le bloc de contrôle possède une hiérarchie visuelle dédiée');
test_assert(str_contains($source_squelette, 'justificatif-description') && str_contains($source_squelette, 'justificatif-statut'), 'chaque document sépare description et statut');
test_assert(str_contains($source_squelette, 'supprimer_justificatifs_cotisation') && str_contains($source_squelette, 'justificatifs_suppression_confirmation'), 'la suppression définitive exige une confirmation');
test_assert(str_contains($source_squelette, '#ENV{id_compte}-#ID_DOCUMENT') && str_contains($source_squelette, '#ENV{id_compte}-0'), 'le bloc propose une suppression par document et une suppression globale');
$source_ligne = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/prive/objets/liste/item_cotisation_adherent.html');
test_assert(str_contains($source_ligne, 'cotisation-statut-stack') && !str_contains($source_ligne, 'cotisation-statut-principal') && str_contains($source_ligne, 'justificatifs-badge'), 'la liste masque le libellé métier et conserve le contrôle documentaire');
test_assert(str_contains($source_ligne, 'fa-user-plus') && str_contains($source_ligne, 'fa-rotate') && str_contains($source_ligne, 'type-inscription-label'), 'les types d inscription utilisent des icônes Font Awesome accessibles');
$source_liste = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/prive/squelettes/contenu/cotisations.html');
test_assert(str_contains($source_liste, 'cotisations-legende') && str_contains($source_liste, 'statut_cotis_ok') && str_contains($source_liste, 'statut_cotis_demande') && str_contains($source_liste, 'statut_cotis_attente'), 'la liste explique les couleurs de statut dans une légende');
$GLOBALS['test_liens'] = array(
    array('id_document' => 1, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'oui'),
    array('id_document' => 2, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'oui'),
);
$resultat = association_justificatifs_cotisation_supprimer(20, 1);
test_assert($resultat['ok'] && count($GLOBALS['test_liens']) === 1 && $GLOBALS['test_liens'][0]['id_document'] === 2, 'la suppression individuelle conserve les autres justificatifs');
$resultat = association_justificatifs_cotisation_supprimer(20);
test_assert($resultat['ok'] && !$GLOBALS['test_liens'], 'la suppression définitive retire tous les justificatifs de la cotisation');
test_assert(($GLOBALS['test_documents_supprimes'] ?? array()) === array(1, 2), 'la suppression définitive traite chaque fichier');
$GLOBALS['test_liens'] = array(
    array('id_document' => 3, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'oui'),
    array('id_document' => 3, 'id_objet' => 40, 'objet' => 'article', 'vu' => 'oui'),
);
$resultat = association_justificatifs_cotisation_supprimer(20);
test_assert(!$resultat['ok'] && count($GLOBALS['test_liens']) === 2, 'un document partagé bloque toute suppression destructive');
$GLOBALS['test_liens'] = array(
	array('id_document' => 4, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'oui'),
);
$GLOBALS['test_echec_suppression'] = true;
$resultat = association_justificatifs_cotisation_supprimer(20);
test_assert(!$resultat['ok'] && count($GLOBALS['test_liens']) === 1, 'un échec SPIP réassocie le justificatif à sa cotisation');
echo "Tests BO justificatifs terminés.\n";
