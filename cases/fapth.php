<?php

//Given I am on "/"
$I->goTo('https://fapth.de');
//Avoid Mobile Layer
$I->maximizeWindow();
//I wait for whole side
$I->wait(3);