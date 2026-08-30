# Association - Bons plans

Module SPIP 4 autonome de la suite Association. Il reprend l'objet éditorial `bon_plan`, les tables historiques `spip_bons_plans` et `spip_bons_plans_liens`, la modération, la dépublication et la proposition depuis le site public.

Il dépend uniquement du socle Association. La notification d'une proposition passe par la capacité `association_notifier_metier` : sans module Communication, la proposition reste enregistrée et modérable sans provoquer d'erreur.

Pages publiques : `spip.php?page=bons_plans`, `spip.php?page=bon_plan&id_bon_plan=…` et `spip.php?page=proposer_bon_plan`.
