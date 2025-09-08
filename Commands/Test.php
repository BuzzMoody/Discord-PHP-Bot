<?php

	function Test($message) {
		
		global $discord;
	
		if (isAdmin($message->author->id) && getenv('BETA') === 'true') {
			
			// test code here

			return;			

		}
		
	}
	
?>