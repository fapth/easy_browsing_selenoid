<?php
namespace Fapth;

use Exception;

class TextToFunction{
    private EasyBrowsingSelenoid $I;
    private $errorHandler = array();
    public function __construct(EasyBrowsingSelenoid $I) {
        $this->I = $I;
    }

    function executeAction($method,$arrMatchingFunction){

        if(0 >= sizeof($arrMatchingFunction))
            return false;

        array_shift($arrMatchingFunction); // remove matching of full sentence, we just want the params
        call_user_func_array(array($this->I, $method), $arrMatchingFunction);
        
        return true;
    }
    //if some days we will change something, we can change it here. This function is used for execute Script and also for Error handling.
    function getAction($searchString, $str){
        $ret = array();
        preg_match($searchString, $str, $ret);
        return $ret;

    }
    function startParsing($file){
        $listOfFunctions2 = $listOfFunctions = EasyBrowsingSelenoid::returnListOfFunction();
        
        $str = '';
        $lang = '';
        
        if($fh = fopen($file,"r")){
            $fn = explode(DIRECTORY_SEPARATOR,$file);
            $this->I->setScreenshotName = str_replace('.','_',end($fn));
            while (!feof($fh)){
                $str = fgets($fh,9999);

                /** Skip Empty Sentences */
                if(strlen($str) <= 1)
                    continue;

                /** Allow comments in file */
                if(substr($str,0,1)=='#')
                    continue;
                
                /** Set language */
                if($lang == ''){
                    if(substr($str,0,5)=='LANG='){
                        $lang = trim(str_replace('LANG=','',$str));
                        continue;
                    }
                    else{
                        die("No Language available");
                    }
                }

                /** load Error Handler */
                if(strpos($str,'On Error:')!==false){
                    $this->errorHandler[] = trim(str_replace('On Error:','',$str));
                    continue;
                }
                
                $found=false;
                foreach($listOfFunctions[$lang] as $searchString => $method){
                    $ret = $this->getAction($searchString, $str);
                    $size = sizeof($ret);
                    if($size>0){
                        $found = true;
                        try{
                            $break = $this->executeAction($method,$ret);
                            if($break){
                                break;
                            }
                        }catch(Exception $e){
                            /** now we try error handling by User Input On Error:*/
                            $gotError = true;
                            foreach($this->errorHandler as $itm){
                                
                                foreach($listOfFunctions2[$lang] as $searchString2 => $method2){
                                    try{
                                        $break = false;
                                        $ret2 = $this->getAction($searchString2, $itm);
                                        $size2 = sizeof($ret2);
                                        $this->executeAction($size2,$method2,$ret2);
                                        try{
                                            $break = $this->executeAction($size,$method,$ret);
                                        }catch(Exception $dump){}
                                        $gotError = false;
                                        if($break){
                                            break;
                                        }
                                    }catch(Exception $e){}
                                }      
                                
                            }
                            if($gotError){
                                die($e->getMessage());
                            }
                        }
                    }
                    
                }
                if(!$found){
                    die("\n######$str######\nCommand not found \n");
                }
            }
            fclose($fh);
          } 
        
    }
}