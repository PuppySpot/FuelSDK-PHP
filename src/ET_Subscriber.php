<?php
spl_autoload_register( function($class_name) {
    include_once 'src/'.$class_name.'.php';
});

/**
 * A person subscribed to receive email or SMS communication.
 */
class ET_Subscriber extends ET_CUDWithUpsertSupport
{
	/** 
	* Initializes a new instance of the class and sets the obj property of parent.
	*/	
	function __construct()
	{
		$this->obj = "Subscriber";
	}	
}
?>