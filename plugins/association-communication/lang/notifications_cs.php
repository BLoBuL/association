<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/*
 * Fichier de langue Tchèque (cs)
 * Stratégie:
 *  - Clés alignées sur la référence FR
 *  - Traductions faites pour un noyau prioritaire
 *  - Le reste marqué TODO (fallback possible côté interface)
 *  - À compléter itérativement
 */

$traductions = [
	'email_formule_politesse' => 'S pozdravem,',
	'email_signature' => 'Tým @nom_site_spip@.',
	'notification_lien_profil' => 'Sledujte stav své registrace na stránce profilu:',
	'activation_cotisation_donation' => 'Děkujeme za Váš dar.',
	'bouton_page_paiement' => 'STRÁNKA PLATBY',
	'bouton_page_profil' => 'MŮJ PROFIL',
	'lien_page_profil' => 'Přejít na mou stránku profilu:',
	'lien_page_paiement' => 'Přejít na stránku platby přes tento odkaz:',
	'votre_nom' => 'Vaše jméno:',
	'montant_a_regler' => 'Částka k úhradě:',

	# ČEKÁNÍ NA PLATBU
	'attente_paiement_sujet' => 'Vaše nová členská platba čeká na úhradu',
	'attente_paiement_title' => 'Máte platbu čekající na úhradu',
	'attente_paiement_intro' => 'Informace o platbě',
	'attente_paiement_inscription_titre' => 'Obdrželi jsme Vaši žádost o členství.',
	'attente_paiement_reinscription_titre' => 'Obdrželi jsme Vaši žádost o obnovení členství',
	'attente_paiement_chapo' => 'Prosíme, proveďte platbu dle níže uvedených pokynů.',
	'attente_paiement_rappel' => 'Shrnutí:',
	'attente_paiement_message_lien_profil' => 'Podrobnosti a sledování na stránce Vašeho profilu:',

	// firemní varianta
	'attente_paiement_entreprise_sujet' => 'Vaše nová firemní registrace čeká na úhradu',
	'attente_paiement_entreprise_title' => 'Máte platbu čekající na úhradu',
	'attente_paiement_entreprise_intro' => 'Informace o platbě',
	'attente_paiement_inscription_entreprise_titre' => 'Obdrželi jsme Vaši žádost o firemní záznam.',
	'attente_paiement_reinscription_entreprise_titre' => 'Obdrželi jsme Vaši žádost o obnovení',
	'attente_paiement_entreprise_chapo' => 'Prosíme, proveďte platbu dle níže uvedených pokynů.',

	// aliasy
	'attente_paiement_adherent_sujet' => 'Pro dokončení členství je vyžadována platba',
	'attente_paiement_adherent_title' => 'Dokončete své členství',
	'attente_paiement_adherent_intro' => 'Žádost zaznamenána. Prosím zaplaťte.',
	'attente_paiement_adherent_titre' => 'Zaplaťte a staňte se členem naší asociace',
	'attente_paiement_adherent_chapo' => 'Vaše platba čeká na uhrazení. Můžete provést platbu pomocí odkazu v dolní části zprávy nebo přes svůj profil.',
	'attente_paiement_mail_sujet' => 'Vaše nová členská platba čeká na úhradu',
	'attente_paiement_mail_entreprise_sujet' => 'Vaše nová firemní registrace čeká na úhradu',

	# AKTIVACE ÚČTU (nebo obnovení)
	'activation_cotisation_inscription_sujet' => 'Vaše registrace v naší asociaci je dokončena',
	'activation_cotisation_reinscription_sujet' => 'Vaše obnovení členství v naší asociaci je dokončeno',
	'activation_cotisation_title' => 'Potvrzení členství',
	'activation_cotisation_intro' => 'Váš účet je aktivní.',
	'activation_cotisation_inscription_titre' => 'Vítejte @nom_adherent@, jste nyní členem @nom_site@!',
	'activation_cotisation_reinscription_titre' => 'Děkujeme @nom_adherent@, Vaše členství bylo úspěšně obnovené!',
	'activation_cotisation_chapo' => 'Gratulujeme, Vaše členství bylo potvrzeno. Využijte aktivity a služby pro členy.',
	'activation_cotisation_message_lien_profil' => "Navštivte <a href='@url_profil@'>svou stránku profilu</a> pro doplnění údajů a preferencí.",
	'activation_cotisation_explication' => 'Jako člen asociace můžete využívat:',
	'activation_cotisation_explication_annuaire' => '<b>adresář členů asociace</b>',
	'activation_cotisation_explication_b' => 'on-line registrace a aktivity',
	'activation_cotisation_explication_c' => 'chráněné dokumenty a galerie',
	'activation_cotisation_inscription_entreprise_sujet' => 'Registrace Vaší společnosti je dokončena',
	'activation_cotisation_reinscription_entreprise_sujet' => 'Obnovení registrace Vaší společnosti je dokončeno',
	'activation_cotisation_entreprise_title' => 'Potvrzení firemní registrace',
	'activation_cotisation_entreprise_intro' => 'Profil Vaší společnosti je nyní aktivní',
	'activation_cotisation_inscription_entreprise_titre' => 'Vítejte @nom_entreprise@, vaše firemní registrace je aktivní na @nom_site@',
	'activation_cotisation_reinscription_entreprise_titre' => 'Děkujeme @nom_entreprise@, Vaše firemní členství bylo obnoveno!',
	'activation_cotisation_entreprise_chapo' => 'Vaše členství bylo potvrzeno. Firemní účet je aktivní a má přidružená oprávnění.',

	# PO PLATBĚ
	'validation_post_paiement_sujet' => 'Potvrzujeme přijetí Vaší platby.',
	'validation_post_paiement_mail_sujet' => 'Potvrzujeme přijetí Vaší platby.',
	'validation_post_paiement_title' => 'Potvrzení přijetí platby',
	'validation_post_paiement_intro' => 'Čeká na validaci',
	'validation_post_paiement_inscription_titre' => 'Potvrzujeme přijetí Vaší platby.',
	'validation_post_paiement_reinscription_titre' => 'Potvrzujeme přijetí Vaší platby.',
	'validation_post_paiement_chapo' => 'Oddělení zkontroluje Vaši registraci a provede ruční validaci.',
	'validation_post_paiement_explication' => 'Budete informováni e-mailem po dokončení validace. Stav členství můžete sledovat na své stránce profilu.',
	'validation_post_paiement_entreprise_sujet' => 'Potvrzujeme přijetí platby za registraci Vaší společnosti. Probíhá validace.',
	'validation_post_paiement_entreprise_title' => 'Potvrzujeme přijetí platby za registraci Vaší společnosti.',
	'validation_post_paiement_entreprise_intro' => 'Potvrzujeme přijetí platby za registraci Vaší společnosti. Žádost je evidována a čeká na validaci odpovědnou osobou.',
	'validation_post_paiement_inscription_entreprise_titre' => 'Potvrzujeme přijetí platby za registraci Vaší společnosti.',
	'validation_post_paiement_reinscription_entreprise_titre' => 'Potvrzujeme přijetí platby za registraci Vaší společnosti.',
	'validation_post_paiement_entreprise_chapo' => 'Odpovědná osoba prověří registraci Vaší společnosti a dokončí validaci.',
	'validation_post_paiement_entreprise_explication' => 'Obdržíte e-mail, jakmile bude validace dokončena. Sledujte stav na stránce profilu Vaší společnosti.',

	# PŘED PLATBOU
	'validation_pre_paiement_sujet' => 'Vaše žádost o členství čeká na validaci',
	'validation_pre_paiement_mail_sujet' => 'Vaše žádost o členství čeká na validaci',
	'validation_pre_paiement_title' => 'Čeká na validaci',
	'validation_pre_paiement_intro' => 'Vaše žádost byla předána odpovědné osobě.',
	'validation_pre_paiement_inscription_titre' => 'Vaše platba čeká na validaci',
	'validation_pre_paiement_reinscription_titre' => 'Vaše platba čeká na validaci',
	'validation_pre_paiement_chapo' => 'Odpovědná osoba zkontroluje Vaši žádost. Budete informováni, kdy můžete provést platbu.',
	'validation_pre_paiement_explication' => 'Po validaci obdržíte e-mail s pokyny k platbě. Sledujte stav na stránce profilu.',
	'validation_pre_paiement_entreprise_sujet' => 'Registrace firmy čeká na validaci',
	'validation_pre_paiement_entreprise_title' => 'Probíhá validace',
	'validation_pre_paiement_entreprise_intro' => 'Dokumentace firmy byla předána odpovědné osobě.',
	'validation_pre_paiement_entreprise_inscription_titre' => 'Registrace firmy čeká na validaci',
	'validation_pre_paiement_entreprise_reinscription_titre' => 'Registrace firmy čeká na validaci',
	'validation_pre_paiement_entreprise_chapo' => 'Odpovědná osoba zkontroluje dokumentaci firmy. Budete informováni, kdy můžete provést platbu.',
	'validation_pre_paiement_entreprise_explication' => 'Po validaci obdržíte e-mail s pokyny k platbě. Sledujte stav na stránce profilu firmy.',

	// Klíč pro dary používaný v šablonách
	'validation_compte_donation' => 'Děkujeme za Váš dar.',

	// Klíče pro oznámení administrátorům o přijetí platby
	'cotisation_encaissement_admin_sujet' => 'Platba přijata: @id_compte@ @nom@ - @type@ - @montant@',
	'cotisation_encaissement_admin_title' => 'Platba přijata',
	'cotisation_encaissement_admin_intro' => 'Byla přijata platba za členský příspěvek.',

	// Oznámení k událostem
	'email_attente_backend_titre' => 'Vaše registrace na akci „@evenement@“ byla zařazena na čekací listinu',
	'email_attente_backend_texte' => 'Odpovědná osoba Vaši registraci potvrdí, pokud se uvolní místo nebo bude navýšena kapacita. Děkujeme za pochopení.',
	'email_modification_inscription' => 'Máte-li k této akci dotazy nebo chcete-li registraci zrušit či změnit, kontaktujte odpovědnou osobu:',
	'email_attente_frontend_titre' => 'Zapsali jste se na čekací listinu akce „@evenement@“',
	'email_attente_frontend_texte' => 'Odpovědná osoba Vaši registraci potvrdí, pokud se uvolní místo nebo bude navýšena kapacita. Děkujeme za pochopení.',
	'email_attente_responsable_frontend' => '@adherents@ se zapsal(a) na čekací listinu akce „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_attente_responsable_frontend_p' => '@adherents@ se zapsali na čekací listinu akce „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_contact_adherent' => 'Kontaktní údaje účastníka:',
	'email_texte_lien_module_inscription' => 'Žádosti o registraci můžete zobrazit a potvrdit pomocí následujícího odkazu:',
	'email_bouton_module_inscription' => 'Správa registrací',
	'email_desinscription_backend' => 'Byli jste odhlášeni z akce „@evenement@“, která se koná @date@ v @heure@.',
	'email_lien_rendez_vous' => 'Veškeré informace o této akci naleznete na našich webových stránkách:',
	'email_desinscription_frontend' => 'Zrušili jste registraci na akci „@evenement@“, která se koná @date@ v @heure@.',
	'email_erreur_desinscription' => 'Pokud jde o omyl, co nejdříve kontaktujte odpovědnou osobu:',
	'email_desinscription_responsable_frontend' => '@adherents@ zrušil(a) registraci na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_desinscription_responsable_frontend_p' => '@adherents@ zrušili registrace na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_ne_pas_repondre' => 'Na tento automatický e-mail neodpovídejte. Kontaktujte přímo odpovědnou osobu akce.',
	'email_expiration_automatique_titre' => 'Zrušení žádosti o registraci na akci „@evenement@“ dne @datev@.',
	'email_expiration_automatique_texte' => 'Vaše žádost o registraci byla <b>automaticky zrušena</b>, protože platba nebyla přijata do <b>@nombre_jours@ dne/dnů</b>. Novou žádost můžete podat na webových stránkách. Pokud jste zaplatili, ale platba nebyla včas zpracována, neprodleně kontaktujte odpovědnou osobu.',
	'email_expiration_responsable_automatique' => '[Automatické zrušení] @adherents@ byl(a) odhlášen(a) z akce „@evenement@“, která se koná @date@ v @heure@, protože platba nebyla přijata včas.',
	'email_expiration_responsable_automatique_p' => '[Automatické zrušení] @adherents@ byli odhlášeni z akce „@evenement@“, která se koná @date@ v @heure@, protože platba nebyla přijata včas.',
	'email_inscription_backend_titre' => 'Byli jste zaregistrováni na akci „@evenement@“',
	'email_inscription_frontend_titre' => 'Zaregistrovali jste se na akci „@evenement@“',
	'email_inscription_frontend_texte' => 'Vaše registrace byla zaznamenána a potvrzena.',
	'email_inscription_responsable_frontend' => '@adherents@ se zaregistroval(a) na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_inscription_responsable_frontend_p' => '@adherents@ se zaregistrovali na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_inscription_automatique_titre' => 'Nyní jste zaregistrováni na akci „@evenement@“',
	'email_inscription_automatique_texte' => 'Vaše žádost byla automaticky potvrzena po uvolnění místa nebo navýšení kapacity. Již nejste na čekací listině.',
	'email_inscription_responsable_automatique' => '[Automatické potvrzení] @adherents@ je nyní zaregistrován(a) na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_inscription_responsable_automatique_p' => '[Automatické potvrzení] @adherents@ jsou nyní zaregistrováni na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_modification_backend' => 'Vaše registrace na akci „@evenement@“, která se koná @date@ v @heure@, byla změněna.',
	'email_modification_frontend' => 'Změnili jste svou registraci na akci „@evenement@“, která se koná @date@ v @heure@.',
	'email_modification_responsable_frontend' => '@adherents@ změnil(a) registraci na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_modification_responsable_frontend_p' => '@adherents@ změnili registrace na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_preinscription_backend_titre' => 'Byli jste předběžně zaregistrováni na akci „@evenement@“',
	'email_preinscription_frontend_titre' => 'Předběžně jste se zaregistrovali na akci „@evenement@“',
	'email_preinscription_responsable_frontend' => '@adherents@ se předběžně zaregistroval(a) na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_preinscription_responsable_frontend_p' => '@adherents@ se předběžně zaregistrovali na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_preinscription_responsable_automatique' => '[Automatické potvrzení] @adherents@ je nyní předběžně zaregistrován(a) na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_preinscription_responsable_automatique_p' => '[Automatické potvrzení] @adherents@ jsou nyní předběžně zaregistrováni na akci „@evenement@“, za kterou odpovídáte a která se koná @date@ v @heure@.',
	'email_date_evenement' => 'Datum akce:',
	'email_adresse_evenement' => 'Adresa akce:',
	'email_info_rendez_vous' => 'Přehled informací o akci:',
	'titre_votre_inscription' => 'Informace o registraci',
	'date_inscription' => 'Datum registrace:',
	'votre_commentaire' => 'Zpráva pro odpovědné osoby:',
	'nombre_participant' => 'Počet účastníků:',
	'nom_accompagnant' => 'Jména účastníků:',
	'noms_accompagnants' => 'Jména účastníků:',
	'montant_recu' => 'Zaplacená částka: ',
	'montant_adherent_a_regler' => 'Částka k úhradě:',
	'mode_paiement_possible' => 'Dostupné způsoby platby:',
	'email_signature_activites_inscription' => 'Tuto zprávu Vám zasílá @prenom@ @nom@ (<a href="mailto:@email@">@email@</a>), protože jste se zaregistrovali na tuto akci.',
	'desinscription_activite_mail_sujet_frontend' => 'Zrušili jste registraci na akci „@evenement@“ dne @datev@',
	'desinscription_activite_mail_sujet_backend' => 'Byli jste odhlášeni z akce „@evenement@“ dne @datev@.',
	'inscription_activite_mail_sujet_frontend' => 'Zaregistrovali jste se na akci „@evenement@“ dne @datev@',
	'inscription_activite_mail_sujet_backend' => 'Byli jste zaregistrováni na akci „@evenement@“ dne @datev@.',
	'modification_activite_mail_sujet_frontend' => 'Změnili jste registraci na akci „@evenement@“ dne @datev@.',
	'modification_activite_mail_sujet_backend' => 'Vaše registrace na akci „@evenement@“ dne @datev@ byla změněna.',
	'preinscription_activite_mail_sujet_frontend' => 'Předběžně jste se zaregistrovali na akci „@evenement@“ dne @datev@.',
	'preinscription_activite_mail_sujet_backend' => 'Byli jste předběžně zaregistrováni na akci „@evenement@“ dne @datev@.',
	'attente_activite_mail_sujet_frontend' => 'Zapsali jste se na čekací listinu akce „@evenement@“ dne @datev@',
	'attente_activite_mail_sujet_backend' => 'Byli jste zařazeni na čekací listinu akce „@evenement@“ dne @datev@.',
	'inscription_automatique_activite_mail_sujet' => 'Nyní jste zaregistrováni na akci „@evenement@“ dne @datev@.',
	'preinscription_automatique_activite_mail_sujet' => 'Nyní jste předběžně zaregistrováni na akci „@evenement@“ dne @datev@.',
	'expiration_automatique_activite_mail_sujet' => 'Zrušení žádosti o registraci na akci „@evenement@“ dne @datev@.',
	'preinscription_activite_mail_sujet_responsable_frontend' => '@adherents@ se předběžně zaregistroval(a) na akci „@evenement@“ dne @datev@.',
	'preinscription_activite_mail_sujet_responsable_frontend_p' => '@adherents@ se předběžně zaregistrovali na akci „@evenement@“ dne @datev@.',
	'desinscription_activite_mail_sujet_responsable_frontend' => '@adherents@ zrušil(a) registraci na akci „@evenement@“ dne @datev@.',
	'desinscription_activite_mail_sujet_responsable_frontend_p' => '@adherents@ zrušili registrace na akci „@evenement@“ dne @datev@.',
	'inscription_activite_mail_sujet_responsable_frontend' => '@adherents@ se zaregistroval(a) na akci „@evenement@“ dne @datev@.',
	'inscription_activite_mail_sujet_responsable_frontend_p' => '@adherents@ se zaregistrovali na akci „@evenement@“ dne @datev@.',
	'modification_activite_mail_sujet_responsable_frontend' => '@adherents@ změnil(a) registraci na akci „@evenement@“ dne @datev@.',
	'attente_activite_mail_sujet_responsable_frontend' => '@adherents@ se zapsal(a) na čekací listinu akce „@evenement@“ dne @datev@.',
	'attente_activite_mail_sujet_responsable_frontend_p' => '@adherents@ se zapsali na čekací listinu akce „@evenement@“ dne @datev@.',
	'inscription_automatique_activite_mail_sujet_responsable' => '[Automatické potvrzení] @adherents@ je nyní zaregistrován(a) na akci „@evenement@“ dne @datev@.',
	'inscription_automatique_activite_mail_sujet_responsable_p' => '[Automatické potvrzení] @adherents@ jsou nyní zaregistrováni na akci „@evenement@“ dne @datev@.',
	'preinscription_automatique_activite_mail_sujet_responsable' => '[Automatické potvrzení] @adherents@ je nyní předběžně zaregistrován(a) na akci „@evenement@“ dne @datev@.',
	'preinscription_automatique_activite_mail_sujet_responsable_p' => '[Automatické potvrzení] @adherents@ jsou nyní předběžně zaregistrováni na akci „@evenement@“ dne @datev@.',
	'expiration_automatique_activite_mail_sujet_responsable' => '[Automatické zrušení] @adherents@ byl(a) odhlášen(a) z akce „@evenement@“ dne @datev@.',
	'expiration_automatique_activite_mail_sujet_responsable_p' => '[Automatické zrušení] @adherents@ byli odhlášeni z akce „@evenement@“ dne @datev@.',
	'evenement_non_presentiel_lien' => 'Úkaz pro připojení:',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
