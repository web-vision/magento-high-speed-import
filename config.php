<?php
include_once __DIR__ . '/fw/cemes.php';

// initialize framework
$cemes = Cemes::load();
// set error handler
$cemes->handleErrors(E_ALL ^ E_STRICT);

if (array_key_exists('dev', $_GET) && $_GET['dev'] !== '') {
    $cemes->displayErrors((int)$_GET['dev']);
}

$bootstrap = new \Fci\View\Bootstrap();
$bootstrap->run();