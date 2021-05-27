<?php
require('vendor/autoload.php');

use Fapth\TextToFunction;
use Fapth\EasyBrowsingSelenoid;

$host = 'http://localhost:4444/wd/hub'; //Add Your Selenoid Host here

$I = new EasyBrowsingSelenoid($host);
$I->setDebug(TRUE);
$I->CreateChromDriver();
// $I->CreateFirefoxDriver();
$text = new TextToFunction($I);
try{
    $text->startParsing(__DIR__.'/prosaCases/singleTest');
}catch(Exception $e){
    print_r($e->getMessage());
    $I = null;
}catch(Throwable $e){
    print_r($e->getMessage());
}
$I = null;
die("\nende");
//Bet90: http://213.239.199.50:4444/video/9d8f416f8d0a7702964394e4fecf5753.mp4
