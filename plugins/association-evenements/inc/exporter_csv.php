<?php

/*\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2015                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/charsets');
include_spip('inc/filtres');
include_spip('inc/texte');

/**
 * Exporter un champ pour un export CSV : pas de retour a la ligne,
 * et echapper les guillements par des doubles guillemets
 * @param string $champ
 * @return string
 */
function exporter_csv_champ($champ) {
	// Vérifier si $champ est null et le remplacer par une chaîne vide
	if ($champ === null) {
		$champ = '';
	}
	$champ = str_replace("\r", "\n", $champ);
	$champ = preg_replace(",[\n]+,ms", "\n", $champ);
	$champ = str_replace("\n", ', ', $champ);
	$champ = preg_replace(',[\s]+,ms', ' ', $champ);
	$champ = str_replace('"', '""', $champ);
	return '"' . $champ . '"';
}
/**
 * Exporter une ligne complete au format CSV, avec delimiteur fourni
 * @param array $ligne
 * @param string $delim
 * @return string
 */
function exporter_csv_ligne($ligne, $delim = ',', $importer_charset = null) {
	// Vérifier que $ligne est bien un tableau
	if (!is_array($ligne)) {
		return '';
	}

	$output = join($delim, array_map('exporter_csv_champ', $ligne)) . "\r\n";

	if ($importer_charset && $output && is_string($output) && strlen($output) > 0) {
		// Convertir avec gestion d'erreur
		$unicode = charset2unicode($output);
		if ($unicode !== null) {
			$html_unicode = html2unicode($unicode);
			if ($html_unicode !== null) {
				try {
					$output = unicode2charset($html_unicode, $importer_charset);
				} catch (Exception $e) {
					// En cas d'échec, garder la chaîne d'origine
				}
			}
		}
	}

	return $output;
}
/**
 * Exporte une ressource sous forme de fichier CSV
 * La ressource peut etre un tableau ou une resource SQL issue d'une requete
 * L'extension est choisie en fonction du delimiteur :
 * si on utilise ',' c'est un vrai csv avec extension csv
 * si on utilise ';' ou tabulation c'est pour E*cel, et on exporte en iso-truc, avec une extension .xls
 *
 * @param string $titre
 *   titre utilise pour nommer le fichier
 * @param array|resource $resource
 * @param string $delim
 *   delimiteur
 * @param array $entetes
 *   tableau d'en-tetes pour nommer les colonnes (genere la premiere ligne)
 * @param bool $envoyer
 *   pour envoyer le fichier exporte (permet le telechargement)
 * @return string
 */

function inc_exporter_csv_dist($titre, $resource, $delim = ';', $entetes = null, $envoyer = true) {
	$filename = preg_replace(',[^-_\w]+,', '_', translitteration(textebrut(typo($titre))));
	$liste_champs_extra = lire_config('champs_extras_spip_auteurs');
	$entete_array = [];

	// Ajout des champs de base qui manquent
	$entete_array['id_auteur'] = '#ID';
	$entete_array['statut'] = 'Statut';
	$entete_array['email'] = 'Email';

	$output = '';
	$fieldset = 'fieldset_';
	foreach ($liste_champs_extra as $champs_extra) {

		if (preg_match('#' . $fieldset . '#', $champs_extra['options']['nom']) and !empty($champs_extra['saisies'])) {
			foreach ($champs_extra['saisies'] as $champs_extra2) {
				$nom2 = $champs_extra2['options']['nom'];
				$label2 = $champs_extra2['options']['label'];
				$entete_array[$nom2] = textebrut($label2);
			}
		}
		$nom = $champs_extra['options']['nom'];
		$label = $champs_extra['options']['label'];
		$entete_array[$nom] = textebrut($label);
	}
	$charset = $importer_charset = 'UTF-8';
	$delim = ';';
	$extension = 'csv';
	$filename = "$filename.$extension";
	if ($entetes and is_array($entetes) and count($entetes)) {
		$liste_entetes = []; // Initialisation du tableau
		foreach ($entetes as $entete) {
			if (isset($entete_array[$entete])) { // Vérifier si la clé existe
				$entete_label = $entete_array[$entete];
				$liste_entetes[] = $entete_label;
			}
		}

		if (!empty($liste_entetes)) { // Vérifier que le tableau n'est pas vide
			$output = exporter_csv_ligne($liste_entetes, $delim, $importer_charset);
		}
	}
	// on passe par un fichier temporaire qui permet de ne pas saturer la memoire
	// avec les gros exports
	$fichier = sous_repertoire(_DIR_CACHE, 'export') . $filename;
	$fp = fopen($fichier, 'w');
	$length = 0;
	if (!empty($output)) {
		$length = fwrite($fp, $output);
	}
	while ($row = is_array($resource) ? array_shift($resource) : sql_fetch($resource)) {
		if (is_array($row) && !empty($row)) {
			$output = exporter_csv_ligne($row, $delim, $importer_charset);
			if ($output) {
				$length += fwrite($fp, $output);
			}
		}
	}
	fclose($fp);
	if ($envoyer) {
		header("Content-Type: text/comma-separated-values; charset=$charset");
		header("Content-Disposition: attachment; filename=$filename");
		// non supporte
		header("Content-Type: text/plain; charset=$charset");
		header("Content-Length: $length");
		ob_clean();
		flush();
		readfile($fichier);
	}
	return $fichier;
}
