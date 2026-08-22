$(function() {
    $(".id_auteur_boucle").clone().insertAfter('#input_id_auteur').removeClass('n2');
    $(".id_auteur_boucle.n2").insertAfter('#input_id_auteur_2');
});

// Le formulaire d'inscription multi construit les choix de famille, d'identite
// et de tarif a partir de l'auteur selectionne. Le script est charge depuis le
// paquet (et non depuis le fragment CVT AJAX) afin que le rechargement reste
// actif apres chaque remplacement du formulaire.
$(document).on('change select2:select', '#champ_membre, #champ_non_membre', function () {
    if (!this.value) {
        return;
    }

    var typeInscrit = this.id === 'champ_membre' ? 'membre' : 'non_membre';
    var autreChamp = typeInscrit === 'membre' ? 'non_membre' : 'membre';
    var destination = new URL(window.location.href);
    destination.searchParams.set('select_type_inscrit', typeInscrit);
    destination.searchParams.set(typeInscrit, this.value);
    destination.searchParams.delete(autreChamp);
    window.location.assign(destination.toString());
});
$.fn.moteurRecherche = function() {
    //repuration du formulaire
    $(".saisie_textarea, .saisie_date, .menu_multilang").addClass("nodisplay");
    var sub = "this.value"+"="+'""';
    $(".asso_recherche").attr("onfocus", sub);
    //retour du moteur de recherche
    //recuperation et application de la class
    $( ".asso_recherche" ).each(function() {
        var newClass = $(this).attr("name");
        $( this ).addClass( newClass );
    });
    $( ".checkbox" ).each(function() {
        var newClassCheck = $(this).attr("name");
        $( this ).addClass( newClassCheck );
    });
    //application de la valeur recherche
    $( ".champ" ).each(function() {
        var result = $(this).attr("result");
        var content = $(this).attr("content");
        //console.log(testclass);
        if ( content == "on"){
            var testClassComplet = ".checkbox"+"." + result;
             $( testClassComplet ).prop( "checked", "on" );
        }else{
            //TODO checkbox à faire
            // if ( $( "." + result ).length ){
            //     var validation = $(this).attr('value');
            //     console.log( validation );
            //     if ( validation == content ){
            //             console.log('test reussi');
            //     }
            // } else {
            //
            var testClassComplet = ".asso_recherche" + "." + result;
            //console.log(testClassCheckbox);
            $( testClassComplet ).attr("value", content);
            $( testClassComplet ).attr("placeholder", content);
            // }
            //$( testClassCheckbox ).attr("value", insideClass);
            //$( testClassCheckbox ).attr("placeholder", insideClass);
        }
    });
    $(".recherche-avance").hide().addClass("cache");
    //ouverture - fermeture du menu
    $(".bouton.option a").click( function () {
        // Si déjà ouvert, on le referme :
        if ($(".recherche-avance").hasClass ("ouvert")) {
            $(".recherche-avance").slideUp("normal").removeClass("ouvert").addClass("cache");
        } else {
            $(".recherche-avance").slideDown("normal").removeClass("cache").addClass("ouvert");
        }
        return false;
    });
};
// Test format et remplacement des dates "mm/jj/aaaa" par "aaaa-mm-jj" juste avant validation
function validateForm(){
    var doc = document.forms["verification_date"]["date"];
    var doc2 = document.forms["verification_date"]["heure"];
    var doc3 = document.forms["verification_date"]["places_utilises"];
    var doc4 = document.forms["verification_date"]["places"];
    var doc5 = document.forms["verification_date"]["date_debut"];
    var doc6 = document.forms["verification_date"]["heure_debut"];
    var doc7 = document.forms["verification_date"]["date_fin"];
    var doc8 = document.forms["verification_date"]["heure_fin"];
    var doc9 = document.forms["verification_date"]["montant"];
    var doc10 = document.forms["verification_date"]["limite_places"];
    var erreur = [];
    //console.log(date);
    // Test des places pour empecher de mettre moins de place que ce qu'il y a déjà de pris
    if (doc3){
        if($.isNumeric(doc3.value) && $.isNumeric(doc4.value)){
            var places_utilises =  parseInt(doc3.value);
            var places =  parseInt(doc4.value);
            console.log(places);
            console.log(places_utilises);
            if (  (places < places_utilises) && (places !== 0)){
                var message = "Les places disponibles ne peuvent pas être inférieures aux places réservées actuellement. \n \n " + places_utilises + " places sont actuellement réservés.";
                alert(message);
                return false;
            }
        } else {
            var message = "Veuillez entrer une valeur numérique pour le nombre de place. \n \n  \n \n Si vous entrez 0, nombre de places illimités;"
            alert(message);
            return false;
        }
    }
    if (doc9){
        console.log(doc9.value);
        if($.isNumeric(doc9.value)){} else {
            var message = "Veuillez entrer une valeur numérique pour le montant. \n \n Ex : 10 \n \n Si vous entrez 0, l'entrée est gratuite";
            alert(message);
            return false;
        }
    }
    if (doc10){
        console.log(doc10.value);
        if($.isNumeric(doc10.value)){} else {
            var message = "Veuillez entrer une valeur numérique la limite par adhérent. \n \n Ex : 10 \n \n Si vous entrez 0, il n'y a pas de limite";
            alert(message);
            return false;
        }
    }
    if (doc5){
        var date_debut = doc5.value;
        var heure_debut = doc6.value;
        var date_fin = doc7.value;
        var heure_fin = doc8.value;
        //test de l'heure
        erreur.push(testHeure(heure_debut));
        erreur.push(testHeure(heure_fin));
        //transformation de la date
        erreur.push(transformDate(date_debut, doc5));
        erreur.push(transformDate(date_fin, doc7));
        console.log('date début et fin changé');
    }
    // Test de l'heure, sinon on ne modifie que la date
    if (doc){
        var date = doc.value;
        if (doc2){
            var heure = doc2.value;
            //Test heure
            erreur.push(testHeure(heure));
            //Transformation de la date
            erreur.push(transformDate(date, doc));
            console.log('date et heure changées');
        } else {
            //Transformation de la date
            erreur.push(transformDate(date, doc));
            console.log('date changée');
        }
    }
    $.each(erreur, function(index, test){
        console.log(test);
        if (test == 'vrai'){
            console.log('passss');
            $erreur_test = 'stop';
            return false;
        } else {
            $erreur_test = '';
        }
    });
    console.log($erreur_test);
    if ($erreur_test == 'stop'){
        console.log('pass pas');
        return false;
    }
}
function transformDate(date, doc) {
    if(!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(date)){
        alert("La date doit être au format 'jj/mm/aaaa'");
        var erreur = 'vrai';
        return erreur;
    }else{
        var reg =  new RegExp("[ / ]");
        var decomp = date.split(reg);
        //decomposer
        var jour = decomp[0];
        var mois = decomp[1];
        var annee = decomp[2];
        //reassocier
        var associerDate = [annee,mois,jour];
        var resultatDate = associerDate.join("-");
        console.log(doc);
        return $(doc).attr('value', resultatDate);
    }
}
function testHeure(heure) {
    var time_arr = heure.split(":");
    var erreur = 'vrai';
    if(time_arr.length!=2){
        alert("L'heure doit être au format 'hh:mm'");
        return erreur;
    }else{
        if(isNaN(time_arr[0]) || isNaN(time_arr[1])){
            alert("L'heure doit être au format 'hh:mm'");
            return erreur;
        }
        if(time_arr[0]<24 && time_arr[1]<60)
        {
        } else{
            alert("L'heure doit être au format 'hh:mm'");
            return erreur;
        }
    }
}
// Test format et remplacement des dates "mm/jj/aaaa" par "aaaa-mm-jj" juste avant validation
function validateFormDeux(){
    var doc = document.forms["verification_date"]["date_sortie"];
    var doc2 = document.forms["verification_date"]["date_retour"];
    var date = doc.value;
    var date2 = doc2.value;
    var values = [];
    if(!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(date)){
    alert("La date doit être au format 'jj/mm/aaaa'");
    return false;
    }else{
        var reg =  new RegExp("[ / ]");
        var decomp = date.split(reg);
        //decomposer
        var jour = decomp[0];
        var mois = decomp[1];
        var annee = decomp[2];
        //reassocier
        var associerDate = [annee,mois,jour];
        var resultatDate = associerDate.join("-");
        console.log(resultatDate);
        values.push($(doc).attr('value', resultatDate));
    }
    var reg =  new RegExp("[ / ]");
    var decomp = date2.split(reg);
    //decomposer
    var jour = decomp[0];
    var mois = decomp[1];
    var annee = decomp[2];
    //reassocier
    var associerDate = [annee,mois,jour];
    var resultatDate = associerDate.join("-");
    console.log(resultatDate);
    values.push($(doc2).attr('value', resultatDate));
    return values;
    console.log(values);
}
function cocherOuDecocherTout(cochePrincipale) {
    var coches = document.querySelectorAll("input[name^='selecteur']");
    console.log(coches)
    for(var i = 0 ; i < coches.length ; i++) {
        var c = coches[i];
        if(c.type.toUpperCase() == 'CHECKBOX' & c != cochePrincipale) {
            c.checked = cochePrincipale.checked;
        }
    }
    return true;
}
function goBack() {
    window.history.back();
}

