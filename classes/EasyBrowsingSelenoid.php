<?php
namespace Fapth;

use Exception;
use Fapth\EasyBrowsingException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Exception\WebDriverException;

class EasyBrowsingSelenoid{
    private $host;
    private RemoteWebDriver $driver;
    private $debug = FALSE;
    private $throwException = FALSE;
    public $screenShotPath = 'screenshots';
    public $setScreenshotName = '';
    private $errors;
    public function __construct($host,RemoteWebDriver $driver = null) {
        $this->host = $host;
        if($driver !== null){
            $this->driver=$driver;
        }
    }
    public function d($val){
        if($this->debug){
            if(is_array($val) || is_object($val)){
                print_r($val);
            }else{
                echo "$val \n";
            }
        }
    }

    public function throwException($name){
        if($this->throwException)
            throw new Exception("$name");
    }
    /**
     * Set Debug modus.
     * 1 = for on
     * else = false
     * Standard = Off
     */
    public function setDebug(bool $bo){
        $this->debug=$bo;
    }
    
    /**
     * Set Exception
     * Standard = Off
     */
    public function setCustomException($text){
        $this->d("I $text custom Exceptions");
        $this->throwException = ($text=='enable')?TRUE:FALSE;
    }
    /**
     * Give a List for phrases with the functions.
     */
    static function returnListOfFunction(){
        $list = array();
        $list['EN']['/^(?:|I )go to "(http*.:\/\/.*)"/'] = "goTo";
        $list['DE']['/^(?:|Ich )gehe auf "(http*.:\/\/.*)"/'] = "goTo";
        $list['EN']['/^(?:|I )want Sourcecode/'] = "wantSourceCode";
        $list['EN']['/^(?:|I )want Function list "(.*)"/'] = "printFunctionList";
        $list['EN']['/^(?:|I )maximize Window/'] = "maximizeWindow";
        $list['DE']['/^(?:|Ich )maximiere das Fenster/'] = "maximizeWindow";
        $list['EN']['/^(?:|I )click on ID "(.*)"/'] = "clickOnId";
        $list['EN']['/^(?:|I )click on Button with text "(.*)"/'] = "clickOnButton";
        $list['EN']['/^(?:|I )will click on checkbox "(.*)"/'] = "checkCheckbox";
        $list['EN']['/^(?:|I )click on Link "(.*)"/'] = "clickOnLink";
        $list['EN']['/^(?:|I )click on class "(.*)" on Attribute "(.*)" when it is "(.*)"/'] = "clickOnClassOnAttributeAndValue";
        $list['EN']['/^(?:|I )wait (.*) Seconds/'] = "wait";
        $list['EN']['/^(?:|I )fill into fields with name "(.*)" the value "(.*)"/'] = "fillIntoFieldsByName";
        $list['EN']['/^(?:|I )fill into field with name "(.*)" the value "(.*)"/'] = "fillIntoFieldByName";
        $list['EN']['/^(?:|I )fill into field with xpath "(.*)" the value "(.*)"/'] = "fillIntoFieldByXPath";
        $list['EN']['/^(?:|I )fill into field with ID "(.*)" the value "(.*)"/'] = "fillIntoFieldById";
        $list['EN']['/^(?:|I )move mouse to Class "(.*)" with text "(.*)"/'] = "moveMouseToClassWithText";
        $list['EN']['/^(?:|I )make screenshot/'] = "makeScreenshot";
        $list['EN']['/^(?:|I )select "(.*)" from "(.*)"/'] = "selectItem";
        $list['EN']['/^(?:|I )find an item "(.*)" with text "(.*)"/'] = "findItemWithText";
        $list['EN']['/^(?:|I )find "(.*)" in Sourcecode/'] = "findInSourcecode";
        $list['EN']['/^(?:|I )"(.*)" custom Exceptions/'] = "setCustomException";
        return $list;
    }
    /**
     * Print the list of available Functions in List
     */
    function printFunctionList($LANG){
        $list = $this->returnListOfFunction();
        $lineBreak = "\n";
        if(php_sapi_name() !== 'cli'){
            $lineBreak = "<br />";
        }
        foreach($list[$LANG] as $key => $itm){
            echo rtrim(str_replace('/^(?:|I )','I ',$key),'/').$lineBreak;
        }
    }
    /** 
     * creates latest Chrome Driver
     */
    function CreateChromDriver(){
        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability("enableVideo",True);
        $this->driver=RemoteWebDriver::create($this->host,$desiredCapabilities);
    }
    /**
     * creates latest Chrome Firefox
     */
    function CreateFirefoxDriver(){
        $desiredCapabilities = DesiredCapabilities::firefox();
        $desiredCapabilities->setCapability("enableVideo",True);
        $this->driver=RemoteWebDriver::create($this->host,$desiredCapabilities);
    }
    /**
     * creates latest Edge Driver
     */
    function CreateEdgeDriver(){
        $desiredCapabilities = DesiredCapabilities::microsoftEdge();
        $desiredCapabilities->setCapability("enableVideo",True);
        $this->driver=RemoteWebDriver::create($this->host,$desiredCapabilities);
    }
    /**
     * This function will open a new webside
     */
    function goTo($site){
        $this->driver->get($site);
        $this->d("visited Side $site");
    }
    /**
     * This function will move the mouse to an Element
     */
    function moveMouseToClassWithText($className,$text){
        try{
            $elements = $this->driver->findElements(WebDriverBy::className($className));
            foreach($elements as $itm){
                if($itm->getText()==$text){
                    $this->driver->getMouse()->mouseMove($itm->getCoordinates());
                    $this->d("I moved mouse over class $className with text \"$text\"");
                    break;                
                }
            }
        }catch(Exception $e){
            $this->d("I couldn't move mouse over class $className with text \"$text\"");
        }
    }
    /**
     * Get the source of the last loaded page.
     *
     * @return string The current page source.
     */
    function wantSourcecode(){
        $this->d("I Want Source Code");
        return $this->driver->getPageSource();
    }
    /**
     * Get a string representing the current URL that the browser is looking at.
     *
     * @return string The current URL.
     */
    function wantCurrentUrl(){
        $this->d("i want URL");
        return $this->driver->getCurrentURL();
    }
    /**
     * This function will maximize the current Browser-Window
     */
    function maximizeWindow(){
        $this->driver->manage()->window()->maximize();
        $this->d("maximzed Window");
    }
    /**
     * Make a Screenshot of current Page
     */
    function makeScreenshot(){
        $now = new \DateTime('now');
        if(substr($this->screenShotPath,-1)!=DIRECTORY_SEPARATOR){
            $this->screenShotPath .= DIRECTORY_SEPARATOR;
        }
        $screenshotFileName = $this->screenShotPath .$this->setScreenshotName.'_'. $now->format('Y-m-d_His').'.png';
        $this->driver->takeScreenshot($screenshotFileName);
        $this->d('tooked Screenshot');

    }
    /**
     * This will fill a value into the first field
     */
    function fillIntoFieldByName($name,$value){
        $element = $this->driver->findElement(WebDriverBy::name($name));
        if($element) {
            $element->sendKeys($value);
        }
        $this->d("i filled into field with name $name the value $value");
    }
    /**
     * This will fill a value into field by id
     */
    function fillIntoFieldById($name,$value){
        $element = $this->driver->findElement(WebDriverBy::id($name));
        if($element) {
            $element->sendKeys($value);
        }
        $this->d("i filled into field with id $name the value $value");
    }
    /**
     * This will fill a value into field by xpath
     */
    function fillIntoFieldByXPath($name,$value){
        $element = $this->driver->findElement(WebDriverBy::xpath($name));
        if($element) {
            $element->sendKeys($value);
        }
        $this->d("i filled into field with xpath $name the value $value");
    }
    /**
     * This will a value into all fields with one name
     */
    function fillIntoFieldsByName($name,$value){
        $this->wait(0,500);
        $elements = $this->driver->findElements(WebDriverBy::name($name));
        foreach($elements as $element){
            $element->sendKeys($value);
        }
        $this->d("i filled into fields with name $name the value $value");
    }
    /**
     * THis will submit a formular
     */
    function submitForm($name){
        $element = $this->driver->findElement(WebDriverBy::tagName("form"));
        $element->submit();
        $this->d("submitted form $name");
    }
    /**
     * This will wait for some Seconds and miliseconds
     * debug = is to hide output if used in internal functions
     */
    function wait($seconds,$miliseconds=0,$debug=true){
        $this->driver->wait($seconds,$miliseconds);
        sleep($seconds);
        if($debug){
            $this->d("I waited $seconds and $miliseconds miliseconds");
        }
    }
    /**
     * Click on a Link with the text
     */
    function clickOnLink($name){
        $elements = $this->driver->findElements(WebDriverBy::tagName("a"));
        foreach($elements as $itm){
            if($itm->getText()==$name){
                $itm->click();
                break;
            }
        }
        $this->d("Clicked on $name");
        // $element->submit();
    }
    /**
     * Click on a class with Attribute and its value is
     */
    function clickOnClassOnAttributeAndValue($name,$attribute,$value){
        try{
            $elements = $this->driver->findElements(WebDriverBy::className($name));
            foreach($elements as $itm){
                if($itm->getAttribute($attribute)==$value){
                    $itm->click();
                    break;                
                }
            }
        }catch(Exception $e){
            //try with JavaScript
            $this->driver->executeScript("document.querySelector('.$name\[$attribute=\"$value\"]').click();");
        }
        $this->d("Clicked on $name");
        // $element->submit();
    }

