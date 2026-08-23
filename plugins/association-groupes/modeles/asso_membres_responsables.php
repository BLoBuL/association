[(#REM)<!-- doc: contrib.spip.net/4174#modeles -->]
<B_recherche>
#ANCRE_PAGINATION
<table class="spip asso_tablo" width="100%">
	<tr class="row_first">
		<th scope="col"><:association_groupes:adherent_libelle_nom_famille:></th>
		<th scope="col"><:association_groupes:adherent_libelle_telephone:></th>
		<th scope="col"><:association_groupes:adherent_libelle_email:></th>
		<th scope="col"><:association_groupes:adherent_libelle_mobile:></th>
		<th scope="col"><:association_groupes:adherent_libelle_activite:></th>

	</tr>
[(#REM)<!-- //!\ "inclusion de fragments" <http://microformats.org/wiki/include-pattern> -->]
<BOUCLE_recherche(AUTEURS)
	{statut='1comite'}
	{par nom_famille,prenom}
	{pagination #ENV{pagination,10} assoequipe}
	>
	<tr class="agent vcard [row_(#COMPTEUR_BOUCLE|alterner{'odd','even'})]">
		<td><a class="spip_in fn" title="<:association_groupes:adherent_label_modifier_visiteur:>" href="[(#ID_AUTEUR|generer_objet_url{auteur})]">[(#SEXE|association_calculer_nom_membre{#PRENOM, #NOM_FAMILLE})]</a></td>
		<td>[<a class="spip_out email" href="mailto:(#EMAIL)">#EMAIL</a>]</td>
		<td>[<a class="spip_out tel" href="tel:[(#TELEPHONE|replace{\D})]">(#TELEPHONE|association_telfr)</a>]</td>
		<td>[<a class="spip_out tel" href="tel:[(#MOBILE|replace{\D})]">(#MOBILE|association_telfr)</a>]</td>
		<td>
		<BOUCLE_correspondance(SPIP_AUTEURS_LIENS){id_auteur}>
			<BOUCLE_articles(ARTICLES){id_article = #ID_OBJET}{0,5}{doublons}>
				[<a class="spip_out" href="#URL_ECRIRE{article,id_article=#ID_ARTICLE}">(#TITRE)</a><br />]
			</BOUCLE_articles>            
            <BOUCLE_articles_autres(ARTICLES){id_article = #ID_OBJET}{doublons}>                
            </BOUCLE_articles_autres>
            <div>Et <b>#TOTAL_BOUCLE autres activités</b></div>    
            </B_articles_autres>
		</BOUCLE_correspondance>
		</td>
	</tr>
</BOUCLE_recherche>
</table>
[<nav class="pagination">(#PAGINATION{prive})</nav>]
</B_recherche>
[<div class="erreur">(#ENV{vide}|sinon{<:ecrire:texte_vide:>})</div>]
<//B_recherche>
