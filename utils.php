<?php
	function checkEmpty($string){
	        if($string == "" OR empty($string)){
        	    return true;
	        }
        	return false;
    	}
	/**prints out the contents key -> value from $_POST */
	function checkPOST($post){
		foreach ($post as $key => $value) {
			echo "\n";
			echo "---\n";
			echo "KEY: ".$key;
			echo "\n";
			echo "Value: ".$value;
			echo "\n";
			echo "---";
			echo "\n";
		}
	}
