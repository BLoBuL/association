<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Fichier utilitaire pour la normalisation et la collecte des adresses email
 * utilisées par le système de notifications du plugin association.
 *
 * Nommage en français conformément aux conventions du projet.
 */

/**
 * Collecter et normaliser les destinataires administrateurs configurés pour les notifications
 * issues d'Inscription3 (admin_notifications et destinataire_supp_notifications).
 * Retourne un tableau d'emails (ou false si aucun valide).
 *
 * @return array|false
 */
function association_collecter_destinataires_admins() {
	$liste = [];

	// admins configurés (inscription3)
	$id_admins = lire_config('inscription3/admin_notifications');
	if (is_array($id_admins) && count($id_admins)) {
		$emails_admin = sql_allfetsel(
			'email,input_email_membres_asso',
			'spip_auteurs',
			'statut="0minirezo" and ' . sql_in('id_auteur', $id_admins)
		);
		foreach ($emails_admin as $row) {
			if (!empty($row['input_email_membres_asso'])) {
				$norm = parser_emails_depuis_config($row['input_email_membres_asso']);
				if ($norm) {
					$liste = array_merge($liste, $norm);
					continue;
				}
			}
			if (!empty($row['email'])) {
				$norm = parser_emails_depuis_config($row['email']);
				if ($norm) {
					$liste = array_merge($liste, $norm);
				}
			}
		}
	}

	// destinataires supplémentaires configurés (inscription3)
	$dest_supp = lire_config('inscription3/destinataire_supp_notifications');
	if (!empty($dest_supp)) {
		$norm = parser_emails_depuis_config($dest_supp);
		if ($norm) {
			$liste = array_merge($liste, $norm);
		}
	}

	$liste = array_values(array_unique($liste));
	if (empty($liste)) {
		association_log('notifications', 'association_collecter_destinataires_admins: aucun destinataire admin valide trouve', 'critique');
		return false;
	}

	return $liste;
}
