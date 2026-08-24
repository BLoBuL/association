<?php

$racine_depot = dirname(__DIR__);
$racine_plugins = is_dir($racine_depot . '/plugins/association-compta')
	? $racine_depot . '/plugins'
	: dirname($racine_depot);
$pages = array(
	'destinations' => array($racine_plugins . '/association-compta/prive/squelettes/contenu/', 'association_compta:destination_comptable'),
	'dons' => array($racine_plugins . '/association-dons/prive/squelettes/contenu/', 'association_dons:tous_les_dons'),
	'ventes' => array($racine_plugins . '/association-ventes/prive/squelettes/contenu/', 'association_ventes:toutes_les_ventes'),
	'ressources' => array($racine_plugins . '/association-prets/prive/squelettes/contenu/', 'association_prets:ressources_titre_liste_ressources'),
	'prets' => array($racine_plugins . '/association-prets/prive/squelettes/contenu/', 'association_prets:prets_titre_liste_reservations'),
	'plan_comptable' => array($racine_plugins . '/association-compta/prive/squelettes/contenu/', 'association_compta:plan_comptable'),
	'bilan' => array($racine_plugins . '/association-compta/prive/squelettes/contenu/', 'association_compta:bilans_comptables'),
);

foreach ($pages as $page => $definition) {
	list($dossier, $titre) = $definition;
	$source = file_get_contents($dossier . $page . '.html');
	if (strpos($source, '<h1 class="grostitre"><:' . $titre . ':></h1>') === false) {
		fwrite(STDERR, "Titre SPIP privé absent de la page $page\n");
		exit(1);
	}
}

$formulaire_compte = file_get_contents($racine_plugins . '/association-compta/formulaires/editer_asso_comptes.php');
$contenu_compte = file_get_contents($racine_plugins . '/association-compta/prive/squelettes/contenu/editer_asso_comptes.html');
if (str_contains($formulaire_compte, "generer_url_ecrire('asso_comptes')")
	|| !str_contains($formulaire_compte, "generer_url_ecrire('comptes')")
	|| str_contains($contenu_compte, '#URL_ECRIRE{asso_comptes}')
	|| !str_contains($contenu_compte, '#URL_ECRIRE{comptes}')) {
	fwrite(STDERR, "L'édition comptable redirige encore vers la page privée legacy asso_comptes.\n");
	exit(1);
}

$racine_adhesions = $racine_plugins . '/association-adhesions/prive/squelettes/contenu/';
$edit_cotisation = file_get_contents($racine_adhesions . 'edit_cotisation.html');
$editer_cotisation = file_get_contents($racine_adhesions . 'editer_asso_cotisation.html');
if (!str_contains($edit_cotisation, '<h1 class="grostitre"><:association_adhesions:ajout_de_cotisation:></h1>')
	|| !str_contains($edit_cotisation, 'titre=non')
	|| !str_contains($editer_cotisation, '#ENV{titre,oui}|=={oui}|oui)<h1 class="grostitre">')) {
	fwrite(STDERR, "Les routes d'édition de cotisation n'ont pas de titre privé SPIP sans doublon.\n");
	exit(1);
}

$voir_activites = file_get_contents($racine_plugins . '/association-evenements/prive/squelettes/contenu/voir_activites.html');
if (!str_contains($voir_activites, '<h1 class="grostitre">#TITRE</h1>')) {
	fwrite(STDERR, "Le tableau de bord d'un événement n'utilise pas son titre comme H1 privé.\n");
	exit(1);
}

$recherche_avancee = file_get_contents($racine_plugins . '/association-adhesions/prive/squelettes/contenu/recherche_avancee.html');
if (!str_contains($recherche_avancee, '<h1 class="grostitre"><:association_adhesions:recherche_avancee_titre:></h1>')
	|| !str_contains($recherche_avancee, '#AUTORISER{adherents_menu}')) {
	fwrite(STDERR, "La recherche avancee ne possede pas son titre et son autorisation prives.\n");
	exit(1);
}

echo "Titres des pages privées conformes\n";
