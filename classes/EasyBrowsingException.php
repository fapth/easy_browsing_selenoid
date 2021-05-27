<?php
namespace Fapth;

class ItemNotFoundException extends \Exception{
    public function errorMessage() {
        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
        .':'.$this->getMessage().' this Item was not found';
        return $errorMsg;
    }
}