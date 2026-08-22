<?php
/***************************************************************************
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

function balise_META($p)
{
        if (!$arg = interprete_argument_balise(1,$p))
          $arg = "''";
        $p->code = 'choisir_meta(' . $arg . ')';
        return $p;
}

function choisir_meta($nom)
{
    if ($nom && $nom[0]!== '/') {
        $table = 'meta';
    } else {
        if ($nom) {
            $parts = explode('/', $nom);
            $table = $parts[1] ?? '';
            $nom   = $parts[2] ?? '';
            $table .= '_metas';
            if (!isset($GLOBALS[$table])) $table = 'meta';
        } else {
            $table = 'meta';
        }
    }
    if (!$nom) {
        return $GLOBALS[$table];
    }
    return isset($GLOBALS[$table][$nom]) ? $GLOBALS[$table][$nom] : null;
}
