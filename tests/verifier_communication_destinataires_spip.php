<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre execute dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/email_collectif');
$auteurs = sql_allfetsel('id_auteur,email', 'spip_auteurs', "email<>''", '', 'id_auteur');
$attendus = array();
foreach ($auteurs as $auteur) {
	$email = strtolower(trim((string) ($auteur['email'] ?? '')));
	if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$attendus[$email] = $email;
	}
}
$obtenus = association_email_collectif_resoudre_destinataires(array_column($auteurs, 'id_auteur'), array());
$obtenus = array_combine($obtenus, $obtenus) ?: array();
ksort($attendus);
ksort($obtenus);
if ($attendus !== $obtenus) {
	fwrite(STDERR, "La resolution distribuee des destinataires differe de la lecture historique.\n");
	exit(1);
}
echo json_encode(array('ok' => true, 'auteurs_lus' => count($auteurs), 'emails_uniques' => count($obtenus))) . "\n";
