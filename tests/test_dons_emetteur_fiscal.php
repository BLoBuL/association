<?php
define('_ECRIRE_INC_VERSION', 1);
function include_spip($fichier) {}
function _T($texte) { return $texte; }
function lire_config($chemin, $defaut) { return $GLOBALS['configuration'][basename($chemin)] ?? $defaut; }
require dirname(__DIR__) . '/plugins/association-dons/inc/association_dons_recu_fiscal.php';
require dirname(__DIR__) . '/plugins/association-dons/formulaires/inc/configurer_association_dons.php';
$GLOBALS['configuration'] = [];
if (association_dons_recu_fiscal_emetteur()['complet']) { throw new RuntimeException('Émetteur vide accepté'); }
foreach (['nom', 'rue', 'cp', 'ville', 'pays', 'num_enregistrement', 'recu_fiscal_objet', 'recu_fiscal_qualite', 'recu_fiscal_signataire_nom', 'recu_fiscal_signataire_fonction'] as $cle) {
	$GLOBALS['configuration'][$cle] = 'RECETTE ' . $cle;
}
$emetteur = association_dons_recu_fiscal_emetteur();
if (!$emetteur['complet'] || $emetteur['emetteur']['nom'] !== 'RECETTE nom') { throw new RuntimeException('Identité commune non reprise'); }
$GLOBALS['configuration']['recu_fiscal_qualite'] = [];
if (association_dons_recu_fiscal_emetteur()['manquants'] !== ['qualite']) { throw new RuntimeException('Qualité invalide acceptée'); }
if (count(association_dons_configurer_saisies('info')[0]['saisies']) !== 4 || association_dons_configurer_saisies('maintenance_bdd')) { throw new RuntimeException('Panneau incorrect'); }
echo "OK : identité mutualisée, précisions fiscales et configuration incomplète détectée.\n";
