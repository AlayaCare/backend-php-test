<?php

use Silex\Application;

/****************************************************************************/
/*   			  				FLASHBAG Helper 							*/
/* 	main class wrapping flashbag requests from controller 					*/
/*  into "raw" flashbag commands (as an abstraction).						*/
/****************************************************************************/

class FlashBagManager
{
	// #TODO: move these constants elsewhere in a dedicated global area */
	private $FLASHBAG_TEXT_BEFORE_DESCRIPTION = "textBeforeDescription";
	private $FLASHBAG_TEXT_AFTER_DESCRIPTION = "textAfterDescription";
	private $FLASHBAG_TEXT_DESCRIPTION_CONTENT = "description";

	private $ERASE_DESCRIPTION_START_MESSAGE = "previous description: ";
	private $ERASE_DESCRIPTION_END_MESSAGE = " has been erased";
	private $ADD_DESCRIPTION_START_MESSAGE = "new description: ";
	private $ADD_DESCRIPTION_END_MESSAGE = " has been added";
	private $EMPTY_DESCRIPTION_START_MESSAGE = "please fill in description ";
	private $EMPTY_DESCRIPTION_END_MESSAGE = " without any more empty text";

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	private function getFlashBag() 
	{
		return $this->app['session']->getFlashBag();
	}

	public function displayDescriptionWarning($action, $description)
	{
		global $DELETE_DESCRIPTION, $INSERT_DESCRIPTION;
	    switch ($action) {
	    	case $INSERT_DESCRIPTION:
	    		if ($description != '' && (strlen(trim($description)) != 0)) { 
        			$this->getFlashBag()->add($this->FLASHBAG_TEXT_BEFORE_DESCRIPTION, $this->ADD_DESCRIPTION_START_MESSAGE);
			    	$this->getFlashBag()->add($this->FLASHBAG_TEXT_DESCRIPTION_CONTENT, $description);
        			$this->getFlashBag()->add($this->FLASHBAG_TEXT_AFTER_DESCRIPTION, $this->ADD_DESCRIPTION_END_MESSAGE);
			    } else {
        			$this->getFlashBag()->add($this->FLASHBAG_TEXT_BEFORE_DESCRIPTION, $this->EMPTY_DESCRIPTION_START_MESSAGE);
        			$this->getFlashBag()->add($this->FLASHBAG_TEXT_AFTER_DESCRIPTION, $this->EMPTY_DESCRIPTION_END_MESSAGE);
		    	}
		    	break;
		    case $DELETE_DESCRIPTION:
        		$this->getFlashBag()->add($this->FLASHBAG_TEXT_BEFORE_DESCRIPTION, $this->ERASE_DESCRIPTION_START_MESSAGE);
			    $this->getFlashBag()->add($this->FLASHBAG_TEXT_DESCRIPTION_CONTENT, $description);
        		$this->getFlashBag()->add($this->FLASHBAG_TEXT_AFTER_DESCRIPTION, $this->ERASE_DESCRIPTION_END_MESSAGE);
        		break;
        	default:
        		break;
        }
	}

}