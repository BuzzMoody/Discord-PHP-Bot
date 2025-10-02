<?php

	function Reload($message) { 
	
		if (isAdmin($message->author->id) && getenv('BETA') !== 'true') {
			die();
		}
		
	}
	
?>