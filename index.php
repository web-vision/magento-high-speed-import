<?php
include_once __DIR__ . '/fw/cemes.php';

// initialize framework
$cemes = Cemes::load();
// set error handler
$cemes->handleErrors(E_ALL ^ E_STRICT);

$fci = new Fci_Controller();
if (Cemes::isCli()) {
    for ($i = 1; $i < $argc; $i += 2) {
        if ($argv[$i] === '--config') {
            $_GET['config'] = $argv[$i + 1];
            continue;
        }

        if ($argv[$i] === '--dev') {
            $_GET['dev'] = $argv[$i + 1];
            continue;
        }
    }
}

if (array_key_exists('config', $_GET) && $_GET['config'] !== '') {
    $fci->setConfig($_GET['config']);
}
$fci->initialisize();
?>

<?php if (!Cemes::isCli()): ?>
<!DOCTYPE html>
<html>
<head>
    <title>HighspeedImportScript</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="NOINDEX,NOFOLLOW">
    <?php $cemes->insertCSS(); ?>
</head>
<body>
<?php endif; ?>

<?php
$fci->run();

if (array_key_exists('dev', $_GET) && $_GET['dev'] !== '') {
    $cemes->displayErrors((int)$_GET['dev']);
}
?>

<?php if (!Cemes::isCli()): ?>
</body>
</html>
<?php endif; ?>