// Rend tous les tableaux metier ASSO lisibles sans faire deborder la page.
// Le traitement est progressif et rejoue apres les remplacements AJAX de SPIP.
(function () {
    'use strict';

    var programmationTableaux = 0;
    var observateurDimensions = typeof ResizeObserver !== 'undefined'
        ? new ResizeObserver(function (entrees) {
            entrees.forEach(function (entree) {
                associationActualiserTableau(entree.target);
            });
        })
        : null;

    function associationNommerTableau(tableau) {
        var legende = tableau.querySelector('caption');
        var nomExistant = tableau.parentElement.getAttribute('aria-label');
        var bloc = tableau.closest('#contenu') || tableau.closest('.box, .cadre-formulaire-editer');
        var titre = bloc ? bloc.querySelector('h1, h2, h3, .grostitre') : null;
        var entetes = tableau.rows.length
            ? Array.prototype.map.call(tableau.rows[0].cells, function (cellule) {
                return cellule.textContent.trim();
            }).filter(Boolean).join(', ')
            : '';
        return (legende && legende.textContent.trim()) || nomExistant || (titre && titre.textContent.trim()) || entetes;
    }

    function associationNombreColonnes(tableau) {
        return Array.prototype.reduce.call(tableau.rows, function (maximum, ligne) {
            var total = Array.prototype.reduce.call(ligne.cells, function (somme, cellule) {
                return somme + (cellule.colSpan || 1);
            }, 0);
            return Math.max(maximum, total);
        }, 1);
    }

    function associationEtiqueterCellules(tableau) {
        var ligneEntetes = tableau.tHead
            ? tableau.tHead.rows[tableau.tHead.rows.length - 1]
            : tableau.rows[0];
        var entetes = [];

        if (!ligneEntetes) {
            return;
        }

        Array.prototype.forEach.call(ligneEntetes.cells, function (cellule) {
            var texte = cellule.textContent.trim();
            for (var colonne = 0; colonne < (cellule.colSpan || 1); colonne += 1) {
                entetes.push(texte);
            }
        });

        Array.prototype.forEach.call(tableau.tBodies, function (corps) {
            Array.prototype.forEach.call(corps.rows, function (ligne) {
                Array.prototype.forEach.call(ligne.cells, function (cellule, index) {
                    if (entetes[index]) {
                        cellule.setAttribute('data-label', entetes[index]);
                    }
                });
            });
        });
    }

    function associationActualiserTableau(conteneur) {
        var tableau = conteneur.querySelector(':scope > table.tableau_asso');
        if (!tableau) {
            return;
        }

        var deborde = conteneur.scrollWidth > conteneur.clientWidth + 1;
        var nom = associationNommerTableau(tableau);
        conteneur.classList.toggle('tableau-association-deborde', deborde);

        if (deborde) {
            conteneur.setAttribute('tabindex', '0');
            if (nom) {
                conteneur.setAttribute('role', 'region');
                conteneur.setAttribute('aria-label', nom);
            }
        } else {
            conteneur.removeAttribute('tabindex');
            conteneur.removeAttribute('role');
            conteneur.removeAttribute('aria-label');
        }
    }

    function associationAmeliorerTableaux() {
        document.querySelectorAll('table.tableau_asso').forEach(function (tableau) {
            var conteneur = tableau.parentElement;
            if (!conteneur.classList.contains('tableau-association-scroll')) {
                if (conteneur.classList.contains('tableau-adherents-scroll') || conteneur.classList.contains('tableau-cotisations-scroll')) {
                    conteneur.classList.add('tableau-association-scroll');
                } else {
                    conteneur = document.createElement('div');
                    conteneur.className = 'tableau-association-scroll';
                    tableau.parentNode.insertBefore(conteneur, tableau);
                    conteneur.appendChild(tableau);
                }
            }

            associationEtiqueterCellules(tableau);
            tableau.style.setProperty('--association-table-min-width', Math.max(20, associationNombreColonnes(tableau) * 6.25) + 'rem');
            if (observateurDimensions && !conteneur.hasAttribute('data-association-observe')) {
                conteneur.setAttribute('data-association-observe', 'true');
                observateurDimensions.observe(conteneur);
            }
            associationActualiserTableau(conteneur);
        });
    }

    function associationProgrammerTableaux() {
        window.cancelAnimationFrame(programmationTableaux);
        programmationTableaux = window.requestAnimationFrame(associationAmeliorerTableaux);
    }

    $(associationAmeliorerTableaux);
    $(document).ajaxComplete(associationProgrammerTableaux);
    window.addEventListener('resize', associationProgrammerTableaux, { passive: true });
}());