    /**
     * This will click on a checkbox
     */
    function checkCheckbox($item){
        $itm = $this->driver->findElement(WebDriverBy::name($item));
        if(!$itm->isSelected()){
            $itm->click();
        }
        $this->d("checkBox $item is checked");
    }
    /**
     * This will click on something by it's ID
     */
    function clickOnId($name){
        $this->wait(0,500,$debug = false);

        
        try{
            $element = $this->driver->findElement(WebDriverBy::id($name));
            if($element !== null){
                $element->click();
            }
        }
        catch(Exception $e){
            sleep(3);
            
            $elements = $this->driver->findElements(WebDriverBy::id($name));
            
            foreach($elements as $element){
                $driver = $this->driver;
                $gotClick=false;
                try{
                    $driver->wait(5,500)->until(
                        function () use ($driver, $element,$gotClick) {
                            try {
                                $driver->executeScript('console.log("clicking");');
                                $element->click();
                            } catch (WebDriverException $e) {
                                return false;
                            }
                            $driver->executeScript('console.log("clickable");');
                            $gotClick=true;
                            return true;
                        }
                    );
                }catch(Exception $e){}
                
                //Last try: try it via javascript
                if(!$gotClick){
                    $this->d("Click on $name with Javascript");
                    $this->driver->executeScript("document.getElementById('$name').click();");

                }
            }
        }
        $this->d("Clicked on ID $name");
        // $element->submit();
    }
    /**
     * This will Click on a Button by its button Text
     */
    function clickOnButton($name){
        $elements = $this->driver->findElements(WebDriverBy::tagName("button"));
        foreach($elements as $itm){
            if($itm->getText()==$name){
                $itm->click();
                break;
            }
        }
        $this->d("Clicked on Button $name");
        // $element->submit();
    }
    /**
     * This will select an visible item on a dropdown
     */
    function selectItem($value,$dropdown){
        $selectingContainer = $this->driver->findElement(WebDriverBy::name($dropdown));
        $selection = new WebDriverSelect($selectingContainer);
        $selection->selectByVisibleText($value);
        $this->d("i selected $value from $dropdown");
    }
    /**
     * Only Check if Item with a Text exists
     */
    function findItemWithText($itemName,$searchText){
        $found = false;
        $items = array();
        $elements = $this->driver->findElements(WebDriverBy::tagName($itemName));
        foreach($elements as $itm){
            // $this->d($itm->getText());
            if($itm->getText()==$searchText){
                $found=true;
                $items[]=$itm->getText();
            }
        }

        if($found){
            $count = sizeof($items);
            $this->d("I found $count item/s with $searchText");
        }else{
            $this->d("I didn't found an item $itemName with $searchText");
            $this->throwException('NotFoundException');
        }
    }

    function findInSourcecode($search){
        $code = $this->driver->getPageSource();

        if(str_contains(strtolower($code), strtolower($search)))
            $this->d("The String \"$search\" was found");
        else{
            $this->d("The String \"$search\" was not found");
            $this->throwException('NotFoundException');
        }

    }
    function __destruct()
    {
        if($this->debug==1){
            $this->makeScreenshot();
        }
        $this->driver->quit();
    }
}