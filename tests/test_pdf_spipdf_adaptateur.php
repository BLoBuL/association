<?php

define('_ECRIRE_INC_VERSION', 1);
function include_spip($chemin) { if ($chemin !== 'inc/spipdf') { throw new RuntimeException('Moteur inattendu'); } }
function recuperer_fond($fond, $contexte) { return '<page lib_pdf="mpdf8">Élodie</page>'; }
function spipdf_html2pdf($html, $file, $contexte) {
	if ($file !== false || !str_contains($html, 'mpdf8')) { throw new RuntimeException('Écriture persistante ou moteur incorrect'); }
	return "%PDF-test<\2\2?";
}
function spipdf_affichage_final($contenu) {
	if ($GLOBALS['html'] !== false) { throw new RuntimeException('Flux non binaire'); }
	return str_replace("<\2\2?", '<?', $contenu);
}
require dirname(__DIR__) . '/inc/association_pdf.php';
foreach (['adhesions/association_adherents', 'evenements/association_evenements'] as $modele) {
	[$module, $fond] = explode('/', $modele);
	$html_modele = file_get_contents(dirname(__DIR__) . '/plugins/association-' . $module . '/prive/pdf/' . $fond . '.html');
	if (preg_match('/#VALEUR(?!\*\*)/', $html_modele)) {
		throw new RuntimeException('SPIP 4.4 ne doit pas supprimer le balisage littéral avant son échappement PDF');
	}
}
$attaque = '<img src="https://example.test/image">Élodie & test</img>';
$nettoye_spipdf = html_entity_decode(association_pdf_texte($attaque), ENT_NOQUOTES, 'UTF-8');
if (str_contains($nettoye_spipdf, '<img') || html_entity_decode($nettoye_spipdf, ENT_QUOTES, 'UTF-8') !== $attaque) {
	throw new RuntimeException('Le nettoyage SpiPDF réactive le HTML des données');
}
$GLOBALS['html'] = true;
ob_start();
association_pdf_envoyer('prive/pdf/association_evenements', [], 'inscriptions-42');
$contenu = ob_get_clean();
if ($contenu !== '%PDF-test<?' || $GLOBALS['html'] !== true) { throw new RuntimeException('Flux corrompu ou contexte non restauré'); }
echo "OK : adaptateur SpiPDF sans stockage, déséchappement du flux et restauration du contexte.\n";
