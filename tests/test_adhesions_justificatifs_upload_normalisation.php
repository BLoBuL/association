<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));
function include_spip($fichier) {}
function test_assert($condition, $message) {
    if (!$condition) { echo "ECHEC: $message\n"; exit(1); }
    echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/api_cotisations.php';

$synthetiques = array(
    'document_justificatif' => array(
        'name' => array('identite.pdf', 'domicile.jpg', 'extra.png'),
        'type' => array('application/pdf', 'image/jpeg', 'image/png'),
        'tmp_name' => array('/tmp/identite.pdf', '/tmp/domicile.jpg', '/tmp/extra.png'),
        'error' => array(UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK),
        'size' => array(10, 20, 30),
    ),
);
$upload = cotisation_documents_vers_upload($synthetiques);
test_assert(count($upload) === 3, 'trois fichiers nommés sont aplatis');
test_assert($upload[1]['name'] === 'domicile.jpg', 'la seconde pièce conserve son nom');

$aplatis = array(
    array('name' => 'identite.pdf', 'type' => 'application/pdf', 'tmp_name' => '/tmp/a', 'error' => UPLOAD_ERR_OK, 'size' => 10),
    array('name' => 'domicile.jpg', 'type' => 'image/jpeg', 'tmp_name' => '/tmp/b', 'error' => UPLOAD_ERR_OK, 'size' => 20),
);
$upload = cotisation_documents_vers_upload($aplatis);
test_assert(count($upload) === 2, 'deux fichiers aplatis sont reconstruits');
test_assert($upload[0]['type'] === 'application/pdf', 'le type MIME est conservé');

$cvtupload = array(
    'document_justificatif' => array(
        array(
            'name' => 'recto.jpg',
            'tmp_name' => '/tmp/recto.jpg',
            'mime' => 'image/jpeg',
            'taille' => 42,
            'infos_encodees' => 'valeur-interne-cvtupload',
        ),
        array(
            'name' => 'verso.png',
            'tmp_name' => '/tmp/verso.png',
            'mime' => 'image/png',
            'taille' => 84,
            'infos_encodees' => 'valeur-interne-cvtupload',
        ),
    ),
);
$upload = cotisation_documents_vers_upload($cvtupload);
test_assert(count($upload) === 2, 'deux fichiers conserves par cvt-upload sont reconstruits');
test_assert($upload[1]['type'] === 'image/png', 'le MIME cvt-upload est converti en type PHP');
test_assert($upload[0]['size'] === 42, 'la taille cvt-upload est convertie en taille PHP');
test_assert(array_column($upload, 'error') === array(UPLOAD_ERR_OK, UPLOAD_ERR_OK), 'les fichiers cvt-upload sont consideres sans erreur');
echo "Tests de normalisation des justificatifs terminés.\n";
