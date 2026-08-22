# Instructions Copilot - blobul-ASSO_BO

## Contexte
- Plugin back-office SPIP pour la gestion d'association (`association`).
- Cible prioritaire : SPIP 4 (respecter la compatibilite declaree dans `paquet.xml`).
- Tout le code, les commentaires et les messages UI sont en francais.

## Regles obligatoires
- Localisation systematique :
  - PHP : `_T('association:cle')`
  - Squelettes : `<:association:cle:>`
- Ne pas ajouter de fallback ad-hoc ; declarer les dependances dans `paquet.xml`.
- Utiliser `include_spip()` / `charger_fonction()` pour les appels externes.
- Respecter le prefixe `association_` pour les fonctions et helpers.
- Utiliser `spip_log()` pour les erreurs metier.

## Fichiers de reference a lire avant modification
- `paquet.xml`
- `association_pipelines.php`
- `base/association.php`
- `inc/notifications_emails.php`
- `lang/`

## Encodage
- Tous les fichiers doivent etre en UTF-8 sans BOM.
- Ne jamais utiliser `Set-Content` (PowerShell 5.1) qui corrompt les accents. Utiliser `[System.IO.File]::WriteAllText()` ou outils .NET equivalents.
- En cas de corruption, restaurer depuis git avant de reappliquer les modifications.

## APIs stables a ne pas casser
- `association_taches_generales_cron($flux)`
- `association_trig_bank_notifier_reglement($flux)`
- `association_sync_repetitions_tarifs($id_evenement)`
- `association_lire_config_liste($meta)`
- `association_normaliser_config_liste($cfg)`

Si une signature change, documenter l'impact et la migration dans la PR.

