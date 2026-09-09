<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Le module appelant déclare SpiPDF et contrôle le jeton et les droits.
 */
function association_pdf_texte($valeur): string {
	// SpiPDF 2.2 décode les entités avant mPDF : deux couches sont nécessaires.
	$texte = is_scalar($valeur) ? (string) $valeur : '';
	return htmlspecialchars(
		htmlspecialchars($texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true),
		ENT_QUOTES | ENT_SUBSTITUTE,
		'UTF-8',
		true
	);
}

function association_pdf_envoyer(string $fond, array $contexte, string $nom): void {
	include_spip('inc/spipdf');
	$contenu = spipdf_html2pdf(recuperer_fond($fond, $contexte), false, $contexte);
	// SpiPDF échappe les séquences PHP pour le cache. Une action sert le flux binaire.
	$ancien_html = $GLOBALS['html'] ?? null;
	$GLOBALS['html'] = false;
	$contenu = spipdf_affichage_final($contenu);
	$GLOBALS['html'] = $ancien_html;
	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nom) . '.pdf"');
	header('Cache-Control: private, no-store');
	header('X-Content-Type-Options: nosniff');
	echo $contenu;
}
