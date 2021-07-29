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
    ob_start();		
    $text->startParsing(__DIR__.'/prosaCases/singleTest');
    $buffer = ob_get_contents();
    @ob_end_clean();
    echo $buffer;
}catch(Exception $e){
    print_r($e->getMessage());
    $I = null;
}catch(Throwable $e){
    print_r($e->getMessage());
}
$I = null;
die("\nende");
