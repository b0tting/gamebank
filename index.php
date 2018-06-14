<?php
// GO F3 GO!
require('lib/base.php');
include('src/baseLineLoad.php');

$f3 = \Base::instance();
$f3->config('config.ini');
$logger = new Log($f3->get('logfile'));
//new Session();

$dbfile = $f3->get('db_file');
if (file_exists($dbfile)&& $f3->get('itm_debug')) {
    $logger->write("Killing the entire database as we are in development mode");
    unlink($dbfile);
    $create = true;
} else {
    $create = !file_exists($dbfile) || filesize($dbfile) == 0;
}

$db = new DB\SQL('sqlite:' . $f3->get('db_file'));
if($create) {
    $logger->write("Refilling the database with baseline structure");
    $sql = file_get_contents('itmbank_2018-06-06.dump.sql');
    $sqlarray = explode(";", $sql);
    if(!$db->exec($sqlarray)) {
        print($db->log());
        die("Failed on SQL import!");
    }


 //   baseLineLoad($db);
};
$f3->set('DB', $db );
setlocale(LC_MONETARY, 'de_DE.UTF-8');
date_default_timezone_set("UTC");
$f3->route('GET|POST @transactions: /transactions','BankController->transactions');
$f3->route('GET @transactionsapi: /api/transactions/@accountnum','ApiController->transactions');
$f3->route('POST @validatelogin: /login','BankController->authenticate');
$f3->route('GET  @logout: /logout','BankController->logout');
$f3->route('GET @questions: /questions/@accountnum','BankController->questions');
$f3->route('POST @answers: /answers/@accountnum','BankController->answers');
$f3->route('GET @login: /','BankController->index');

// Systeem admin spul
$f3->route('GET @logging: /logging','AdminController->logging');
$f3->route('GET @resetdb: /resetdb','AdminController->resetdb');
$f3->route('GET @resetblocks: /resetblocks','AdminController->resetLockouts');



$f3->run();
